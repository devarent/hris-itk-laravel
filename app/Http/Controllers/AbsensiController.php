<?php
namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Services\AiRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Absensi::with('karyawan');
        if ($user->isKaryawan()) {
            $query->where('karyawan_id', $user->karyawan_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('karyawan_id') && !$user->isKaryawan()) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensi = $query->latest()->paginate(30)->withQueryString();
        $karyawanList = Karyawan::where('aktif', true)->get();

        $todayQuery = Absensi::whereDate('tanggal', today());
        if ($user->isKaryawan()) {
            $todayQuery->where('karyawan_id', $user->karyawan_id);
        }
        $todayRecords = $todayQuery->get();

        $stats = [
            'hadir' => $todayRecords->whereIn('status', ['hadir', 'terlambat'])->count(),
            'terlambat' => $todayRecords->where('status', 'terlambat')->count(),
            'izin_sakit' => $todayRecords->whereIn('status', ['izin', 'sakit', 'cuti'])->count(),
            'total_riwayat' => $absensi->total(),
        ];

        $absensiHariIni = $user->isKaryawan() && $user->karyawan_id
            ? Absensi::where('karyawan_id', $user->karyawan_id)->whereDate('tanggal', today())->first()
            : null;

        return view('absensi.index', compact('absensi', 'karyawanList', 'stats', 'absensiHariIni'));
    }

    public function create()
    {
        $karyawan = Karyawan::where('aktif', true)->get();
        return view('absensi.create', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/',
            'jam_pulang' => 'nullable|regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/',
            'status' => 'required|in:hadir,terlambat,izin,sakit,cuti,dinas_luar,alfa',
            'keterangan' => 'nullable|string|max:500',
        ]);

        Absensi::updateOrCreate(
            ['karyawan_id' => $validated['karyawan_id'], 'tanggal' => $validated['tanggal']],
            $validated
        );

        return redirect()->route('absensi.index')->with('success', 'Absensi berhasil dicatat.');
    }

    public function edit(Absensi $absensi)
    {
        $karyawan = Karyawan::where('aktif', true)->get();
        return view('absensi.edit', compact('absensi', 'karyawan'));
    }

    public function update(Request $request, Absensi $absensi)
    {
        $validated = $request->validate([
            'jam_masuk' => 'nullable|regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/',
            'jam_pulang' => 'nullable|regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/',
            'status' => 'required|in:hadir,terlambat,izin,sakit,cuti,dinas_luar,alfa',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $validated['verified_by'] = Auth::id();
        $absensi->update($validated);

        return redirect()->route('absensi.index')->with('success', 'Absensi berhasil diperbarui.');
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();
        return redirect()->route('absensi.index')->with('success', 'Absensi berhasil dihapus.');
    }

    public function absenHarian(Request $request)
    {
        $karyawan = Auth::user()->karyawan;
        if (!$karyawan) return back()->with('error', 'Akun Anda tidak terhubung ke data karyawan.');

        $request->validate([
            'status' => 'nullable|in:hadir,terlambat,izin,sakit',
            'aksi' => 'nullable|in:masuk,pulang',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $existing = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        if ($request->input('aksi') === 'pulang' || ($existing && $existing->jam_masuk && !$existing->jam_pulang && !$request->filled('status'))) {
            if (!$existing) {
                return back()->with('error', 'Anda belum melakukan check-in hari ini.');
            }
            if ($existing->jam_pulang) {
                return back()->with('error', 'Anda sudah melakukan check-out hari ini.');
            }

            $existing->update([
                'jam_pulang' => now()->format('H:i:s'),
            ]);

            return back()->with('success', 'Check-out (absen pulang) hari ini berhasil dicatat.');
        }

        if ($existing && $existing->jam_masuk) {
            return back()->with('error', 'Anda sudah melakukan check-in hari ini.');
        }

        $status = $request->input('status', 'hadir');
        if ($status === 'hadir' && now()->format('H:i') > '08:00') {
            $status = 'terlambat';
        }

        Absensi::updateOrCreate(
            ['karyawan_id' => $karyawan->id, 'tanggal' => today()],
            [
                'jam_masuk' => now()->format('H:i:s'),
                'status' => $status,
                'keterangan' => $request->keterangan,
            ]
        );

        return back()->with('success', 'Check-in absensi hari ini berhasil dicatat.');
    }

    public function rekap(Request $request)
    {
        $bulan = $request->bulan ?? now()->format('m');
        $tahun = $request->tahun ?? now()->format('Y');

        // Satu query agregat untuk seluruh karyawan (menghindari N+1 query).
        $ringkasan = Absensi::query()
            ->selectRaw(
                "karyawan_id,
                 SUM(status IN ('hadir','terlambat')) AS hadir,
                 SUM(status = 'terlambat') AS terlambat,
                 SUM(status = 'izin') AS izin,
                 SUM(status = 'sakit') AS sakit,
                 SUM(status = 'cuti') AS cuti,
                 SUM(status = 'alfa') AS alfa,
                 COUNT(*) AS total"
            )
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->groupBy('karyawan_id')
            ->get()
            ->keyBy('karyawan_id');

        $rekap = Karyawan::where('aktif', true)->get()
            ->map(function ($k) use ($ringkasan) {
                $a = $ringkasan->get($k->id);
                return [
                    'karyawan' => $k,
                    'hadir' => (int) ($a->hadir ?? 0),
                    'terlambat' => (int) ($a->terlambat ?? 0),
                    'izin' => (int) ($a->izin ?? 0),
                    'sakit' => (int) ($a->sakit ?? 0),
                    'cuti' => (int) ($a->cuti ?? 0),
                    'alfa' => (int) ($a->alfa ?? 0),
                    'total' => (int) ($a->total ?? 0),
                ];
            })
            ->all();

        return view('absensi.rekap', compact('rekap', 'bulan', 'tahun'));
    }

    public function aiAssistant(Request $request, AiRouterService $aiRouter)
    {
        $result = null;
        $prompt = $request->input('prompt', '');
        $tier = $request->input('tier', 'auto');

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'prompt' => 'required|string|max:2000',
                'tier' => 'required|in:auto,hydrogen,neon,carbon,glm',
            ]);

            $todayStats = Absensi::whereDate('tanggal', today())
                ->selectRaw("
                    SUM(status = 'hadir') AS hadir,
                    SUM(status = 'terlambat') AS terlambat,
                    SUM(status IN ('izin','sakit','cuti')) AS izin_sakit,
                    SUM(status = 'alfa') AS alfa,
                    COUNT(*) AS total
                ")
                ->first();

            $contextSummary = sprintf(
                "Tanggal: %s | Hadir: %d | Terlambat: %d | Izin/Sakit/Cuti: %d | Alfa: %d | Total Tercatat: %d",
                today()->format('Y-m-d'),
                (int) ($todayStats->hadir ?? 0),
                (int) ($todayStats->terlambat ?? 0),
                (int) ($todayStats->izin_sakit ?? 0),
                (int) ($todayStats->alfa ?? 0),
                (int) ($todayStats->total ?? 0)
            );

            $forceTier = $validated['tier'] === 'auto' ? null : $validated['tier'];
            $result = $aiRouter->ask($validated['prompt'], $contextSummary, $forceTier);
        }

        return view('absensi.ai-assistant', compact('result', 'prompt', 'tier'));
    }
}

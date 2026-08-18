<!DOCTYPE html>
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head><meta charset="UTF-8"><title>Rekap Absensi</title>
<style>
    body { font-family: 'Times New Roman', serif; font-size: 12pt; }
    h2 { text-align: center; font-size: 16pt; margin-bottom: 4pt; }
    .periode { text-align: center; font-size: 11pt; margin-bottom: 16pt; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 4pt 6pt; text-align: center; font-size: 10pt; }
    th { background: #1a237e; color: #fff; font-weight: bold; }
</style>
</head>
<body>
    <h2>REKAPITULASI ABSENSI KARYAWAN</h2>
    <div class="periode">Periode: {{ $periode }}</div>
    <table>
        <thead><tr><th>No</th><th>NIP</th><th>Nama</th><th>Unit</th><th>Hadir</th><th>Terlambat</th><th>Izin</th><th>Sakit</th><th>Cuti</th><th>D.Luar</th><th>Alfa</th></tr></thead>
        <tbody>
            @foreach($rekap as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r['nip'] }}</td>
                <td style="text-align:left">{{ $r['nama'] }}</td>
                <td>{{ $r['unit'] }}</td>
                <td>{{ $r['hadir'] }}</td>
                <td>{{ $r['terlambat'] }}</td>
                <td>{{ $r['izin'] }}</td>
                <td>{{ $r['sakit'] }}</td>
                <td>{{ $r['cuti'] }}</td>
                <td>{{ $r['dinas_luar'] }}</td>
                <td>{{ $r['alfa'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <!-- Section Tanda Tangan Halaman Terakhir -->
    <table style="width: 100%; border: none; margin-top: 30px; font-family: 'Courier New', Courier, monospace; font-size: 10pt; line-height: 1.0; page-break-inside: avoid;">
        <tr>
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 10pt; line-height: 1.0;">
                <p style="margin: 0; padding: 0;">Balikpapan, {{ now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 0; padding: 0; font-weight: bold;">Mengetahui / Menyetujui,</p>
                <p style="margin: 0; padding: 0;">Kepala HRD / Manager</p>
                <div style="height: 60px;"></div>
                <p style="margin: 0; padding: 0; font-weight: bold;"><u>( .................... )</u></p>
                <p style="margin: 0; padding: 0;">NIP. ..................</p>
            </td>
        </tr>
    </table>
</body>
</html>
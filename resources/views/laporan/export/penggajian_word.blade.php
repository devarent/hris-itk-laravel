<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Laporan Penggajian</title>
<style>
    body { font-family: 'Calibri', Arial, sans-serif; font-size: 11pt; color: #333; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 3px double #1a237e; padding-bottom: 10px; }
    .header h2 { margin: 0 0 4px; color: #1a237e; font-size: 18pt; }
    .header p { margin: 0; color: #666; font-size: 10pt; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #1a237e; color: #fff; padding: 8px 10px; text-align: left; font-size: 10pt; }
    td { padding: 6px 10px; border-bottom: 1px solid #ddd; }
    tr:nth-child(even) td { background: #f4f4f4; }
    .total-row td { font-weight: bold; background: #e8eaf6 !important; border-top: 2px solid #1a237e; }
    .text-right { text-align: right; }
    .footer { text-align: center; font-size: 9pt; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 6px; }
</style></head>
<body>
    <div class="header">
        <h2>LAPORAN PENGGAJIAN</h2>
        <p>HRIS - Human Resource Information System</p>
        <p><small>Dicetak: {{ now()->format('d/m/Y H:i') }}</small></p>
    </div>
    <table>
        <thead><tr><th>No</th><th>Nama Karyawan</th><th>NIP</th><th>Periode</th><th>Gaji Pokok</th><th>Total Gaji</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($penggajian as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->karyawan->nama_lengkap ?? '-' }}</td>
                <td>{{ $p->karyawan->nip ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($p->periode.'-01')->format('F Y') }}</td>
                <td class="text-right">Rp {{ number_format($p->gaji_pokok,0,',','.') }}</td>
                <td class="text-right">Rp {{ number_format($p->total_diterima,0,',','.') }}</td>
                <td>{{ ucfirst($p->status) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-right">Rp {{ number_format($totalKeseluruhan,0,',','.') }}</td>
                <td></td>
            </tr>
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

    <div class="footer">HRIS - Laporan Penggajian &mdash; {{ now()->format('d/m/Y') }}</div>
</body>
</html>

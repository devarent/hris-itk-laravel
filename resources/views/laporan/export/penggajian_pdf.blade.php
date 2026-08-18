<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penggajian</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; }
        .kop { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px double #1a237e; }
        .kop h2 { margin: 0 0 4px; color: #1a237e; font-size: 18px; }
        .kop p { margin: 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1a237e; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 6px 10px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .total-row td { font-weight: bold; background: #e8eaf6 !important; border-top: 2px solid #1a237e; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 6px; }
        .text-right { text-align: right; }
        
        /* Tanda Tangan Section - Courier New 10pt, 1 Spasi */
        .ttd-container {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            line-height: 1.0;
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 20px;
        }
        .ttd-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            line-height: 1.0;
        }
        .ttd-space { height: 60px; }
    </style>
</head>
<body>
    <div class="kop">
        <h2>LAPORAN PENGGAJIAN</h2>
        <p>HRIS - Human Resource Information System</p>
        <p><small>Dicetak: {{ now()->format('d/m/Y H:i') }}</small></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th><th>Nama Karyawan</th><th>NIP</th><th>Periode</th><th>Gaji Pokok</th><th>Total Gaji</th><th>Status</th>
            </tr>
        </thead>
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
    <div class="ttd-container">
        <table class="ttd-table">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: center;">
                    <p style="margin: 0; padding: 0;">Balikpapan, {{ now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0; padding: 0; font-weight: bold;">Mengetahui / Menyetujui,</p>
                    <p style="margin: 0; padding: 0;">Kepala HRD / Manager</p>
                    <div class="ttd-space"></div>
                    <p style="margin: 0; padding: 0; font-weight: bold;"><u>( .................... )</u></p>
                    <p style="margin: 0; padding: 0;">NIP. ..................</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">HRIS - Laporan Penggajian &mdash; {{ now()->format('d/m/Y') }}</div>
</body>
</html>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Data Karyawan</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #333; padding: 5px 8px; text-align: center; }
    th { background: #1a237e; color: #fff; font-size: 10px; }
    td { font-size: 10px; }
    h2 { text-align: center; margin-bottom: 4px; }
</style>
</head>
<body>
    <h2>DATA KARYAWAN</h2>
    <table>
        <thead><tr><th>No</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Unit</th><th>Tgl Masuk</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($karyawan as $i => $k)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $k->nip }}</td>
                <td style="text-align:left">{{ $k->nama_lengkap }}</td>
                <td>{{ $k->jabatan?->nama_jabatan }}</td>
                <td>{{ $k->satuanKerja?->singkatan }}</td>
                <td>{{ $k->tanggal_masuk?->format('d-m-Y') }}</td>
                <td>{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</td>
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
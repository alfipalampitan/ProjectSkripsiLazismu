<!DOCTYPE html>
<html>

<head>
    <title>Laporan Donasi {{ $bulan }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .total {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN DONASI MASUK</h2>
        <!-- Tambahkan keterangan Type di sini -->
        <p>Periode: {{ $bulan }} ({{ strtoupper($type) }})</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Donatur</th>
                <th>Kategori</th>
                <!-- Tambahkan kolom Type kalau mau lebih jelas -->
                <th>Metode</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donasi as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                    <td>{{ $item->user_name }}</td>
                    <td>{{ $item->kategori }}</td>
                    <td>{{ strtoupper($item->type) }}</td>
                    <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Dana Masuk ({{ $type }}): Rp {{ number_format($total, 0, ',', '.') }}
    </div>
</body>

</html>
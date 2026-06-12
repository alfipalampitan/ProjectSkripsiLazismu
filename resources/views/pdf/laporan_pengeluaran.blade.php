<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pengeluaran {{ $bulan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        /* Style Kop Surat */
        .kop-surat {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 2px;
        }

        .kop-surat td {
            border: none;
            padding: 0;
        }

        .logo-box {
            width: 70px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            border: 1px solid #000;
            position: relative;
            margin: 0 auto;
        }

        .logo-placeholder:before,
        .logo-placeholder:after {
            content: "";
            position: absolute;
            width: 100%;
            height: 1px;
            background-color: #000;
            top: 50%;
            left: 0;
        }

        .logo-placeholder:before {
            transform: rotate(45deg);
        }

        .logo-placeholder:after {
            transform: rotate(-45deg);
        }

        .text-kop {
            text-align: center;
            padding-left: 10px;
        }

        .text-kop h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }

        .text-kop h3 {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .text-kop h4 {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 6px 0;
            letter-spacing: 0.5px;
        }

        .text-kop p {
            font-size: 10px;
            margin: 0;
            color: #111;
        }

        .line-double {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin-bottom: 20px;
        }

        /* Style Tabel Data */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }

        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .total {
            margin-top: 15px;
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }

        /* Style Tanda Tangan */
        .ttd-container {
            width: 100%;
            margin-top: 40px;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .ttd-table td {
            border: none;
            padding: 0;
        }

        .ttd-space {
            height: 70px;
        }
    </style>
</head>

<body>

    <table class="kop-surat">
        <tr>
            <td class="logo-box">
                <img src="{{ public_path('images/Lazismu.png') }}" width="65" height="65">
            </td>
            <td class="text-kop">
                <h2>LAPORAN TRANSAKSI PENGELUARAN</h2>
                <h3>
                    @if($sifat_pengeluaran === 'all')
                        KESELURUHAN
                    @elseif($sifat_pengeluaran === 'Terikat')
                        DANA TERIKAT
                    @elseif($sifat_pengeluaran === 'Tidak_Terikat' || $sifat_pengeluaran === 'Tidak Terikat')
                        DANA TIDAK TERIKAT
                    @else
                        {{ strtoupper($sifat_pengeluaran) }}
                    @endif
                </h3>
                <h4>LAZISMU KOTA BANJARMASIN</h4>
                <p>Jl. Pd. Metro Indah, Alalak Utara, Kec. Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70123
                </p>
            </td>
        </tr>
    </table>

    <div class="line-double"></div>

    <div style="margin-bottom: 10px; font-weight: bold;">
        Periode Laporan: {{ $bulan }} (Sifat Dana: {{ strtoupper($sifat_pengeluaran) }})
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 25%;">Penerima / Keperluan</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%;">Nominal Keluar</th>
                <th style="width: 15%;">Sifat Dana</th>
                <th style="width: 25%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengeluaran as $key => $item)
                @php
                    // --- LOGIKA 1: MENENTUKAN PENERIMA ---
                    // Diambil dari nama pemohon asli. Jika kosong, pakai judul pengeluaran / default
                    $penerima = $item->applicant ? $item->applicant->nama_pemohon : ($item->judul_pengeluaran ?: 'Pengeluaran Lazismu');

                    // --- LOGIKA 2: MENENTUKAN KEPERLUAN ---
                    // Jika applicant punya kaitan ke pilarForm, ambil nama_penyaluran-nya. Jika tidak ada, beri tanda strip (-)
                    if ($item->applicant && $item->applicant->pilarForm && $item->applicant->pilarForm->nama_penyaluran) {
                        $keperluan = $item->applicant->pilarForm->nama_penyaluran;
                    } else {
                        $keperluan = '-';
                    }
                @endphp

                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>

                    <td>
                        <strong>{{ $penerima }}</strong>
                        @if($keperluan !== '-')
                            <br><span style="color: #555; font-size: 10px; font-style: italic;">Program: {{ $keperluan }}</span>
                        @endif
                    </td>

                    <td class="text-center">{{ $item->created_at->format('d/m/Y') }}</td>
                    <td class="text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->sifat_pengeluaran ?: '-' }}</td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @endforeach
            @if($pengeluaran->isEmpty())
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; font-style: italic; color: #888;">
                        Tidak ada data transaksi pengeluaran pada periode ini.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="total">
        Total Dana Keluar: Rp {{ number_format($total, 0, ',', '.') }}
    </div>

    <div class="ttd-container">
        <table class="ttd-table">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: left; font-size: 11px;">
                    Banjarmasin, {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}<br>
                    <strong>Mengetahui Pimpinan,</strong>
                    <div class="ttd-space"></div>
                    <span>Ahmad Fitri Rusli, S.Ag</span>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
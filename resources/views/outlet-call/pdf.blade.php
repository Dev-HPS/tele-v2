<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Outlet Call</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding: 8px 0;
            border-bottom: 1px solid #333;
        }

        .header h1 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .header p {
            margin: 2px 0;
            font-size: 9px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8px;
        }

        th,
        td {
            border: 0.5px solid #333;
            padding: 3px 2px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            line-height: 1.1;
        }

        td {
            font-size: 7px;
            line-height: 1.2;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        .badge {
            display: inline-block;
            padding: 1px 3px;
            background-color: #007bff;
            color: white;
            border-radius: 2px;
            font-size: 6px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .filter-info {
            margin-top: 8px;
            font-size: 7px;
            color: #666;
            line-height: 1.1;
        }

        @page {
            margin: 10mm;
        }

        .page-break {
            page-break-before: always;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN DATA OUTLET CALL</h1>
        {{-- <p>Total Data: {{ count($data) }} outlet</p> --}}
        {{-- @if (isset($filters))
            <div class="filter-info">
                <strong>Filter Diterapkan:</strong><br>
                SBU: {{ $filters['SBU'] }} | TP: {{ $filters['TP'] }}<br>
                Kabupaten: {{ $filters['Kabupaten'] }} | Kecamatan: {{ $filters['Kecamatan'] }}<br>
                Hari: {{ $filters['Hari'] }}
            </div>
        @endif --}}
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="14%">TP</th>
                <th width="20%">Nama Toko</th>
                <th width="16%">Nama PKP</th>
                <th width="12%">No Telepon</th>
                <th width="7%">Hari</th>
                <th width="27%">Alamat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-size: 7px;">{{ $item->tp_name ?? $item->tp }}</td>
                    <td style="font-size: 7px; font-weight: bold;">{{ $item->outlet_name }}</td>
                    <td style="font-size: 7px;">{{ $item->outlet_owner }}</td>
                    <td style="font-size: 7px;">{{ $item->outlet_phone }}</td>
                    <td class="text-center">
                        <span>
                            {{ $item->day }}
                        </span>
                    </td>
                    <td style="font-size: 7px;">{{ $item->outlet_address }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; font-style: italic; font-size: 8px;">
                        Tidak ada data yang ditemukan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>

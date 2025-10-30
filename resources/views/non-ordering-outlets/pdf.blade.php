<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Non Ordering Outlets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .filter-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .filter-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
        }

        .filter-item {
            margin-bottom: 5px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
        }

        .description {
            max-width: 150px;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN NON ORDERING OUTLETS</h1>
    </div>

    {{-- @if (!empty($filterInfo))
        <div class="filter-info">
            <h3>Filter Yang Diterapkan:</h3>
            @if (isset($filterInfo['date_filter']))
                <div class="filter-item"><strong>Filter Tanggal:</strong> {{ $filterInfo['date_filter'] }}</div>
            @endif
            @if (isset($filterInfo['date_range']))
                <div class="filter-item"><strong>Range Tanggal:</strong> {{ $filterInfo['date_range'] }}</div>
            @endif
            @if (isset($filterInfo['category']))
                <div class="filter-item"><strong>Kategori:</strong> {{ $filterInfo['category'] }}</div>
            @endif
        </div>
    @endif --}}

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="16%">Kode Outlet</th>
                <th width="10%">Nama Outlet</th>
                <th width="11%">Kecamatan</th>
                <th width="11%">Kabupaten</th>
                <th width="11%">Karesidenan</th>
                <th width="8%">Kategori</th>
                <th width="10%">Deskripsi</th>
                <th width="8%">Dibuat Oleh</th>
                <th width="10%">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @if ($data->count() > 0)
                @foreach ($data as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->outlet_code }}</td>
                        <td>{{ $item->outlet_name ?? $item->outlet_code }}</td>
                        <td>{{ $item->district_name ?? '-' }}</td>
                        <td>{{ $item->city_name ?? '-' }}</td>
                        <td>{{ $item->residency_name ?? '-' }}</td>
                        <td>{{ $item->category_name ?? '-' }}</td>
                        <td class="description">{{ $item->description }}</td>
                        <td>{{ $item->created_by_name ?? '-' }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="no-data">Tidak ada data yang ditemukan</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>

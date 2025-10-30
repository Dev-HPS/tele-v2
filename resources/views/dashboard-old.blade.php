@extends('layouts.main')

@push('style')
    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Service Require</h4>
                </div>
                <div class="card-body">
                    <div id="sr_chart" data-colors='["#2ab57d", "#5156be", "#fd625e", "#4ba6ef", "#ffbf53"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div><!--end card-->
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Non Ordering Outlets</h4>
                        <div class="text-end">
                            <h5 class="mb-1">{{ $nonOrderingData['totalThisMonth'] }}</h5>
                            <p class="text-muted mb-0">
                                {{ $nonOrderingData['monthName'] }}
                                {{-- @if ($nonOrderingData['percentageChange'] > 0)
                                    <span class="badge bg-success">+{{ $nonOrderingData['percentageChange'] }}%</span>
                                @elseif($nonOrderingData['percentageChange'] < 0)
                                    <span class="badge bg-danger">{{ $nonOrderingData['percentageChange'] }}%</span>
                                @else
                                    <span class="badge bg-secondary">{{ $nonOrderingData['percentageChange'] }}%</span>
                                @endif --}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (empty($nonOrderingData['series']))
                        <div class="text-center py-5">
                            <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Tidak ada data non-ordering outlets bulan ini</p>
                        </div>
                    @else
                        <div id="non_ordering_chart" data-colors='["#fd625e", "#ffbf53", "#2ab57d", "#5156be", "#4ba6ef"]'
                            class="apex-charts" dir="ltr"></div>
                    @endif
                </div>
            </div><!--end card-->
        </div>

        {{-- <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Inquiry</h4>
                </div>
                <div class="card-body">
                    <div id="inq_chart" data-colors='["#2ab57d", "#5156be", "#fd625e", "#4ba6ef", "#ffbf53"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div><!--end card-->
        </div> --}}

        {{-- <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Complaint</h4>
                </div>
                <div class="card-body">
                    <div id="cp_chart" data-colors='["#2ab57d", "#5156be", "#fd625e", "#4ba6ef", "#ffbf53"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div><!--end card-->
        </div> --}}

        @if (in_array(auth()->user()->role_id, [
                '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed',
                '346d417a-544d-48f3-bb4d-1da4ce54dffc',
                'a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b',
            ]))
            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Filter Dashboard TP</h4>
                    </div>
                    <div class="card-body">
                        <form class="d-flex flex-wrap align-items-center justify-content-start mb-3" method="GET"
                            action="{{ route('dashboard') }}">
                            <div class="p-2">
                                <label class="form-label d-block">SBU</label>
                                <select class="form-control form-select" name="sbu" id="sbu">
                                    <option value="">Pilih SBU</option>
                                    @foreach ($sbuList as $item)
                                        <option value="{{ $item['sbu_code'] }}"
                                            {{ $selectedSbu == $item['sbu_code'] ? 'selected' : '' }}>
                                            {{ $item['sbu_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="p-2">
                                <label class="form-label d-block">TP</label>
                                <select class="form-control form-select" name="tp" id="tp">
                                    <option value="">Pilih TP</option>
                                    @foreach ($tpList as $item)
                                        <option value="{{ $item->tp }}"
                                            {{ $selectedTp == $item->tp ? 'selected' : '' }}>
                                            {{ $item->tp_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="p-2">
                                <label class="form-label">Kategori</label>
                                <select class="form-control form-select" name="category" id="category">
                                    @foreach ($category as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $selectedCategory == $item->id ? 'selected' : '' }}>{{ $item->kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="p-2">
                                <label class="form-label">Sort By</label>
                                <select class="form-control form-select" name="sort" id="sort">
                                    <option value="1" {{ $selectedSort == 1 ? 'selected' : '' }}>Qty</option>
                                    <option value="2" {{ $selectedSort == 2 ? 'selected' : '' }}>Value</option>
                                </select>
                            </div>
                            {{-- <div class="p-2">
                            <button class="btn btn-primary mt-4" type="submit">Filter</button>
                        </div> --}}
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array(auth()->user()->role_id, [
                '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed',
                '346d417a-544d-48f3-bb4d-1da4ce54dffc',
                'a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b',
            ]))
            @if ($selectedSbu && $selectedTp && !empty($tpData))
                <div class="col-md-12 mt-4" id="tp-dashboard-container">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Data Dashboard TP - {{ $tpData[0]->tp_name ?? $selectedTp }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive mb-4">
                                <table class="table align-middle table-nowrap table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">TP</th>
                                            <th scope="col">Nama TP</th>
                                            <th scope="col">Target</th>
                                            <th scope="col">Omset</th>
                                            <th scope="col">Sisa Piutang</th>
                                            <th scope="col">Jumlah Outlet</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tpData as $item)
                                            <tr>
                                                <td><strong>{{ $item->tp_code }}</strong></td>
                                                <td><strong>{{ $item->tp_name }}</strong></td>
                                                <td><span
                                                        class="badge bg-primary fs-6">{{ number_format($item->target ?? 0, 0, ',', '.') }}</span>
                                                </td>
                                                <td><span
                                                        class="badge bg-success fs-6">{{ number_format($item->omset ?? 0, 0, ',', '.') }}</span>
                                                </td>
                                                <td><span
                                                        class="badge bg-warning fs-6">{{ number_format($item->sld_piutang ?? 0, 0, ',', '.') }}</span>
                                                </td>
                                                <td><span class="badge bg-info fs-6">{{ $item->outlet_count }}
                                                        Outlet</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-12 mt-4" id="tp-dashboard-container" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0" id="tp-title">Data Dashboard TP</h4>
                        </div>
                        <div class="card-body">
                            <div id="loading-tp" class="text-center" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p>Memuat data...</p>
                            </div>
                            <div class="table-responsive mb-4" id="tp-table-container">
                                <table class="table align-middle table-nowrap table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">TP</th>
                                            <th scope="col">Nama TP</th>
                                            <th scope="col">Target</th>
                                            <th scope="col">Omset</th>
                                            <th scope="col">Sisa Piutang</th>
                                            <th scope="col">Jumlah Outlet</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tp-table-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif


        <div class="col-md-12 mt-4">
            <div class="table-responsive mb-4">
                <table class="table align-middle datatable dt-responsive table-check nowrap"
                    style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="dt-tp-call">
                    <thead>
                        <tr>
                            <th colspan="4" class="text-center">Jadwal Call
                                {{ \Carbon\Carbon::now()->translatedFormat('l') }}</th>
                        </tr>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">TP</th>
                            <th scope="col">Call</th>
                            <th style="width: 80px; min-width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <!-- end table -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-city-call" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal title</h5>
                </div>
                <div class="modal-body">
                    <table class="table align-middle datatable dt-responsive table-check nowrap"
                        style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="dt-city-call">
                        <thead>
                            <tr>
                                <th colspan="4" class="text-center">Jadwal Kirim
                                    {{ \Carbon\Carbon::now()->translatedFormat('l') }}</th>
                            </tr>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Kabupaten / Kota</th>
                                <th scope="col">Call</th>
                                <th style="width: 80px; min-width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Required datatable js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#sbu').select2({
                placeholder: 'Pilih SBU',
                width: '150px'
            });

            $('#tp').select2({
                placeholder: 'Pilih TP',
                width: '150px'
            });

            $('#category').select2({
                placeholder: 'Pilih Kategori',
                width: '100%'
            });

            $('#sort').select2({
                placeholder: 'Pilih Sort',
                width: '100%'
            });

            // Handle SBU change
            $('#sbu').on('change', function() {
                let sbuCode = $(this).val();

                // Clear TP select
                $('#tp').empty().append('<option value="">Pilih TP</option>');

                // Hide TP dashboard container
                $('#tp-dashboard-container').hide();

                if (sbuCode) {
                    $.ajax({
                        url: '{{ route('get-tp-by-sbu', '') }}/' + sbuCode,
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                $.each(response.data, function(index, tp) {
                                    $('#tp').append('<option value="' + tp.tp + '">' +
                                        tp.tp_name + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching TP data:', error);
                        }
                    });
                }
            });

            // Handle TP change - Load data automatically
            $('#tp').on('change', function() {
                let tpCode = $(this).val();
                let sbuCode = $('#sbu').val();
                let category = $('#category').val() || 1;
                let sort = $('#sort').val() || 1;

                if (tpCode && sbuCode) {
                    loadTpDashboardData(sbuCode, tpCode, category, sort);
                } else {
                    $('#tp-dashboard-container').hide();
                }
            });

            // Handle category and sort change
            $('#category, #sort').on('change', function() {
                let tpCode = $('#tp').val();
                let sbuCode = $('#sbu').val();
                let category = $('#category').val() || 1;
                let sort = $('#sort').val() || 1;

                if (tpCode && sbuCode) {
                    loadTpDashboardData(sbuCode, tpCode, category, sort);
                }
            });

            function loadTpDashboardData(sbuCode, tpCode, category, sort) {
                $('#tp-dashboard-container').show();
                $('#loading-tp').show();
                $('#tp-table-container').hide();

                $.ajax({
                    url: '{{ route('get-tp-dashboard-data') }}',
                    type: 'POST',
                    data: {
                        sbu: sbuCode,
                        tp: tpCode,
                        category: category,
                        sort: sort
                    },
                    success: function(response) {
                        $('#loading-tp').hide();

                        if (response.success && response.data.length > 0) {
                            let item = response.data[0];

                            // Update title
                            $('#tp-title').text('Data Dashboard TP - ' + item.tp_name);

                            // Update table body
                            let tableHtml = `
                                <tr>
                                    <td><strong>${item.tp_code}</strong></td>
                                    <td><strong>${item.tp_name}</strong></td>
                                    <td><span class="badge bg-primary fs-6">${formatNumber(item.target)}</span></td>
                                    <td><span class="badge bg-success fs-6">${formatNumber(item.omset)}</span></td>
                                    <td><span class="badge bg-warning fs-6">${formatNumber(item.sld_piutang)}</span></td>
                                    <td><span class="badge bg-info fs-6">${item.outlet_count} Outlet</span></td>
                                </tr>
                            `;

                            $('#tp-table-body').html(tableHtml);
                            $('#tp-table-container').show();
                        } else {
                            $('#tp-table-body').html(
                                '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>');
                            $('#tp-table-container').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading-tp').hide();
                        $('#tp-table-body').html(
                            '<tr><td colspan="6" class="text-center text-danger">Error memuat data</td></tr>'
                        );
                        $('#tp-table-container').show();
                        console.error('Error loading TP dashboard data:', error);
                    }
                });
            }

            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num || 0);
            }
        });

        function showModal(identifier) {
            let tp = identifier.getAttribute('data-tp');

            $('#dt-city-call').DataTable().clear().destroy();

            // Inisialisasi ulang DataTable dengan parameter tp
            $('#dt-city-call').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('dt.city-call') }}",
                    data: {
                        tp: tp
                    }
                },
                columns: [{
                        data: 'DT_RowIndex'
                    },
                    {
                        data: 'city_name'
                    },
                    {
                        data: 'call'
                    },
                    {
                        data: 'action'
                    }
                ],
                columnDefs: [{
                        searchable: false,
                        targets: [0]
                    },
                    {
                        orderable: false,
                        targets: [0]
                    }
                ],
                order: [
                    [1, 'asc']
                ]
            });


            $('#modal-city-call').modal('show')
        }

        function closeModal() {
            $('#modal-city-call').modal('hide')
        }
    </script>

    </script>

    <script>
        $(document).ready(function() {
            $('#dt-tp-call').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.tp-call') }}",
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'tp_name'
                    },
                    {
                        "data": 'call'
                    },
                    {
                        "data": 'action'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0]
                    },
                    {
                        "orderable": false,
                        "targets": [0]
                    }
                ],
                "order": [
                    [1, 'asc']
                ]
            });
        });
    </script>

    <!-- apexcharts js -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- apexcharts init -->
    <script>
        function getChartColorsArray(e) {
            e = $(e).attr("data-colors");
            return (e = JSON.parse(e)).map(function(e) {
                e = e.replace(" ", "");
                if (-1 == e.indexOf("--")) return e;
                e = getComputedStyle(document.documentElement).getPropertyValue(e);
                return e || void 0
            })
        }

        let seriesSR = @json($sr)

        var srColors = getChartColorsArray("#sr_chart"),
            optionsSR = {
                chart: {
                    height: 320,
                    type: "pie"
                },
                series: seriesSR,
                labels: @json($status),
                colors: srColors,
                legend: {
                    show: !0,
                    position: "bottom",
                    horizontalAlign: "center",
                    verticalAlign: "middle",
                    floating: !1,
                    fontSize: "14px",
                    offsetX: 0
                },
                responsive: [{
                    breakpoint: 600,
                    options: {
                        chart: {
                            height: 240
                        },
                        legend: {
                            show: !1
                        }
                    }
                }]
            };
        (chartSR = new ApexCharts(document.querySelector("#sr_chart"), optionsSR)).render();

        // Non-Ordering Outlets Chart
        @if (!empty($nonOrderingData['series']))
            let nonOrderingSeries = @json($nonOrderingData['series']);
            let nonOrderingCategories = @json($nonOrderingData['categories']);

            var nonOrderingColors = getChartColorsArray("#non_ordering_chart"),
                optionsNonOrdering = {
                    chart: {
                        height: 320,
                        type: "donut"
                    },
                    series: nonOrderingSeries,
                    labels: nonOrderingCategories,
                    colors: nonOrderingColors,
                    legend: {
                        show: !0,
                        position: "bottom",
                        horizontalAlign: "center",
                        verticalAlign: "middle",
                        floating: !1,
                        fontSize: "14px",
                        offsetX: 0
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        fontSize: '16px',
                                        fontWeight: 600,
                                        color: '#373d3f',
                                        formatter: function(w) {
                                            return w.globals.seriesTotals.reduce((a, b) => {
                                                return a + b
                                            }, 0)
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return Math.round(val) + "%"
                        },
                        style: {
                            fontSize: '12px',
                            colors: ['#fff']
                        }
                    },
                    responsive: [{
                        breakpoint: 600,
                        options: {
                            chart: {
                                height: 240
                            },
                            legend: {
                                show: !1
                            }
                        }
                    }]
                };
            (chartNonOrdering = new ApexCharts(document.querySelector("#non_ordering_chart"), optionsNonOrdering)).render();
        @endif

        let seriesINQ = @json($inq)

        var inqColors = getChartColorsArray("#inq_chart"),
            optionsINQ = {
                chart: {
                    height: 320,
                    type: "pie"
                },
                series: seriesINQ,
                labels: ["Open", "Hold", "Close"],
                colors: inqColors,
                legend: {
                    show: !0,
                    position: "bottom",
                    horizontalAlign: "center",
                    verticalAlign: "middle",
                    floating: !1,
                    fontSize: "14px",
                    offsetX: 0
                },
                responsive: [{
                    breakpoint: 600,
                    options: {
                        chart: {
                            height: 240
                        },
                        legend: {
                            show: !1
                        }
                    }
                }]
            };
        (chartINQ = new ApexCharts(document.querySelector("#inq_chart"), optionsINQ)).render();

        let seriesCP = @json($cp)

        var cpColors = getChartColorsArray("#cp_chart"),
            optionsCP = {
                chart: {
                    height: 320,
                    type: "pie"
                },
                series: seriesCP,
                labels: ["Open", "Hold", "Close"],
                colors: cpColors,
                legend: {
                    show: !0,
                    position: "bottom",
                    horizontalAlign: "center",
                    verticalAlign: "middle",
                    floating: !1,
                    fontSize: "14px",
                    offsetX: 0
                },
                responsive: [{
                    breakpoint: 600,
                    options: {
                        chart: {
                            height: 240
                        },
                        legend: {
                            show: !1
                        }
                    }
                }]
            };
        (chartCP = new ApexCharts(document.querySelector("#cp_chart"), optionsCP)).render();
    </script>
@endpush

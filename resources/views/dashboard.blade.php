@extends('layouts.main')

@push('style')
    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $title }} - {{ $dayName }},
                        {{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Filter Dashboard</h6>
                                </div>
                                <div class="card-body">
                                    <form id="filterForm" method="GET" action="{{ route('dashboard') }}">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Tanggal</label>
                                                <input type="date" class="form-control" name="date" id="date"
                                                    value="{{ $selectedDate }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">TP</label>
                                                <select class="form-control form-select" name="tp" id="tp">
                                                    <option value="">Semua TP</option>
                                                    @foreach ($tpList as $tp)
                                                        <option value="{{ $tp->tp }}"
                                                            {{ $selectedTp == $tp->tp ? 'selected' : '' }}>
                                                            {{ $tp->tp_name ?? $tp->tp }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if (!empty($userList))
                                                <div class="col-md-3">
                                                    <label class="form-label">User Telemarketing</label>
                                                    <select class="form-control form-select" name="user_id" id="user_id">
                                                        <option value="">Semua User</option>
                                                        @foreach ($userList as $user)
                                                            <option value="{{ $user->id }}"
                                                                {{ $selectedUser == $user->id ? 'selected' : '' }}>
                                                                {{ $user->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="col-md-3">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bx bx-search"></i> Filter
                                                    </button>
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="resetFilter()">
                                                        <i class="bx bx-refresh"></i> Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h4 class="mb-1 text-white">{{ $dashboardData['totalOutletCalls'] }}</h4>
                                            <p class="mb-0">Total Outlet Calls</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-phone text-white" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h4 class="mb-1 text-white">{{ $dashboardData['totalTransactions'] }}</h4>
                                            <p class="mb-0">Transactions</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-check-circle text-white" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h4 class="mb-1 text-white">{{ $dashboardData['totalNonOrdering'] }}</h4>
                                            <p class="mb-0">Non Ordering</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-x-circle text-white" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-1">
                                            <h4 class="mb-1 text-white">{{ $dashboardData['remainingOutlets'] }}</h4>
                                            <p class="mb-0">Remaining Outlets</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-time text-white" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Outlet Status Overview</h4>
                                </div>
                                <div class="card-body">
                                    @if (array_sum($dashboardData['chartData']['data']) > 0)
                                        <div id="outlet_status_chart" data-colors='["#2ab57d", "#fd625e", "#ffbf53"]'
                                            class="apex-charts" dir="ltr"></div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">Tidak ada data outlet call untuk hari ini</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Data by TP</h4>
                                </div>
                                <div class="card-body">
                                    @if ($dashboardData['tpData']->count() > 0)
                                        <div id="tp_comparison_chart"
                                            data-colors='["#5156be", "#2ab57d", "#fd625e", "#ffbf53"]' class="apex-charts"
                                            dir="ltr"></div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">Tidak ada data TP untuk hari ini</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TP Data Table -->
                    @if ($dashboardData['tpData']->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Detail Data per TP</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>TP</th>
                                                        <th>Nama TP</th>
                                                        <th>Total Outlets</th>
                                                        <th>Transactions</th>
                                                        <th>Non Ordering</th>
                                                        <th>Remaining</th>
                                                        <th>Progress</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($dashboardData['tpData'] as $index => $tp)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td><strong>{{ $tp['tp'] }}</strong></td>
                                                            <td>{{ $tp['tp_name'] }}</td>
                                                            <td><span
                                                                    class="badge bg-primary">{{ $tp['total_outlets'] }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge bg-success">{{ $tp['transactions'] }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge bg-warning">{{ $tp['non_ordering'] }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge bg-danger">{{ $tp['remaining'] }}</span>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $progress =
                                                                        $tp['total_outlets'] > 0
                                                                            ? round(
                                                                                (($tp['transactions'] +
                                                                                    $tp['non_ordering']) /
                                                                                    $tp['total_outlets']) *
                                                                                    100,
                                                                                1,
                                                                            )
                                                                            : 0;
                                                                @endphp
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-success"
                                                                        role="progressbar"
                                                                        style="width: {{ $progress }}%"
                                                                        aria-valuenow="{{ $progress }}"
                                                                        aria-valuemin="0" aria-valuemax="100">
                                                                        {{ $progress }}%
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- select2 js -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- apexcharts js -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize select2
            $('#tp, #user_id').select2({
                width: '100%'
            });
        });

        function resetFilter() {
            $('#date').val('{{ \Carbon\Carbon::now()->format('Y-m-d') }}');
            $('#tp, #user_id').val('').trigger('change');
        }

        function getChartColorsArray(e) {
            e = $(e).attr("data-colors");
            return (e = JSON.parse(e)).map(function(e) {
                e = e.replace(" ", "");
                if (-1 == e.indexOf("--")) return e;
                e = getComputedStyle(document.documentElement).getPropertyValue(e);
                return e || void 0
            })
        }

        // Outlet Status Chart
        @if (array_sum($dashboardData['chartData']['data']) > 0)
            var outletStatusColors = getChartColorsArray("#outlet_status_chart"),
                outletStatusOptions = {
                    chart: {
                        height: 350,
                        type: "donut"
                    },
                    series: @json($dashboardData['chartData']['data']),
                    labels: @json($dashboardData['chartData']['labels']),
                    colors: outletStatusColors,
                    legend: {
                        show: true,
                        position: "bottom",
                        horizontalAlign: "center",
                        verticalAlign: "middle",
                        floating: false,
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
                                        label: 'Total Outlets',
                                        fontSize: '16px',
                                        fontWeight: 600,
                                        color: '#373d3f',
                                        formatter: function(w) {
                                            return {{ $dashboardData['totalOutletCalls'] }}
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
                                show: false
                            }
                        }
                    }]
                };
            (outletStatusChart = new ApexCharts(document.querySelector("#outlet_status_chart"), outletStatusOptions)).render
                ();
        @endif

        // TP Comparison Chart
        @if ($dashboardData['tpData']->count() > 0)
            var tpComparisonColors = getChartColorsArray("#tp_comparison_chart"),
                tpComparisonOptions = {
                    chart: {
                        height: 350,
                        type: 'bar'
                    },
                    series: [{
                        name: 'Total Outlets',
                        data: @json($dashboardData['tpData']->pluck('total_outlets'))
                    }, {
                        name: 'Transactions',
                        data: @json($dashboardData['tpData']->pluck('transactions'))
                    }, {
                        name: 'Non Ordering',
                        data: @json($dashboardData['tpData']->pluck('non_ordering'))
                    }, {
                        name: 'Remaining',
                        data: @json($dashboardData['tpData']->pluck('remaining'))
                    }],
                    xaxis: {
                        categories: @json($dashboardData['tpData']->pluck('tp_name'))
                    },
                    colors: tpComparisonColors,
                    legend: {
                        show: true,
                        position: "top"
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + " outlets"
                            }
                        }
                    }
                };
            (tpComparisonChart = new ApexCharts(document.querySelector("#tp_comparison_chart"), tpComparisonOptions)).render
                ();
        @endif
    </script>
@endpush

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

    <!-- datepicker css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $title }}</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Filter Data</h6>
                                </div>
                                <div class="card-body">
                                    <form id="filterForm">
                                        <!-- First Row: Location Filters -->
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">SBU</label>
                                                <select class="form-control form-select" name="sbu_filter" id="sbu_filter">
                                                    <option value="All">All</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">TP</label>
                                                <select class="form-control form-select" name="tp_filter" id="tp_filter">
                                                    <option value="All">All</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Kabupaten</label>
                                                <select class="form-control form-select" name="city_filter"
                                                    id="city_filter">
                                                    <option value="All">All</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Kecamatan</label>
                                                <select class="form-control form-select" name="district_filter"
                                                    id="district_filter">
                                                    <option value="All">All</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Second Row: Date and Category Filters -->
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Filter Tanggal</label>
                                                <select class="form-control form-select" name="date_filter"
                                                    id="date_filter">
                                                    <option value="">Semua Tanggal</option>
                                                    <option value="today">Hari Ini</option>
                                                    <option value="yesterday">Kemarin</option>
                                                    <option value="this_week">Minggu Ini</option>
                                                    <option value="last_week">Minggu Lalu</option>
                                                    <option value="this_month">Bulan Ini</option>
                                                    <option value="last_month">Bulan Lalu</option>
                                                    <option value="date_range">Range Tanggal</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3" id="date_range_container" style="display: none;">
                                                <label class="form-label">Range Tanggal</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="date" class="form-control" name="start_date"
                                                            id="start_date" placeholder="Tanggal Mulai">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="date" class="form-control" name="end_date"
                                                            id="end_date" placeholder="Tanggal Selesai">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Kategori</label>
                                                <select class="form-control form-select" name="category_id"
                                                    id="category_id">
                                                    <option value="">Semua Kategori</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary" onclick="applyFilter()">
                                                        <i class="bx bx-search"></i> Filter
                                                    </button>
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="resetFilter()">
                                                        <i class="bx bx-refresh"></i> Reset
                                                    </button>
                                                    <button type="button" class="btn btn-success" onclick="exportPdf()">
                                                        <i class="bx bx-download"></i> Export PDF
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-check nowrap"
                            style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;"
                            id="datatable-non-ordering">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Kode Outlet</th>
                                    <th scope="col">Nama Outlet</th>
                                    <th scope="col">Kecamatan</th>
                                    <th scope="col">Kabupaten</th>
                                    <th scope="col">Karesidenan</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Deskripsi</th>
                                    <th scope="col">Dibuat Oleh</th>
                                    <th scope="col">Tanggal Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via DataTables -->
                            </tbody>
                        </table>
                    </div>
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

    <!-- select2 js -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- datepicker js -->
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize select2
            $('#sbu_filter, #tp_filter, #city_filter, #district_filter, #date_filter, #category_id').select2({
                width: '100%'
            });

            // Load initial data
            loadSbuOptions();

            // Initialize DataTable
            initializeDataTable();

            // Cascading filter handlers
            $('#sbu_filter').on('change', function() {
                var sbuCode = $(this).val();

                // Reset dependent filters
                $('#tp_filter').empty().append('<option value="All">All</option>').trigger('change');
                $('#city_filter').empty().append('<option value="All">All</option>').trigger('change');
                $('#district_filter').empty().append('<option value="All">All</option>').trigger('change');

                if (sbuCode && sbuCode != 'All') {
                    loadTpBySbu(sbuCode);
                }

                dataTable.ajax.reload();
            });

            $('#tp_filter').on('change', function() {
                var tp = $(this).val();
                var sbuCode = $('#sbu_filter').val();

                // Reset dependent filters
                $('#city_filter').empty().append('<option value="All">All</option>').trigger('change');
                $('#district_filter').empty().append('<option value="All">All</option>').trigger('change');

                if (tp && tp != 'All') {
                    loadCitiesByTp(tp, sbuCode);
                }

                dataTable.ajax.reload();
            });

            $('#city_filter').on('change', function() {
                var city = $(this).val();
                var tp = $('#tp_filter').val();
                var sbuCode = $('#sbu_filter').val();

                // Reset dependent filters
                $('#district_filter').empty().append('<option value="All">All</option>').trigger('change');

                if (city && city != 'All') {
                    loadDistrictsByCity(city, tp, sbuCode);
                }

                dataTable.ajax.reload();
            });

            $('#district_filter').on('change', function() {
                dataTable.ajax.reload();
            });

            // Handle date filter change
            $('#date_filter').on('change', function() {
                if ($(this).val() === 'date_range') {
                    $('#date_range_container').show();
                } else {
                    $('#date_range_container').hide();
                    $('#start_date, #end_date').val('');
                }
            });
        });

        let dataTable;

        function initializeDataTable() {
            dataTable = $('#datatable-non-ordering').DataTable({
                responsive: !1,
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('non-ordering-outlets.datatable') }}",
                    data: function(d) {
                        d.sbu_filter = $('#sbu_filter').val();
                        d.tp_filter = $('#tp_filter').val();
                        d.city_filter = $('#city_filter').val();
                        d.district_filter = $('#district_filter').val();
                        d.date_filter = $('#date_filter').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.category_id = $('#category_id').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'outlet_code',
                        name: 'outlet_code',
                        searchable: true
                    },
                    {
                        data: 'outlet_name',
                        name: 'outlet_name',
                        searchable: true
                    },
                    {
                        data: 'district_name',
                        name: 'district_name',
                        searchable: true
                    },
                    {
                        data: 'city_name',
                        name: 'city_name',
                        searchable: true
                    },
                    {
                        data: 'residency_name',
                        name: 'residency_name',
                        searchable: true
                    },
                    {
                        data: 'category_name',
                        name: 'category_name',
                        searchable: true
                    },
                    {
                        data: 'description',
                        name: 'description',
                        searchable: true
                    },
                    {
                        data: 'created_by_name',
                        name: 'created_by_name',
                        searchable: true
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false
                    }
                ],
                order: [
                    [9, 'desc']
                ],
                columnDefs: [{
                    targets: [7], // Description column
                    render: function(data, type, row) {
                        if (type === 'display' && data.length > 50) {
                            return data.substr(0, 50) + '...';
                        }
                        return data;
                    }
                }]
            });
        }

        function loadSbuOptions() {
            console.log('andi');
            $.ajax({
                url: "{{ route('outlet-call.get-sbu-options') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#sbu_filter').empty().append('<option value="All">All</option>');
                        $.each(response.data, function(index, sbu) {
                            $('#sbu_filter').append('<option value="' + sbu.sbu_code + '">' + sbu
                                .sbu_name + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading SBU options:', error);
                }
            });
        }

        function loadTpBySbu(sbuCode) {
            $.ajax({
                url: "{{ route('non-ordering-outlets.get-tp-by-sbu') }}",
                type: 'GET',
                data: {
                    sbu_code: sbuCode
                },
                success: function(response) {
                    if (response.success) {
                        $('#tp_filter').empty().append('<option value="All">All</option>');
                        $.each(response.data, function(index, tp) {
                            $('#tp_filter').append('<option value="' + tp.tp + '">' + tp.tp_name +
                                '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading TP options:', error);
                }
            });
        }

        function loadCitiesByTp(tp, sbuCode) {
            $.ajax({
                url: "{{ route('non-ordering-outlets.get-cities-by-tp') }}",
                type: 'GET',
                data: {
                    tp: tp,
                    sbu_code: sbuCode
                },
                success: function(response) {
                    if (response.success) {
                        $('#city_filter').empty().append('<option value="All">All</option>');
                        $.each(response.data, function(index, city) {
                            $('#city_filter').append('<option value="' + city.city + '">' + city
                                .city_name + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading city options:', error);
                }
            });
        }

        function loadDistrictsByCity(city, tp, sbuCode) {
            $.ajax({
                url: "{{ route('non-ordering-outlets.get-districts-by-city') }}",
                type: 'GET',
                data: {
                    city: city,
                    tp: tp,
                    sbu_code: sbuCode
                },
                success: function(response) {
                    if (response.success) {
                        $('#district_filter').empty().append('<option value="All">All</option>');
                        $.each(response.data, function(index, district) {
                            $('#district_filter').append('<option value="' + district.district + '">' +
                                district.district_name + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading district options:', error);
                }
            });
        }

        function applyFilter() {
            dataTable.ajax.reload();
        }

        function resetFilter() {
            $('#filterForm')[0].reset();
            $('#date_range_container').hide();
            $('#sbu_filter, #tp_filter, #city_filter, #district_filter, #date_filter, #category_id').val('All').trigger(
                'change');

            // Reset dependent filters
            $('#tp_filter').empty().append('<option value="All">All</option>');
            $('#city_filter').empty().append('<option value="All">All</option>');
            $('#district_filter').empty().append('<option value="All">All</option>');

            // Reload SBU options
            loadSbuOptions();

            dataTable.ajax.reload();
        }

        function exportPdf() {
            const formData = $('#filterForm').serialize();

            // Create a form and submit it to trigger download
            const form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('non-ordering-outlets.export-pdf') }}",
                'target': '_blank'
            });

            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': '{{ csrf_token() }}'
            }));

            // Add filter parameters
            const params = new URLSearchParams(formData);
            for (let [key, value] of params) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': key,
                    'value': value
                }));
            }

            $('body').append(form);
            form.submit();
            form.remove();
        }
    </script>
@endpush

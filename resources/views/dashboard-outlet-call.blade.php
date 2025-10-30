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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Filter Data</h4>
                </div>
                <div class="card-body">
                    <form id="filter-form" class="d-flex flex-wrap align-items-center justify-content-start">
                        <div class="p-2">
                            <label class="form-label">Kategori</label>
                            <select class="form-control form-select" name="category" id="category">
                                @foreach ($category as $item)
                                    <option value="{{ $item->id }}"
                                        {{ isset($selectedCategory) && $selectedCategory == $item->id ? 'selected' : '' }}>
                                        {{ $item->kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="p-2">
                            <label class="form-label">Sort By</label>
                            <select class="form-control form-select" name="sort" id="sort">
                                <option value="1" {{ isset($selectedSort) && $selectedSort == 1 ? 'selected' : '' }}>
                                    Qty
                                </option>
                                <option value="2" {{ isset($selectedSort) && $selectedSort == 2 ? 'selected' : '' }}>
                                    Value
                                </option>
                            </select>
                        </div>
                        <div class="p-2">
                            <button class="btn btn-primary mt-4" type="button" id="filter-btn">
                                <i class="bx bx-search me-1"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Data Outlet Call</h4>
                    <div class="card-title-desc">Klik tombol Filter untuk memuat data</div>
                </div>
                <div class="card-body">
                    <div id="initial-message" class="text-center">
                        <div class="mb-3">
                            <i class="bx bx-data bx-lg text-muted"></i>
                        </div>
                        <p class="text-muted">Silakan pilih filter dan klik tombol Filter untuk memuat data</p>
                    </div>

                    <div id="loading-data" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat data...</p>
                    </div>

                    <div id="data-container" style="display: none;">
                        <div class="table-responsive mb-4">
                            <table class="table align-middle datatable dt-responsive table-check nowrap"
                                style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="dt">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama Outlet</th>
                                        <th scope="col">Kecamatan</th>
                                        <th scope="col">Target</th>
                                        <th scope="col">Omset</th>
                                        <th scope="col">Persentase %</th>
                                        <th scope="col">Sisa Piutang</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="data-tbody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="error-container" class="alert alert-danger" style="display: none;">
                        <i class="bx bx-error-circle me-2"></i>
                        <span id="error-message"></span>
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

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        let dataTable;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#category').select2({
                placeholder: 'Pilih Kategori',
                width: '100%'
            });

            $('#sort').select2({
                placeholder: 'Pilih Sort',
                width: '100%'
            });

            // Handle filter button click
            $('#filter-btn').on('click', function() {
                loadOutletCallData();
            });

            // Handle form submit (Enter key)
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                loadOutletCallData();
            });
        });

        function loadOutletCallData() {
            let category = $('#category').val() || {{ $selectedCategory }};
            let sort = $('#sort').val() || {{ $selectedSort }};

            // Show loading, hide other states
            $('#initial-message').hide();
            $('#loading-data').show();
            $('#data-container').hide();
            $('#error-container').hide();

            // Disable filter button during loading
            $('#filter-btn').prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i>Loading...');

            // Destroy existing DataTable if exists
            if (dataTable) {
                dataTable.destroy();
            }

            $.ajax({
                url: "{{ route('get-outlet-call-data', $city) }}",
                type: 'POST',
                data: {
                    category: category,
                    sort: sort
                },
                success: function(response) {
                    $('#loading-data').hide();

                    if (response.success && response.data.length > 0) {
                        populateTable(response.data);
                        $('#data-container').show();

                        // Initialize DataTable
                        dataTable = $('#dt').DataTable({
                            "responsive": true,
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                            }
                        });
                    } else {
                        $('#data-tbody').html(
                            '<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>');
                        $('#data-container').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading-data').hide();
                    $('#error-message').text('Gagal memuat data: ' + error);
                    $('#error-container').show();
                    console.error('Error loading data:', error);
                },
                complete: function() {
                    // Re-enable filter button
                    $('#filter-btn').prop('disabled', false).html('<i class="bx bx-search me-1"></i>Filter');
                }
            });
        }

        function populateTable(data) {
            let html = '';
            data.forEach(function(item, index) {
                html += `

                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.outlet_name || ''}</td>
                        <td>${item.nama_kecamatan || ''}</td>
                        <td>${formatNumber(item.target || 0)}</td>
                        <td>${formatNumber(item.omset || 0)}</td>
                        <td>${item.omset / item.target * 100 || 0 }</td>
                        <td>${formatNumber(item.sld_piutang || 0)}</td>
                        <td><button type="button" onclick="buttonCall()" class="btn btn-primary btn-sm"><i class="fas fa-phone-alt"></i></button></td>
                    </tr>
                `;
            });
            $('#data-tbody').html(html);
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function buttonCall() {
            Swal.fire({
                title: 'Info!',
                text: 'This feature is under development',
                icon: 'info'
            });
        }
    </script>
@endpush

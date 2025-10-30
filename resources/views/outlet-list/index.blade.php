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
                                    <h6 class="card-title mb-0">Filter Data</h6>
                                </div>
                                <div class="card-body">
                                    <form id="filterForm" method="GET" action="{{ route('outlet-list.index') }}">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label class="form-label">Tanggal</label>
                                                <input type="date" class="form-control" name="date" id="date"
                                                    value="{{ $selectedDate }}" required>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">SBU</label>
                                                <select class="form-control form-select" name="sbu" id="sbu">
                                                    <option value="">Semua SBU</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">TP</label>
                                                <select class="form-control form-select" name="tp" id="tp">
                                                    <option value="">Semua TP</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Kabupaten/Kota</label>
                                                <select class="form-control form-select" name="kota" id="kota">
                                                    <option value="">Semua Kota</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Kecamatan</label>
                                                <select class="form-control form-select" name="kecamatan" id="kecamatan">
                                                    <option value="">Semua Kecamatan</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="d-flex gap-2">
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

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-check nowrap"
                            style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="dt-outlet-list">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Outlet</th>
                                    <th scope="col">Telepon</th>
                                    <th scope="col">TP</th>
                                    <th scope="col">Karesidenan</th>
                                    <th scope="col">Kab/Kota</th>
                                    <th scope="col">Kecamatan</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" style="width: 200px;">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Non-Ordering Modal -->
    <div class="modal fade" id="modal-no-order" tabindex="-1" role="dialog" aria-labelledby="noOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noOrderModalLabel">Alasan Tidak Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-no-order">
                    <div class="modal-body">
                        <input type="hidden" id="outlet_code_no_order" name="outlet_code">

                        <div class="form-group mb-3">
                            <label class="form-label">Kategori Alasan</label>
                            <select class="form-control form-select" name="category_id" id="category_no_order" required>
                                <option value="">Pilih Kategori</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" id="description_no_order" rows="4"
                                placeholder="Jelaskan alasan tidak order..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Piutang Detail Modal -->
    <div class="modal fade" id="modal-piutang-detail" tabindex="-1" role="dialog"
        aria-labelledby="piutangDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="piutangDetailModalLabel">Detail Piutang Outlet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Loading State -->
                    <div id="piutang-loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Mengambil data piutang...</p>
                    </div>

                    <!-- Data Content -->
                    <div id="piutang-content" style="display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Kode Outlet</strong></td>
                                            <td id="piutang-outlet-code">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama Outlet</strong></td>
                                            <td id="piutang-outlet-name">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Target</strong></td>
                                            <td id="piutang-target">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Omset</strong></td>
                                            <td id="piutang-omset">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sisa Piutang</strong></td>
                                            <td id="piutang-sld">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div id="piutang-error" style="display: none;" class="alert alert-danger">
                        <h6>Error!</h6>
                        <p id="piutang-error-message">Terjadi kesalahan saat mengambil data.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize select2
            $('#sbu, #tp, #kota, #kecamatan').select2({
                width: '100%'
            });

            // Initialize DataTable
            var table = $('#dt-outlet-list').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "responsive": !1,
                "ajax": {
                    "url": "{{ route('outlet-list.datatable') }}",
                    "data": function(d) {
                        d.date = $('#date').val();
                        d.sbu = $('#sbu').val();
                        d.tp = $('#tp').val();
                        d.kota = $('#kota').val();
                        d.kecamatan = $('#kecamatan').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'outlet_name'
                    },
                    {
                        "data": 'outlet_phone'
                    },
                    {
                        "data": 'tp_name'
                    },
                    {
                        "data": 'residency_name'
                    },
                    {
                        "data": 'city_name'
                    },
                    {
                        "data": 'district_name'
                    },
                    {
                        "data": 'status'
                    },
                    {
                        "data": 'action'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 8]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 8]
                    }
                ],
                "order": [
                    [1, 'asc']
                ]
            });

            // Filter change events
            $('#date, #sbu, #tp, #kota, #kecamatan').on('change', function() {
                table.ajax.reload();
            });

            // SBU change - load TPs
            $('#sbu').on('change', function() {
                var sbuCode = $(this).val();

                // Clear dependent filters
                $('#tp').empty().append('<option value="">Semua TP</option>');
                $('#kota').empty().append('<option value="">Semua Kota</option>');
                $('#kecamatan').empty().append('<option value="">Semua Kecamatan</option>');

                if (sbuCode) {
                    loadTpBySbu(sbuCode);
                }
            });

            // TP change - load cities
            $('#tp').on('change', function() {
                var tp = $(this).val();
                var sbuCode = $('#sbu').val();

                // Clear dependent filters
                $('#kota').empty().append('<option value="">Semua Kota</option>');
                $('#kecamatan').empty().append('<option value="">Semua Kecamatan</option>');

                if (tp && sbuCode) {
                    loadCitiesByTp(tp, sbuCode);
                }
            });

            // Kota change - load districts
            $('#kota').on('change', function() {
                var city = $(this).val();
                var tp = $('#tp').val();
                var sbuCode = $('#sbu').val();

                // Clear kecamatan
                $('#kecamatan').empty().append('<option value="">Semua Kecamatan</option>');

                if (city && tp && sbuCode) {
                    loadDistrictsByCity(city, tp, sbuCode);
                }
            });

            // Load non-ordering categories
            loadNonOrderingCategories();

            // Load initial SBU options
            loadSbuOptions();
        });

        function resetFilter() {
            $('#date').val('{{ \Carbon\Carbon::now()->format('Y-m-d') }}');
            $('#sbu, #tp, #kota, #kecamatan').val('').trigger('change');
            $('#dt-outlet-list').DataTable().ajax.reload();
        }

        function loadSbuOptions() {
            $.ajax({
                url: "{{ route('outlet-list.get-sbu-options') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#sbu').empty().append('<option value="">Semua SBU</option>');
                        $.each(response.data, function(index, sbu) {
                            $('#sbu').append('<option value="' + sbu.sbu_code + '">' + sbu
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
                url: "{{ route('outlet-list.get-tp') }}",
                type: 'GET',
                data: {
                    sbu_code: sbuCode
                },
                success: function(response) {
                    if (response.status && response.data) {
                        let data = response.data;
                        $('#tp').empty().append('<option value="">Semua TP</option>');
                        for (let i = 0; i < data.length; i++) {
                            $('#tp').append('<option value="' + data[i].kodetp + '">' + data[i].nama_tp +
                                '</option>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading TP options:', error);
                }
            });
        }

        function loadCitiesByTp(tp, sbuCode) {
            $.ajax({
                url: "{{ route('outlet-list.get-cities-by-tp') }}",
                type: 'GET',
                data: {
                    tp: tp,
                    sbu_code: sbuCode
                },
                success: function(response) {
                    if (response.status && response.data) {
                        let data = response.data;
                        $('#kota').empty().append('<option value="">Semua Kota</option>');
                        for (let i = 0; i < data.length; i++) {
                            $('#kota').append('<option value="' + data[i].kode + '">' + data[i]
                                .nama_kabupaten + '</option>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading city options:', error);
                }
            });
        }

        function loadDistrictsByCity(city, tp, sbuCode) {
            $.ajax({
                url: "{{ route('outlet-list.get-districts') }}",
                type: 'GET',
                data: {
                    city: city,
                    tp: tp,
                    sbu_code: sbuCode
                },
                success: function(response) {
                    if (response.status && response.data) {
                        let data = response.data;
                        $('#kecamatan').empty().append('<option value="">Semua Kecamatan</option>');
                        for (let i = 0; i < data.length; i++) {
                            $('#kecamatan').append('<option value="' + data[i].kode_kecamatan + '">' +
                                data[i].nama_kecamatan + '</option>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading district options:', error);
                }
            });
        }

        // Outlet functions
        function orderOutlet(outletCode, residencyCode, cityCode, districtCode) {
            const url =
                `{{ route('transactions.create') }}?outlet_code=${outletCode}&residency=${residencyCode}&city=${cityCode}&district=${districtCode}`;
            window.open(url, '_blank');
        }

        function noOrderOutlet(outletCode) {
            $('#outlet_code_no_order').val(outletCode);
            $('#modal-no-order').modal('show');
        }

        function showPiutangDetail(outletCode) {
            // Reset modal state
            $('#piutang-loading').show();
            $('#piutang-content').hide();
            $('#piutang-error').hide();

            // Show modal
            $('#modal-piutang-detail').modal('show');

            // Make AJAX request to get piutang data
            $.ajax({
                url: '{{ route('outlet-list.piutang-detail') }}',
                type: 'GET',
                data: {
                    outlet_code: outletCode
                },
                success: function(response) {
                    $('#piutang-loading').hide();

                    if (response.success) {
                        // Format numbers with thousand separators
                        const formatNumber = (num) => {
                            return new Intl.NumberFormat('id-ID').format(num);
                        };

                        // Populate data
                        $('#piutang-outlet-code').text(response.data.outlet_code);
                        $('#piutang-outlet-name').text(response.data.outlet_name);
                        $('#piutang-target').text(response.data.target);
                        $('#piutang-omset').text(response.data.omset);
                        $('#piutang-sld').text('Rp ' + formatNumber(response.data.sld_piutang));

                        $('#piutang-content').show();
                    } else {
                        $('#piutang-error-message').text(response.message || 'Data tidak ditemukan');
                        $('#piutang-error').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#piutang-loading').hide();

                    let message = 'Terjadi kesalahan saat mengambil data piutang';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    $('#piutang-error-message').text(message);
                    $('#piutang-error').show();
                }
            });
        }

        function loadNonOrderingCategories() {
            $.ajax({
                url: '{{ route('get-non-ordering-categories') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">Pilih Kategori</option>';
                        response.data.forEach(function(category) {
                            options += `<option value="${category.id}">${category.name}</option>`;
                        });
                        $('#category_no_order').html(options);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading categories:', error);
                }
            });
        }

        // Handle no-order form submission
        $('#form-no-order').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: '{{ route('store-non-ordering-outlet') }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#modal-no-order').modal('hide');
                        $('#form-no-order')[0].reset();
                        $('#dt-outlet-list').DataTable().ajax.reload();

                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    let message = 'Terjadi kesalahan saat menyimpan data';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        title: "Error!",
                        text: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    });
                }
            });
        });

        // Reset modal when closed
        $('#modal-no-order').on('hidden.bs.modal', function() {
            $('#form-no-order')[0].reset();
        });
    </script>
@endpush

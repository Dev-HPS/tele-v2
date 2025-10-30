@extends('layouts.main')

@push('style')
    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- datepicker css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row align-items-center">
        <div class="col-md-2">

            @if (!in_array(auth()->user()->role_id, ['26be63b7-b410-46fc-a2f7-8dc3ed9e29ed']))
                <div class="mb-3">
                    <a href="{{ route('outlet-call.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>
                        Tambah</a>
                </div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center justify-content-start mb-3">
                <div class="p-2">
                    <label class="form-label d-block">SBU</label>
                    <select class="form-control form-select" name="sbu_filter" id="sbu_filter">
                        <option value="All">All</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label d-block">TP</label>
                    <select class="form-control form-select" name="tp_filter" id="tp_filter">
                        <option value="All">All</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label d-block">Kabupaten</label>
                    <select class="form-control form-select" name="city_filter" id="city_filter">
                        <option value="All">All</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label d-block">Kecamatan</label>
                    <select class="form-control form-select" name="district_filter" id="district_filter">
                        <option value="All">All</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label d-block">Hari</label>
                    <select class="form-control form-select" name="day_filter" id="day_filter">
                        <option value="All">All</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label d-block">Filter Double</label>
                    <select class="form-control form-select" name="duplicate_filter" id="duplicate_filter">
                        <option value="All">All</option>
                        <option value="duplicate">Double</option>
                        <option value="unique">Unique</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label d-block">Empty Data</label>
                    <select class="form-control form-select" name="empty_filter" id="empty_filter">
                        <option value="All">All</option>
                        <option value="outlet_name">Nama Toko</option>
                        <option value="outlet_owner">Nama PKP</option>
                        <option value="outlet_phone">No Telepon</option>
                        <option value="outlet_address">Alamat</option>
                    </select>
                </div>
                <div class="p-2">
                    {{-- <button class="btn btn-success mt-4" id="export-pdf">
                        <i class="bx bx-download me-1"></i>Cetak PDF
                    </button> --}}
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="table-responsive mb-4">
                <table class="table align-middle datatable dt-responsive table-check nowrap"
                    style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="dt">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Toko</th>
                            <th scope="col">Nama PKP</th>
                            <th scope="col">No Telepon</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">Kecamatan</th>
                            <th scope="col">Kabupaten</th>
                            <th scope="col">TP</th>
                            <th scope="col">Hari</th>
                            <th scope="col">Status</th>
                            <th style="width: 150px; min-width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <!-- end table -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-delete" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Hapus Data</h5>
                </div>
                <div class="modal-body">
                    <form id="form-delete">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea id="reason" name="reason" rows="3" class="form-control"
                                placeholder="Masukkan alasan penghapusan..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" onclick="closeDeleteModal()">Close</button>
                    <button type="button" onclick="deleteData()" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-piutang" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalPiutangLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPiutangLabel">Detail Piutang Outlet</h5>
                    <button type="button" class="btn-close" onclick="closePiutangModal()"></button>
                </div>
                <div class="modal-body">
                    <div id="piutang-loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat data piutang...</p>
                    </div>
                    <div id="piutang-content" style="display: none;">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td width="150"><strong>Kode Outlet</strong></td>
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
                    <div id="piutang-error" class="alert alert-danger" style="display: none;">
                        <i class="bx bx-error-circle me-2"></i>
                        <span id="piutang-error-message"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closePiutangModal()">Tutup</button>
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

    <!-- datepicker js -->
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        let uniqId;

        $(document).ready(function() {
            let residency, city, district;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize select2
            $('#sbu_filter').select2({
                placeholder: 'Pilih SBU',
                width: '200px'
            });

            $('#tp_filter').select2({
                placeholder: 'Pilih TP',
                width: '150px'
            });

            $('#city_filter').select2({
                placeholder: 'Pilih Kabupaten',
                width: '150px'
            });

            $('#district_filter').select2({
                placeholder: 'Pilih Kecamatan',
                width: '150px'
            });

            $('#day_filter').select2({
                placeholder: 'Pilih Hari',
                width: '120px'
            });

            $('#duplicate_filter').select2({
                placeholder: 'Filter Double',
                width: '150px'
            });

            $('#empty_filter').select2({
                placeholder: 'Filter Data Kosong',
                width: '150px'
            });

            // Initialize DataTable
            var table = $('#dt').DataTable({
                responsive: !1,
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.outlet-call') }}",
                    "data": function(d) {
                        d.sbu_filter = $('#sbu_filter').val();
                        d.tp_filter = $('#tp_filter').val();
                        d.city_filter = $('#city_filter').val();
                        d.district_filter = $('#district_filter').val();
                        d.day_filter = $('#day_filter').val();
                        d.duplicate_filter = $('#duplicate_filter').val();
                        d.empty_filter = $('#empty_filter').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'outlet_name'
                    },
                    {
                        "data": 'outlet_owner'
                    },
                    {
                        "data": 'outlet_phone'
                    },
                    {
                        "data": 'outlet_address'
                    },
                    {
                        "data": 'district_name'
                    },
                    {
                        "data": 'city_name'
                    },
                    {
                        "data": 'tp_name'
                    },
                    {
                        "data": 'day'
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
                        "targets": [0]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    }
                ],
            });

            // Load initial data
            loadSbuOptions();

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

                table.ajax.reload();
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

                table.ajax.reload();
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

                table.ajax.reload();
            });

            // Other filter change handlers
            $('#district_filter, #day_filter, #duplicate_filter, #empty_filter').on('change', function() {
                table.ajax.reload();
            });

            // Export PDF handler
            $('#export-pdf').on('click', function() {
                exportToPDF();
            });
        });

        function deleteModal(identifier) {
            console.log(identifier);
            uniqId = identifier.getAttribute('data-id');
            $('#modal-delete').modal('show');
        }

        function closeDeleteModal() {
            $('#form-delete').trigger('reset');
            $('#modal-delete').modal('hide');
        }


        // ganti function deleteData jadi pake sweetalert buat messagenya

        function deleteData() {
            let reason = $('#reason').val();

            if (!reason.trim()) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Alasan penghapusan harus diisi!',
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('outlet-call.destroy', '') }}/" + uniqId,
                        data: {
                            reason: reason
                        },
                        success: function(response) {
                            if (response.success) {
                                closeDeleteModal();
                                $('#dt').DataTable().ajax.reload();
                                Swal.fire(
                                    'Deleted!',
                                    'Data berhasil dihapus.',
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Terjadi kesalahan',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat memproses permintaan.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function loadSbuOptions() {
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
                url: "{{ route('outlet-call.get-tp-by-sbu') }}",
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
                url: "{{ route('outlet-call.get-cities-by-tp') }}",
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
                                .city_name +
                                '</option>');
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
                url: "{{ route('outlet-call.get-districts-by-city') }}",
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

        function loadTpOptions() {
            // Load TP options from outlet_calls
            $.ajax({
                url: "{{ route('outlet-call.get-tp-options') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#tp_filter').empty()
                            .append('<option value="All">All</option>');
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

        function exportToPDF() {
            var filters = {
                sbu_filter: $('#sbu_filter').val(),
                tp_filter: $('#tp_filter').val(),
                city_filter: $('#city_filter').val(),
                district_filter: $('#district_filter').val(),
                day_filter: $('#day_filter').val()
            };

            // Create form and submit for PDF export
            var form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('outlet-call.export-pdf') }}"
            });

            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content')
            }));

            // Add filter parameters
            $.each(filters, function(key, value) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': key,
                    'value': value
                }));
            });

            // Submit form
            form.appendTo('body').submit().remove();
        }

        function showPiutangModal(outletCode, outletName) {
            // Reset modal state
            $('#piutang-loading').show();
            $('#piutang-content').hide();
            $('#piutang-error').hide();

            // Show modal
            $('#modal-piutang').modal('show');

            // Fetch piutang data
            $.ajax({
                url: "{{ route('outlet-call.get-piutang') }}",
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

        function closePiutangModal() {
            $('#modal-piutang').modal('hide');
        }

        function syncOutletData(outletCode, outletName) {
            Swal.fire({
                title: 'Sinkronisasi Data',
                text: `Apakah Anda yakin ingin mensinkronkan data outlet ${outletName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Sync!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ route('outlet-call.sync') }}",
                        type: 'POST',
                        data: {
                            outlet_code: outletCode,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json'
                    }).then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Terjadi kesalahan')
                        }
                        return response
                    }).catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.message || error.responseJSON?.message || 'Terjadi kesalahan'}`
                        )
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Reload DataTable after successful sync
                    $('#dt').DataTable().ajax.reload(null, false);

                    Swal.fire({
                        title: 'Berhasil!',
                        text: result.value.message || 'Data outlet berhasil disinkronkan',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    });
                }
            });
        }
    </script>
@endpush

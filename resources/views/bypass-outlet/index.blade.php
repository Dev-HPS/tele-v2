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
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $title }}</h4>
                        @if (in_array(auth()->user()->role_id, ['a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b', '346d417a-544d-48f3-bb4d-1da4ce54dffc']))
                            <button type="button" class="btn btn-primary" onclick="showCreateModal()">
                                <i class="fas fa-plus"></i> Tambah Bypass Outlet
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-check nowrap"
                            style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="dt-bypass-outlet">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Outlet</th>
                                    <th scope="col">Kecamatan</th>
                                    <th scope="col">Kota / Kabupaten</th>
                                    <th scope="col">TP</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Deskripsi</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Dibuat Oleh</th>
                                    <th scope="col">Disetujui Oleh</th>
                                    <th scope="col" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="modal-bypass-outlet" tabindex="-1" role="dialog" aria-labelledby="bypassOutletModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bypassOutletModalLabel">Tambah Bypass Outlet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-bypass-outlet">
                    <div class="modal-body">
                        <input type="hidden" id="bypass_outlet_id" name="bypass_outlet_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">TP <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="tp" id="tp" required>
                                        <option value="">Pilih TP</option>
                                        @foreach ($tpList as $tp)
                                            <option value="{{ $tp->tp }}">{{ $tp->tp_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Kota <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="city" id="city" required>
                                        <option value="">Pilih Kota</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="district" id="district" required>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Outlet <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="outlet_code" id="outlet_code" required>
                                        <option value="">Pilih Outlet</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date" id="date" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Deskripsi / Keterangan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" id="description" rows="4"
                                placeholder="Jelaskan alasan bypass outlet..." required></textarea>
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
            $('#tp, #city, #district, #outlet_code').select2({
                width: '100%',
                dropdownParent: $('#modal-bypass-outlet')
            });

            // Initialize DataTable
            var table = $('#dt-bypass-outlet').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('bypass-outlet.datatable') }}"
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'outlet.outlet_name'
                    },
                    {
                        "data": 'outlet.district_name'
                    },
                    {
                        "data": 'outlet.city_name'
                    },
                    {
                        "data": 'outlet.tp_name'
                    },
                    {
                        "data": 'date_formatted'
                    },
                    {
                        "data": 'description'
                    },
                    {
                        "data": 'status_badge'
                    },
                    {
                        "data": 'creator_name'
                    },
                    {
                        "data": 'approver_name'
                    },
                    {
                        "data": 'action'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 1, 2, 3, 4, 6, 7, 8, 9, 10]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 10]
                    }
                ],
                "order": [
                    [1, 'desc']
                ]
            });

            // TP change event
            $('#tp').on('change', function() {
                var tp = $(this).val();

                // Clear dependent dropdowns
                $('#city, #district, #outlet_code').empty().append('<option value="">Pilih...</option>');

                if (tp) {
                    loadCitiesByTp(tp);
                }
            });

            // City change event
            $('#city').on('change', function() {
                var tp = $('#tp').val();
                var city = $(this).val();

                // Clear dependent dropdowns
                $('#district, #outlet_code').empty().append('<option value="">Pilih...</option>');

                if (tp && city) {
                    loadDistrictsByCity(tp, city);
                }
            });

            // District change event
            $('#district').on('change', function() {
                var tp = $('#tp').val();
                var district = $(this).val();

                // Clear outlet dropdown
                $('#outlet_code').empty().append('<option value="">Pilih Outlet</option>');

                if (tp && district) {
                    loadOutletsByDistrict(tp, district);
                }
            });
        });

        function showCreateModal() {
            $('#bypassOutletModalLabel').text('Tambah Bypass Outlet');
            $('#form-bypass-outlet')[0].reset();
            $('#bypass_outlet_id').val('');
            $('#tp, #city, #district, #outlet_code').val('').trigger('change');
            $('#modal-bypass-outlet').modal('show');
        }

        function editBypassOutlet(id) {
            $.ajax({
                url: '{{ route('bypass-outlet.edit', '') }}/' + id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#bypassOutletModalLabel').text('Edit Bypass Outlet');
                        $('#bypass_outlet_id').val(response.data.id);
                        $('#outlet_code').append('<option value="' + response.data.outlet_code + '" selected>' +
                            response.data.outlet_code + '</option>');
                        $('#date').val(response.data.date);
                        $('#description').val(response.data.description);
                        $('#modal-bypass-outlet').modal('show');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: "Error!",
                        text: xhr.responseJSON?.message || "Terjadi kesalahan",
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    });
                }
            });
        }

        function deleteBypassOutlet(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('bypass-outlet.destroy', '') }}/' + id,
                        type: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#5156be"
                                });
                                $('#dt-bypass-outlet').DataTable().ajax.reload();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error!",
                                text: xhr.responseJSON?.message || "Terjadi kesalahan",
                                icon: "error",
                                confirmButtonColor: "#5156be"
                            });
                        }
                    });
                }
            });
        }

        function loadCitiesByTp(tp) {
            $.ajax({
                url: '{{ route('bypass-outlet.cities-by-tp') }}',
                type: 'GET',
                data: {
                    tp: tp
                },
                success: function(response) {
                    if (response.success) {
                        $('#city').empty().append('<option value="">Pilih Kota</option>');
                        $.each(response.data, function(index, city) {
                            $('#city').append('<option value="' + city.city_code + '">' + city
                                .city_name + '</option>');
                        });
                    }
                }
            });
        }

        function loadDistrictsByCity(tp, city) {
            $.ajax({
                url: '{{ route('bypass-outlet.districts-by-city') }}',
                type: 'GET',
                data: {
                    tp: tp,
                    city: city
                },
                success: function(response) {
                    if (response.success) {
                        $('#district').empty().append('<option value="">Pilih Kecamatan</option>');
                        $.each(response.data, function(index, district) {
                            $('#district').append('<option value="' + district.district_code + '">' +
                                district.district_name + '</option>');
                        });
                    }
                }
            });
        }

        function loadOutletsByDistrict(tp, district) {
            $.ajax({
                url: '{{ route('bypass-outlet.outlets-by-district') }}',
                type: 'GET',
                data: {
                    tp: tp,
                    district: district
                },
                success: function(response) {
                    if (response.success) {
                        $('#outlet_code').empty().append('<option value="">Pilih Outlet</option>');
                        $.each(response.data, function(index, outlet) {
                            $('#outlet_code').append('<option value="' + outlet.outlet_code + '">' +
                                outlet.outlet_name + '</option>');
                        });
                    }
                }
            });
        }

        // Handle form submission
        $('#form-bypass-outlet').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            const id = $('#bypass_outlet_id').val();
            const url = id ? '{{ route('bypass-outlet.update', '') }}/' + id :
                '{{ route('bypass-outlet.store') }}';
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#modal-bypass-outlet').modal('hide');
                        $('#form-bypass-outlet')[0].reset();
                        $('#dt-bypass-outlet').DataTable().ajax.reload();

                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat menyimpan data';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        title: "Error!",
                        html: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    });
                }
            });
        });

        // Reset modal when closed
        $('#modal-bypass-outlet').on('hidden.bs.modal', function() {
            $('#form-bypass-outlet')[0].reset();
            $('#tp, #city, #district, #outlet_code').val('').trigger('change');
        });
    </script>
@endpush

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
    {{-- <div class="row align-items-center">
        <div class="col-md-2">
            <div class="mb-3">
                <a href="{{ route('outlet-call.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>
                    Tambah</a>
            </div>
        </div>
    </div> --}}
    <div class="row">
        {{-- <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center justify-content-start mb-3">

                <div class="p-2">
                    <label class="form-label">TP</label>
                    <select class="form-control form-select" name="residency_filter" id="residency_filter">
                        <option value="All">All</option>
                        @foreach ($tp as $row)
                            <option value="{{ $row->kodetp }}">{{ $row->nama_tp }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label">Kabupaten</label>
                    <select class="form-control form-select" name="city_filter" id="city_filter">
                        <option value="All">All</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label">Kecamatan</label>
                    <select class="form-control form-select" name="district_filter" id="district_filter">
                        <option value="All">All</option>
                    </select>
                </div>

            </div>
        </div> --}}

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
                            <th scope="col">Tipe</th>
                            <th scope="col">Alasan</th>
                            <th scope="col">Hari</th>
                            <th scope="col">SBU</th>
                            <th style="width: 80px; min-width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <!-- end table -->
            </div>
        </div>
    </div>


    <div class="modal fade" id="modal-reject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal Reject</h5>
                </div>
                <div class="modal-body">
                    <form id="form-reject" action="{{ route('approve-outlet-call.reject') }}" method="POST">
                        @csrf
                        <input type="hidden" id="id" name="id">
                        <div class="form-group mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea id="description" name="description" rows="3" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" onclick="closeDeleteModal()">Close</button>
                    <button type="button" onclick="reject()" class="btn btn-primary">Submit</button>
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
        $(document).ready(function() {
            let residency, city, district
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })

            $('#dt').DataTable({
                responsive: !1,
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.approve-outlet-call') }}",
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
                        "data": 'type'
                    },
                    {
                        "data": 'reason'
                    },
                    {
                        "data": 'day'
                    },
                    {
                        "data": 'sbu_code'
                    },
                    {
                        "data": 'action'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 2]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 2]
                    }
                ],
                "order": [
                    [1, 'asc']
                ]
            })


        });
    </script>

    <script>
        function rejectModal(identifier) {
            console.log(identifier);
            uniqId = identifier.getAttribute('data-id')
            $('#id').val(uniqId)
            $('#modal-reject').modal('show')
        }

        function closeDeleteModal() {
            $('#form-reject').trigger('reset')
            $('#modal-reject').modal('hide')
        }

        function reject() {
            let id = $('#id').val()
            let description = $('#description').val()

            if (description.trim() === '') {
                Swal.fire(
                    'Error!',
                    'Alasan penolakan tidak boleh kosong.',
                    'error'
                )
                return
            }

            $.ajax({
                url: "{{ route('approve-outlet-call.reject') }}",
                type: 'POST',
                data: {
                    id: id,
                    description: description,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire(
                        'Rejected!',
                        'Data telah ditolak.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat menolak data.',
                        'error'
                    );
                }
            });
        }

        function approve(uniqId) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan diapprove!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, approve!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('approve-outlet-call') }}/" + encodeURIComponent(uniqId) + "/approve",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire(
                                'Approved!',
                                'Data telah diapprove.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat mengapprove data.',
                                'error'
                            );
                        }
                    });
                }
            })
        }
    </script>
@endpush

@extends('layouts.main')

@push('style')
    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $title }}</h4>
                </div>
                <div class="card-body">
                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-check nowrap"
                            style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;"
                            id="dt-approval-bypass-outlet">
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
                                    <th scope="col">Tanggal Dibuat</th>
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

    <!-- Reject Modal -->
    <div class="modal fade" id="modal-reject" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Alasan Penolakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-reject">
                    <div class="modal-body">
                        <input type="hidden" id="reject_bypass_outlet_id" name="bypass_outlet_id">

                        <div class="form-group mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="reason" id="reason" rows="4" placeholder="Jelaskan alasan penolakan..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="modal-view" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Detail Bypass Outlet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Outlet Code:</label>
                                <p id="view_outlet_code" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal:</label>
                                <p id="view_date" class="mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p id="view_status" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dibuat Oleh:</label>
                                <p id="view_creator" class="mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Disetujui Oleh:</label>
                                <p id="view_approver" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Dibuat:</label>
                                <p id="view_created_at" class="mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi:</label>
                        <p id="view_description" class="mb-0"></p>
                    </div>

                    <div id="reason_section" class="mb-3" style="display: none;">
                        <label class="form-label fw-bold text-danger">Alasan Penolakan:</label>
                        <p id="view_reason" class="mb-0 text-danger"></p>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            var table = $('#dt-approval-bypass-outlet').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('approval-bypass-outlet.datatable') }}"
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
                        "data": 'created_at_formatted'
                    },
                    {
                        "data": 'action'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 11]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 11]
                    }
                ],
                "order": [
                    [7, 'desc']
                ]
            });
        });

        function approveBypassOutlet(id) {
            Swal.fire({
                title: 'Konfirmasi Approval',
                text: "Apakah Anda yakin ingin menyetujui bypass outlet ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = '{{ url('approval-bypass-outlet') }}/' + id + '/approve';

                    $.ajax({
                        url: url,
                        type: 'POST',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#5156be"
                                });
                                $('#dt-approval-bypass-outlet').DataTable().ajax.reload();
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

        function rejectBypassOutlet(id) {
            $('#reject_bypass_outlet_id').val(id);
            $('#reason').val('');
            $('#modal-reject').modal('show');
        }

        function viewBypassOutlet(id) {
            $.ajax({
                url: '{{ route('approval-bypass-outlet.show', '') }}/' + id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        $('#view_outlet_code').text(data.outlet_code);
                        $('#view_date').text(data.date);
                        $('#view_status').text(data.status);
                        $('#view_creator').text(data.creator_name);
                        $('#view_approver').text(data.approver_name);
                        $('#view_created_at').text(data.created_at);
                        $('#view_description').text(data.description);

                        // Show reason section if rejected
                        if (data.reason) {
                            $('#view_reason').text(data.reason);
                            $('#reason_section').show();
                        } else {
                            $('#reason_section').hide();
                        }

                        $('#modal-view').modal('show');
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

        // Handle reject form submission
        $('#form-reject').on('submit', function(e) {
            e.preventDefault();

            const id = $('#reject_bypass_outlet_id').val();
            const reason = $('#reason').val();

            $.ajax({
                url: '{{ route('approval-bypass-outlet.reject', '') }}/' + id,
                type: 'POST',
                data: {
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        $('#modal-reject').modal('hide');
                        $('#form-reject')[0].reset();
                        $('#dt-approval-bypass-outlet').DataTable().ajax.reload();

                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat menolak data';

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
        $('#modal-reject').on('hidden.bs.modal', function() {
            $('#form-reject')[0].reset();
        });
    </script>
@endpush

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
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle bg-primary">
                            <span class="avatar-title">
                                <i class="bx bx-list-ul font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-1 ms-3">
                            <h5 class="mb-1" id="total-logs">-</h5>
                            <p class="mb-0">Total Log</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle bg-success">
                            <span class="avatar-title">
                                <i class="bx bx-calendar font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-1 ms-3">
                            <h5 class="mb-1" id="today-logs">-</h5>
                            <p class="mb-0">Hari Ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle bg-warning">
                            <span class="avatar-title">
                                <i class="bx bx-time font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-1 ms-3">
                            <h5 class="mb-1" id="pending-logs">-</h5>
                            <p class="mb-0">Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle bg-info">
                            <span class="avatar-title">
                                <i class="bx bx-calendar-week font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-1 ms-3">
                            <h5 class="mb-1" id="month-logs">-</h5>
                            <p class="mb-0">Bulan Ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center justify-content-start mb-3">
                <div class="p-2">
                    <label class="form-label">Aksi</label>
                    <select class="form-control form-select" name="action_filter" id="action_filter">
                        <option value="All">Semua</option>
                        <option value="create">Tambah Data</option>
                        <option value="update">Update Data</option>
                        <option value="delete">Hapus Data</option>
                        <option value="approve">Approve Data</option>
                        <option value="reject">Reject Data</option>
                        <option value="restore">Restore Data</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label">Status</label>
                    <select class="form-control form-select" name="status_filter" id="status_filter">
                        <option value="All">Semua</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
                <div class="p-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="date_from" id="date_from">
                </div>
                <div class="p-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="date_to" id="date_to">
                </div>
                <div class="p-2">
                    <button class="btn btn-primary mt-4" id="apply-filter">
                        <i class="bx bx-filter me-1"></i>Filter
                    </button>
                </div>
                <div class="p-2">
                    <button class="btn btn-secondary mt-4" id="reset-filter">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Log Table -->
        <div class="col-md-12">
            <div class="table-responsive mb-4">
                <table class="table align-middle datatable dt-responsive table-check nowrap"
                    style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="dt-logs">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">User</th>
                            <th scope="col">Outlet</th>
                            <th scope="col">Aksi</th>
                            <th scope="col">Status</th>
                            <th scope="col">Deskripsi</th>
                            <th scope="col">Waktu</th>
                            <th scope="col">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel">Detail Log Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="log-detail-content">
                        <!-- Detail content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
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

            // Initialize select2
            $('#action_filter, #status_filter').select2({
                width: '150px'
            });

            // Load stats
            loadStats();

            // Initialize DataTable
            var table = $('#dt-logs').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('outlet-call-logs.datatable') }}",
                    data: function(d) {
                        d.action_filter = $('#action_filter').val();
                        d.status_filter = $('#status_filter').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'user_name'
                    },
                    {
                        data: 'outlet_name'
                    },
                    {
                        data: 'action_label'
                    },
                    {
                        data: 'status_label'
                    },
                    {
                        data: 'description'
                    },
                    {
                        data: 'created_at_formatted'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [6, 'desc']
                ]
            });

            // Filter handlers
            $('#apply-filter').on('click', function() {
                table.ajax.reload();
                loadStats();
            });

            $('#reset-filter').on('click', function() {
                $('#action_filter').val('All').trigger('change');
                $('#status_filter').val('All').trigger('change');
                $('#date_from').val('');
                $('#date_to').val('');
                table.ajax.reload();
                loadStats();
            });
        });

        function loadStats() {
            $.ajax({
                url: "{{ route('outlet-call-logs.stats') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#total-logs').text(response.stats.total_logs.toLocaleString());
                        $('#today-logs').text(response.stats.today_logs.toLocaleString());
                        $('#month-logs').text(response.stats.month_logs.toLocaleString());
                        $('#pending-logs').text(response.stats.pending_logs.toLocaleString());
                    }
                },
                error: function() {
                    console.error('Failed to load stats');
                }
            });
        }

        function showLogDetail(id) {
            $.ajax({
                url: "{{ route('outlet-call-logs.show', '') }}/" + id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const log = response.data;
                        let content = `
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>User:</strong> ${log.user_name}<br>
                                    <strong>Outlet:</strong> ${log.outlet_name}<br>
                                    <strong>Aksi:</strong> ${log.action}<br>
                                    <strong>Status:</strong> ${log.status}<br>
                                    <strong>Waktu:</strong> ${log.created_at}<br>
                                </div>
                                <div class="col-md-6">
                                    <strong>IP Address:</strong> ${log.ip_address || 'N/A'}<br>
                                    <strong>User Agent:</strong> ${log.user_agent ? log.user_agent.substring(0, 50) + '...' : 'N/A'}<br>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <strong>Deskripsi:</strong><br>
                                    <p class="text-muted">${log.description || 'Tidak ada deskripsi'}</p>
                                </div>
                            </div>
                        `;

                        if (log.old_data) {
                            content += `
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Data Lama:</strong><br>
                                        <pre class="bg-light p-2" style="font-size: 12px; max-height: 300px; overflow-y: auto;">${JSON.stringify(log.old_data, null, 2)}</pre>
                                    </div>
                            `;
                        }

                        if (log.new_data) {
                            if (!log.old_data) {
                                content += `<div class="row"><div class="col-md-6">`;
                            }
                            content += `
                                    <div class="col-md-6">
                                        <strong>Data Baru:</strong><br>
                                        <pre class="bg-light p-2" style="font-size: 12px; max-height: 300px; overflow-y: auto;">${JSON.stringify(log.new_data, null, 2)}</pre>
                                    </div>
                                </div>
                            `;
                        }

                        $('#log-detail-content').html(content);
                        $('#modal-detail').modal('show');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat detail log', 'error');
                }
            });
        }
    </script>
@endpush

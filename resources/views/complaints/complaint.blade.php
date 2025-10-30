@extends('layouts.main')

@push('style')
    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <div class="row align-items-center">
        <div class="col-md-2">
            <div class="mb-3">
                <a href="{{ route('complaints.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>
                    Tambah</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center justify-content-start mb-3">
                <div class="p-2">
                    <label class="form-label">Karesidenan</label>
                    <select class="form-control form-select" name="residency_filter" id="residency_filter">
                        <option value="All">All</option>
                        @foreach ($residency as $row)
                            <option value="{{ $row->kode_kar }}">{{ $row->nama_karesidenan }}</option>
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
                <div class="p-2">
                    <label class="form-label">Tanggal Dibuat</label>
                    <input type="text" name="date_from_filter" id="date_from_filter" class="form-control datepicker">
                </div>
                <div class="p-2">
                    <label class="form-label"></label>
                    <input type="text" name="date_to_filter" id="date_to_filter" class="form-control datepicker">
                </div>
                <div class="p-2">
                    <label class="form-label">Jenis Pelanggan</label>
                    <select class="form-select" name="type" id="type">
                        <option value="All">All</option>
                        <option value="pelanggan">Pelanggan</option>
                        <option value="non">Non Pelanggan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive mb-4">
        <table class="table align-middle datatable dt-responsive table-check nowrap"
            style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama</th>
                    <th scope="col">No Tiket</th>
                    <th scope="col">Tanggal Dibuat</th>
                    <th scope="col">Jenis Pelanggan</th>
                    <th scope="col">Nama Outlet</th>
                    <th scope="col">Karesidenan</th>
                    <th scope="col">Detail</th>
                    <th scope="col">Status</th>
                    <th style="width: 80px; min-width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <!-- end table -->
    </div>

    <x-modal id="modal-detail" title="Detail">
        <x-slot name="body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>Nomor Tiket</td>
                        <td>:</td>
                        <td id="ticket_number"></td>
                    </tr>
                    <tr>
                        <td>Nama Outlet</td>
                        <td>:</td>
                        <td id="outlet_name"></td>
                    </tr>
                    <tr>
                        <td>Karesidenan</td>
                        <td>:</td>
                        <td id="residency_name"></td>
                    </tr>
                    <tr>
                        <td>Kabupaten/Kota</td>
                        <td>:</td>
                        <td id="city_name"></td>
                    </tr>
                    <tr>
                        <td>Kecamatan</td>
                        <td>:</td>
                        <td id="district_name"></td>
                    </tr>
                    <tr>
                        <td>Deskripsi</td>
                        <td>:</td>
                        <td>
                            <textarea id="description" col="10" rows="3" disabled
                                style=" min-width:500px; max-width:100%;min-height:50px;height:100%;width:100%;"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Deskripsi Tambahan</td>
                        <td>:</td>
                        <td>
                            <textarea id="additional_description" col="10" rows="3" disabled
                                style=" min-width:500px; max-width:100%;min-height:50px;height:100%;width:100%;"></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </x-slot>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </x-slot>
    </x-modal>
@endsection

@push('scripts')
    <!-- Required datatable js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- datepicker js -->
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            flatpickr('.datepicker')

            $(".datatable").DataTable({
                responsive: !1,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ $url }}",
                    data: function(d) {
                        d.search = $('input[type="search"]').val(),
                            d.residency = $('#residency_filter').val(),
                            d.city = $('#city_filter').val(),
                            d.district = $('#district_filter').val(),
                            d.date_from = $('#date_from_filter').val(),
                            d.date_to = $('#date_to_filter').val(),
                            d.type = $('#type').val()
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'user.name',
                        name: 'user.name',
                        orderable: false
                    },
                    {
                        data: 'ticket_number',
                        name: 'ticket_number',
                        orderable: false
                    },
                    {
                        data: 'created_txt',
                        name: 'created_txt',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'is_customer',
                        name: 'is_customer',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'outlet_name',
                        name: 'outlet_name',
                        orderable: false
                    },
                    {
                        data: 'residency_name',
                        name: 'residency_name',
                        orderable: false
                    },
                    {
                        data: 'detail',
                        name: 'detail',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_txt',
                        name: 'status_txt',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            })

            $('#residency_filter').on('change', function() {
                let residencyCode = $(this).val()
                citiesFilter(residencyCode)
                $('.datatable').DataTable().draw(true)
            })

            $('#city_filter').on('change', function() {
                let residencyCode = $('#residency_filter').val()
                districtsFilter(residencyCode, $(this).val())
                $('.datatable').DataTable().draw(true)
            })

            $('#district_filter').on('change', function() {
                $('.datatable').DataTable().draw(true)
            })

            $('#date_to_filter').on('change', function() {
                $('.datatable').DataTable().draw(true)
            })

            $('#type').on('change', function() {
                $('.datatable').DataTable().draw(true)
            })
        })
    </script>
@endpush

@push('scripts')
    <script>
        function citiesFilter(residencyCode) {
            let urlCity = '{{ route('cities.show', ['residency' => ':residency']) }}'
            urlCity = urlCity.replace(':residency', residencyCode)
            $.ajax({
                url: urlCity,
                type: "GET",
                cache: false,
                success: function(r) {
                    let data = r.data
                    $('#city_filter').empty()
                    $('#city_filter').append('<option value="All">All</option>')
                    for (let i = 0; i < data.length; i++) {
                        $('#city_filter').append(
                            `<option value="${data[i].kode}">${data[i].nama_kabupaten}</option>`)
                    }
                }
            })
        }

        function districtsFilter(residencyCode, cityCode) {
            let urlDistrict = '{{ route('districts.show', ['residency' => ':residency', 'city' => ':city']) }}'
            urlDistrict = urlDistrict.replace(':residency', residencyCode)
            urlDistrict = urlDistrict.replace(':city', cityCode)
            $.ajax({
                url: urlDistrict,
                type: "GET",
                cache: false,
                success: function(r) {
                    let data = r.data
                    $('#district_filter').empty()
                    $('#district_filter').append('<option value="All">All</option>')
                    for (let i = 0; i < data.length; i++) {
                        $('#district_filter').append(
                            `<option value="${data[i].kode_kecamatan}">${data[i].nama_kecamatan}</option>`)
                    }
                }
            })
        }
    </script>
@endpush

@push('scripts')
    <script>
        function detail(identifier) {
            let urlShow = '{{ route('transactions.show', ':id') }}'
            urlShow = urlShow.replace(':id', identifier.getAttribute('data-id'))
            $.ajax({
                url: urlShow,
                type: 'GET',
                cache: false,
                success: function(result) {
                    if (result.success) {
                        let data = result.data
                        $('#modal-detail #ticket_number').html(data.ticket_number)
                        $('#modal-detail #outlet_name').html(data.outlet_name)
                        $('#modal-detail #residency_name').html(data.residency_name)
                        $('#modal-detail #city_name').html(data.city_name)
                        $('#modal-detail #district_name').html(data.district_name)
                        $('#modal-detail #order_date').html(dateID(data.order_date))
                        $('#modal-detail #delivery_date').html(dateID(data.delivery_date))
                        $('#modal-detail #description').val(data.description)
                        $('#modal-detail #additional_description').val(data.additional_description)
                        $('#vehicle').html(data.vehicle_plate)
                        $('#modal-detail').modal('show')
                    }
                },
                error: function(data, ajaxOptions, thrownError) {
                    Swal.fire({
                        title: thrownError,
                        text: 'Silahkan hubungi helpdesk',
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        }

        function status(identifier, param) {
            let text, title
            if (param == 2) {
                text = 'hold'
                title = 'Holded'
            } else if (param == 3) {
                text = 'close'
                title = 'Closed'
            }
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, " + text + " it!",
                cancelButtonText: "No, cancel!",
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ms-2 mt-2",
                buttonsStyling: !1
            }).then(function(e) {
                if (e.value) {
                    url = '{{ route('complaints.update-status', ':complaints') }}'
                    url = url.replace(':complaints', identifier.getAttribute('data-id'))
                    $.ajax({
                        url: url,
                        type: 'PUT',
                        cache: false,
                        data: {
                            status: param
                        },
                        success: function(r) {
                            if (r.success) {
                                Swal.fire({
                                    title: title,
                                    text: "Your data has been " + title + ".",
                                    icon: "success",
                                    confirmButtonColor: "#5156be"
                                })
                                $(".datatable").DataTable().ajax.reload()
                            }
                        },
                        error: function(data, ajaxOptions, thrownError) {
                            Swal.fire({
                                title: thrownError,
                                text: 'Silahkan hubungi helpdesk',
                                icon: "error",
                                confirmButtonColor: "#5156be"
                            })
                        }
                    })
                } else {
                    Swal.fire({
                        title: "Cancelled",
                        text: "Your data is safe :)",
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        }

        function cancel(identifier) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, cancel it!",
                cancelButtonText: "No, cancel!",
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ms-2 mt-2",
                buttonsStyling: !1
            }).then(function(e) {
                if (e.value) {
                    url = '{{ route('complaints.cancel', ':id') }}'
                    url = url.replace(':id', identifier.getAttribute('data-id'))
                    $.ajax({
                        url: url,
                        type: 'PUT',
                        cache: false,
                        data: {
                            status: 'cancel'
                        },
                        success: function(r) {
                            if (r.success) {
                                Swal.fire({
                                    title: "Cancelled!",
                                    text: "Your ticket has been cancel.",
                                    icon: "success",
                                    confirmButtonColor: "#5156be"
                                })
                                $(".datatable").DataTable().ajax.reload()
                            }
                        },
                        error: function(data, ajaxOptions, thrownError) {
                            Swal.fire({
                                title: thrownError,
                                text: 'Silahkan hubungi helpdesk',
                                icon: "error",
                                confirmButtonColor: "#5156be"
                            })
                        }
                    })
                } else {
                    Swal.fire({
                        title: "Cancelled",
                        text: "Your data is safe :)",
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        }
    </script>
@endpush

@push('scripts')
    <script>
        if ('{{ Session::has('success') }}') {
            Swal.fire({
                title: "Berhasil!",
                text: "{{ Session::get('success') }}.",
                icon: "success",
                confirmButtonColor: "#5156be"
            })
        }
    </script>
@endpush

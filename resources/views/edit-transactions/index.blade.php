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
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center justify-content-start mb-3">
                <div class="p-2">
                    <label class="form-label">Status</label>
                    <select class="form-control form-select" name="status_filter" id="status_filter">
                        <option value="All">All</option>
                        @foreach ($status as $item)
                            <option value="{{ $item->id }}">{{ $item->status_name }}</option>
                        @endforeach
                    </select>
                </div>
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
                    <label class="form-label">Tanggal Order</label>
                    <input type="text" name="order_date_filter" id="order_date_filter" class="form-control datepicker">
                </div>
                <div class="p-2">
                    <label class="form-label">Tanggal Pengiriman</label>
                    <input type="text" name="delivery_date_filter" id="delivery_date_filter"
                        class="form-control datepicker">
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="table-responsive mb-4">
                <table class="table align-middle datatable dt-responsive table-check nowrap"
                    style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nama</th>
                            <th scope="col">No Tiket</th>
                            <th scope="col">Tanggal Dibuat</th>
                            <th scope="col">Nama Outlet</th>
                            <th scope="col">Karesidenan</th>
                            <th scope="col">Tanggal Order</th>
                            <th scope="col">Tanggal Pengiriman</th>
                            <th scope="col">Status</th>
                            <th scope="col">Detail</th>
                            <th style="width: 80px; min-width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <!-- end table -->
            </div>
        </div>
    </div>



    <div class="modal fade" id="modal-detail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <h5 class="modal-title" id="modalLabel">Modal title</h5>
                <div class="modal-header">
                </div>
                <div class="modal-body">
                    <form id="form">
                        @csrf
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
                                    <td>Tanggal Order</td>
                                    <td>:</td>
                                    <td id="order_date"></td>
                                </tr>
                                <tr>
                                    <td>Tanggal Pengiriman</td>
                                    <td>:</td>
                                    <td id="delivery_date"></td>
                                </tr>
                                <tr>
                                    <td>Jam Tiket</td>
                                    <td>:</td>
                                    <td id="ticket_time"></td>
                                </tr>
                                <tr>
                                    <td>Produk</td>
                                    <td>:</td>
                                    <td id="product_list"></td>
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
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeDetail()" class="btn btn-light">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-reject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal title</h5>
                </div>
                <div class="modal-body">
                    <form id="form-reject">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea id="additional_description_cancel" name="additional_description" rows="3" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" onclick="closeCancelModal()">Close</button>
                    <button type="button" onclick="cancel()" class="btn btn-primary">Submit</button>
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

            $(".datatable").DataTable({
                responsive: !1,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ $url }}",
                    data: function(d) {
                        d.statusCode = $('#status_filter').val(),
                            d.search = $('input[type="search"]').val(),
                            d.residency = $('#residency_filter').val(),
                            d.city = $('#city_filter').val(),
                            d.district = $('#district_filter').val(),
                            d.orderDate = $('#order_date_filter').val(),
                            d.deliveryDate = $('#delivery_date_filter').val()
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
                        data: 'order_txt',
                        name: 'order_txt',
                        orderable: false
                    },
                    {
                        data: 'delivery_txt',
                        name: 'delivery_txt',
                        orderable: false
                    },
                    {
                        data: 'status_txt',
                        name: 'status_txt',
                        orderable: false
                    },
                    {
                        data: 'detail',
                        name: 'detail',
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

            $('#residency').select2({
                placeholder: 'Pilih Karesidenan',
                dropdownParent: $('#modal'),
                width: '100%'
            })

            $('#city').select2({
                placeholder: 'Pilih Kabupaten/Kota',
                dropdownParent: $('#modal'),
                width: '100%'
            })

            $('#district').select2({
                placeholder: 'Pilih Kecamatan',
                dropdownParent: $('#modal'),
                width: '100%'
            })

            $('#outlet_code').select2({
                placeholder: 'Pilih Outlet',
                dropdownParent: $('#modal'),
                width: '100%'
            })

            flatpickr('.datepicker')

            $('#status_filter').on('change', function() {
                $('.datatable').DataTable().draw(true)
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

            $('#order_date_filter').on('change', function() {
                $('.datatable').DataTable().draw(true)
            })

            $('#delivery_date_filter').on('change', function() {
                $('.datatable').DataTable().draw(true)
            })
        });
    </script>
@endpush

@push('scripts')
    <script>
        let uniqId

        function cancelModal(identifier) {
            uniqId = identifier.getAttribute('data-id')
            $('#modal-reject').modal('show')
        }

        function closeCancelModal() {
            $('#form-reject').trigger('reset')
            $('#modal-reject').modal('hide')
        }

        function cancel() {
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
                    url = '{{ route('transactions.cancel', ':id') }}'
                    url = url.replace(':id', uniqId)
                    $.ajax({
                        url: url,
                        type: 'PUT',
                        cache: false,
                        data: {
                            description: $('#additional_description_cancel').val(),
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
                                $('#modal-reject').modal('hide')
                                $('#form-reject').trigger('reset')
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

        function closeOrder(identifier) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, close it's order!",
                cancelButtonText: "No, cancel!",
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ms-2 mt-2",
                buttonsStyling: !1
            }).then(function(e) {
                if (e.value) {
                    url = '{{ route('transactions.close', ':id') }}'
                    url = url.replace(':id', identifier.getAttribute('data-id'))
                    $.ajax({
                        url: url,
                        type: 'PUT',
                        cache: false,
                        data: {
                            status: 'close'
                        },
                        success: function(r) {
                            if (r.success) {
                                Swal.fire({
                                    title: "Order Closed!",
                                    text: "Your ticket has been closed.",
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
        let id

        function detail(identifier) {
            id = identifier.getAttribute('data-id')
            let urlShow = '{{ route('transactions.show', ':id') }}'
            urlShow = urlShow.replace(':id', identifier.getAttribute('data-id'))
            $.ajax({
                url: urlShow,
                type: 'GET',
                cache: false,
                success: function(result) {
                    if (result.success) {
                        $('#modalLabel').text('Detail')
                        let data = result.data
                        $('#modal-detail #ticket_number').html(data.ticket_number)
                        $('#modal-detail #outlet_name').html(data.outlet_name)
                        $('#modal-detail #residency_name').html(data.residency_name)
                        $('#modal-detail #city_name').html(data.city_name)
                        $('#modal-detail #district_name').html(data.district_name)
                        $('#modal-detail #order_date').html(dateID(data.order_date))
                        $('#modal-detail #delivery_date').html(dateID(data.delivery_date))
                        $('#modal-detail #ticket_time').html(dateID(data.created_at, true))

                        $('#product_list').html(products(data.details))

                        $('#modal-detail #description').val(data.description)
                        $('#modal-detail #additional_description').val(data.additional_description)

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

        function vehicle(vehiclePlate = null) {
            let url = '{{ route('vehicle.index') }}'
            $.ajax({
                url: url,
                type: 'GET',
                cache: false,
                success: function(result) {
                    $('#modal-detail #modalLabel').html('Detail')
                    let data = result
                    $('#vehicle_plate').empty()
                    $('#vehicle_plate').append('<option></option>')
                    for (let i = 0; i < data.length; i++) {
                        if (vehiclePlate != null) {
                            if (data[i].NO_POL == vehiclePlate) {
                                $('#vehicle_plate').append(
                                    `<option value="${data[i].NO_POL}" selected>${data[i].NO_POL} ${data[i].NAMA}</option>`
                                )
                            }
                        }
                        $('#vehicle_plate').append(
                            `<option value="${data[i].NO_POL}">${data[i].NO_POL} ${data[i].NAMA}</option>`)
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

        function updateVehicle() {
            let urlUpdateVehicle = '{{ route('vehicle.update', ':vehicle') }}'
            urlUpdateVehicle = urlUpdateVehicle.replace(":vehicle", id)

            let formData = $('#modal-detail #form').serialize()

            $.ajax({
                url: urlUpdateVehicle,
                type: 'PUT',
                data: formData,
                cache: false,
                success: function(r) {
                    if (r.success) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Berhasil menginput data.",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        })
                        $('#modal-detail').modal('hide')
                        $(".datatable").DataTable().ajax.reload()
                        $('#modal-detail #form').reset()
                        $('#vehicle_plate').val(null).trigger('change')
                    } else {
                        Swal.fire({
                            title: "Request error",
                            text: "Kendaraan harus diisi",
                            icon: "error",
                            confirmButtonColor: "#5156be"
                        })
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

        function closeDetail() {
            $('#modal-detail').modal('hide')
        }

        function validate(identifier) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, validate it!",
                cancelButtonText: "No, cancel!",
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ms-2 mt-2",
                buttonsStyling: !1
            }).then(function(e) {
                if (e.value) {
                    let urlValidate = '{{ route('transactions.validate', ':transaction') }}'
                    urlValidate = urlValidate.replace(':transaction', identifier.getAttribute('data-id'))
                    $.ajax({
                        url: urlValidate,
                        type: 'PUT',
                        cache: false,
                        success: function(r) {
                            if (r.success) {
                                let title, text
                                if ('{{ Auth::user()->role->id }}' ==
                                    '2629192e-1c3f-477e-a157-4def565dace3') {
                                    title = "Selesai"
                                    text = "Order telah diselesaikan"
                                }
                                if ('{{ Auth::user()->role->id }}' ==
                                    'dc9f2cb5-258e-4039-9b6f-6025a6ae389e') {
                                    title = "Tervalidasi"
                                    text = "Order berhasil ditambahkan"
                                }
                                Swal.fire({
                                    title: title,
                                    text: text,
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
                        text: "Validate has been cancelled:)",
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })

        }

        const products = (data) => {
            let html = ''
            html += '<ul>'
            for (let i = 0; i < data.length; i++) {
                html += `<li>${data[i].product_name} (${data[i].product_code}) (${data[i].qty} ${data[i].unit})</li>`
            }
            html += '</ul>'

            return html
        }
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

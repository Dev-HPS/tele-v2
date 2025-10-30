@extends('layouts.main')

@push('style')
    <style>
        .c-details span {
            font-weight: 300;
            font-size: 13px
        }

        .icon {
            width: 50px;
            height: 50px;
            background-color: #eee;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px
        }

        .badge span {
            background-color: #fffbec;
            width: 60px;
            height: 25px;
            padding-bottom: 3px;
            border-radius: 5px;
            display: flex;
            color: #fed85d;
            justify-content: center;
            align-items: center
        }

        .qty {
            max-width: 90%;
        }
    </style>

    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- datepicker css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-row align-items-center">
                        <div class="icon"> <i class="bx bx-home-circle"></i> </div>
                        <div class="ms-2 c-details">
                            <h6 class="mb-0">{{ $data->outlet_name }}</h6>
                            <span>{{ CustomHelper::parseDate($data->order_date) }}</span>
                        </div>
                    </div>
                    <div class="badge"> <span>{{ $data->sbu_code }}</span> </div>

                </div>
                <div class="absolute">
                    <button type="button" class="btn btn-sm btn-primary mt-2 w-25 float-end edit-outlet">Edit
                        Outlet</button>
                </div>
            </div>
        </div>
        <div class="col-md-12 mt-5">
            <table class="table">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th style="width: 100px;">Jumlah</th>
                        <th>Satuan</th>
                        <th>Gudang</th>
                        <th>Supplier</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->details as $key => $detail)
                        <tr id="{{ $detail->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->product_name }}</td>
                            <td>{{ $detail->qty }}</td>
                            <td>{{ $detail->unit }}</td>
                            <td>
                                @foreach ($warehouse as $wh)
                                    @if ($wh->id_gudang == $detail->warehouse)
                                        {{ $wh->namagd }}
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach ($supplier as $sup)
                                    @if ($sup->kodesup == $detail->supplier)
                                        {{ $sup->nasup }}
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                <button class="btn btn-warning edit" data-product-name="{{ $detail->product_name }}"
                                    data-product-code="{{ $detail->product_code }}"
                                    data-product-type="{{ $detail->product_type }}" data-unit="{{ $detail->unit }}"
                                    data-qty="{{ $detail->qty }}" data-id="{{ $detail->id }}">Edit</button>
                                <button class="btn btn-danger destroy" data-product-name="{{ $detail->product_name }}"
                                    data-product-code="{{ $detail->product_code }}"
                                    data-product-type="{{ $detail->product_type }}" data-unit="{{ $detail->unit }}"
                                    data-qty="{{ $detail->qty }}" data-id="{{ $detail->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal Edit Product</h5>
                </div>
                <div class="modal-body">
                    <form id="form">
                        @csrf
                        <div id="msg" class="alert alert-danger d-none"></div>
                        <div class="form-group mb-3">
                            <label class="form-label">Jenis Produk</label>
                            <select name="product_type" id="product_type" class="form-control form-select"
                                disabled></select>
                            <div id="product_type_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Nama Produk</label>
                            <select name="product_name" id="product_name" class="form-control form-select"
                                disabled></select>
                            <div id="product_name_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Qty</label>
                            <input type="text" onkeypress="return onlyNumberKey(event)" maxlength="3"
                                class="form-control" id="qty" name="qty" min="1">
                            <div id="qty_error" style="color:red;"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Alasan Perubahan Data</label>
                            <textarea name="reason" id="reason" class="form-control" col="10" rows="3" style=""></textarea>
                            <div id="reason_error" style="color:red;"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="close" class="btn btn-light">Close</button>
                    <button type="button" id="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-delete" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal Delete Product</h5>
                </div>
                <div class="modal-body">
                    <form id="form-delete">
                        @csrf
                        @method('DELETE')
                        <div id="msg" class="alert alert-danger d-none"></div>

                        <div class="form-group mb-3">
                            <label class="form-label">Alasan Penghapusan Data</label>
                            <textarea name="reason" id="reason" class="form-control" col="10" rows="3" style=""></textarea>
                            <div id="reason_error" style="color:red;"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="close" class="btn btn-light">Close</button>
                    <button type="button" id="submit-delete" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Outlet --}}
    <div class="modal fade" id="modal-outlet" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal Edit Outlet</h5>
                </div>
                <div class="modal-body">
                    <form id="form">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Karesidenan</label>
                            <select class="form-control form-select" name="residency" id="residency">
                                <option></option>

                                @foreach ($residency as $row)
                                    <option {{ $row->kode_kar == $data->residency ? 'selected' : '' }}
                                        value="{{ $row->kode_kar }}">{{ $row->nama_karesidenan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Kabupaten/Kota</label>
                            <select class="form-control form-select" name="city" id="city">
                            </select>
                            <div id="city_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Kecamatan</label>
                            <select class="form-control form-select" name="district" id="district">
                            </select>
                            <div id="district_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Outlet</label>
                            <select class="form-control form-select" name="outlet_code" id="outlet_code">
                            </select>
                            <div id="outlet_code_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Alasan Perubahan Data</label>
                            <textarea id="reasons" name="reason" class="form-control" col="10" rows="3" style=""
                                required></textarea>
                            <div id="reason_error" style="color:red;"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="close-outlet" class="btn btn-light">Close</button>
                    <button type="button" id="submit-outlet" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- datepicker js -->
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        let id, url, product_code, product_unit, method

        let outlet_code, outlet_name, residency, residency_name, city, city_name, district, district_name, outlet_address,
            outlet_owner, outlet_phone, outlet_longitude, outlet_latitude
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })


            $('#product_type').select2({
                placeholder: 'Pilih Jenis Produk',
                dropdownParent: $('#modal'),
                width: '100%'
            })


            $('#product_name').select2({
                placeholder: 'Pilih Nama Produk',
                dropdownParent: $('#modal'),
                width: '100%'
            })

            $('#product_type').on('select2:select', function(e) {
                let code = e.params.data.id
                products(code)
            })

            $('#product_name').on('select2:select', function(e) {
                let unit = e.params.data.text.split('-')[1]
                product_unit = unit
                let code = e.params.data.id
                product_code = code
            })

            $('#district').on('select2:select', function(e) {
                district = e.params.data.id;
                district_name = e.params.data.text;
            })

            $('#city').on('select2:select', function(e) {
                city = e.params.data.id;
                city_name = e.params.data.text;
            })

            $('#outlet_code').on('select2:select', function(e) {
                outlet_code = e.params.data.id;
                outlet_name = e.params.data.text;
            })

            flatpickr('.datepicker')
        })

        function onlyNumberKey(evt) {
            // Only ASCII character in that range allowed
            let ASCIICode = (evt.which) ? evt.which : evt.keyCode
            if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
                return false
            return true
        }

        // Modal Outlet
        $(document).on('click', '.edit-outlet', function() {
            $('#modal-outlet').modal('show')

            $('#reasons').val('');

            $('#residency').select2({
                placeholder: 'Pilih Karesidenan',
                dropdownParent: $('#modal-outlet'),
                width: '100%'
            })

            $('#city').select2({
                placeholder: 'Pilih Kabupaten/Kota',
                dropdownParent: $('#modal-outlet'),
                width: '100%'
            })

            $('#district').select2({
                placeholder: 'Pilih Kecamatan',
                dropdownParent: $('#modal-outlet'),
                width: '100%'
            })

            $('#outlet_code').select2({
                placeholder: 'Pilih Outlet',
                dropdownParent: $('#modal-outlet'),
                width: '100%'
            })

            $('#reasons').attr({
                dropdownParent: $('#modal-outlet'),
                width: '100%'
            })

            $('#residency').on('select2:select', function(e) {
                residency = e.params.data.id;
                residency_name = e.params.data.text;
                cities(residency)
            })

            cities($('#residency').val())

            function cities(residencyCode, city = null) {
                let urlCity = '{{ route('cities.show', ['residency' => ':residency']) }}'
                urlCity = urlCity.replace(':residency', residencyCode)
                $.ajax({
                    url: urlCity,
                    type: "GET",
                    cache: false,
                    success: function(r) {
                        residency = residencyCode
                        let data = r.data
                        $('#city').empty()
                        $('#district').empty()
                        $('#outlet_code').empty()
                        $('#product_type').empty()
                        $('#outlet_address').val('')
                        $('#city').append('<option></option>')
                        for (let i = 0; i < data.length; i++) {
                            if (city != null) {
                                if (data[i].kode === city) {
                                    $('#city').append(
                                        `<option value="${data[i].kode}" selected>${data[i].nama_kabupaten}</option>`
                                    )
                                }
                            }
                            $('#city').append(
                                `<option value="${data[i].kode}">${data[i].nama_kabupaten}</option>`
                            )
                        }
                    },
                    error: function(data, ajaxOptions, thrownError) {
                        let message
                        if (ajaxOptions == 'timeout') {
                            message = 'Silahkan coba lagi'
                        } else {
                            message = 'Silahkan hubungi helpdesk'
                        }
                        Swal.fire({
                            title: thrownError,
                            text: message,
                            icon: "error",
                            confirmButtonColor: "#5156be"
                        })
                    }
                })
            }

            $('#city').on('select2:select', function(e) {
                let code = e.params.data.id;
                districts(residency, code)
            })

            function districts(residencyCode, cityCode, district = null) {
                let urlDistrict = '{{ route('districts.show', ['residency' => ':residency', 'city' => ':city']) }}'
                urlDistrict = urlDistrict.replace(':residency', residencyCode)
                urlDistrict = urlDistrict.replace(':city', cityCode)
                $.ajax({
                    url: urlDistrict,
                    type: "GET",
                    cache: false,
                    success: function(r) {
                        city = cityCode
                        let data = r.data
                        $('#district').empty()
                        $('#outlet_code').empty()
                        $('#product_type').empty()
                        $('#outlet_address').val('')
                        $('#district').append('<option></option>')
                        for (let i = 0; i < data.length; i++) {
                            if (district != null) {
                                if (data[i].kode_kecamatan == district) {
                                    $('#district').append(
                                        `<option value="${data[i].kode_kecamatan}" selected>${data[i].nama_kecamatan}</option>`
                                    )
                                }
                            }
                            $('#district').append(
                                `<option value="${data[i].kode_kecamatan}">${data[i].nama_kecamatan}</option>`
                            )
                        }
                    },
                    error: function(data, ajaxOptions, thrownError) {
                        let message
                        if (ajaxOptions == 'timeout') {
                            message = 'Silahkan coba lagi'
                        } else {
                            message = 'Silahkan hubungi helpdesk'
                        }
                        Swal.fire({
                            title: thrownError,
                            text: message,
                            icon: "error",
                            confirmButtonColor: "#5156be"
                        })
                    }
                })
            }

            $('#district').on('select2:select', function(e) {
                let code = e.params.data.id
                district = code
                outlet(residency, city, code)
            })

            function outlet(residencyCode, cityCode, districtCode, outlet) {
                let urlOutlet =
                    '{{ route('outlet.index', ['residency' => ':residency', 'city' => ':city', 'district' => ':district']) }}'
                urlOutlet = urlOutlet.replace(':residency', residencyCode)
                urlOutlet = urlOutlet.replace(':city', cityCode)
                urlOutlet = urlOutlet.replace(':district', districtCode)
                $.ajax({
                    url: urlOutlet,
                    type: "GET",
                    cache: false,
                    success: function(r) {
                        district = districtCode
                        let data = r.data
                        $('#outlet_code').empty();
                        $('#product_type').empty()
                        $('#outlet_address').val('')
                        $('#outlet_code').append('<option></option>');
                        for (let i = 0; i < data.length; i++) {
                            if (outlet != null) {
                                if (data[i].outlet_code == outlet) {
                                    $('#outlet_code').append(
                                        `<option value="${data[i].outlet_code}" selected>${data[i].outlet_code} - ${data[i].outlet_name}</option>`
                                    )
                                }
                            }
                            $('#outlet_code').append(
                                `<option value="${data[i].outlet_code}">${data[i].outlet_name}</option>`
                            )
                        }
                    },
                    error: function(data, ajaxOptions, thrownError) {
                        let message
                        if (ajaxOptions == 'timeout') {
                            message = 'Silahkan coba lagi'
                        } else {
                            message = 'Silahkan hubungi helpdesk'
                        }
                        Swal.fire({
                            title: thrownError,
                            text: message,
                            icon: "error",
                            confirmButtonColor: "#5156be"
                        })
                    }
                })
            }

            $('#outlet_code').on('select2:select', function(e) {
                let code = e.params.data.id
                outletDetail(residency, city, district, code)
            })

            function outletDetail(residencyCode, cityCode, districtCode, outlet) {
                let urlOutlet =
                    '{{ route('outlet.detail', ['residency' => ':residency', 'city' => ':city', 'district' => ':district', 'outlet' => ':outlet']) }}'
                urlOutlet = urlOutlet.replace(':residency', residencyCode)
                urlOutlet = urlOutlet.replace(':city', cityCode)
                urlOutlet = urlOutlet.replace(':district', districtCode)
                urlOutlet = urlOutlet.replace(':outlet', outlet)

                $.ajax({
                    url: urlOutlet,
                    type: "GET",
                    cache: false,
                    success: function(r) {
                        outletName = r.outlet_name
                        $('#outlet_address').val(r.outlet_alamat)
                        $('#outlet_phone').val(r.no_telp)

                        outlet_address = r.outlet_alamat;
                        outlet_owner = r.outlet_pemilik;
                        outlet_phone = r.no_telp;
                        outlet_longitude = r.outlet_longitude;
                        outlet_latitude = r.outlet_latitude;
                    },
                    error: function(data, ajaxOptions, thrownError) {
                        let message
                        if (ajaxOptions == 'timeout') {
                            message = 'Silahkan coba lagi'
                        } else {
                            message = 'Silahkan hubungi helpdesk'
                        }
                        Swal.fire({
                            title: thrownError,
                            text: message,
                            icon: "error",
                            confirmButtonColor: "#5156be"
                        })
                    }
                })
            }

        })

        $(document).on('click', '.edit', function() {
            method = 'patch'
            const productName = $(this).attr('data-product-name')
            const productCode = $(this).attr('data-product-code')
            const productType = $(this).attr('data-product-type')
            const qty = $(this).attr('data-qty')
            const unit = $(this).attr('data-unit')

            productTypes(productType)
            id = $(this).attr('data-id')
            product_code = productCode
            product_unit = unit
            $('#qty').val(qty)
            $('#product_name').empty();
            $('#product_name').append(`<option value="${productName}" selected>${productName} - ${unit}</option>`);

            $('#modal').modal('show')
        });

        $(document).on('click', '.destroy', function() {
            const productName = $(this).attr('data-product-name')
            const productCode = $(this).attr('data-product-code')
            const productType = $(this).attr('data-product-type')
            const qty = $(this).attr('data-qty')
            const unit = $(this).attr('data-unit')

            productTypes(productType)
            id = $(this).attr('data-id')
            product_code = productCode
            product_unit = unit
            $('#qty').val(qty)
            $('#product_name').empty();
            $('#product_name').append(`<option value="${productName}" selected>${productName} - ${unit}</option>`);

            $('#modal-delete').modal('show')
        });



        // Update Outlet
        $(document).on('click', '#submit-outlet', function() {
            url = '{{ route('edit-outlet.update', ['transaction' => ':transaction']) }}'
            url = url.replace(":transaction", "{{ $data->id }}");

            const reason = $('#reasons').val();

            let formData = $('#form').serialize() + "&outlet_code=" + outlet_code + "&outlet_name=" + outlet_name +
                "&residency=" + residency + "&residency_name=" + residency_name + "&outlet_address=" +
                outlet_address + "&outlet_owner=" + outlet_owner + "&outlet_phone=" + outlet_phone +
                "&outlet_longitude=" + outlet_longitude + "&outlet_latitude=" + outlet_latitude + "&city=" + city +
                "&city_name=" + city_name + "&district=" + district + "&district_name=" + district_name +
                "&reason=" + $('#reasons').val()

            $.ajax({
                url: url,
                type: "PUT",
                cache: false,
                data: formData,
                beforeSend: function() {
                    spinner_show()
                },
                complete: function() {
                    spinner_hide()
                },
                success: function(r) {
                    if (r.success) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Berhasil mengubah data.",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        })
                        location.reload()
                    } else {
                        let message = r.message

                        Swal.fire({
                            title: "Error",
                            text: message,
                            icon: "error",
                            confirmButtonColor: "#5156be"
                        })
                    }
                },
                error: function(data, ajaxOptions, thrownError) {
                    let message
                    if (ajaxOptions == 'timeout') {
                        message = 'Silahkan coba lagi'
                    } else {
                        message = 'Silahkan hubungi helpdesk'
                    }
                    Swal.fire({
                        title: thrownError,
                        text: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })

        })

        $(document).on('click', '#submit-delete', function() {

            let formData = $('#form-delete').serialize() + "&product_code=" + product_code + "&product_unit=" +
                product_unit + "&id=" + id
            url =
                '{{ route('edit-detail-transactions.delete-detail', ['transaction' => ':transaction', 'detail' => ':detail']) }}'
            url = url.replace(":transaction", "{{ $data->id }}")
            url = url.replace(":detail", id)
            $.ajax({
                url: url,
                type: "DELETE",
                cache: false,
                data: formData,
                beforeSend: function() {
                    spinner_show()
                },
                complete: function() {
                    spinner_hide()
                },
                success: function(r) {
                    if (r.success) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Berhasil. Silahkan menunggu approve dari kepala non op",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload()
                            }
                        });


                    } else {
                        let message = r.message

                        console.log(message)

                        if (typeof message !== 'object' && !Array.isArray(message)) {
                            Swal.fire({
                                title: "Error",
                                text: message,
                                icon: "error",
                                confirmButtonColor: "#5156be"
                            })
                            return true;
                        }
                        for (key in message) {
                            for (key2 in message[key]) {
                                if (key2 == 0) {
                                    $('#' + key + '_error').html(message[key][key2])
                                } else {
                                    $('#' + key + '_error').html(message[key][key2])
                                }
                            }
                        }
                    }
                },
                error: function(data, ajaxOptions, thrownError) {
                    let message
                    if (ajaxOptions == 'timeout') {
                        message = 'Silahkan coba lagi'
                    } else {
                        message = 'Silahkan hubungi helpdesk'
                    }
                    Swal.fire({
                        title: thrownError,
                        text: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        })


        // Update Detail Outlet
        $(document).on('click', '#submit', function() {
            if (method === 'post') {
                store()
                return;
            }
            let formData = $('#form').serialize() + "&product_code=" + product_code + "&product_unit=" +
                product_unit + "&id=" + id
            url =
                '{{ route('edit-detail-transactions.update-detail', ['transaction' => ':transaction', 'detail' => ':detail']) }}'
            url = url.replace(":transaction", "{{ $data->id }}")
            url = url.replace(":detail", id)
            $.ajax({
                url: url,
                type: "PATCH",
                cache: false,
                data: formData,
                beforeSend: function() {
                    spinner_show()
                },
                complete: function() {
                    spinner_hide()
                },
                success: function(r) {
                    if (r.success) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Berhasil. Silahkan menunggu approve dari kepala non op",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload()
                            }
                        });
                    } else {
                        let message = r.message

                        if (typeof message !== 'object' && !Array.isArray(message)) {
                            Swal.fire({
                                title: "Error",
                                text: message,
                                icon: "error",
                                confirmButtonColor: "#5156be"
                            })
                            return true;
                        }
                        for (key in message) {
                            for (key2 in message[key]) {
                                if (key2 == 0) {
                                    $('#' + key + '_error').html(message[key][key2])
                                } else {
                                    $('#' + key + '_error').html(message[key][key2])
                                }
                            }
                        }
                    }
                },
                error: function(data, ajaxOptions, thrownError) {
                    let message
                    if (ajaxOptions == 'timeout') {
                        message = 'Silahkan coba lagi'
                    } else {
                        message = 'Silahkan hubungi helpdesk'
                    }
                    Swal.fire({
                        title: thrownError,
                        text: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        })

        $(document).on('click', '#close', function() {
            $('#form').trigger('reset')
            $('#modal').modal('hide')
            $('#product_type').val(null)
            $('#product_type').trigger('change')
            $('#product_type').empty()
            $('#product_name').val(null)
            $('#product_name').trigger('change')
            $('#product_name').empty()
        })

        $(document).on('click', '#close-outlet', function() {
            $('#form').trigger('reset')
            $('#reasons').val('');
            $('#modal-outlet').modal('hide')
        })

        const productTypes = (type) => {
            const route = '{{ route('product.types') }}'
            $.ajax({
                url: route,
                type: "GET",
                cache: false,
                success: function(r) {
                    let data = r.data
                    $('#product_type').empty();
                    $('#product_type').append('<option></option>');
                    for (let i = 0; i < data.length; i++) {
                        $('#product_type').append(
                            `<option value="${data[i].id}" ${data[i].id === type ? 'selected' : ''}>${data[i].jenisProduct_name}</option>`
                        )
                    }
                },
                error: function(data, ajaxOptions, thrownError) {
                    let message
                    if (ajaxOptions === 'timeout') {
                        message = 'Silahkan coba lagi'
                    } else {
                        message = 'Silahkan hubungi helpdesk'
                    }
                    Swal.fire({
                        title: thrownError,
                        text: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        }

        function products(type) {
            let urlProduct = '{{ route('type.products', ':type') }}'
            urlProduct = urlProduct.replace(':type', type)
            $.ajax({
                url: urlProduct,
                type: "GET",
                cache: false,
                success: function(r) {
                    let data = r.data
                    $('#product_name').empty();
                    $('#product_name').append('<option></option>');
                    for (let i = 0; i < data.length; i++) {
                        $('#product_name').append(
                            `<option value="${data[i].itemProduct_code}">${data[i].itemProduct_name} - ${data[i].itemProduct_satuan}</option>`
                        )
                    }
                },
                error: function(data, ajaxOptions, thrownError) {
                    let message
                    if (ajaxOptions === 'timeout') {
                        message = 'Silahkan coba lagi'
                    } else {
                        message = 'Silahkan hubungi helpdesk'
                    }
                    Swal.fire({
                        title: thrownError,
                        text: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        }

        // $(document).on('click', '.destroy', function() {
        //     const id = $(this).data('id')
        //     const transaction = "{{ $transaction }}"
        //     Swal.fire({
        //         title: "Are you sure?",
        //         text: "You won't be able to revert this!",
        //         icon: "warning",
        //         showCancelButton: !0,
        //         confirmButtonText: "Yes, delete it!",
        //         cancelButtonText: "No, cancel!",
        //         confirmButtonClass: "btn btn-success mt-2",
        //         cancelButtonClass: "btn btn-danger ms-2 mt-2",
        //         buttonsStyling: !1
        //     }).then(function(e) {
        //         if (e.value) {
        //             let url =
        //                 "{{ route('transactions.detail.destroy', ['transaction' => ':transaction', 'detail' => ':detail']) }}"
        //             url = url.replace(':transaction', transaction)
        //             url = url.replace(':detail', id)
        //             $.ajax({
        //                 url: url,
        //                 type: 'DELETE',
        //                 cache: false,
        //                 success: function(r) {
        //                     Swal.fire({
        //                         title: "Deleted!",
        //                         text: "Your data has been deleted.",
        //                         icon: "success",
        //                         confirmButtonColor: "#5156be"
        //                     })
        //                     location.reload()
        //                 },
        //                 error: function(data, ajaxOptions, thrownError) {
        //                     let message
        //                     if (ajaxOptions === 'timeout') {
        //                         message = 'Silahkan coba lagi'
        //                     } else {
        //                         message = 'Silahkan hubungi helpdesk'
        //                     }
        //                     Swal.fire({
        //                         title: thrownError,
        //                         text: message,
        //                         icon: "error",
        //                         confirmButtonColor: "#5156be"
        //                     })
        //                 }
        //             })
        //         } else {
        //             Swal.fire({
        //                 title: "Cancelled",
        //                 text: "Your data is safe :)",
        //                 icon: "error",
        //                 confirmButtonColor: "#5156be"
        //             })
        //         }
        //     })

        // })
    </script>
@endpush

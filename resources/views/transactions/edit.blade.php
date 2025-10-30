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
            </div>
        </div>
        <div class="col-md-8 p-3">
            <button type="button" class="btn btn-md btn-info" onclick="editHeader()">Edit Tanggal Pengiriman</button>
            <button type="button" class="btn btn-md btn-primary float-end" onclick="create()">Tambah Produk</button>
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
                                <button class="btn btn-danger destroy" data-id="{{ $detail->id }}">Delete</button>
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
                    <h5 class="modal-title" id="modalLabel">Modal title</h5>
                </div>
                <div class="modal-body">
                    <form id="form">
                        @csrf
                        <div id="msg" class="alert alert-danger d-none"></div>
                        <div class="form-group mb-3">
                            <label class="form-label">Jenis Produk</label>
                            <select name="product_type" id="product_type" class="form-control form-select"></select>
                            <div id="product_type_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Nama Produk</label>
                            <select name="product_name" id="product_name" class="form-control form-select"></select>
                            <div id="product_name_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Qty</label>
                            <input type="text" onkeypress="return onlyNumberKey(event)" maxlength="3"
                                class="form-control" id="qty" name="qty" min="1">
                            <div id="qty_error" style="color:red;"></div>
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

    <div class="modal fade" id="modal-header" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelHeader">Modal title</h5>
                </div>
                <div class="modal-body">
                    <form id="form">
                        @csrf
                        <div id="msg" class="alert alert-danger d-none"></div>
                        <div class="form-group mb-3">
                            <label class="form-label">Tanggal Pengiriman</label>
                            <input type="text" required
                                data-pristine-required-message="Tanggal Pengiriman tidak boleh kosong"
                                value="{{ $data->delivery_date }}" name="delivery_date" class="form-control datepicker"
                                id="delivery_date" placeholder="Tanggal Pengiriman">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="close-header" class="btn btn-light"
                        onclick="closeHeader()">Close</button>
                    <button type="button" id="submit-header" class="btn btn-primary"
                        onclick="storeHeader()">Save</button>
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

            flatpickr('.datepicker')

        })

        function onlyNumberKey(evt) {
            // Only ASCII character in that range allowed
            let ASCIICode = (evt.which) ? evt.which : evt.keyCode
            if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
                return false
            return true
        }

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
        })

        $(document).on('click', '#submit', function() {
            if (method === 'post') {
                store()
                return;
            }
            let formData = $('#form').serialize() + "&product_code=" + product_code + "&product_unit=" +
                product_unit
            url =
                '{{ route('transactions.update-detail', ['transaction' => ':transaction', 'detail' => ':detail']) }}'
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
                            text: "Berhasil mengubah data.",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        })
                        location.reload()
                    } else {
                        let message = r.message
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

        function create() {
            method = 'post'
            $('#modalLabel').html('Tambah Produk')
            $('#modal').modal('show')
            productTypes(null)
        }

        function store() {
            const transaction = "{{ $transaction }}"
            url = "{{ route('transactions.store.detail', ':transaction') }}"
            url = url.replace(':transaction', transaction)
            const formData = $('#form').serialize() + "&product_code=" + product_code + "&product_unit=" + product_unit
            $.ajax({
                url: url,
                type: "POST",
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
                            text: "Berhasil menambah produk.",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        })
                        location.reload()
                    } else {
                        let message = r.message
                        for (let key in message) {
                            for (let key2 in message[key]) {
                                if (key2 === 0) {
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

        function editHeader() {
            $('#modal-header').modal('show')
            $('#modalLabelHeader').html('Edit Tanggal Pengiriman')
        }

        function closeHeader() {
            $('#modal-header').modal('hide')
        }

        function storeHeader() {
            const transaction = "{{ $transaction }}"
            url = "{{ route('transactions.updateDeliveryDate', ':transaction') }}"
            url = url.replace(':transaction', transaction)
            $.ajax({
                url: url,
                type: "PATCH",
                cache: false,
                data: {
                    delivery_date: $('#delivery_date').val()
                },
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
                            text: "Berhasil mengubah tanggal pengiriman.",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        })
                        location.reload()
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

        $(document).on('click', '.destroy', function() {
            const id = $(this).data('id')
            const transaction = "{{ $transaction }}"
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ms-2 mt-2",
                buttonsStyling: !1
            }).then(function(e) {
                if (e.value) {
                    let url =
                        "{{ route('transactions.detail.destroy', ['transaction' => ':transaction', 'detail' => ':detail']) }}"
                    url = url.replace(':transaction', transaction)
                    url = url.replace(':detail', id)
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        cache: false,
                        success: function(r) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "Your data has been deleted.",
                                icon: "success",
                                confirmButtonColor: "#5156be"
                            })
                            location.reload()
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
                } else {
                    Swal.fire({
                        title: "Cancelled",
                        text: "Your data is safe :)",
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })

        })
    </script>
@endpush

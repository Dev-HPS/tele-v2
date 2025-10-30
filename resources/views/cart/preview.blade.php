@extends('layouts.main')

@push('style')
    <!-- datepicker css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
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

        .c-details span {
            font-weight: 300;
            font-size: 13px
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card p-3 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-row align-items-center">
                        <div class="icon"> <i class="bx bx-home-circle"></i> </div>
                        <div class="ms-2 c-details">
                            <h6 class="mb-0">{{ $cart[0]['outlet_name'] }}</h6>
                            <span>{{ $cart[0]['outlet_address'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div class="card">
                <div class="card-header border border-0">
                    <button class="btn btn-success" id="checkout">Checkout</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0" id="table">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Produk</th>
                                    <th style="max-width: 80%;">Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cart as $item)
                                    <tr id="{{ $item['id'] }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item['product_name'] }}</td>
                                        <td>
                                            <input type="text" onkeypress="return onlyNumberKey(event)" maxlength="3" class="form-control form-control-sm qty" min="1" value="{{ $item['qty'] }}">
                                        </td>
                                        <td>{{ $item['unit'] }}</td>
                                        <td>
                                            <button type="button" class="btn btn-danger" id="remove" data-id="{{ $item['product_code'] }}_{{ $item['outlet_code'] }}">x</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal title</h5>
                </div>
                <div class="modal-body">
                    <form id="form">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Tanggal Order</label>
                            <input type="text" required data-pristine-required-message="Tanggal Order tidak boleh kosong"
                            name="order_date" class="form-control datepicker" id="order_date" placeholder="Tanggal Order">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Tanggal Pengiriman</label>
                            <input type="text" required data-pristine-required-message="Tanggal Pengiriman tidak boleh kosong"
                             name="delivery_date" class="form-control datepicker" id="delivery_date" placeholder="Tanggal Pengiriman">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea rows="4" class="form-control" name="description" id="description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="close">Close</button>
                    <button type="button" id="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- datepicker js -->
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pristinejs/pristine.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            flatpickr('.datepicker')

            $('.warehouse').select2({
                placeholder: 'Pilih Gudang',
                width: '100%'
            })

            $('.supplier').select2({
                placeholder: 'Pilih Supplier',
                width: '100%'
            })
        })

        let pristine = new Pristine(document.querySelector('#form'), {
            // class of the parent element where the error/success class is added
            classTo: 'form-group',
            errorClass: 'has-danger',
            successClass: 'has-success',
            // class of the parent element where error text element is appended
            errorTextParent: 'form-group',
            // type of element to create for the error text
            errorTextTag: 'div',
            // class of the error text element
            errorTextClass: 'text-help'
        }, false)

        $(document).on('change', '.qty', function () {
            const id = $(this).parents('tr').attr('id')
            let url = '{{ route("cart.update", ":cart") }}'
            url = url.replace(':cart', id)
            $.ajax({
                url: url,
                type: 'PATCH',
                data: {
                    qty: $(this).val()
                },
                cache: false,
                beforeSend: function(){
                    spinner_show()
                },
                complete: function(){
                    spinner_hide()
                },
                success: function (r) {
                    window.location.reload()
                },
                error: function (data, ajaxOptions, thrownError) {
                    let message
                    if(ajaxOptions == 'timeout') {
                        message = 'Silahkan coba lagi'
                    }
                    else {
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

        function onlyNumberKey(evt) {
            // Only ASCII character in that range allowed
            let ASCIICode = (evt.which) ? evt.which : evt.keyCode
            if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
                return false
            return true
        }

        $(document).on('click', '#remove', function(){
            const id = $(this).data('id')
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
                if(e.value) {
                    let url = '{{ route("cart.destroy", ":cart") }}'
                    url = url.replace(':cart', id)
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        cache: false,
                        success: function (r) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "Your data has been deleted.",
                                icon: "success",
                                confirmButtonColor: "#5156be"
                            })
                            location.reload()
                        },
                        error: function (data, ajaxOptions, thrownError) {
                            let message
                            if(ajaxOptions == 'timeout') {
                                message = 'Silahkan coba lagi'
                            }
                            else {
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
                else {
                    Swal.fire({
                        title: "Cancelled",
                        text: "Your data is safe :)",
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })

        })

        $('#checkout').on('click', function () {
            $('#modalLabel').html('Checkout')
            $('#modal').modal('show')
            const cart = @json($cart);
            let html = ''
            for(const key in cart) {
                html += `${cart[key].qty} ${cart[key].unit} ${cart[key].product_name}, `
            }
            $('#description').val(html)
        })

        document.getElementById('submit').onclick = () => {
            let valid = pristine.validate()

            if(valid) {
                const outlet = '{{ $outlet }}'
                let url = '{{ route("cart.checkout", ":outlet") }}'
                url = url.replace(':outlet', outlet)
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $('#form').serialize(),
                    cache: false,
                    beforeSend: function(){
                        spinner_show()
                    },
                    complete: function(){
                        spinner_hide()
                    },
                    success: function (r) {
                        if(r.success) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: "Berhasil menginput data.",
                                icon: "success",
                                confirmButtonColor: "#5156be"
                            })
                            window.location.href = '{{ route("transactions.index") }}'
                        }
                        else {
                            if(r.dso) {
                                Swal.fire({
                                    title: 'Error',
                                    text: r.message,
                                    icon: "error",
                                    confirmButtonColor: "#5156be"
                                })
                                spinner_hide()
                            }
                            let message = r.message
                            for (key in message) {
                                for (key2 in message[key]) {
                                    if(key2 == 0){
                                        pristine.addError(document.getElementById(key), message[key][key2])
                                    }else{
                                        pristine.addError(document.getElementById(key), message[key][key2])
                                    }
                                }
                            }
                        }
                    },
                    error: function (data, ajaxOptions, thrownError) {
                        let message
                        if(ajaxOptions === 'timeout') {
                            message = 'Silahkan coba lagi'
                        }
                        else {
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
        }

        document.getElementById('close').onclick = () => {
            $('#modal').modal('hide')
            $('#form')[0].reset()
            pristine.reset()
        }
    </script>
@endpush

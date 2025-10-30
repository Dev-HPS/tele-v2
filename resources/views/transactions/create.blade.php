@extends('layouts.main')

@push('style')
    <!-- datepicker css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .card-img-top {
            width: 100%;
            max-height: 20vh;
            object-fit: contain;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4">
            <form id="form">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">Karesidenan</label>
                    <select required data-pristine-required-message="Karesidenan tidak boleh kosong"
                        class="form-control form-select" name="residency" id="residency">
                        <option></option>
                        @foreach ($residency as $row)
                            <option value="{{ $row->kode_kar }}">{{ $row->nama_karesidenan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Kabupaten/Kota</label>
                    <select required data-pristine-required-message="Kabupaten/Kota tidak boleh kosong"
                        class="form-control form-select" name="city" id="city">
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Kecamatan</label>
                    <select required data-pristine-required-message="Kecamatan tidak boleh kosong"
                        class="form-control form-select" name="district" id="district">
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Outlet</label>
                    <select required data-pristine-required-message="Outlet tidak boleh kosong"
                        class="form-control form-select" name="outlet_code" id="outlet_code">
                    </select>
                    <div id="outlet-validation-status" class="mt-2"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Jenis Produk</label>
                    <select required data-pristine-required-message="Jenis Produk tidak boleh kosong"
                        class="form-control form-select" name="product_type" id="product_type">
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Alamat Outlet</label>
                    <textarea rows="4" class="form-control" name="outlet_address" id="outlet_address" disabled></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">No Telp Outlet</label>
                    <input type="text" name="outlet_phone" id="outlet_phone" class="form-control" disabled>
                </div>
            </form>
        </div>
        <div class="col-md-8">
            <div class="row mb-3">
                <div class="col-md-4" id="product-search"></div>
                <div class="col-md-8 float-end" id="cart-btn"></div>
            </div>
            <form id="product-form">
                <div class="row" id="product"></div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/pristinejs/pristine.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- datepicker js -->
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let residency, city, district, outletName
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#residency').select2({
                placeholder: 'Pilih Karesidenan',
                width: '100%'
            })

            $('#city').select2({
                placeholder: 'Pilih Kabupaten/Kota',
                width: '100%'
            })

            $('#district').select2({
                placeholder: 'Pilih Kecamatan',
                width: '100%'
            })

            $('#outlet_code').select2({
                placeholder: 'Pilih Outlet',
                width: '100%'
            })

            $('#product_type').select2({
                placeholder: 'Pilih Jenis Produk',
                width: '100%'
            })

            flatpickr('.datepicker')
        })
    </script>
@endpush

@push('scripts')
    <script>
        // Pre-populate data from dashboard if available
        @if (isset($prePopulate) && $prePopulate['outlet_code'])
            $(document).ready(function() {
                // Pre-populate residency
                @if ($prePopulate['residency'])
                    $('#residency').val('{{ $prePopulate['residency'] }}').trigger('change');

                    // Wait a bit for the cities to load, then populate city
                    setTimeout(function() {
                        @if ($prePopulate['city'])
                            cities('{{ $prePopulate['residency'] }}', '{{ $prePopulate['city'] }}');

                            // Wait for districts to load, then populate district
                            setTimeout(function() {
                                @if ($prePopulate['district'])
                                    districts('{{ $prePopulate['residency'] }}',
                                        '{{ $prePopulate['city'] }}',
                                        '{{ $prePopulate['district'] }}');

                                    // Wait for outlets to load, then populate outlet
                                    setTimeout(function() {
                                        outlet('{{ $prePopulate['residency'] }}',
                                            '{{ $prePopulate['city'] }}',
                                            '{{ $prePopulate['district'] }}',
                                            '{{ $prePopulate['outlet_code'] }}');

                                        // Wait for outlet to be selected, then check validation and load product types
                                        setTimeout(function() {
                                            $('#outlet_code').val(
                                                '{{ $prePopulate['outlet_code'] }}'
                                                ).trigger('change');

                                            // Trigger outlet detail load and validation check
                                            outletDetail(
                                                '{{ $prePopulate['residency'] }}',
                                                '{{ $prePopulate['city'] }}',
                                                '{{ $prePopulate['district'] }}',
                                                '{{ $prePopulate['outlet_code'] }}'
                                                );

                                            // Check outlet validation and display status
                                            checkOutletValidation(
                                                '{{ $prePopulate['outlet_code'] }}'
                                                );

                                            // Load product types
                                            productTypes();
                                        }, 500);
                                    }, 500);
                                @endif
                            }, 500);
                        @endif
                    }, 500);
                @endif
            });
        @endif

        $('#residency').on('select2:select', function(e) {
            let code = e.params.data.id;
            cities(code)
        })

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
                    $('#outlet_phone').val('')
                    emptyProducts()
                    $('#city').append('<option></option>')
                    for (let i = 0; i < data.length; i++) {
                        if (city != null && data[i].kode === city) {
                            $('#city').append(
                                `<option value="${data[i].kode}" selected>${data[i].nama_kabupaten}</option>`
                            )
                        } else {
                            $('#city').append(
                                `<option value="${data[i].kode}">${data[i].nama_kabupaten}</option>`)
                        }
                    }

                    // If city is pre-populated, don't trigger change (handled by pre-populate flow)
                    // Manual selection will trigger via select2:select event
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
                    emptyProducts()
                    $('#outlet_address').val('')
                    $('#outlet_phone').val('')
                    $('#district').append('<option></option>')
                    for (let i = 0; i < data.length; i++) {
                        if (district != null && data[i].kode_kecamatan == district) {
                            $('#district').append(
                                `<option value="${data[i].kode_kecamatan}" selected>${data[i].nama_kecamatan}</option>`
                            )
                        } else {
                            $('#district').append(
                                `<option value="${data[i].kode_kecamatan}">${data[i].nama_kecamatan}</option>`
                            )
                        }
                    }

                    // If district is pre-populated, don't trigger change (handled by pre-populate flow)
                    // Manual selection will trigger via select2:select event
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
                    emptyProducts()
                    $('#outlet_address').val('')
                    $('#outlet_phone').val('')
                    $('#outlet_code').append('<option></option>');
                    for (let i = 0; i < data.length; i++) {
                        if (outlet != null && data[i].outlet_code == outlet) {
                            $('#outlet_code').append(
                                `<option value="${data[i].outlet_code}" selected>${data[i].outlet_code} - ${data[i].outlet_name}</option>`
                            )
                        } else {
                            $('#outlet_code').append(
                                `<option value="${data[i].outlet_code}">${data[i].outlet_name}</option>`)
                        }
                    }

                    // If outlet is pre-populated, don't trigger change (handled by pre-populate flow)
                    // Manual selection will trigger via select2:select event
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
            emptyProducts()
            outletDetail(residency, city, district, code)
            productTypes()
            checkOutletValidation(code)
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

        $('#product_type').on('select2:select', function(e) {
            let code = e.params.data.id
            products(code)
        })
    </script>
@endpush

@push('scripts')
    <script>
        let productList = []
        let form = document.querySelector('#form')
        let submitButton = document.querySelector('#submit')

        function skeleton() {
            var output = '';
            for (var count = 0; count < 2; count++) {
                output += `<div class="col-md-4">
                    <div class="ph-item border border-0">
                        <div class="ph-col-12">
                            <div class="ph-picture"></div>
                            <div class="ph-row">
                                <div class="ph-col-6 big"></div>
                                <div class="ph-col-4 empty big"></div>
                                <div class="ph-col-2 big"></div>
                                <div class="ph-col-4"></div>
                                <div class="ph-col-8 empty"></div>
                                <div class="ph-col-6"></div>
                                <div class="ph-col-6 empty"></div>
                                <div class="ph-col-12"></div>
                            </div>
                        </div>
                    </div>
                </div>`
            }
            return output;
        }

        function products(type) {
            let urlProduct = '{{ route('type.products', ':type') }}'
            urlProduct = urlProduct.replace(':type', type)
            $.ajax({
                url: urlProduct,
                type: "GET",
                cache: false,
                beforeSend: function() {
                    $('#product').html(skeleton())
                },
                success: function(r) {
                    let data = r.data
                    productList = data
                    searchBox()
                    cartButton()
                    loadProducts(data)
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

        function loadProducts(data) {
            let html = '';
            for (let i = 0; i < data.length; i++) {
                html += `<div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <img class="card-img-top img-fluid" src="{{ CustomHelper::productURL('${data[i].itemProduct_pic}') }}" onerror="this.onerror=null;this.src='https://public.hastaprimasolusi.com/product/not-found.png';" alt="product image cap">
                                <div class="card-body">
                                    <h4 class="card-title">${data[i].itemProduct_name} (${data[i].itemProduct_code})</h4>
                                    <div class="row">
                                        <div class="col-8">
                                            <input type="hidden" name="item_code[]" value="${data[i].itemProduct_code}">
                                            <input type="hidden" name="item_name[]" value="${data[i].itemProduct_name}">
                                            <input type="hidden" name="item_unit[]" value="${data[i].itemProduct_satuan}">
                                            <input type="hidden" name="item_picture[]" value="${data[i].itemProduct_pic}">
                                            <input type="number" class="form-control" name="qty[]" min="0" placeholder="Jumlah">
                                        </div>
                                        <div class="col-4">
                                            <span class="badge bg-success fs-6">${data[i].itemProduct_satuan}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`
            }
            $('#product').html(html)
        }

        function search() {
            let toSearch = $('#search').val()
            $('#product').html('')

            let result = productList.filter(product => product.itemProduct_name.toLowerCase().indexOf(toSearch
                .toLowerCase()) >= 0)
            loadProducts(result)
        }

        function searchBox() {
            let htmlSearchProduct =
                `<input type="text" name="search" id="search" class="form-control" onkeyup="search()" placeholder="Cari nama produk">`
            $('#product-search').html(htmlSearchProduct)
        }

        const productTypes = () => {
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
                            `<option value="${data[i].id}">${data[i].jenisProduct_name}</option>`)
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

        const cartButton = () => {
            let btn = '<button class="btn btn-primary" onclick="addToCart()">Tambah ke keranjang</button>'
            $('#cart-btn').html(btn)
        }

        const addToCart = () => {
            const formData = $('#product-form').serialize() + "&outlet_code=" + $('#outlet_code').val() +
                "&outlet_name=" + outletName + "&outlet_address=" + $('#outlet_address').val() + "&product_type=" + $(
                    '#product_type').val()
            $.ajax({
                url: '{{ route('cart.store') }}',
                type: 'POST',
                data: formData,
                cache: false,
                beforeSend: function() {
                    spinner_show()
                },
                complete: function() {
                    spinner_hide()
                },
                success: function(r) {
                    Swal.fire({
                        title: "Berhasil!",
                        text: "Berhasil ditambahkan ke keranjang.",
                        icon: "success",
                        confirmButtonColor: "#5156be"
                    })
                    cart()
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    let message = 'Terjadi kesalahan, silahkan hubungi helpdesk'

                    // Handle validation error dari server
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message
                    } else if (ajaxOptions == 'timeout') {
                        message = 'Silahkan coba lagi'
                    }

                    Swal.fire({
                        title: thrownError || "Error!",
                        text: message,
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        }

        const emptyProducts = () => {
            $('#product-search').html('')
            $('#product').html('')
            $('#cart-btn').html('')
            $('#outlet-validation-status').html('')
        }

        const checkOutletValidation = (outletCode) => {
            $.ajax({
                url: '{{ route('cart.check-outlet-validation') }}',
                type: 'POST',
                data: {
                    outlet_code: outletCode
                },
                cache: false,
                success: function(r) {
                    if (r.success && r.validation) {
                        displayValidationStatus(r.validation)
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.error('Error checking outlet validation:', thrownError)
                }
            })
        }

        const displayValidationStatus = (validation) => {
            let html = ''
            let alertClass = 'alert-info'
            let icon = 'bx-info-circle'

            if (validation.status) {
                alertClass = 'alert-success'
                icon = 'bx-check-circle'
            } else {
                alertClass = 'alert-danger'
                icon = 'bx-x-circle'
            }

            html = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="bx ${icon} me-2"></i>
                        <strong>Status Validasi Outlet:</strong> ${validation.message}
                    </div>`

            $('#outlet-validation-status').html(html)
        }
    </script>
@endpush

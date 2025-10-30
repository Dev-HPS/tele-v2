@extends('layouts.main')

@push('style')
    <!-- datepicker css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* .card-img-top {
                                                        width: 100%;
                                                        max-height: 20vh;
                                                        object-fit: contain;
                                                    } */
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4">
            <form id="form">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">SBU</label>
                    <select required data-pristine-required-message="SBU tidak boleh kosong" class="form-control form-select"
                        name="sbu_code" id="sbu_code">
                        <option></option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">TP</label>
                    <select required data-pristine-required-message="TP tidak boleh kosong" class="form-control form-select"
                        name="tp" id="tp">
                        <option></option>
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
                        class="form-control form-select" name="outlet_code[]" id="outlet_code" multiple>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Hari</label>
                    <select required data-pristine-required-message="Jaro tidak boleh kosong"
                        class="form-control form-select" name="day" id="day">
                        <option></option>
                        @foreach ($day as $row)
                            <option value="{{ $row }}">{{ $row }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn btn-primary" type="button" onclick="store()">Tambah</button>
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
        let tp, city, district, outletName
        $(document).ready(function() {
            let residency, city, district, outletName
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#sbu_code').select2({
                placeholder: 'Pilih SBU',
                width: '100%'
            })

            $('#tp').select2({
                placeholder: 'Pilih TP',
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

            $('#day').select2({
                placeholder: 'Pilih Hari',
                width: '100%'
            })

            flatpickr('.datepicker')

            // Load SBU options on page load
            loadSbuOptions()
        })
    </script>
@endpush

@push('scripts')
    <script>
        function loadSbuOptions() {
            $.ajax({
                url: "{{ route('outlet-call.get-sbu-options') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#sbu_code').empty().append('<option></option>');
                        $.each(response.data, function(index, sbu) {
                            $('#sbu_code').append('<option value="' + sbu.sbu_code + '">' + sbu
                                .sbu_name + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading SBU options:', error);
                }
            });
        }

        $('#sbu_code').on('select2:select', function(e) {
            let sbuCode = e.params.data.id;
            loadTpBySbu(sbuCode);

            // Reset dependent dropdowns
            $('#tp').empty().append('<option></option>');
            $('#city').empty().append('<option></option>');
            $('#district').empty().append('<option></option>');
            $('#outlet_code').empty().append('<option></option>');
        })

        function loadTpBySbu(sbuCode) {
            $.ajax({
                url: "{{ route('outlet-call.get-tp') }}",
                type: 'GET',
                data: {
                    sbu_code: sbuCode
                },
                success: function(response) {
                    if (response.status && response.data) {
                        const data = response.data;
                        const $tp = $('#tp');
                        $tp.empty().append('<option></option>');
                        for (let i = 0; i < data.length; i++) {
                            $tp.append(`<option value="${data[i].kodetp}">${data[i].nama_tp}</option>`);
                        }
                        // pastikan state select2 sinkron
                        $tp.val(null).trigger('change');
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.data || 'Gagal memuat data TP',
                            icon: 'error',
                            confirmButtonColor: '#5156be'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat data TP',
                        icon: 'error',
                        confirmButtonColor: '#5156be'
                    });
                }
            });
        }

        $('#tp').on('change', function() {
            const code = this.value; // sama dengan $('#tp').val()
            // guard: jika user clear/select placeholder
            if (!code) return;
            console.log('tpCode:', code);
            cities(code);
        });

        function cities(tpCode, city = null) {
            let sbuCode = $('#sbu_code').val();
            let urlCity = '{{ route('cities-outlet-call.show', ['tp' => ':tp']) }}'
            urlCity = urlCity.replace(':tp', tpCode)
            $.ajax({
                url: urlCity,
                type: "GET",
                data: {
                    sbu_code: sbuCode
                },
                cache: false,
                success: function(r) {
                    if (r.status && r.data) {
                        tp = tpCode
                        let data = r.data
                        $('#city').empty()
                        $('#district').empty()
                        $('#outlet_code').empty()
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
                                `<option value="${data[i].kode}">${data[i].nama_kabupaten}</option>`)
                        }
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: r.data || 'Gagal memuat data kabupaten/kota',
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
        }

        $('#city').on('select2:select', function(e) {
            let code = e.params.data.id;
            districts(tp, code)
        })

        function districts(tpCode, cityCode, district = null) {
            let sbuCode = $('#sbu_code').val();
            let urlDistrict = '{{ route('districts-outlet-call.show', ['tp' => ':tp', 'city' => ':city']) }}'
            urlDistrict = urlDistrict.replace(':tp', tpCode)
            urlDistrict = urlDistrict.replace(':city', cityCode)
            $.ajax({
                url: urlDistrict,
                type: "GET",
                data: {
                    sbu_code: sbuCode
                },
                cache: false,
                success: function(r) {
                    if (r.status && r.data) {
                        city = cityCode
                        let data = r.data
                        $('#district').empty()
                        $('#outlet_code').empty()
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
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: r.data || 'Gagal memuat data kecamatan',
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
        }

        $('#district').on('select2:select', function(e) {
            let code = e.params.data.id
            district = code
            outlet(tp, city, code)
        })

        function outlet(tpCode, cityCode, districtCode, outlet) {
            let sbuCode = $('#sbu_code').val();
            let urlOutlet =
                '{{ route('data-outlet-call.show', ['tp' => ':tp', 'city' => ':city', 'district' => ':district']) }}'
            urlOutlet = urlOutlet.replace(':tp', tpCode)
            urlOutlet = urlOutlet.replace(':city', cityCode)
            urlOutlet = urlOutlet.replace(':district', districtCode)
            console.log(tpCode)
            console.log(cityCode)
            console.log(districtCode)
            $.ajax({
                url: urlOutlet,
                type: "GET",
                data: {
                    sbu_code: sbuCode
                },
                cache: false,
                success: function(r) {
                    if (r.status && r.data) {
                        district = districtCode
                        let data = r.data
                        $('#outlet_code').empty();
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
                                `<option value="${JSON.stringify(data[i]).replace(/"/g, '&quot;')}">${data[i].outlet_name}</option>`
                            );
                        }
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: r.data || 'Gagal memuat data outlet',
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
        }

        const store = () => {
            const formData = $('#form').serialize();
            $.ajax({
                url: '{{ route('outlet-call.store') }}',
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
                        text: "Berhasil menambahkan data",
                        icon: "success",
                        confirmButtonColor: "#5156be"
                    })
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

            return false;
        }
    </script>
@endpush

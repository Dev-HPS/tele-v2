@extends('layouts.main')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-md-3">
            <form id="form">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">Jenis Pelanggan</label>
                    <select class="form-control form-select" required 
                    name="type" id="type">
                        <option selected disabled>Pilih Jenis Pelanggan</option>
                        <option value="pelanggan">Pelanggan</option>
                        <option value="non">Non Pelanggan</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Karesidenan</label>
                    <select
                     class="form-control form-select" name="residency" id="residency">
                        <option></option>
                        @foreach ($residency as $row)
                            <option value="{{ $row->kode_kar }}">{{ $row->nama_karesidenan }}</option>
                        @endforeach
                    </select>
                    <div id="residency_error" class="text-danger"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Kabupaten/Kota</label>
                    <select
                     class="form-control form-select" name="city" id="city">
                    </select>
                    <div id="city_error" class="text-danger"></div>

                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Kecamatan</label>
                    <select
                     class="form-control form-select" name="district" id="district">
                    </select>
                    <div id="district_error" class="text-danger"></div>
                </div>
                <div class="form-group mb-3 customer">
                    <label class="form-label">Outlet</label>
                    <select
                     class="form-control form-select" name="outlet_code" id="outlet_code">
                    </select>
                    <div id="outlet_code_error" class="text-danger"></div>
                </div>
                <div class="form-group mb-3 non">
                    <label class="form-label">Outlet</label>
                    <input type="text" name="outlet_name" id="outlet_name" class="form-control">
                    <input type="hidden" name="residency_name" id="residency_name">
                    <input type="hidden" name="city_name" id="city_name">
                    <input type="hidden" name="district_name" id="district_name">
                    <div id="outlet_name_error" class="text-danger"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea rows="4"
                     class="form-control" name="description" id="description"></textarea>    
                    <div id="description_error" class="text-danger"></div>
                </div>
            </form>
            <div class="float-start">
                <button id="submit" onclick="save()" class="btn btn-primary">Simpan</button>
            </div>
        </div>
        <div class="col-md-9 mt-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Piutang</h5>
                    <table class="table table-striped" id="credit-table">
                        <thead>
                            <tr>
                                <th scope="col">No Invoice</th>
                                <th scope="col">Jatuh Tempo</th>
                                <th scope="col">Tanggal Transaksi</th>
                                <th scope="col">Tanggal Bayar</th>
                                <th scope="col">Jumlah Piutang</th>
                                <th scope="col">Bayar</th>
                            </tr>
                        </thead>
                    </table>    
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/pristinejs/pristine.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Required datatable js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    
    <!-- Responsive examples -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js" integrity="sha512-42PE0rd+wZ2hNXftlM78BSehIGzezNeQuzihiBCvUEB3CVxHvsShF86wBWwQORNxNINlBPuq7rG4WWhNiTVHFg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endpush

@push('scripts')
    <script>
        let datatable
        $(document).ready(function () {
            moment.locale('id')
            let residency,city, district
    
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

            $('.customer').hide()
            $('.non').hide()

            datatable = $('#credit-table').DataTable({
                responsive: true,
                data: [],
                columns: [
                    {
                        data: 'no_inv'
                    },
                    {
                        data: 'tgltempo',
                        render: function(d) {
                            return moment(d).format("LL");
                        }
                    },
                    {
                        data: 'tgltrans',
                        render: function(d) {
                            return moment(d).format("LL");
                        }
                    },
                    {
                        data: 'tglbayar',
                        render: function(d) {
                            return moment(d).format("LL");
                        }
                    },
                    {
                        data: 'jum_piutang',
                        render: $.fn.dataTable.render.number( '.', ',', 0, 'Rp ' )
                    },
                    {
                        data: 'bayar',
                        render: $.fn.dataTable.render.number( '.', ',', 0, 'Rp ' )
                    },
                ]
            })
        })

        $('#residency').on('select2:select', function (e) {
            let code = e.params.data.id;
            let type = $('#type').val()
            
            if(type == 'non') {
                $('#residency_name').val(e.params.data.text)
            }
            cities(code)
            $('#district').empty()
            $('#outlet_code').empty()
        })

        function cities(residencyCode, city = null) {
            let urlCity = '{{ route("cities.show", ["residency" => ":residency"]) }}'
            urlCity = urlCity.replace(':residency', residencyCode)
            $.ajax({
                url: urlCity,
                type: "GET",
                cache: false,
                success: function (r) {
                    residency = residencyCode
                    let data = r.data
                    $('#city').empty()
                    $('#city').append('<option></option>')
                    for(let i = 0; i < data.length; i++) {
                        if(city != null) {
                            if(data[i].kode == city) {
                                $('#city').append(`<option value="${data[i].kode}" selected>${data[i].nama_kabupaten}</option>`)
                            }
                        }
                        $('#city').append(`<option value="${data[i].kode}">${data[i].nama_kabupaten}</option>`)
                    }
                }
            })
        }

        $('#city').on('select2:select', function (e) {
            let code = e.params.data.id;
            let type = $('#type').val()
            
            if(type == 'non') {
                $('#city_name').val(e.params.data.text)
            }
            districts(residency, code)
            $('#outlet_code').empty()
        })

        function districts(residencyCode, cityCode, district = null) {
            let urlDistrict = '{{ route("districts.show", ["residency" => ":residency", "city" => ":city"]) }}'
            urlDistrict = urlDistrict.replace(':residency', residencyCode)
            urlDistrict = urlDistrict.replace(':city', cityCode)
            $.ajax({
                url: urlDistrict,
                type: "GET",
                cache: false,
                success: function (r) {
                    city = cityCode
                    let data = r.data
                    $('#district').empty()
                    $('#district').append('<option></option>')
                    for(let i = 0; i < data.length; i++) {
                        if(district != null) {
                            if(data[i].kode_kecamatan == district) {
                                $('#district').append(`<option value="${data[i].kode_kecamatan}" selected>${data[i].nama_kecamatan}</option>`)
                            }
                        }
                        $('#district').append(`<option value="${data[i].kode_kecamatan}">${data[i].nama_kecamatan}</option>`)
                    }
                }
            })
        }

        $('#district').on('select2:select', function (e) {
            let code =  e.params.data.id
            let type = $('#type').val()
            if(type == 'pelanggan') {
                outlet(residency, city, code)
            }
            else {
                $('#district_name').val(e.params.data.text)
            }
        })

        function outlet(residencyCode, cityCode, districtCode, outlet) {
            let urlOutlet = '{{ route("outlet.index", ["residency" => ":residency", "city" => ":city", "district" => ":district"]) }}'
            urlOutlet = urlOutlet.replace(':residency', residencyCode)
            urlOutlet = urlOutlet.replace(':city', cityCode)
            urlOutlet = urlOutlet.replace(':district', districtCode)
            $.ajax({
                url: urlOutlet,
                type: "GET",
                cache: false,
                success: function (r) {
                    district = districtCode
                    let data = r.data
                    $('#outlet_code').empty();
                    $('#outlet_code').append('<option></option>')
                    for(let i = 0; i < data.length; i++) {
                        if(outlet != null) {
                            if(data[i].outlet_code == outlet) {
                                $('#outlet_code').append(`<option value="${data[i].outlet_code}" selected>${data[i].outlet_name}</option>`)
                            }
                        }
                        $('#outlet_code').append(`<option value="${data[i].outlet_code}">${data[i].outlet_name}</option>`)
                    }
                }
            })
        }

        $('#outlet_code').on('select2:select', function (e) {
            let code =  e.params.data.id
            creditByOutlet(code)
        })

        function creditByOutlet(outlet) {
            let urlCredit = '{{ route("outlet.credit", ":outlet") }}'
            urlCredit = urlCredit.replace(':outlet', outlet)

            $.ajax({
                url: urlCredit,
                type: "GET",
                cache: false,
                success: function (r) {
                    let data = r
                    datatable.clear()
                    $.each(data, function(index, value) {
                        datatable.row.add(value)    
                    })
                    datatable.draw()
                }
            })
        }
    </script>
@endpush

@push('scripts')
    <script>
        $('#type').on('change', function () {
            let type = $(this).val()

            if(type == 'pelanggan') {
                $('.customer').show()
                $('.non').hide()
            }
            else {
                $('.customer').hide()
                $('.non').show()
            }
        })

        function save() {
            let formData = $('#form').serialize()
            
            $.ajax({
                url: '{{ route("inquiry.store") }}',
                type: "POST",
                data: formData,
                cache: false,
                success: function (r) {
                    if(r.success) {
                        window.location.href = r.redirect
                    }
                    else {
                        let message = r.message
                        for (key in message) {
                            for (key2 in message[key]) {
                                if(key2 == 0){
                                    $('#'+ key + '_error').html(message[key][key2]) 
                                }else{
                                    $('#' + key + '_error').html(message[key][key2])
                                }
                            }
                        }     
                    }
                },
                error: function (data, ajaxOptions, thrownError) {
                    Swal.fire({
                        title: thrownError,
                        text: 'Silahkan hubungi helpdesk',
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            })
        }
    </script>
@endpush

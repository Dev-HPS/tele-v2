@extends('layouts.main')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row">
        <div class="col-md-6">
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
                     class="form-control" name="description" id="description" disabled></textarea>    
                    <div id="description_error" class="text-danger"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Deskripsi Tambahan</label>
                    <textarea rows="4"
                     class="form-control" name="additional_description" id="additional_description"></textarea>    
                    <div id="additional_description_error" class="text-danger"></div>
                </div>
            </form>
            <div class="float-start">
                <button id="submit" onclick="save()" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/pristinejs/pristine.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
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

            let data = @json($data)
            
            $('#residency').val(data.residency)
            $('#residency').trigger('change')
            
            cities(data.residency, data.city)
            districts(data.residency, data.city, data.district)

            if(data.outlet_code != null) {
                outlet(data.residency, data.city, data.district, data.outlet_code)
                $('#type').val('pelanggan')
                $('.customer').show()
                $('.non').hide()
            }
            else {
                $('#type').val('non')
                $('.customer').hide()
                $('.non').show()
                $('#outlet_name').val(data.outlet_name)
                $('#residency_name').val(data.residency_name)
                $('#city_name').val(data.city_name)
                $('#district_name').val(data.district_name)
            }

            $('#description').val(data.description)
            $('#additional_description').val(data.additional_description)
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
            outlet(residency, city, code)
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
            let url = '{{ route("complaints.update", ":id") }}'
            url = url.replace(':id', '{{ $complaints }}')
            $.ajax({
                url: url,
                type: "PUT",
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

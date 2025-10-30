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

@php
    use App\Models\Transaction;
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <table class="table align-middle datatable dt-responsive table-check nowrap"
                            style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Kode Order</th>
                                    <th scope="col">SBU</th>
                                    <th scope="col">Tanggal Order</th>
                                    <th scope="col">Kode Outlet</th>
                                    <th scope="col">Nama Outlet</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    @php
                                        $data = Transaction::where('order_code', $item->ORDER_CODE)->first();
                                    @endphp

                                    @if ($data)
                                        @continue;
                                    @endif
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->ORDER_CODE }}</td>
                                        <td>{{ $item->ORDER_SBU }}</td>
                                        <td>{{ \App\Helpers\CustomHelper::parseDate($item->ORDER_DATE, true) }}</td>
                                        <td>{{ $item->ORDER_BUYER_INFO->DCODE }}</td>
                                        <td>{{ $item->ORDER_BUYER_INFO->NAME }}</td>
                                        <td>
                                            <button type="button" class="btn btn-md btn-primary"
                                                onclick="detail('{{ $item->ORDER_ID }}', '{{ $item->ORDER_BUYER_INFO->DCODE }}')">Detail</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- end table -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Modal title</h5>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td>Kode Order</td>
                                <td>:</td>
                                <td id="order_code"></td>
                            </tr>
                            <tr>
                                <td>Kode Outlet</td>
                                <td>:</td>
                                <td id="outlet_code"></td>
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
                                <td>Produk</td>
                                <td>:</td>
                                <td id="product_list"></td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td>:</td>
                                <td>
                                    <textarea id="description" col="10" rows="3" name="description" class="form-control"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" id="close" class="btn btn-light">Close</button>
                    <button type="button" id="submit" class="btn btn-primary">Submit</button>
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
        let identifier, dsoOrder, outlet
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })

            $(".datatable").DataTable()

            flatpickr('.datepicker')

        });

        function detail(id, outletCode) {
            let url = '{{ route('dso-order.detail', ['id' => ':id', 'outlet' => ':outlet']) }}'
            url = url.replace(':id', id)
            url = url.replace(':outlet', outletCode)
            $.ajax({
                url: url,
                type: 'GET',
                cache: false,
                success: function(result) {
                    if (result.success) {
                        if (result.exist) {
                            $('#submit').hide()
                        }
                        identifier = id
                        dsoOrder = result.order
                        outlet = result.outlet
                        $('#modalLabel').html('Detail Order')
                        $('#order_code').html(result.order.ORDER_CODE)
                        $('#outlet_code').html(result.outlet.outlet_code)
                        $('#outlet_name').html(result.outlet.outlet_name)
                        $('#residency_name').html(result.outlet.nama_karesidenan)
                        $('#city_name').html(result.outlet.nama_kabupaten)
                        $('#district_name').html(result.outlet.nama_kecamatan)
                        $('#order_date').html(dateID(result.order.ORDER_DATE))
                        $('#delivery_date').html(result.order.ORDER_ADD_INFO.TGL_KIRIM === '' ? '' : dateID(
                            result.order.ORDER_ADD_INFO.TGL_KIRIM))
                        $('#product_list').html(products(result.order.ORDER_PRODUCT_INFO))
                        $('#modal').modal('show')
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

        function products(data) {
            let html = ''
            html += '<ul>'
            for (let i = 0; i < data.length; i++) {
                html += `<li>${data[i].PROD_CODE} - ${data[i].PROD_NAME} (${data[i].PROD_QTY} ${data[i].PROD_UNIT})</li>`
            }
            html += '</ul>'

            return html
        }

        document.getElementById('close').onclick = () => {
            $('#modal').modal('hide')
        }

        document.getElementById('submit').onclick = () => {
            if ($('#description').val() == '') {
                Swal.fire({
                    title: 'Error',
                    text: 'Silahkan isi deskripsi',
                    icon: "error",
                    confirmButtonColor: "#5156be"
                })
                return
            }
            let url = '{{ route('dso-order.store', ['id' => ':id']) }}'
            url = url.replace(':id', identifier)
            $.ajax({
                url: url,
                type: 'POST',
                cache: false,
                data: {
                    order: dsoOrder,
                    outlet: outlet,
                    description: $('#description').val()
                },
                success: function(r) {
                    if (r.success) {
                        Swal.fire({
                            title: "Created!",
                            text: "Your order has been created.",
                            icon: "success",
                            confirmButtonColor: "#5156be"
                        })
                        $('#modal').modal('hide')
                        $('#description').val('')
                    } else {
                        Swal.fire({
                            title: 'Terjadi Kesalahan',
                            text: 'Silahkan hubungi helpdesk',
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
    </script>
@endpush

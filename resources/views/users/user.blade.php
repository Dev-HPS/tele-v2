@extends('layouts.main')

@push('style')
    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- select2 css -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row align-items-center">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center justify-content-start gap-2 mb-3">
                <div>
                    <a href="#" onclick="create()" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah</a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="table-responsive mb-4">
        <table class="table align-middle datatable dt-responsive table-check nowrap"
            style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">SBU</th>
                    <th scope="col">Karesidenan</th>
                    <th scope="col">Role</th>
                    <th style="width: 80px; min-width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <!-- end table -->
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
                            <label class="form-label">Full Name</label>
                            <input type="text" required name="name" class="form-control" id="name"
                                placeholder="Enter Full Name">
                            <div id="name_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" required name="username" class="form-control" id="username"
                                placeholder="Enter username">
                            <div id="username_error" style="color:red;"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Role</label>
                            <select required class="form-control form-select" name="role_id" id="role_id">
                                <option selected disabled>Pilih Role</option>
                                @foreach ($roles as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <div id="role_id_error" style="color:red;"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">SBU</label>
                            <select required class="form-control form-select" name="sbu_code" id="sbu_code">
                                <option selected disabled>Pilih SBU</option>
                                @foreach ($branch as $item)
                                    <option value="{{ $item->group_code }}">{{ $item->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Karesidenan</label>
                            <select required data-pristine-required-message="Karesidenan tidak boleh kosong"
                                class="form-control form-select js-example-basic-multiple" name="residency[]" id="residency"
                                multiple="multiple">
                            </select>
                        </div>
                        <div class="form-group mb-3" id="tp_section" style="display: none;">
                            <label class="form-label">TP</label>
                            <select class="form-control form-select js-example-basic-multiple" name="tp[]" id="tp"
                                multiple="multiple">
                            </select>
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
@endsection

@push('scripts')
    <!-- Required datatable js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pristinejs/pristine.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(".datatable").DataTable({
                responsive: !1,
                processing: true,
                serverSide: true,
                ajax: "{{ $url }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        'orderable': false,
                        'searchable': false
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'sbu_code',
                        name: 'sbu_code'
                    },
                    {
                        data: 'residency_raw',
                        name: 'residency_raw'
                    },
                    {
                        data: 'role.name',
                        name: 'role.name'
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
                dropdownParent: $('#modal'),
                placeholder: 'Pilih Karesidenan',
                width: '100%'
            });

            $('#tp').select2({
                dropdownParent: $('#modal'),
                placeholder: 'Pilih TP',
                width: '100%'
            });

            // Handle role change to show/hide TP section
            $('#role_id').on('change', function() {
                var roleId = $(this).val();
                if (roleId === '2629192e-1c3f-477e-a157-4def565dace3' || roleId ===
                    'a4e960e0-467f-4534-9555-f06ab8b901f7') {
                    $('#tp_section').show();
                    loadTpBySbu($('#sbu_code').val());
                } else {
                    $('#tp_section').hide();
                    $('#tp').val(null).trigger('change');
                }
            });

            // Handle SBU change to load TP options
            $('#sbu_code').on('change', function() {
                var sbuCode = $(this).val();
                var roleId = $('#role_id').val();

                if (roleId === '2629192e-1c3f-477e-a157-4def565dace3' || roleId ===
                    'a4e960e0-467f-4534-9555-f06ab8b901f7') {
                    loadTpBySbu(sbuCode);
                }

                // Original residency logic
                residency(sbuCode);
            });
        });
    </script>
@endpush

@push('scripts')
    <script>
        let modalSelector = document.getElementById('modal')
        let modal = new bootstrap.Modal(modalSelector)
        let form = modalSelector.querySelector('#form')
        let submitButton = modalSelector.querySelector('#submit')
        let closeButton = modalSelector.querySelector('#close')
        let url, method

        let pristine = new Pristine(form, {
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

        function create() {
            modalSelector.querySelector('#modalLabel').innerHTML = 'Tambah User'
            method = 'POST'
            url = '{{ route('users.store') }}'
            modal.show()
        }

        async function edit(identifier) {
            let urlShow = '{{ route('users.show', ':id') }}'
            urlShow = urlShow.replace(':id', identifier.getAttribute('data-id'))

            try {
                let response = await fetch(urlShow);

                let result = await response.json();
                if (result.success) {
                    modalSelector.querySelector('#modalLabel').innerHTML = 'Edit User'
                    method = 'PUT'
                    url = '{{ route('users.update', ':id') }}'
                    url = url.replace(':id', identifier.getAttribute('data-id'))

                    form.querySelector('#name').value = result.data.name
                    form.querySelector('#username').value = result.data.username
                    form.querySelector('#sbu_code').value = result.data.sbu_code
                    form.querySelector('#role_id').value = result.data.role_id

                    // Handle role-specific visibility
                    if (result.data.role_id === '2629192e-1c3f-477e-a157-4def565dace3' || result.data.role_id ===
                        'a4e960e0-467f-4534-9555-f06ab8b901f7') {
                        $('#tp_section').show();
                    } else {
                        $('#tp_section').hide();
                    }

                    // Handle residency
                    let details = result.data.user_details.map((item) => {
                        return item.residency
                    });
                    let mapped = result.data.user_details.map(item => ({
                        [item.residency]: item.residency
                    }));
                    let newObj = Object.assign({}, ...mapped);
                    residency(result.data.sbu_code, newObj)

                    // Handle TP data if exists
                    if (result.data.user_tp && result.data.user_tp.length > 0) {
                        let selectedTp = result.data.user_tp.map(item => item.tp);
                        loadTpBySbu(result.data.sbu_code, selectedTp);
                    } else if (result.data.role_id === '2629192e-1c3f-477e-a157-4def565dace3' || result.data.role_id ===
                        'a4e960e0-467f-4534-9555-f06ab8b901f7') {
                        // If role requires TP but no TP assigned, just
                        loadTpBySbu(result.data.sbu_code);
                    }
                    modal.show()
                } else {
                    Swal.fire({
                        title: "Request error",
                        text: "Please try again!",
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    })
                }
            } catch (error) {
                Swal.fire({
                    title: "Request errors",
                    text: "Please try again!",
                    icon: "error",
                    confirmButtonColor: "#5156be"
                })
            }

        }

        submitButton.addEventListener('click', function(e) {
            e.preventDefault()

            let valid = pristine.validate()

            if (valid) {
                let formData = $('#form').serialize()

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    cache: false,
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: "Berhasil",
                                icon: "success",
                                confirmButtonColor: "#5156be"
                            })
                            modal.hide()
                            $(".datatable").DataTable().ajax.reload()
                            form.reset()
                            pristine.reset()
                        } else {
                            let message = r.message
                            for (let key in message) {
                                for (let key2 in message[key]) {
                                    if (key2 === 0) {
                                        pristine.addError(document.getElementById(key), message[key][
                                            key2
                                        ])
                                    } else {
                                        pristine.addError(document.getElementById(key), message[key][
                                            key2
                                        ])
                                    }
                                }
                            }
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
        })

        function destroy(identifier) {
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
                    url = '{{ route('users.destroy', ':id') }}'
                    url = url.replace(':id', identifier.getAttribute('data-id'))
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        cache: false,
                        success: function(r) {
                            if (r.success) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Your data has been deleted.",
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

        closeButton.addEventListener('click', function(e) {
            e.preventDefault()
            modal.hide()
            form.reset()
            pristine.reset()
            $('#residency').val(null)
            $('#residency').trigger('change')
            $('#tp').val(null)
            $('#tp').trigger('change')
            $('#tp_section').hide()
        })
    </script>
@endpush

@push('scripts')
    <script>
        $("#sbu_code").change(function() {
            const value = $(this).val()
            residency(value)
        });

        function residency(sbu, data = []) {
            let urlResidence = '{{ route('getResidence', ['sbu' => ':sbu']) }}'
            urlResidence = urlResidence.replace(':sbu', sbu)
            $.ajax({
                url: urlResidence,
                type: "GET",
                cache: false,
                success: function(r) {
                    $('#residency').empty()
                    let selectedVal = ''
                    for (let i = 0; i < r.length; i++) {
                        let index = r[i].kode_kar
                        if (Object.keys(data).length > 0 && index in data) {
                            selectedVal = 'selected'
                        } else {
                            selectedVal = ''
                        }
                        $('#residency').append(
                            `<option value="${r[i].kode_kar}" ${selectedVal}>${r[i].nama_karesidenan}</option>`
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

        function loadTpBySbu(sbu, selectedTp = []) {
            if (!sbu) {
                $('#tp').empty();
                return;
            }

            let urlTp = '{{ route('users.getTpBySbu', ['sbu' => ':sbu']) }}';
            urlTp = urlTp.replace(':sbu', sbu);

            $.ajax({
                url: urlTp,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#tp').empty();
                    if (response.success && response.data.length > 0) {
                        for (let i = 0; i < response.data.length; i++) {
                            let tp = response.data[i];
                            let isSelected = selectedTp.includes(tp.tp) ? 'selected' : '';
                            $('#tp').append(
                                `<option value="${tp.kodetp}" ${isSelected}>${tp.nama_tp}</option>`
                            );
                        }
                        $('#tp').trigger('change');
                    }
                },
                error: function(data, ajaxOptions, thrownError) {
                    console.error('Error loading TP:', thrownError);
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat data TP',
                        icon: "error",
                        confirmButtonColor: "#5156be"
                    });
                }
            });
        }
    </script>
@endpush

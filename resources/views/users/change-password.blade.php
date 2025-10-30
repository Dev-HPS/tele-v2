@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <form method="post" action="{{ route('update-password') }}">
                @csrf
                @if (Session::has('success'))
                    <div class="alert alert-success" role="alert">
                        {{ Session::get('success') }}
                    </div>
                @endif
                @if (Session::has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ Session::get('error') }}
                    </div>
                @endif
                <div class="form-group mb-3">
                    <label class="form-label">Password Lama</label>
                    <input type="password" name="old_password" class="form-control" id="old_password" placeholder="Masukkan password lama">
                    @error('old_password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Password Baru</label>
                    <div class="input-group auth-pass-inputgroup">
                        <input type="password" id="new_password" name="new_password"
                        class="form-control" placeholder="Masukkan password baru" aria-label="Password" aria-describedby="password-addon">
                        <button class="btn btn-light shadow-none ms-0" type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                    </div>
                    @error('new_password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" class="form-control" id="new_password_confirmation" placeholder="Masukkan password baru">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary w-25 waves-effect waves-light" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- password addon init -->
    <script src="{{ asset('assets/js/pages/pass-addon.init.js') }}"></script>

@endpush
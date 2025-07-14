@extends('layout.app')
@section('content')

<!-- Page Heading -->
<h1 class="h3 mb-4 text-gray-800">Edit Karyawan</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Formulir Edit Karyawan</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div class="form-group row">
                <label for="nama" class="col-sm-3 col-form-label">Nama Lengkap:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $karyawan->nama) }}" placeholder="Masukkan Nama Lengkap">
                </div>
            </div>

            {{-- Email --}}
            <div class="form-group row">
                <label for="email" class="col-sm-3 col-form-label">Email:</label>
                <div class="col-sm-9">
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $karyawan->email) }}" placeholder="Masukkan Email">
                </div>
            </div>

            {{-- Checkbox Jadikan Admin --}}
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Jadikan Admin:</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="role" name="role" value="admin" onchange="togglePasswordField()" {{ old('role', $karyawan->role) == 'admin' ? 'checked' : '' }}>
                        <label class="form-check-label" for="role">Centang jika ingin menjadikan user sebagai admin</label>
                    </div>
                </div>
            </div>

            {{-- Password: hanya tampil jika checkbox dicentang --}}
            <div class="form-group row" id="password-group" style="display: none;">
                <label for="password" class="col-sm-3 col-form-label">Password Baru:</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                </div>
            </div>

            {{-- Phone --}}
            <div class="form-group row">
                <label for="phone" class="col-sm-3 col-form-label">Nomor HP:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $karyawan->phone) }}" placeholder="Masukkan Nomor HP (Opsional)">
                </div>
            </div>

            {{-- Address --}}
            <div class="form-group row">
                <label for="address" class="col-sm-3 col-form-label">Alamat:</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="address" name="address" placeholder="Masukkan Alamat">{{ old('address', $karyawan->address) }}</textarea>
                </div>
            </div>

            {{-- Image --}}
            <div class="form-group row">
                <label for="image" class="col-sm-3 col-form-label">Foto Karyawan:</label>
                <div class="col-sm-9">
                    <input type="file" class="form-control-file" id="image" name="image">
                    @if(isset($karyawan) && $karyawan->image)
                        <small>Foto saat ini: <br>
                        <img src="{{ asset('storage/foto_karyawan/' . $karyawan->image) }}" alt="Foto" class="img-thumbnail mt-2" width="150">
                        </small>
                    @endif
                </div>
            </div>


            {{-- Tombol --}}
            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Script untuk toggle password --}}
<script>
    function togglePasswordField() {
        const checkbox = document.getElementById('role');
        const passwordGroup = document.getElementById('password-group');
        if (checkbox.checked) {
            passwordGroup.style.display = 'flex';
        } else {
            passwordGroup.style.display = 'none';
        }
    }

    // Panggil di awal saat halaman dimuat
    window.onload = togglePasswordField;
</script>

@endsection

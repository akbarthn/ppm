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

        <form action="{{ route('karyawan.update', $karyawan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group row">
                <label for="nama" class="col-sm-3 col-form-label">Nama Lengkap:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $karyawan->nama) }}" placeholder="Masukkan Nama Lengkap">
                </div>
            </div>

            <div class="form-group row">
                <label for="email" class="col-sm-3 col-form-label">Email:</label>
                <div class="col-sm-9">
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $karyawan->email) }}" placeholder="Masukkan Email">
                </div>
            </div>

            <div class="form-group row">
                <label for="phone" class="col-sm-3 col-form-label">Nomor HP:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $karyawan->phone) }}" placeholder="Masukkan Nomor HP (Opsional)">
                </div>
            </div>

            <div class="form-group row">
                <label for="address" class="col-sm-3 col-form-label">Alamat:</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="address" name="address" placeholder="Masukkan Alamat">{{ old('address', $karyawan->address) }}</textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layout.app')

@section('content')
<div class="container">
    <h3>Edit Profil</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $user->nama) }}">
        </div>

        <div class="form-group">
            <label>Foto</label><br>
            @if($user->image)
                <img src="{{ asset('foto_karyawan/' . $user->image) }}" width="100" class="mb-2">
            @endif
            <input type="file" name="image" class="form-control-file">
        </div>

        <button type="submit" class="btn btn-primary" >Simpan</button>
        <a href="{{ route('profil.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection

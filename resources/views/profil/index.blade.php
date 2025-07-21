@extends('layout.app') {{-- sesuaikan dengan layout kamu --}}
@section('content')

<div class="container mt-4">
    <h2>Profil Saya</h2>

    <div class="card mt-3">
        <div class="card-body">
            <p><strong>Nama:</strong> {{ $user->nama }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Nomor Telepon:</strong> {{ $user->phone }}</p>
            <p><strong>Alamat:</strong> {{ $user->address }}</p>
            <p><strong>Role:</strong> {{ $user->role }}</p>

            @if($user->image)
                <p><strong>Foto:</strong></p>
                <img src="{{ asset('foto_karyawan/' . $user->image) }}" width="150">
            @else
                <p><strong>Foto:</strong> Belum ada</p>
            @endif
        </div>

        <a href="{{ route('profil.edit') }}" class="btn btn-primary mt-3">Edit</a>
        
        <a href="{{ route('dashboard.index') }}" class="btn btn-danger mt-3">Keluar</a>
        

    </div>
</div>

@endsection

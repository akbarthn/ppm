@extends('layout.app')
@section('content')
<div class="container">
    <h2>Buat Jadwal</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('jadwal.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="start_date">Tanggal Mulai:</label>
            <input type="date" class="form-control" name="start_date" required>
        </div>

        <div class="form-group">
            <label for="end_date">Tanggal Selesai:</label>
            <input type="date" class="form-control" name="end_date" required>
        </div>

        <hr>
        <h4>Jadwal Per Hari</h4>

        @php
            $hariList = [
                'monday' => 'Senin',
                'tuesday' => 'Selasa',
                'wednesday' => 'Rabu',
                'thursday' => 'Kamis',
                'friday' => 'Jumat',
                'saturday' => 'Sabtu',
                'sunday' => 'Minggu'
            ];
        @endphp

        @foreach($hariList as $key => $label)
            <div class="card mb-3">
                <div class="card-header">
                    <strong>{{ $label }}</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Shift:</label>
                        <select name="jadwal[{{ $key }}][shift]" class="form-control">
                            <option value="">-- Pilih Shift --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label>Karyawan:</label>
                    <div class="row">
                        @foreach($users as $user)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="jadwal[{{ $key }}][users][]"
                                           value="{{ $user->id }}"
                                           id="check-{{ $key }}-{{ $user->id }}">
                                    <label class="form-check-label" for="check-{{ $key }}-{{ $user->id }}">
                                        {{ $user->nama }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
        <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection

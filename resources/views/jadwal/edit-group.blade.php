@extends('layout.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Jadwal Grup</h2>

    <form action="{{ route('jadwal.updateGroup') }}" method="POST" id="jadwalForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="date_schedule" value="{{ $date }}">
        <input type="hidden" name="id_shift" value="{{ $shiftId }}">

        {{-- Tanggal --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Tanggal:</label>
            <input type="text" class="form-control" 
                   value="{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}" 
                   readonly>
        </div>

        {{-- Shift --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Shift:</label>
            @php
                $shift = optional($jadwal->first())->shift;
            @endphp
            <input type="text" class="form-control" 
                   value="{{ $shift ? $shift->name . ' (' . $shift->start . ' - ' . $shift->end . ')' : 'Data shift tidak ditemukan' }}" 
                   readonly>
        </div>

        {{-- Checkbox karyawan --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Pilih Karyawan:</label><br>
            @foreach ($users as $user)
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="users[]" 
                           value="{{ $user->id }}"
                           id="user_{{ $user->id }}"
                           {{ in_array($user->id, $jadwal->pluck('id_user')->toArray()) ? 'checked' : '' }}>
                    <label class="form-check-label" for="user_{{ $user->id }}">
                        {{ $user->nama }}
                    </label>
                </div>
            @endforeach
        </div>

        {{-- Tombol aksi --}}
        <div class="mt-4">
            <button class="btn btn-success" type="submit">Simpan Perubahan</button>
            <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

{{-- Script untuk validasi agar minimal 1 karyawan dipilih --}}
<script>
document.getElementById('jadwalForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('input[name="users[]"]:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu karyawan sebelum menyimpan.');
    }
});
</script>
@endsection

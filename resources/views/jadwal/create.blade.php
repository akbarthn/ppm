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
            <input type="date" class="form-control" name="start_date" id="start_date" required>
        </div>

        <div class="form-group">
            <label for="end_date">Tanggal Selesai:</label>
            <input type="date" class="form-control" name="end_date" id="end_date" required>
        </div>

        <div class="form-group">
            <label for="shift_id">Shift (Berlaku untuk Semua Hari):</label>
            <select name="shift_id" id="shift_id" class="form-control" required>
                <option value="">-- Pilih Shift --</option>
                @foreach($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                @endforeach
            </select>
        </div>

        <hr>
        <h4>Jadwal Per Hari</h4>
        <div id="jadwalContainer"></div>

        <button type="submit" class="btn btn-primary mt-3">Simpan Jadwal</button>
        <a href="{{ route('jadwal.index') }}" class="btn btn-secondary mt-3">Batal</a>
    </form>
</div>

@php
$hariLabel = [
'sunday' => 'Minggu',
'monday' => 'Senin',
'tuesday' => 'Selasa',
'wednesday' => 'Rabu',
'thursday' => 'Kamis',
'friday' => 'Jumat',
'saturday' => 'Sabtu',
];
@endphp

<script>
    const users = @json($users);
    const hariLabel = @json($hariLabel);

    document.getElementById('start_date').addEventListener('change', renderHari);
    document.getElementById('end_date').addEventListener('change', renderHari);
    document.getElementById('shift_id').addEventListener('change', renderHari);

    function renderHari() {
        const start = new Date(document.getElementById('start_date').value);
        const end = new Date(document.getElementById('end_date').value);
        const shift = document.getElementById('shift_id').value;
        const container = document.getElementById('jadwalContainer');

        container.innerHTML = ''; // Bersihkan

        if (!start || !end || !shift || start > end) return;

        const hariUnik = new Set();

        let current = new Date(start);
        while (current <= end) {
            const dayName = current.toLocaleDateString('en-US', {
                weekday: 'long'
            }).toLowerCase(); // 'monday'
            hariUnik.add(dayName);
            current.setDate(current.getDate() + 1);
        }

        // Buat kartu untuk tiap hari unik
        hariUnik.forEach(hariKey => {
            const card = document.createElement('div');
            card.classList.add('card', 'mb-3');

            card.innerHTML = `
    <div class="card-header"><strong>${hariLabel[hariKey]}</strong></div>
    <div class="card-body">
        <label>Karyawan:</label>
        <div class="row">
            ${users.filter(user => user.role === null).map(user => `
                <div class="col-md-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="jadwal[${hariKey}][]" value="${user.id}" id="check-${hariKey}-${user.id}">
                        <label class="form-check-label" for="check-${hariKey}-${user.id}">${user.nama}</label>
                    </div>
                </div>
            `).join('')}
        </div>
    </div>
`;
            container.appendChild(card);
        });
    }
</script>
@endsection
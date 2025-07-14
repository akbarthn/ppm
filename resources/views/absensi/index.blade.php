@extends('layout.app')

@section('content')
<h2>Absensi Hari Ini ({{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }})</h2>

@foreach($jadwal as $group)
    @php
        $shift = $group['shift'];
        $items = $group['items'];
    @endphp

    <div class="card mb-4">
        <div class="card-header">
            <strong>Shift: {{ $shift->name }} ({{ substr($shift->start, 0, 5) }} - {{ substr($shift->end, 0, 5) }})</strong>
            <a href="{{ route('absensi.scan', $shift->id) }}" class="btn btn-primary btn-sm float-end">Mulai Scan</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Keterangan</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $jadwal)
                        <tr class="text-center align-middle">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $jadwal->user->nama ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($jadwal->date_schedule)->format('d-m-Y') }}</td>
                            <td>{{ $shift->name }} ({{ substr($shift->start, 0, 5) }} - {{ substr($shift->end, 0, 5) }})</td>
                            <td>{{ $jadwal->keterangan ?? 'Alfa' }}</td>
                            <td>{{ $jadwal->check_in ?? '-' }}</td>
                            <td>{{ $jadwal->check_out ?? '-' }}</td>
                            <td>
                                <form action="{{ route('absensi.keterangan') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_user" value="{{ $jadwal->id_user }}">
                                    <input type="hidden" name="date_schedule" value="{{ $jadwal->date_schedule }}">
                                    <input type="hidden" name="id_shift" value="{{ $jadwal->id_shift }}">
                                    <input type="text" name="keterangan" class="form-control d-inline w-auto" placeholder="Isi keterangan">
                                    <button class="btn btn-warning btn-sm">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada karyawan dijadwalkan untuk shift ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection

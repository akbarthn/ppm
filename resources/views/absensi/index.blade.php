@extends('layout.app')

@section('content')
<h2>Absensi Hari Ini ({{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }})</h2>

@foreach($jadwal as $shiftId => $list)
    @php $shift = $list->first()->shift; @endphp

    <div class="card mb-4">
        <div class="card-header">
            <strong>Shift: {{ $shift->name }} ({{ $shift->start }} - {{ $shift->end }})</strong>
            <a href="{{ route('absensi.scan', $shiftId) }}" class="btn btn-primary btn-sm float-end">Mulai Scan</a>
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
                @forelse($list as $index => $jadwal)
                    <tr class="text-center align-middle">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $jadwal->user->nama ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($jadwal->date_schedule)->translatedFormat('d-m-Y') }}</td>
                        <td>{{ $jadwal->shift->name }} ({{ $jadwal->shift->start }} - {{ $jadwal->shift->end }})</td>
                        <td>{{ $jadwal->keterangan ?? 'Alfa' }}</td>
                        <td>{{ $jadwal->check_in ?? '-' }}</td>
                        <td>{{ $jadwal->check_out ?? '-' }}</td>
                        <td>
                            <a href="{{ route('absensi.scan', ['shift' => $jadwal->id]) }}" class="btn btn-success btn-sm">
                                Scan Wajah
                            </a>
                            <form action="{{ route('absensi.keterangan') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id" value="{{ $jadwal->id }}">
                                <input type="text" name="keterangan" class="form-control d-inline w-auto" placeholder="Isi keterangan">
                                <button class="btn btn-warning btn-sm">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada jadwal untuk shift ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endforeach

@endsection

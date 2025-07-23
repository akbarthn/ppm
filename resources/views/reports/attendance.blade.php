@extends('layout.app')

@section('title', 'Attendance Report')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Attendance Report</h2>
        <div>
            <a href="{{ route('reports.attendance') }}" class="btn btn-primary me-2">
                Refresh Report
            </a>
            <button onclick="window.print()" class="btn btn-success">
                Print
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($scheadules as $index => $item)
                        @php
                            $checkIn = $item->check_in ? \Carbon\Carbon::parse($item->check_in) : null;
                            $scheduleDate = \Carbon\Carbon::parse($item->date_schedule);
                            $shiftStart = \Carbon\Carbon::parse($item->date_schedule . ' ' . $item->shift->start_time);

                            // Cek keterlambatan lebih dari 30 menit
                            $isLate = $checkIn && $checkIn->diffInMinutes($shiftStart, false) < -30;
                        @endphp
                        <tr class="text-center">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->user->nama ?? '-' }}</td>
                            <td>{{ $scheduleDate->translatedFormat('d M Y') }}</td>
                            <td>{{ $item->shift->name }}</td>
                            <td class="{{ $isLate ? 'text-white bg-danger' : '' }}">
                                {{ $item->check_in ?? '-' }}
                            </td>
                            <td>{{ $item->check_out ?? '-' }}</td>
                            <td>
                                {{ $item->check_in ? 'Masuk' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data kehadiran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

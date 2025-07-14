@extends('layout.app')

@section('title', 'Data Jadwal Kerja')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Data Jadwal Kerja</h2>
    <a href="{{ route('jadwal.create') }}" class="btn btn-primary mb-3">Tambah Jadwal Baru</a>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Jadwal Kerja</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <form method="GET" action="{{ route('jadwal.index') }}" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan tanggal, nama, atau shift" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </div>
</form>

                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Shift</th>
                            <th>Nama Karyawan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $index => $item)
                            <tr class="text-center align-middle">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item['date_schedule'])->translatedFormat('d-m-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item['date_schedule'])->translatedFormat('l') }}</td>
                                <td>
                                    {{ $item['shift']->name }}
                                    ({{ \Carbon\Carbon::createFromFormat('H:i:s', $item['shift']->start)->format('H:i') }} -
                                     {{ \Carbon\Carbon::createFromFormat('H:i:s', $item['shift']->end)->format('H:i') }})
                                </td>
                                <td class="text-start">
                                    {{ implode(', ', $item['users']) }}
                                </td>
                                <td>
                                    <a href="{{ route('jadwal.editGroup', ['date_schedule' => $item['date_schedule'], 'id_shift' => $item['shift']->id]) }}"
                                        class="btn btn-sm btn-warning">Edit</a>

                                    <form action="{{ route('jadwal.destroyGroup') }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="date_schedule" value="{{ $item['date_schedule'] }}">
                                        <input type="hidden" name="id_shift" value="{{ $item['id_shift'] }}">
                                        <button onclick="return confirm('Yakin ingin menghapus semua jadwal ini?')" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada jadwal kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

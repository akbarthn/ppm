@extends('layout.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Shift Management</h2>
    <a href="{{ route('shift.create') }}" class="btn btn-primary mb-3">Add New Shift</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Shift</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $shift->name }}</td>
                        <td>{{ $shift->start_formatted }}</td>
                        <td>{{ $shift->end_formatted }}</td>
                        <td>
                            <a href="{{ route('shift.edit', $shift->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('shift.destroy', $shift->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus shift ini?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data shift.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

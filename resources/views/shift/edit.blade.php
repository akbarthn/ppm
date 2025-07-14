@extends('layout.app')

@section('content')
<div class="container">
    <h2>Edit Shift</h2>

    <form action="{{ route('shift.update', $shift->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Shift Name</label>
            <input type="text" name="name" class="form-control" value="{{ $shift->name }}" required>
        </div>

        <div class="mb-3">
            <label>Start</label>
            <input type="time" name="start" class="form-control" value="{{ $shift->start_time }}" required>
        </div>

        <div class="mb-3">
            <label>End</label>
            <input type="time" name="end" class="form-control" value="{{ $shift->end_time }}" required>
        </div>

        <button class="btn btn-primary">Update</button>
        <a href="{{ route('shift.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
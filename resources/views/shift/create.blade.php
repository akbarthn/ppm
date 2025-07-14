@extends('layout.app')

@section('content')
<div class="container">
    <h2>Add Shift</h2>

    <form action="{{ route('shift.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Shift Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Start </label>
            <input type="time" name="start" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>End </label>
            <input type="time" name="end" class="form-control" required>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('shift.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
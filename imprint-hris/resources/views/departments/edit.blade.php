@extends('layouts.app')

@section('title', 'Edit Department | Imprint HRIS')
@section('heading', 'Edit Department')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit Department</h1>
            <p>Update this department's details.</p>
        </div>
        <div class="head-actions"><a href="/departments" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form action="/departments/{{ $department->id }}" method="POST">
                @csrf @method('PUT')
                <div class="form-grid">
                    <div class="field full"><label>Department Name</label><input type="text" name="name" value="{{ old('name', $department->name) }}" required></div>
                    <div class="field full"><label>Description</label><textarea name="description" rows="3">{{ old('description', $department->description) }}</textarea></div>
                </div>
                <div class="form-actions">
                    <a href="/departments" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
@endsection

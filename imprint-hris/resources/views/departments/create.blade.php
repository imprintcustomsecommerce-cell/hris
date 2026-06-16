@extends('layouts.app')

@section('title', 'Add Department | Imprint HRIS')
@section('heading', 'Add Department')

@section('content')
    <div class="page-head">
        <div>
            <h1>Add Department</h1>
            <p>Create a new department.</p>
        </div>
        <div class="head-actions"><a href="/departments" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form action="/departments" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="field full"><label>Department Name</label><input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Marketing" required></div>
                    <div class="field full"><label>Description</label><textarea name="description" rows="3" placeholder="Short description">{{ old('description') }}</textarea></div>
                </div>
                <div class="form-actions">
                    <a href="/departments" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
@endsection

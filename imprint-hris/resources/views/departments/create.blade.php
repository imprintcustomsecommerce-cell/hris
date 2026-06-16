<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    <section class="page-header">
        <div>
            <span class="badge">Organization</span>
            <h1>Add Department</h1>
            <p>Create a new department for your organization.</p>
        </div>
        <a href="/departments" class="back-btn">← Back to Departments</a>
    </section>

    <section class="form-panel">
        <form action="/departments" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group full">
                    <label>Department Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Marketing" required>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Short description">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="/departments" class="cancel-btn">Cancel</a>
                <button type="submit" class="save-btn">Save Department</button>
            </div>
        </form>

        @if ($errors->any())
            <div class="error-alert">
                <strong>Please check the form.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
    </section>
</main>

@include('components.page-style')

</body>
</html>

@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Task Management';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($task) && $task?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="card shadow my-3">
        <div class="card-body my-4">
            <form action="{{ isset($task) && $task?->id ? route($route . '.update', [$task?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($task)
                    @method('PUT')
                @endisset
                
                <div class="row">
                    <!-- Date (default current date) -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="date" class="form-control @error('date') is-invalid @enderror"
                                value="{{ old('date', isset($task->date) ? \Carbon\Carbon::parse($task->date)->format('Y-m-d\TH:i') : \Carbon\Carbon::now()->format('Y-m-d\TH:i')) }}" required>
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Staff (dropdown) -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Staff (Faculty) <span class="text-danger">*</span></label>
                            <select id="assigned_to" name="assigned_to" class="form-control select2 @error('assigned_to') is-invalid @enderror" required>
                                <option value="">Select Faculty</option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}" {{ old('assigned_to', $task->assigned_to ?? '') == $faculty->id ? 'selected' : '' }}>
                                        {{ $faculty->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Task (Title) -->
                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Task <span class="text-danger">*</span></label>
                            <textarea name="title" class="form-control @error('title') is-invalid @enderror"
                                placeholder="Enter task details" rows="3" style="resize: vertical;" required autofocus>{{ old('title', $task->title ?? '') }}</textarea>
                            @error('title')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Deadline (default current date with time) -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Deadline <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                value="{{ old('due_date', isset($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d\TH:i') : \Carbon\Carbon::now()->format('Y-m-d\TH:i')) }}" required>
                            @error('due_date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Status Hidden -->
                    <input type="hidden" name="status" value="{{ old('status', $task->status ?? '0') }}">


                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1">
                            {{ isset($task) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
@endsection


@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->

    <div class="card shadow">
        <div class="card-header d-none">
            <h2 class="mb-0">
                {{ $page_title }}
            </h2>
        </div>
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset
                <div class="row">
                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Name <span class="text-danger">*</span> </label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ isset($edit?->name) ? $edit?->name : old('name') }}"
                                autocomplete="name" autofocus placeholder="Enter name">
                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="formGroupExampleInput" class="d-block">Gived Permission</label>

                        @php
                            $groupedPermissions = collect($permissions)->groupBy('group');
                        @endphp

                        @foreach ($groupedPermissions as $group => $perms)
                            <div class="border rounded p-3 mb-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input group-checkbox"
                                        id="group-{{ Str::slug($group) }}">
                                    <label class="custom-control-label font-weight-bold"
                                        for="group-{{ Str::slug($group) }}">{{ ucfirst($group) }}</label>
                                </div>
                                <div class="row mt-2">
                                    @foreach ($perms as $perm)
                                        <div class="col-md-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input permission-checkbox"
                                                    id="perm-{{ $perm->id }}" name="permission[]"
                                                    value="{{ $perm->id }}"
                                                    {{ isset($rolePermissions) && in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                    for="perm-{{ $perm->id }}">{{ $perm->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @error('permission')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- <div class="row">
                    <div class="col-md-4">
                        <label class="form-label"> Permission Groups </label>
                        <ul class="list-group">
                            @php
                                $groupedPermissions = collect($permissions)->groupBy('group');
                            @endphp
                            @foreach ($groupedPermissions as $group => $perms)
                                <li class="list-group-item">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input group-checkbox" id="group-{{ Str::slug($group) }}">
                                        <label class="custom-control-label font-weight-bold" for="group-{{ Str::slug($group) }}">{{ ucfirst($group) }}</label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label"> Permissions </label>
                        <div id="permissions-container">
                            @foreach ($groupedPermissions as $group => $perms)
                                <div class="permissions-group d-none" id="permissions-{{ Str::slug($group) }}">
                                    <div class="row">
                                        @foreach ($perms as $perm)
                                            <div class="col-md-4">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input permission-checkbox" id="perm-{{ $perm->id }}" name="permission[]" value="{{ $perm->id }}"
                                                        {{ isset($rolePermissions) && in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="perm-{{ $perm->id }}">{{ $perm->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div> --}}

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-success mt-1 mb-1">
                                {{ isset($edit) ? 'Update' : 'Submit' }}
                            </button>
                            <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                        </div>
                    </div>
            </form>
        </div>
    </div>

@endsection


@section('page_leavel_script1')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Show permissions for a group when clicked
            document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', function() {
                    let groupId = this.id.replace('group-', '');
                    let permissionsDiv = document.getElementById(`permissions-${groupId}`);

                    if (this.checked) {
                        permissionsDiv.classList.remove('d-none');
                        permissionsDiv.querySelectorAll('.permission-checkbox').forEach(checkbox =>
                            checkbox.checked = true);
                    } else {
                        permissionsDiv.classList.add('d-none');
                        permissionsDiv.querySelectorAll('.permission-checkbox').forEach(checkbox =>
                            checkbox.checked = false);
                    }
                });
            });

            // Handle individual permission checkbox click
            document.querySelectorAll('.permission-checkbox').forEach(permissionCheckbox => {
                permissionCheckbox.addEventListener('change', function() {
                    let groupElement = this.closest('.permissions-group').previousElementSibling
                        .querySelector('.group-checkbox');
                    let groupPermissions = this.closest('.permissions-group').querySelectorAll(
                        '.permission-checkbox');
                    let allChecked = [...groupPermissions].every(checkbox => checkbox.checked);
                    let anyChecked = [...groupPermissions].some(checkbox => checkbox.checked);

                    groupElement.checked = allChecked;
                    groupElement.indeterminate = !allChecked && anyChecked;
                });
            });

            // Initialize checkboxes based on existing selections
            document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
                let groupId = groupCheckbox.id.replace('group-', '');
                let permissionsDiv = document.getElementById(`permissions-${groupId}`);
                let groupPermissions = permissionsDiv.querySelectorAll('.permission-checkbox');
                let allChecked = [...groupPermissions].every(checkbox => checkbox.checked);
                let anyChecked = [...groupPermissions].some(checkbox => checkbox.checked);

                groupCheckbox.checked = allChecked;
                groupCheckbox.indeterminate = !allChecked && anyChecked;
                if (anyChecked) {
                    permissionsDiv.classList.remove('d-none');
                }
            });
        });
    </script>
@endsection
@section('page_leavel_script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Handle group checkbox click
            document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', function() {
                    let groupId = this.id.replace('group-', '');
                    let checkboxes = document.querySelectorAll(`.permission-checkbox[id^="perm-"]`);

                    checkboxes.forEach(checkbox => {
                        if (checkbox.closest('.border').querySelector(
                                `#group-${groupId}`)) {
                            checkbox.checked = this.checked;
                        }
                    });
                });
            });

            // Handle individual permission checkbox click
            document.querySelectorAll('.permission-checkbox').forEach(permissionCheckbox => {
                permissionCheckbox.addEventListener('change', function() {
                    let groupElement = this.closest('.border').querySelector('.group-checkbox');
                    let groupPermissions = this.closest('.border').querySelectorAll(
                        '.permission-checkbox');
                    let allChecked = [...groupPermissions].every(checkbox => checkbox.checked);
                    let anyChecked = [...groupPermissions].some(checkbox => checkbox.checked);

                    groupElement.checked = allChecked;
                    groupElement.indeterminate = !allChecked && anyChecked;
                });
            });

            // Initialize group checkboxes based on existing selections
            document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
                let groupPermissions = groupCheckbox.closest('.border').querySelectorAll(
                    '.permission-checkbox');
                let allChecked = [...groupPermissions].every(checkbox => checkbox.checked);
                let anyChecked = [...groupPermissions].some(checkbox => checkbox.checked);

                groupCheckbox.checked = allChecked;
                groupCheckbox.indeterminate = !allChecked && anyChecked;
            });
        });
    </script>
@endsection

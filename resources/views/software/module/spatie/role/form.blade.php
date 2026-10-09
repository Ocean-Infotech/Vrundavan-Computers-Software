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
                        @php
                            $groupedPermissions = collect($permissions)->groupBy('group');
                        @endphp

                        <!-- Modules Grid -->
                        <div class="permission-modules-container">
                            @foreach ($groupedPermissions as $group => $perms)
                                @php
                                    $groupSlug = Str::slug($group ?: 'other');
                                    $totalGroupPerms = count($perms);
                                @endphp
                                <div class="card border mb-3 shadow-none permission-group-card" style="border-radius: 10px; overflow: hidden;">
                                    <!-- Module Header with Group Select All Toggle -->
                                    <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e9ecef;">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 fw-bold text-dark text-capitalize">{{ str_replace(['_', '-'], ' ', $group ?: 'General') }}</h6>
                                            <span class="badge bg-label-secondary ms-2 group-count-badge" style="font-size: 11px;">0 / {{ $totalGroupPerms }}</span>
                                        </div>

                                        <div class="form-check mb-0 d-flex align-items-center">
                                            <input type="checkbox" class="form-check-input group-checkbox cursor-pointer custom-lg-checkbox me-2" 
                                                   id="group-{{ $groupSlug }}" 
                                                   data-group="{{ $groupSlug }}">
                                            <label class="form-check-label fw-bold text-primary cursor-pointer" for="group-{{ $groupSlug }}" style="font-size: 0.9rem;">
                                                Select All Module
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Permissions List -->
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            @foreach ($perms as $perm)
                                                @php
                                                    $permId = $perm->id;
                                                    $permName = $perm->name;
                                                    $isChecked = isset($rolePermissions) && in_array($perm->id, $rolePermissions);
                                                @endphp
                                                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                                                    <div class="permission-item-box p-2 rounded border d-flex align-items-center h-100 {{ $isChecked ? 'border-primary bg-light-primary' : 'bg-white' }}"
                                                         style="transition: all 0.2s ease; cursor: pointer;">
                                                        <div class="form-check mb-0 w-100 d-flex align-items-center">
                                                            <input type="checkbox" 
                                                                   class="form-check-input permission-checkbox me-2 cursor-pointer custom-lg-checkbox"
                                                                   id="perm-{{ $permId }}" 
                                                                   name="permission[]" 
                                                                   value="{{ $permId }}"
                                                                   data-group="{{ $groupSlug }}"
                                                                   {{ $isChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label cursor-pointer text-dark fw-medium text-truncate w-100" 
                                                                   for="perm-{{ $permId }}"
                                                                   style="font-size: 0.88rem;"
                                                                   title="{{ $permName }}">
                                                                {{ $permName }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('permission')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

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


@section('page_leavel_script')
    <style>
        .custom-lg-checkbox {
            width: 19px !important;
            height: 19px !important;
            min-width: 19px !important;
            cursor: pointer;
            border-radius: 4px !important;
            border: 2px solid #8e85f3 !important;
        }
        .custom-lg-checkbox:checked {
            background-color: #7367f0 !important;
            border-color: #7367f0 !important;
        }
        .permission-item-box:hover {
            border-color: #7367f0 !important;
            background-color: #f8faff !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(115, 103, 240, 0.1);
        }
        .bg-light-primary {
            background-color: rgba(115, 103, 240, 0.07) !important;
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function updateRoleCounters() {
                const allPerms = document.querySelectorAll('.permission-checkbox');
                const totalCount = allPerms.length;
                let checkedCount = 0;

                allPerms.forEach(chk => {
                    const box = chk.closest('.permission-item-box');
                    if (chk.checked) {
                        checkedCount++;
                        if (box) box.classList.add('border-primary', 'bg-light-primary');
                    } else {
                        if (box) box.classList.remove('border-primary', 'bg-light-primary');
                    }
                });

                const countElem = document.getElementById('role-selected-count');
                const totalElem = document.getElementById('role-total-count');
                if (countElem) countElem.textContent = checkedCount;
                if (totalElem) totalElem.textContent = totalCount;

                document.querySelectorAll('.permission-group-card').forEach(card => {
                    const groupChk = card.querySelector('.group-checkbox');
                    const perms = card.querySelectorAll('.permission-checkbox');
                    const badge = card.querySelector('.group-count-badge');
                    
                    const groupTotal = perms.length;
                    const groupChecked = [...perms].filter(p => p.checked).length;

                    if (badge) {
                        badge.textContent = `${groupChecked} / ${groupTotal}`;
                        if (groupChecked === groupTotal && groupTotal > 0) {
                            badge.className = 'badge bg-success ms-2 group-count-badge';
                        } else if (groupChecked > 0) {
                            badge.className = 'badge bg-primary ms-2 group-count-badge';
                        } else {
                            badge.className = 'badge bg-label-secondary ms-2 group-count-badge';
                        }
                    }

                    if (groupChk) {
                        groupChk.checked = (groupChecked === groupTotal && groupTotal > 0);
                        groupChk.indeterminate = (groupChecked > 0 && groupChecked < groupTotal);
                    }
                });
            }

            const btnSelectAll = document.getElementById('btn-role-select-all');
            if (btnSelectAll) {
                btnSelectAll.onclick = function () {
                    document.querySelectorAll('.permission-checkbox').forEach(chk => chk.checked = true);
                    updateRoleCounters();
                };
            }

            const btnDeselectAll = document.getElementById('btn-role-deselect-all');
            if (btnDeselectAll) {
                btnDeselectAll.onclick = function () {
                    document.querySelectorAll('.permission-checkbox').forEach(chk => chk.checked = false);
                    updateRoleCounters();
                };
            }

            document.querySelectorAll('.group-checkbox').forEach(groupChk => {
                groupChk.onchange = function () {
                    const card = this.closest('.permission-group-card');
                    if (card) {
                        card.querySelectorAll('.permission-checkbox').forEach(chk => {
                            chk.checked = groupChk.checked;
                        });
                        updateRoleCounters();
                    }
                };
            });

            document.querySelectorAll('.permission-checkbox').forEach(permChk => {
                permChk.onchange = function () {
                    updateRoleCounters();
                };
            });

            updateRoleCounters();
        });
    </script>
@endsection


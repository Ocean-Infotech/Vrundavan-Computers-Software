@extends('software.layout.app')

@php
    $isEdit = isset($edit) && $edit?->id;
    $page_title = ($isEdit ? 'Edit' : 'Add') . ' Number';
    $route = $modules['route'] ?? 'contact-us';
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => 'Other', 'url' => ''],
                ['title' => 'Contact Us', 'url' => route('contact-us.index')],
                ['title' => $page_title, 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="card my-3">
        <div class="card-body">
            <form method="post"
                action="{{ $isEdit ? route('contact-us.numbers.update', $edit->id) : route('contact-us.numbers.store') }}">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" name="label"
                            class="form-control @error('label') is-invalid @enderror"
                            value="{{ old('label', $edit->label ?? '') }}"
                            placeholder="College mobile / School Mobile / Principal" required>
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="text" name="mobile"
                            class="form-control @error('mobile') is-invalid @enderror"
                            value="{{ old('mobile', $edit->mobile ?? '') }}"
                            placeholder="96874 51774" required>
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        @php $currentStatus = old('status', $edit->status ?? 'active'); @endphp
                        <select id="status" name="status"
                            class="form-select select2 @error('status') is-invalid @enderror" required>
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                            class="form-control @error('sort_order') is-invalid @enderror"
                            value="{{ old('sort_order', $edit->sort_order ?? 0) }}">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center">
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
                    <a href="{{ route('contact-us.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('#status').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity
                });
            }
        });
    </script>
@endsection

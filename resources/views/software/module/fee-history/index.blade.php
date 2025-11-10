@extends('software.layout.app')

@php
    $page_title = 'Fee History';
@endphp

@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $modules['route'],
        ])
    </div>

    <div class="row my-3">
        <div class="col-12 mb-4" id="filter_section">
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    @forelse($data->getCollection() as $date => $records)
                        <div class="mb-3 rounded p-2">
                            <h6 class="text-center text-primary mb-3">
                                =================== {{ $date }} ===================
                            </h6>
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>GR No</th>
                                        <th>Student Name</th>
                                        <th>Course</th>
                                        <th>Fees</th>
                                        <th>Receipt No</th>
                                        <th>Check</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $item)
                                        <tr>
                                            <td>{{ $item->student_id }}</td>
                                            <td>{{ $item->student_name }}</td>
                                            <td>{{ $item->course->course_name ?? '-' }}</td>
                                            <td>{{ $item->fees }}</td>
                                            <td>{{ $item->id }}</td>
                                            <td>
                                                <input type="checkbox" class="row-checkbox" value="{{ $item->id }}"
                                                    {{ $item->checked_status == 1 ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <p class="text-center text-danger">No records found.</p>
                    @endforelse

                    {{-- ✅ Pagination links --}}
                    <div class="d-flex justify-content-center mt-3">
                        {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        // Clear filter button functionality
        $('#clear_filter').on('click', function() {
            window.location.href = "{{ route('fee-history.index') }}";
        });

        // Checkbox status update
        $(document).on('change', '.row-checkbox', function() {
            let id = $(this).val();
            let status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: "{{ route('fee-history.update-status') }}",
                type: "POST",
                data: {
                    id: id,
                    status: status,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong while updating status.');
                }
            });
        });
    </script>
@endsection

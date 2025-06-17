@extends('software.layout.app')

@section('content')
    @include('software.partials.flash_messages')

    {{-- Developer-only debug info --}}
    @if (in_array(\App\Helpers\Helper::getLoginUserRole(), ['developer', 'Developer']))
        <div class="d-none">
            {{-- Full authenticated user dump --}}
            <pre> {!! Auth::user() !!} </pre>

            <br />
            Roles: {{ Auth::user()->getRoleNames() }}
            <hr />
            Permissions: {{ Auth::user()->getPermissionNames() }}
            <hr />
            @directCan('users-list')
                Direct Permissions: {!! Auth::user()->getDirectPermissions() !!}
            @enddirectCan
        </div>
    @endif

    {{-- Show all admissions to all users --}}
    <div class="row">
        @foreach ($admission as $admissions)
            <div class="col-xl-2 mb-3">
                <div class="card h-100">
                    <div class="d-flex align-items-end row">
                        <div class="col-12">
                            <div class="card-body text-nowrap">
                                <h5 class="card-title mb-0 text-truncate" style="overflow: hidden; white-space: nowrap;">
                                    <b>{{ strtoupper($admissions->first_name . ' ' . $admissions->last_name . ' ' . $admissions->father_name) }}</b>
                                </h5>
                                <div class="mt-2">
                                    {!! $admissions->folder_button !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Open folder in new tab when button clicked
    $(document).on('click', '.open_google_drive_folder', function() {
        const url = $(this).data('folderurl');
        if (url) {
            window.open(url, '_blank');
        } else {
            alert('No folder URL provided.');
        }
    });
</script>

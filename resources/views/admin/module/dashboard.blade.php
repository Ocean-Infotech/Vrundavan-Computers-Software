@extends('admin.layout.app')

@section('content')
    @include('admin.partials.flash_messages')
    @if (in_array(\App\Helpers\Helper::getLoginUserRole(), ['developer', 'Developer']))
    <div class="d-none">
        <pre> {!! Auth::user() !!} </pre>

        <br />
        Roles {{ Auth::user()->getRoleNames() }}
        <hr />
        Permissions : {{ Auth::user()->getPermissionNames() }}
        <hr />
        @directCan('users-list')
            Direct Permissions : {!! Auth::user()->getDirectPermissions() !!}
        @enddirectCan
    </div>
    @endif
@endsection

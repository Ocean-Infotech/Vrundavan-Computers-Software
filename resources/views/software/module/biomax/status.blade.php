@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $i = 0;
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>
    <div class="card">
        <div class="card-header">
            <h3>
                Check the biomax status
            </h3>
            <button type="button" class="btn btn-info" onclick="checkBiomaxStatus();">Check the Biomax Status</button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="biomax_status"> <b>Biomax Login :- </b></label>
                        <span id="biomax_login"></span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="divice_detail"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            checkBiomaxStatus();
        });

        function checkBiomaxStatus() {

            var _token = '{{ csrf_token() }}';
            $.ajax({
                type: "POST",
                url: "{{ route('biomax.status') }}",
                data: {
                    _token: _token,
                    _method: 'POST',
                },
                success: function(data) {
                    console.log("data 27", data);

                    if (data?.status == "true") {
                        toastr.success(data?.message);
                        let resposneData = data?.data;
                        if (resposneData) {
                            console.log("data 71", resposneData?.external_api_token);
                            if (resposneData?.external_api_token) {
                                $("#biomax_login").text("Login Success. External API Token: " + resposneData
                                    ?.external_api_token);
                            }
                            if (resposneData?.api_device && resposneData?.api_device_html) {
                                $("#divice_detail").html(resposneData?.api_device_html);
                            }
                        }
                    } else {
                        toastr.error(data?.message);
                    }
                },
                error: function() {
                    toastr.error('Something Went wrong Update Status failed.');
                }
            });
        }
    </script>
@endsection

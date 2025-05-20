<script>
    $(document).on('click', '.update-status', function() {
        var _token = '{{ csrf_token() }}';
        var _actionUrl = $(this).attr("data-url");
        var _dataId = $(this).attr("data-id");
        var _updateStatus = $(this).attr("data-update_status");

       
        

        const urlParams = new URLSearchParams(_actionUrl);

        if(!_dataId && urlParams.get('id')){
            _dataId = urlParams.get('id');
        }
        if(!_updateStatus && urlParams.get('update_status')){
            _updateStatus = urlParams.get('update_status');
        }

        if(_token && _actionUrl && _dataId && _updateStatus){
            console.log("generate-inquiry-no L-31");
            $.ajax({
                type: "POST",
                url: _actionUrl,
                data: {
                    _token: _token,
                    _method: 'POST',
                    id: _dataId,
                    update_status: _updateStatus,
                },
                success: function(data) {
                    console.log("data 27",data);

                    if(data?.status == "true"){
                        toastr.success(data?.message);
                        $("#yajra-datatables").DataTable().ajax.reload();
                    }else{
                        toastr.error(data?.message);
                    }
                },
                error: function() {
                    toastr.error('Something Went wrong Update Status failed.');
                }
            });
        }else{
            console.log("Update Script", _token, _actionUrl, urlParams, _dataId, _updateStatus);

        }
    });
</script>

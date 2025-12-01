<script>
$(document).on('click', '.record-restore', function() {
    var _restoreUrl = $(this).attr("data-restore");
    var _token = '{{ csrf_token() }}';

    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to restore this data?",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Yes, restore it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-primary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: _restoreUrl,
                data: {
                    _token: _token,
                    _method: 'POST'
                },
                success: function(res) {
                    if(res.status === 'success'){
                        toastr.success(res.message); // Show success from controller
                        $("#yajra-datatables").DataTable().ajax.reload();
                    } else {
                        toastr.error(res.message); // Show error from controller
                    }
                },
                error: function(xhr) {
                    if(xhr.responseJSON && xhr.responseJSON.message){
                        toastr.error(xhr.responseJSON.message); // Show backend error
                    } else {
                        toastr.error("Something went wrong, restore failed.");
                    }
                }
            });
        } else {
            toastr.info('Data is safe 🙂');
        }
    });
});
</script>

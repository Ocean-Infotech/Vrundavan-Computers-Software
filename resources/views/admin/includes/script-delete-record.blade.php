<script>
    $(document).on('click', '.deletebutton', function() {
    var uid = $(this).attr("data-id");
    var did = $(this).attr("data-did");
    var pageTitle = '{{ $page_title }}'; // Get page title dynamically if needed
    var _token = '{{ csrf_token() }}'; // Safer way via meta tag

    Swal.fire({
        title: "Are you sure?",
        text: "You will not be able to recover this data!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-success'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: did,
                data: {
                    _token: _token,
                    _method: 'DELETE',
                },
                success: function(data) {
                    Swal.fire({
                        title: "Deleted!",
                        text: pageTitle + " has been deleted.",
                        icon: "success",
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light',
                        },
                    });
                    $("#yajra-datatables").DataTable().ajax.reload();
                },
                error: function() {
                    Swal.fire({
                        title: "Error!",
                        text: "Something went wrong, deletion failed.",
                        icon: "error",
                    });
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                title: "Cancelled",
                text: "Your data is safe 🙂",
                icon: "info",
            });
        }
    });
});

</script>

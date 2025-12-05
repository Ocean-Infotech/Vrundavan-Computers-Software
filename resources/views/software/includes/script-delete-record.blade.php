<script>
  $(document).on('click', '.permanentDeleteButton', function() {
        var did = $(this).attr("data-did");
        var _token = '{{ csrf_token() }}';

        Swal.fire({
            title: "Are you sure?",
            text: "This record will be permanently deleted. You cannot undo this action!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, permanently delete!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-dark',
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
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $("#yajra-datatables").DataTable().ajax.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error("Permanent deletion failed!");
                        }
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                toastr.info('Permanent delete cancelled.');
            }
        });
    });
   $(document).on('click', '.deletebutton', function() {
        var did = $(this).attr("data-did");
        var pageTitle = '{{ $page_title }}';
        var _token = '{{ csrf_token() }}';

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
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message); // Show success from controller
                            $("#yajra-datatables").DataTable().ajax.reload();
                        } else {
                            toastr.error(res.message); // Show error message from controller
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message); // Backend error message
                        } else {
                            toastr.error(
                            "Something went wrong, deletion failed."); // Fallback
                        }
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                toastr.info('Your data is safe 🙂');
            }
        });
    });
</script>

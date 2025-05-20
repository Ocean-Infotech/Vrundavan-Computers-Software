<script>
    $(document).on('click', '.record-restore', function() {
        var _restoreUrl = $(this).attr("data-restore");
        var _token = '{{ csrf_token() }}';

        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure you want to restore this data?",
            icon: "info",
            customClass: {
                confirmButton: 'btn btn-success waves-effect waves-light',
                cancelButton: "btn btn-primary waves-effect waves-light",
            },
            confirmButtonText: "Yes, restore it!",
            cancelButtonText: "No, cancel please!",
            showCancelButton: true,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: _restoreUrl,
                    data: {
                        _token: _token,
                        _method: 'POST',
                    },
                    success: function(data) {
                        toastr.success('{{ $page_title1 ?? "record" }} data has been restored.');
                        $("#yajra-datatables").DataTable().ajax.reload();
                    },
                    error: function() {
                        toastr.error('Something Went wrong deleted failed.');
                    }
                });
            } else {
                toastr.info('Data is safe :)');
            }
        });
    });
</script>

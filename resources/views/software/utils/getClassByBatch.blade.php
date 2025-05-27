<script>
    $(document).on('change', '.search_by_batch', function() {
        console.log("search_by_class L-3");

        let instance = $('.search_by_batch');
        let batch_id = instance.val();
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');
        let class_id = instance.attr("data-selectedClassId");
        
        if (!batch_id) {
            batch_id = instance.attr("data-selectedBatchId");
        }

        if (!class_id) {
            class_id = $(".search_by_class").attr("data-selectedClassId");
        }

        if (batch_id) {
            $.ajax({
                type: 'POST',
                url: "{{ route('get-class-bybatch') }}",
                'beforeSend': function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                        'content'));
                },
                data: {
                    batch_id: batch_id
                },
                success: function(response) {
                    if (response.status) {
                        // console.log("getClass 30",response.data, batch_id, class_id);
                        let options = "<option value=''>Select Class</option>";
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (class_id == item.id) {
                                    options += "<option value='" + item.id +
                                        "' selected >" + item.name + "</option>";
                                } else {
                                    options += "<option value='" + item.id +
                                        "'>" +
                                        item.name + "</option>";
                                }
                            });
                        }
                        // console.log("getclass 33",instance.parent(),);
                        if ($(".search_by_class").attr('data-append')) {
                            $(".search_by_class").attr('data-selectedBatchId', batch_id);
                            if ($(document).find("." + $(".search_by_class").attr('data-append'))
                                .length > 0) {
                                $("." + $(".search_by_class").attr('data-append')).empty();
                                $("." + $(".search_by_class").attr('data-append')).append(options);
                            } else {
                                $("." + $(".search_by_class").attr('data-append')).empty();
                                $("." + $(".search_by_class").attr('data-append')).append(options);
                            }
                        } else if (instance.parent().parent().hasClass("col-md-6") || instance
                            .parent().parent().hasClass("col-md-4") || instance.parent().parent()
                            .hasClass("col-md-3")) {

                            let select_tag =
                                '<select name="batch_id" class="form-select search_by_batch';
                            if (is_select2) {
                                select_tag += ' select2 ';
                            }
                            select_tag += '"';
                            if (is_required) {
                                select_tag += ' required ';
                            }
                            select_tag += '>' + options + '</select>';
                            let html = '';
                            if (instance.parent().parent().hasClass("col-md-4")) {
                                html = '<div class="col-md-4 batch_div">';
                            } else if (instance.parent().parent().hasClass("col-md-3")) {
                                html = '<div class="col-md-3 batch_div">';
                            } else {
                                html = '<div class="col-md-6 batch_div">';
                            }
                            html +=
                                '<div class="form-group"> <label for="batch_id">Select Class ';
                            if (is_required) {
                                html += '<span class="text-danger">*</span>';
                            }
                            html += '</label>';
                            html += select_tag;
                            html += '</div></div>';
                            $(".batch_div").remove();
                            instance.parent().parent().after(html);
                        } else if (instance.parent("tr")) {
                            // console.log("getClass 39", instance, instance.parent(), instance.next("th"));
                            $(".search_by_batch").removeClass('d-none').empty().append(options);
                            /*
                            // $(".search_by_batch").remove();
                            // instance.parent().next("th").remove();
                            // instance.parent().after('<th>'+options+'</th>');
                            */
                        } else {
                            console.log("getClass 82", instance.parent());
                            instance.parent().after(options);
                        }
                        // $('.search_by_class').trigger("change");
                        $('.select2').select2();
                    }
                }

            });

        }
    });
    $('.search_by_batch').trigger("change");
</script>

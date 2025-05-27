<script>
    $(document).ready(function() {
        console.log("search_by_class L-3");

        let instance = $('.search_by_class');
        let class_id = instance.val();
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');

        if (!class_id) {
            class_id = instance.attr("data-selectedClassId");
        }

        $.ajax({
            type: 'POST',
            url: "{{ route('get-class') }}",
            'beforeSend': function(request) {
                request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                    'content'));
            },
            success: function(response) {
                if (response.status) {
                    // console.log("getClass 18",response.data, class_id);
                    let options = "<option value=''>Select Class</option>";
                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(i, item) {
                            if (class_id == item.id) {
                                options += "<option value='" + item.id +
                                    "' data-fees='" + item.fees +
                                    "' selected >" + item.name + "</option>";
                            } else {
                                options += "<option value='" + item.id +
                                    "' data-fees='" + item.fees + "'>" +
                                    item.name + "</option>";
                            }
                        });
                    }
                    // console.log("getClass 33",instance.parent());
                    if (instance.attr('data-append')) {
                        if ($(document).find("." + instance.attr('data-append')).length > 0) {
                            $("." + instance.attr('data-append')).empty();
                            $("." + instance.attr('data-append')).append(options);
                        } else {
                            $("." + instance.attr('data-append')).empty();
                            $("." + instance.attr('data-append')).append(options);
                        }
                    } else if (instance.parent().parent().hasClass("col-md-6") || instance
                        .parent().parent().hasClass("col-md-4") || instance.parent().parent()
                        .hasClass("col-md-3")) {

                        let select_tag =
                            '<select name="class_id" class="form-select search_by_class';
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
                            html = '<div class="col-md-4 class_div">';
                        } else if (instance.parent().parent().hasClass("col-md-3")) {
                            html = '<div class="col-md-3 class_div">';
                        } else {
                            html = '<div class="col-md-6 class_div">';
                        }
                        html +=
                            '<div class="form-group"> <label for="class_id">Select Class ';
                        if (is_required) {
                            html += '<span class="text-danger">*</span>';
                        }
                        html += '</label>';
                        html += select_tag;
                        html += '</div></div>';
                        $(".class_div").remove();
                        instance.parent().parent().after(html);
                    } else if (instance.parent("tr")) {
                        // console.log("getClass 39", instance, instance.parent(), instance.next("th"));
                        $(".search_by_class").removeClass('d-none').empty().append(options);
                        /*
                        // $(".search_by_class").remove();
                        // instance.parent().next("th").remove();
                        // instance.parent().after('<th>'+options+'</th>');
                        */
                    } else {
                        console.log("getClass 82", instance.parent());
                        instance.parent().after(options);
                    }
                    $('.search_by_class').trigger("change");
                    $('.select2').select2();
                }
            }
        });

    });
    $('.search_by_class').trigger("change");
</script>

<script>
    $(document).ready(function() {
        console.log("search_by_courceregistration L-3");

        let instance = $('.search_by_courceregistration');
        let courceregistration_id = instance.val();
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');

        if (!courceregistration_id) {
            courceregistration_id = instance.attr("data-selectedCourceRegistrationId");
        }

        $.ajax({
            type: 'POST',
            url: "{{ route('get-courceregistration') }}",
            'beforeSend': function(request) {
                request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                    'content'));
            },
            success: function(response) {
                if (response.status) {
                    // console.log("getcourceregistration 18",response.data, courceregistration_id);
                    let options = "";
                    if (!instance.is('datalist')) {
                        options = "<option value=''>Select Fees Collection</option>";
                    }
                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(i, item) {
                            if (courceregistration_id == item.id) {
                                options += "<option value='" + item.id +
                                    "' selected >" +
                                    item.name +" - "+
                                    item?.course_name + "</option>";
                            } else {
                                options += "<option value='" + item.id + "'>" +
                                    item.name +" - "+
                                    item?.course_name + "</option>";
                            }
                        });
                    }
                    // console.log("getFeesCollection 33",instance.parent(), instance.attr('data-append'));
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
                            '<select name="courceregistration_id" class="form-select search_by_courceregistration';
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
                            html = '<div class="col-md-4 courceregistration_div">';
                        } else if (instance.parent().parent().hasClass("col-md-3")) {
                            html = '<div class="col-md-3 courceregistration_div">';
                        } else {
                            html = '<div class="col-md-6 courceregistration_div">';
                        }
                        html +=
                            '<div class="form-group"> <label for="courceregistration_id">Select Fees Collection ';
                        if (is_required) {
                            html += '<span class="text-danger">*</span>';
                        }
                        html += '</label>';
                        html += select_tag;
                        html += '</div></div>';
                        $(".courceregistration_div").remove();
                        instance.parent().parent().after(html);
                    } else if (instance.parent("tr")) {
                        // console.log("getCourceRegistration 39", instance, instance.parent(), instance.next("th"));
                        $(".search_by_courceregistration").removeClass('d-none').empty().append(options);
                        /*
                        // $(".search_by_courceregistration").remove();
                        // instance.parent().next("th").remove();
                        // instance.parent().after('<th>'+options+'</th>');
                        */
                    } else {
                        console.log("getCourceRegistration 82", instance.parent());
                        instance.parent().after(options);
                    }
                    $('.search_by_courceregistration').trigger("change");
                    $('.select2').select2();
                }
            }
        });

    });
    $('.search_by_courceregistration').trigger("change");
</script>

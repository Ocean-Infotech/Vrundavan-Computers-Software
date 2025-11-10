<script>
    $(document).ready(function() {
        console.log("search_by_admission L-3");

        let instance = $('.search_by_admission');
        let admission_id = instance.val();
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');

        if (!admission_id) {
            admission_id = instance.attr("data-selectedAdmissionId");
        }

        $.ajax({
            type: 'POST',
            url: "{{ route('get-admission') }}",
            'beforeSend': function(request) {
                request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                    'content'));
            },
            success: function(response) {
                console.log(response);

                if (response.status) {
                    // console.log("getAdmission 18",response.data, admission_id);
                    let options = "<option value=''>Select Admission</option>";
                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(i, item) {
                            if (admission_id == item.id) {
                                options += "<option value='" + item.id +
                                    "' selected >" + item.name + "</option>";
                            } else {
                                options += "<option value='" + item.id + "'>" +
                                    item.name + "</option>";
                            }
                        });
                    }
                    // console.log("getAdmission 33",instance.parent());
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
                            '<select name="admission_id" class="form-select search_by_admission';
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
                            html = '<div class="col-md-4 admission_div">';
                        } else if (instance.parent().parent().hasClass("col-md-3")) {
                            html = '<div class="col-md-3 admission_div">';
                        } else {
                            html = '<div class="col-md-6 admission_div">';
                        }
                        html +=
                            '<div class="form-group"> <label for="admission_id">Select Admission ';
                        if (is_required) {
                            html += '<span class="text-danger">*</span>';
                        }
                        html += '</label>';
                        html += select_tag;
                        html += '</div></div>';
                        $(".admission_div").remove();
                        instance.parent().parent().after(html);
                    } else if (instance.parent("tr")) {
                        // console.log("getAdmission 39", instance, instance.parent(), instance.next("th"));
                        $(".search_by_admission").removeClass('d-none').empty().append(options);
                        /*
                        // $(".search_by_admission").remove();
                        // instance.parent().next("th").remove();
                        // instance.parent().after('<th>'+options+'</th>');
                        */
                    } else {
                        console.log("getAdmission 82", instance.parent());
                        instance.parent().after(options);
                    }
                    $('.search_by_admission').trigger("change");
                    $('.select2').select2();
                }
            }
        });

    });
    $('.search_by_admission').trigger("change");
</script>

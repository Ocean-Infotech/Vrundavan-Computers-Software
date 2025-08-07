<script>
    $(document).on('change', '.search_by_course', function() {
        console.log("search_by_semester L-3");

        let instance = $('.search_by_course');
        let course_id = instance.val();
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');
        let semester_id = instance.attr("data-selectedSemesterId");

        if (!semester_id) {
            semester_id = $(".search_by_semester").attr("data-selectedSemesterId");
        }

        if (course_id) {
            $.ajax({
                type: 'POST',
                url: "{{ route('get-semester') }}",
                'beforeSend': function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                        'content'));
                },
                data: {
                    course_id: course_id
                },
                success: function(response) {
                    if (response.status) {
                        console.log("getBatch 30", response.data, course_id, semester_id);
                        let options = "<option value=''>Select Semester</option>";
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (semester_id == item.semester) {
                                    options += "<option value='" + item.semester +
                                        "' selected >" + item.semester + "</option>";
                                } else {
                                    options += "<option value='" + item.semester +
                                        "'>" +
                                        item.semester + "</option>";
                                }
                            });
                        }
                        // console.log("getBatch 33",instance.parent(),);
                        if ($(".search_by_semester").attr('data-append')) {
                            $(".search_by_semester").attr('data-selectedCourseId', course_id);
                            if ($(document).find("." + $(".search_by_semester").attr('data-append'))
                                .length > 0) {
                                $("." + $(".search_by_semester").attr('data-append')).empty();
                                $("." + $(".search_by_semester").attr('data-append')).append(
                                    options);
                            } else {
                                $("." + $(".search_by_semester").attr('data-append')).empty();
                                $("." + $(".search_by_semester").attr('data-append')).append(
                                    options);
                            }
                        } else if (instance.parent().parent().hasClass("col-md-6") || instance
                            .parent().parent().hasClass("col-md-4") || instance.parent().parent()
                            .hasClass("col-md-3")) {

                            let select_tag =
                                '<select name="course_id" class="form-select search_by_course';
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
                                html = '<div class="col-md-4 course_div">';
                            } else if (instance.parent().parent().hasClass("col-md-3")) {
                                html = '<div class="col-md-3 course_div">';
                            } else {
                                html = '<div class="col-md-6 course_div">';
                            }
                            html +=
                                '<div class="form-group"> <label for="course_id">Select Batch ';
                            if (is_required) {
                                html += '<span class="text-danger">*</span>';
                            }
                            html += '</label>';
                            html += select_tag;
                            html += '</div></div>';
                            $(".course_div").remove();
                            instance.parent().parent().after(html);
                        } else if (instance.parent("tr")) {
                            // console.log("getBatch 39", instance, instance.parent(), instance.next("th"));
                            $(".search_by_course").removeClass('d-none').empty().append(options);
                            /*
                            // $(".search_by_course").remove();
                            // instance.parent().next("th").remove();
                            // instance.parent().after('<th>'+options+'</th>');
                            */
                        } else {
                            console.log("getBatch 82", instance.parent());
                            instance.parent().after(options);
                        }
                        // $('.search_by_semester').trigger("change");
                        $('.select2').select2();
                    }
                }

            });

        }
    });
    $('.search_by_course').trigger("change");
</script>

<script>
$(document).ready(function() {
    console.log("search_by_department L-3");

    let instance = $('.search_by_department');
    let department_name = instance.val(); // current value
    let is_select2 = instance.hasClass('select2');

    if (!department_name) {
        department_name = instance.attr("data-selected-department-id");
    }

    $.ajax({
        type: 'GET',
        url: "{{ route('get_department') }}",
        success: function(response) {
            if (response.status === "true") {
                let options = "<option value=''>Select Department</option>";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, item) {
                        let selected = (department_name == item.name) ? "selected" : "";
                        options += `<option value='${item.name}' ${selected}>${item.name}</option>`;
                    });
                }
                instance.empty().append(options);
                if (is_select2) instance.select2();
            }
        },
        error: function(err) {
            console.error("Failed to fetch departments", err);
        }
    });
});
$('.search_by_department').trigger("change");
</script>
    
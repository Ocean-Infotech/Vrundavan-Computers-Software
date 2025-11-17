<script>
$(document).ready(function() {
    console.log("search_by_university L-3");

    let instance = $('.search_by_university');
    let university_name = instance.val(); // current value
    let is_select2 = instance.hasClass('select2');

    if (!university_name) {
        university_name = instance.attr("data-selected-university-id");
    }

    $.ajax({
        type: 'GET',
        url: "{{ route('get_university') }}",
        success: function(response) {
            if (response.status === "true") {
                let options = "<option value=''>Select University</option>";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, item) {
                        let selected = (university_name == item.name) ? "selected" : "";
                        options += `<option value='${item.name}' ${selected}>${item.name}</option>`;
                    });
                }
                instance.empty().append(options);
                if (is_select2) instance.select2();
            }
        },
        error: function(err) {
            console.error("Failed to fetch universities", err);
        }
    });
});

$('.search_by_university').trigger("change");
</script>

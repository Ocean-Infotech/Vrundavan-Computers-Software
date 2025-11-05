<script>
$(document).ready(function() {
    console.log("search_by_book L-3");

    let instance = $('.search_by_book');
    let selected_book_no = instance.val(); // current value
    let is_select2 = instance.hasClass('select2');

    if (!selected_book_no) {
        selected_book_no = instance.attr("data-selected-book-id");
    }

    $.ajax({
        type: 'GET',
        url: "{{ route('get_books') }}", // your defined API route
        success: function(response) {
            if (response.status === "true" || response.success === true) {
                let options = "<option value=''>Select Book</option>";

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, item) {
                        let selected = (selected_book_no == item.id) ? "selected" : "";
                        options += `<option value='${item.id}' ${selected}>${item.book_name}</option>`;
                    });
                }

                instance.empty().append(options);

                if (is_select2) instance.select2();
            }
        },
        error: function(err) {
            console.error("Failed to fetch book list", err);
        }
    });
});

$('.search_by_book').trigger("change");
</script>

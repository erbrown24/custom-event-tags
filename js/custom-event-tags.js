jQuery(document).ready(function($) {
    $('#event_tags').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: customEventTags.ajax_url,
                dataType: 'json',
                data: {
                    action: 'get_tag_suggestions',
                    term: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            // Append the selected tag to the input field.
            var currentTags = $(this).val();
            if (currentTags.length > 0) {
                currentTags += ', ' + ui.item.value;
            } else {
                currentTags = ui.item.value;
            }
            $(this).val(currentTags);
            return false;
        }
    });
});

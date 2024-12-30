jQuery(document).ready(function($) {
    var imageUploader;

    // Open Media Uploader
    $('#slider_image_button').click(function(e) {
        e.preventDefault();

        if (imageUploader) {
            imageUploader.open();
            return;
        }

        imageUploader = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageUploader.on('select', function() {
            var attachment = imageUploader.state().get('selection').first().toJSON();

            // Update the hidden input with the image URL
            $('#slider_image').val(attachment.url);

            // Dynamically update the image preview
            $('#slider_image_preview').html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;" />');

            // Add a "Remove Image" button if it doesn't already exist
            if (!$('#slider_image_remove_button').length) {
                $('#slider_image_preview').after('<button id="slider_image_remove_button" class="button button-secondary">Remove Image</button>');
            }
        });

        imageUploader.open();
    });

    // Remove Image
    $(document).on('click', '#slider_image_remove_button', function(e) {
        e.preventDefault();

        // Clear the hidden input
        $('#slider_image').val('');

        // Remove the image preview
        $('#slider_image_preview').html('');

        // Remove the "Remove Image" button
        $(this).remove();
    });
});

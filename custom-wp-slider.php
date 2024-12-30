<?php
/**
 * Plugin Name: Custom WP Slider
 * Description: A plugin to create a custom post type "Slider" with custom fields: Title, Description, Image.
 * Version: 1.0
 * Author: Ozgur M. Tunca
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
add_action('after_setup_theme', function() {
    add_theme_support('post-thumbnails', array('slider'));
});

// Register Custom Post Type
function custom_wp_slider_register_post_type() {
    $labels = array(
        'name'               => _x('WP Sliders', 'Post Type General Name', 'custom-wp-slider'),
        'singular_name'      => _x('WP Slider', 'Post Type Singular Name', 'custom-wp-slider'),
        'menu_name'          => __('WP Sliders', 'custom-wp-slider'),
        'all_items'          => __('All Sliders', 'custom-wp-slider'),
        'add_new_item'       => __('Add New Slider', 'custom-wp-slider'),
        'edit_item'          => __('Edit Slider', 'custom-wp-slider'),
        'view_item'          => __('View Slider', 'custom-wp-slider'),
        'search_items'       => __('Search Sliders', 'custom-wp-slider'),
        'not_found'          => __('No sliders found', 'custom-wp-slider'),
        'not_found_in_trash' => __('No sliders found in Trash', 'custom-wp-slider'),
    );

    $args = array(
        'label'               => __('WP Slider', 'custom-wp-slider'),
        'labels'              => $labels,
        'supports'            => array('title', 'thumbnail'),
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-images-alt2',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'has_archive'         => false,
        'rewrite'             => false,
        'query_var'           => false,
    );

    register_post_type('slider', $args);
}
add_action('init', 'custom_wp_slider_register_post_type');


/**
 * Frontend assets
 */
function wp_slider_assets() {
    wp_enqueue_style( 'slider-styles', plugin_dir_url(__FILE__) . '/assets/css/slider.css' );
    wp_enqueue_script( 'slider-scripts', plugin_dir_url(__FILE__) . '/assets/js/slider.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wp_slider_assets' );
/**
 * Admin assets
 */
function wp_slider_admin_assets() {
    wp_enqueue_media(); 
    wp_enqueue_style( 'slider-styles', plugin_dir_url(__FILE__) . '/assets/css/admin.css' );
    wp_enqueue_script( 'slider-scripts', plugin_dir_url(__FILE__) . '/assets/js/admin.js', array(), '1.0.0', true );
 
}
add_action('admin_enqueue_scripts', 'wp_slider_admin_assets');

// Add Meta Boxes
function custom_wp_slider_add_meta_boxes() {
    add_meta_box(
        'custom_wp_slider_meta_box',
        __('Slider Details', 'custom-wp-slider'),
        'custom_wp_slider_meta_box_callback',
        'slider',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'custom_wp_slider_add_meta_boxes');

// Meta Box Callback
function custom_wp_slider_meta_box_callback($post) {
    wp_nonce_field('custom_wp_slider_save_meta_box_data', 'custom_wp_slider_meta_box_nonce');

    $description = get_post_meta($post->ID, '_slider_description', true);
    $image = get_post_meta($post->ID, '_slider_image', true);

    // Description Field
    echo '<p><label for="slider_description">' . __('Slider Description', 'custom-wp-slider') . '</label></p>';
    echo '<textarea id="slider_description" name="slider_description" rows="4" style="width: 100%;">' . esc_textarea($description) . '</textarea>';

    // Image Upload Field
    echo '<p><label for="slider_image">' . __('Slider Image', 'custom-wp-slider') . '</label></p>';
    echo '<input type="hidden" id="slider_image" name="slider_image" value="' . esc_attr($image) . '">';
    echo '<button id="slider_image_button" class="button">' . __('Upload Image', 'custom-wp-slider') . '</button>';

    // Image Preview
    echo '<p id="slider_image_preview">';
    if ($image) {
        echo '<img src="' . esc_url($image) . '" style="max-width: 100%; height: auto;" />';
    }
    echo '</p>';

    // Remove Button
    if ($image) {
        echo '<button id="slider_image_remove_button" class="button button-secondary">' . __('Remove Image', 'custom-wp-slider') . '</button>';
    }
}


// Save Meta Box Data
function custom_wp_slider_save_meta_box_data($post_id) {
    if (!isset($_POST['custom_wp_slider_meta_box_nonce']) || !wp_verify_nonce($_POST['custom_wp_slider_meta_box_nonce'], 'custom_wp_slider_save_meta_box_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['slider_description'])) {
        update_post_meta($post_id, '_slider_description', sanitize_textarea_field($_POST['slider_description']));
    }

    if (isset($_POST['slider_image'])) {
        update_post_meta($post_id, '_slider_image', esc_url_raw($_POST['slider_image']));
    }
}
add_action('save_post', 'custom_wp_slider_save_meta_box_data');

// Set shortcode
function custom_wp_slider_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => -1,  
    ), $atts, 'custom_wp_slider');

    // Query Sliders
    $args = array(
        'post_type'      => 'slider',
        'posts_per_page' => intval($atts['limit']),
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No sliders found.</p>';
    }

    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/slider-template.php';
    return ob_get_clean();
}
add_shortcode('wp-slider', 'custom_wp_slider_shortcode');

// Modify the Columns in the All Sliders Table
function custom_wp_slider_add_columns($columns) {
    // Add new columns and reorder
    $new_columns = array(
        'cb'          => $columns['cb'],         // Checkbox column
        'title'       => __('Title', 'custom-wp-slider'),
        'thumbnail'   => __('Thumbnail', 'custom-wp-slider'),
        'description' => __('Description', 'custom-wp-slider'),
        'date'        => $columns['date'],       // Date column
    );
    return $new_columns;
}
add_filter('manage_edit-slider_columns', 'custom_wp_slider_add_columns');

// Populate the Custom Columns
function custom_wp_slider_populate_columns($column, $post_id) {
    switch ($column) {
        case 'thumbnail':
            $image = get_post_meta($post_id, '_slider_image', true);
            if ($image) {
                echo '<img src="' . esc_url($image) . '" style="width: 75px; height: auto;" />';
            } else {
                echo __('No Image', 'custom-wp-slider');
            }
            break;

        case 'description':
            $description = get_post_meta($post_id, '_slider_description', true);
            if ($description) {
                echo esc_html(wp_trim_words($description, 10, '...')); // Limit to 10 words
            } else {
                echo __('No Description', 'custom-wp-slider');
            }
            break;
    }
}
add_action('manage_slider_posts_custom_column', 'custom_wp_slider_populate_columns', 10, 2);

// Make Columns Sortable (Optional)
function custom_wp_slider_sortable_columns($columns) {
    $columns['description'] = 'description';
    return $columns;
}
add_filter('manage_edit-slider_sortable_columns', 'custom_wp_slider_sortable_columns');


function save_custom_thumbnail_for_uploaded_image($post_id) {
    // Only run for the 'slider' post type
    if (get_post_type($post_id) !== 'slider') {
        return;
    }

    // Get the uploaded image URL
    $image_url = get_post_meta($post_id, '_slider_image', true);
    if (empty($image_url)) {
        return; // No image found
    }

    // Translate URL to file path
    $upload_dir = wp_upload_dir();
    $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);

    if (!file_exists($file_path)) {
        return; // File doesn't exist
    }

    // Generate the custom size
    $editor = wp_get_image_editor($file_path);
    if (is_wp_error($editor)) {
        return; // Error loading image editor
    }

    $editor->resize(9999, 370, false); // Resize to custom size
    $resized_file = $editor->save();

    if (is_wp_error($resized_file)) {
        return; // Error during save
    }

    // Save the custom-sized image URL
    $resized_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $resized_file['path']);
    update_post_meta($post_id, '_slider_image_thumbnail', $resized_url);
}
add_action('save_post', 'save_custom_thumbnail_for_uploaded_image');

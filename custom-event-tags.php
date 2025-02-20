<?php
/*
Plugin Name: Custom Event Tags
Description: Enhances the event tag input functionality with AJAX-based tag suggestions and creation.
Version: 1.0
Author: Your Name
*/

// Enqueue jQuery UI and custom script on the event submission page (adjust condition as needed)
add_action('wp_enqueue_scripts', 'custom_event_tags_enqueue_scripts');
function custom_event_tags_enqueue_scripts() {
    // Optionally restrict this to only load on the event submission page
    if ( is_page('event-submission') ) { // Change 'event-submission' to your page slug or use another condition if needed
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('custom-event-tags', plugin_dir_url(__FILE__) . 'js/custom-event-tags.js', array('jquery', 'jquery-ui-autocomplete'), '1.0', true);
        wp_localize_script('custom-event-tags', 'customEventTags', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}

// AJAX handler to return tag suggestions
add_action('wp_ajax_get_tag_suggestions', 'custom_event_tags_get_tag_suggestions');
add_action('wp_ajax_nopriv_get_tag_suggestions', 'custom_event_tags_get_tag_suggestions');
function custom_event_tags_get_tag_suggestions() {
    $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
    $suggestions = array();

    if ( ! empty($term) ) {
        $tags = get_terms(array(
            'taxonomy'   => 'post_tag',
            'name__like' => $term,
            'hide_empty' => false
        ));
        if ( ! is_wp_error($tags) ) {
            foreach ($tags as $tag) {
                $suggestions[] = $tag->name;
            }
        }
    }

    wp_send_json($suggestions);
}

// Save tags when an event is saved using the custom event form.
add_filter('em_event_save', 'custom_event_save_tags', 1, 2);
function custom_event_save_tags($result, $EM_Event) {
    if ( ! empty($_POST['event_tags']) ) {
        // Get tags from the submitted string (comma-separated)
        $tags = explode(',', $_POST['event_tags']);
        $tags = array_map('sanitize_text_field', $tags);
        // Assign tags to the event (using post_tag taxonomy)
        wp_set_post_terms($EM_Event->post_id, $tags, 'post_tag');
    }
    return $result;
}

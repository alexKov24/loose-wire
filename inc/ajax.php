<?php
use LooseWire\WireManager;

// Hooks work with global functions
add_action('wp_ajax_loose_wire_pull', 'loose_wire_handle_pull');
add_action('wp_ajax_nopriv_loose_wire_pull', 'loose_wire_handle_pull');

function loose_wire_handle_pull()
{
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'loose_wire_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }

    $className = sanitize_text_field($_POST['wire_class']);
    $method = sanitize_text_field($_POST['wire_method']);
    $publicVars = json_decode(stripslashes($_POST['vars']), true);

    $manager = new WireManager();

    try {
        $html = $manager->pullTheWire($className, $method, $publicVars);
        wp_send_json_success(['html' => $html]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
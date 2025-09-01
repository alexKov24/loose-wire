<?php
/*
Plugin Name: Loose Wire
Plugin URI: https://koval.tech/projects/wp/loose-wire
Description: Inspired by Laravel's Live Wire allows you to define ajax interactions in php
Version: 0.0.1
Author: Alex Kovalev
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include files
require_once __DIR__ . "/inc/ajax.php";
require_once __DIR__ . "/inc/class-wire.php";
require_once __DIR__ . "/inc/class-wire-manager.php";




// Hook the enqueue function
add_action('wp_enqueue_scripts', 'loose_wire_enqueue_scripts');
function loose_wire_enqueue_scripts()
{

    wp_enqueue_script(
        'loose-wire-js',
        plugin_dir_url(__FILE__) . 'assets/loose-wire.js', // Fixed: Use plugin_dir_url()
        ['jquery'],
        '1.0.0',
        true
    );

    wp_localize_script('loose-wire-js', 'looseWireAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('loose_wire_nonce')
    ]);
}

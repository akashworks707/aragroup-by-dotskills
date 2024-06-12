<?php
include_once("inc/header-function/product_cat_search_filter.php");
include_once("inc/header-function/product_cat_list.php");
include_once("inc/wishlist-function/wishlist_function.php");

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );


//========= enqueue stylesheed and js files =========
//===================================================
function hello_elementor_child_scripts_styles() {
    // Enqueue the child theme stylesheet
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('hello-elementor-theme-style'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );
    
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    // Enqueue custom JavaScript with jQuery as a dependency
    wp_enqueue_script('hello-elementor-child-custom-js', get_stylesheet_directory_uri() . '/js/custom-js.js', array('jquery'), null, true);
    
    // Localize script for AJAX
    wp_localize_script('hello-elementor-child-custom-js', 'ajax_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
	
	   wp_localize_script('wishlist-script', 'wishlist_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wishlist_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20);


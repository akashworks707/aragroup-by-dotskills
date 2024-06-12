<?php
/* Template Name: Wishlist Page */

get_header();

global $wpdb;
$user_id = get_current_user_id();
$table_name = $wpdb->prefix . 'wishlist';

if ($user_id) {
    $wishlist_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id));
} else {
    $wishlist = isset($_COOKIE['wishlist']) ? unserialize($_COOKIE['wishlist']) : array();
    $wishlist_items = array();
    foreach ($wishlist as $product_id) {
        $wishlist_items[] = (object) ['product_id' => $product_id];
    }
}

if ($wishlist_items) {
    echo '<ul class="wishlist-items">';
    foreach ($wishlist_items as $item) {
        $product = wc_get_product($item->product_id);
        if ($product) {
            echo '<li data-product-id="' . $product->get_id() . '">';
            echo '<a href="' . get_permalink($product->get_id()) . '">' . $product->get_title() . '</a>';
            echo ' <a href="#" class="remove-from-wishlist-page" data-product-id="' . $product->get_id() . '">Remove</a>';
            echo '</li>';
        }
    }
    echo '</ul>';
} else {
    echo '<p>Your wishlist is empty.</p>';
}

get_footer();
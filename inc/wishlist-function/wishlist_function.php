<?php

//============= Wishlist ========
function create_wishlist_post_type() {
    register_post_type('wishlist',
        array(
            'labels'      => array(
                'name'          => __('Wishlists'),
                'singular_name' => __('Wishlist'),
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => 'wishlist'),
        )
    );
}
add_action('init', 'create_wishlist_post_type');

function create_wishlist_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wishlist';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        product_id mediumint(9) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_wishlist_table');

function add_wishlist_button() {
    global $product;
    $product_id = $product->get_id();
    $user_id = get_current_user_id();
    global $wpdb;
    $table_name = $wpdb->prefix . 'wishlist';

    if ($user_id) {
        $wishlist_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d AND product_id = %d", $user_id, $product_id));
    } else {
        $wishlist = isset($_COOKIE['wishlist']) ? unserialize($_COOKIE['wishlist']) : array();
        $wishlist_item = in_array($product_id, $wishlist) ? true : false;
    }

    $button_text = $wishlist_item ? 'Remove from Wishlist' : 'Add to Wishlist';
    $button_class = $wishlist_item ? 'remove-from-wishlist' : 'add-to-wishlist';

    echo '<a href="#" class="wishlist-toggle-button ' . $button_class . '" data-product-id="' . $product_id . '">' . $button_text . '</a>';
}
add_action('woocommerce_after_add_to_cart_button', 'add_wishlist_button');


function handle_wishlist() {
    global $wpdb;
    $user_id = get_current_user_id();
    $product_id = intval($_POST['product_id']);
    $action_type = $_POST['action_type']; // added

    $table_name = $wpdb->prefix . 'wishlist';

    if ($user_id) {
        if ($action_type == 'remove') { // added
            $wpdb->delete($table_name, array('user_id' => $user_id, 'product_id' => $product_id));
            $action = 'removed';
        } else {
            $wishlist_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d AND product_id = %d", $user_id, $product_id));
            if ($wishlist_item) {
                $wpdb->delete($table_name, array('user_id' => $user_id, 'product_id' => $product_id));
                $action = 'removed';
            } else {
                $wpdb->insert($table_name, array('user_id' => $user_id, 'product_id' => $product_id));
                $action = 'added';
            }
        }
    } else {
        $wishlist = isset($_COOKIE['wishlist']) ? unserialize($_COOKIE['wishlist']) : array();
        if ($action_type == 'remove') { // added
            $wishlist = array_diff($wishlist, array($product_id));
            $action = 'removed';
        } else {
            if (in_array($product_id, $wishlist)) {
                $wishlist = array_diff($wishlist, array($product_id));
                $action = 'removed';
            } else {
                $wishlist[] = $product_id;
                $action = 'added';
            }
        }
        setcookie('wishlist', serialize($wishlist), time() + 3600 * 24 * 30, '/');
    }

    wp_send_json_success(array('action' => $action));
}
add_action('wp_ajax_add_to_wishlist', 'handle_wishlist');
add_action('wp_ajax_nopriv_add_to_wishlist', 'handle_wishlist');



function wishlist_scripts() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function updateWishlistCount() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_wishlist_count'
                },
                success: function(response) {
                    if (response.success) {
                        $('.wishlist-count').text(response.data.count);
                    }
                }
            });
        }

        $('.wishlist-toggle-button').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var product_id = $button.data('product-id');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: product_id,
                    action_type: $button.hasClass('remove-from-wishlist') ? 'remove' : 'toggle'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.action === 'added') {
                            $button.removeClass('add-to-wishlist').addClass('remove-from-wishlist').text('Remove from Wishlist');
                        } else {
                            $button.removeClass('remove-from-wishlist').addClass('add-to-wishlist').text('Add to Wishlist');
                        }
                        updateWishlistCount();
                    }
                }
            });
        });

        $('.remove-from-wishlist-page').on('click', function(e) {
            e.preventDefault();
            var $link = $(this);
            var product_id = $link.data('product-id');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: product_id,
                    action_type: 'remove'
                },
                success: function(response) {
                    if (response.success) {
                        $link.closest('li').remove();
                        updateWishlistCount();
                    }
                }
            });
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'wishlist_scripts');



function get_wishlist_count() {
    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'wishlist';
    $count = 0;

    if ($user_id) {
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE user_id = %d", $user_id));
    } else {
        $wishlist = isset($_COOKIE['wishlist']) ? unserialize($_COOKIE['wishlist']) : array();
        $count = count($wishlist);
    }

    return $count;
}
function wishlist_count_shortcode() {
    $count = get_wishlist_count();
    return '<a href="/wishlist"><svg aria-hidden="true" class="e-font-icon-svg e-far-heart" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M458.4 64.3C400.6 15.7 311.3 23 256 79.3 200.7 23 111.4 15.6 53.6 64.3-21.6 127.6-10.6 230.8 43 285.5l175.4 178.7c10 10.2 23.4 15.9 37.6 15.9 14.3 0 27.6-5.6 37.6-15.8L469 285.6c53.5-54.7 64.7-157.9-10.6-221.3zm-23.6 187.5L259.4 430.5c-2.4 2.4-4.4 2.4-6.8 0L77.2 251.8c-36.5-37.2-43.9-107.6 7.3-150.7 38.9-32.7 98.9-27.8 136.5 10.5l35 35.7 35-35.7c37.8-38.5 97.8-43.2 136.5-10.6 51.1 43.1 43.5 113.9 7.3 150.8z"></path></svg> <span class="wishlist-count">' . $count . '</span></a>';
}
add_shortcode('wishlist_count', 'wishlist_count_shortcode');

function ajax_get_wishlist_count() {
    $count = get_wishlist_count();
    wp_send_json_success(array('count' => $count));
}
add_action('wp_ajax_get_wishlist_count', 'ajax_get_wishlist_count');
add_action('wp_ajax_nopriv_get_wishlist_count', 'ajax_get_wishlist_count');

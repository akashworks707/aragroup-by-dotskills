<?php
// Register Shortcode
function shop_categories_dropdown_shortcode() {
    // Start output buffering
    ob_start();
    ?>
    <div class="shop-categories-dropdown">
        <button class="shop-categories-btn"><i aria-hidden="true" class="fas fa-bars"></i><span>Shop Categories </span></span><i aria-hidden="true" class="fas fa-angle-down"></i></button>
        <div class="shop-categories-dropdown-content">
            <?php
            $args = array(
                'taxonomy' => 'product_cat',
                'orderby' => 'name',
                'order'   => 'ASC',
                'parent'  => 0,
                'exclude' => get_term_by('slug', 'uncategorized', 'product_cat')->term_id
            );
            $parent_categories = get_categories($args);
            foreach($parent_categories as $parent_category) {
                echo '<div class="parent-category">';
                $thumbnail_id = get_term_meta($parent_category->term_id, 'thumbnail_id', true);
                $image = wp_get_attachment_url($thumbnail_id);
                if ($image) {
                    echo '<div class="parent-category-image"><img src="' . esc_url($image) . '" alt="' . esc_attr($parent_category->name) . '" /></div>';
                }
                echo '<a class="parent-category-link" href="' . get_term_link($parent_category) . '">' . $parent_category->name . '</a>';
                
                // Get child categories
                $child_args = array(
                    'taxonomy' => 'product_cat',
                    'orderby' => 'name',
                    'order'   => 'ASC',
                    'parent'  => $parent_category->term_id
                );
                $child_categories = get_categories($child_args);
                
                if (!empty($child_categories)) {
                    echo '<div class="child-categories">';
                    foreach($child_categories as $child_category) {
                        echo '<a class="child-category-link" href="' . get_term_link($child_category) . '">' . $child_category->name . '</a>';
                    }
                    echo '</div>';
                }
                
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <?php
    // Return the output
    return ob_get_clean();
}
add_shortcode('shop_categories_dropdown', 'shop_categories_dropdown_shortcode');

function enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            $('.shop-categories-dropdown').hover(
                function() {
                    $(this).find('.shop-categories-dropdown-content').stop(true, true).slideDown(300);
                }, 
                function() {
                    $(this).find('.shop-categories-dropdown-content').stop(true, true).slideUp(300);
                }
            );
        });
    ");
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

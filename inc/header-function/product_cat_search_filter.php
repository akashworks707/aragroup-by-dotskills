<?php

//======= Header categories and subsubcategories search =====
function custom_product_search_form_with_subcategories() {
    // Get all top-level product categories except "Uncategorized"
    $product_categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => 0,
        'exclude' => array(get_term_by('slug', 'uncategorized', 'product_cat')->term_id)
    ));
    ?>
    <form role="search" method="get" id="product-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <select name="product_cat" id="product-category">
            <option value=""><?php _e('All Categories', 'woocommerce'); ?></option>
            <?php foreach ($product_categories as $category) : ?>
                <option value="<?php echo esc_attr($category->slug); ?>"><?php echo esc_html($category->name); ?></option>
                <?php 
                // Get subcategories of this category
                $subcategories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'parent' => $category->term_id
                ));
                foreach ($subcategories as $subcategory) : ?>
                    <option value="<?php echo esc_attr($subcategory->slug); ?>">&nbsp;&nbsp;&nbsp;<?php echo esc_html($subcategory->name); ?></option>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </select>
		<input type="hidden" name="post_type" value="product" />
        <input type="search" id="product-search-field" class="search-field" placeholder="<?php echo esc_attr__('Search for products', 'woocommerce'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
        
        <button type="submit" class="search-submit"><?php echo esc_html__('Search', 'woocommerce'); ?></button>
    </form>
    <?php
}
add_shortcode('custom_product_search', 'custom_product_search_form_with_subcategories');

?>
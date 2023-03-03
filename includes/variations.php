<?php
function insert_product_variations ($post_id, $variations)  
{
    $variation_data = array();
    foreach ($variations as $index => $variation)
    {
        $variation_post = array( // Setup the post data for the variation

            'post_title'  => 'Variation #'.$index.' of '.count($variations).' for product#'. $post_id,
            'post_name'   => 'product-'.$post_id.'-variation-'.$index,
            'post_status' => 'publish',
            'post_parent' => $post_id,
            'post_type'   => 'product_variation',
            'guid'        => home_url() . '/?product_variation=product-' . $post_id . '-variation-' . $index
        );

        $variation_post_id = wp_insert_post($variation_post); // Insert the variation
        // $variation_data []= $variation_post_id;

        foreach ($variation['attributes'] as $attribute => $value) // Loop through the variations attributes
        {   
            $attribute_term = get_term_by('name', $value, 'pa_'.$attribute); // We need to insert the slug not the name into the variation post meta

            update_post_meta($variation_post_id, 'attribute_pa_'.$attribute, $attribute_term->slug);
          // Again without variables: update_post_meta(25, 'attribute_pa_size', 'small')

            if ($attribute == 'color') {
                $variation_data []= array($value => $variation_post_id);
            }
        }

        update_post_meta($variation_post_id, '_price', $variation['price']);
        update_post_meta($variation_post_id, '_regular_price', $variation['price']);
    }
    
    return $variation_data;
}

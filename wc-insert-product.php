<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../wp-load.php';
include_once 'includes/attributes.php';
include_once 'includes/variations.php';
include_once 'includes/attachments.php';
include_once 'includes/functions.php';

function insert_product ($product_data)  
{
    $post = array( // Set up the basic post data to insert for our product

        'post_author'  => 1,
        'post_content' => $product_data['description'],
        'post_excerpt' => $product_data['short_description'],
        'post_status'  => 'publish',
        'post_title'   => $product_data['name'],
        'post_parent'  => '',
        'post_type'    => 'product'
    );

    $post_id = wp_insert_post($post); // Insert the post returning the new post id

    if (!$post_id) // If there is no post id something has gone wrong so don't proceed
    {
        return false;
    }

    update_post_meta($post_id, '_sku', $product_data['sku']); // Set its SKU
    update_post_meta( $post_id,'_visibility','visible'); // Set the product to visible, if not it won't show on the front end

    wp_set_object_terms($post_id, $product_data['categories'], 'product_cat'); // Set up its categories
    wp_set_object_terms($post_id, 'variable', 'product_type'); // Set it to a variable product type

    insert_product_attributes($post_id, $product_data['available_attributes'], $product_data['variations']); // Add attributes passing the new post id, attributes & variations
    $variations = insert_product_variations($post_id, $product_data['variations']); // Insert variations passing the new post id & variations   
    print_r($variations);
    // Adding Product Gallery Images
    // 

    $filename = [];

    foreach ($product_data['attachments'] as $value) {
        $filename []= pathinfo($value, PATHINFO_BASENAME);
    }

    $agents = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.73 Safari/537.36";
    $tmp = 'tmp/';

    $multicurl = multiCurl($product_data['attachments'], $agents, $tmp, $filename);

    $att_ids = array();

    foreach ($multicurl as $image)  
    {
        $att_ids[] = insert_product_attachment($image, $post_id, $product_data['name']);
    }

    for ($i=0; $i < count($variations); $i++) { 
        add_post_meta($variations[$i]['Black'], '_thumbnail_id', $att_ids[0]);
        add_post_meta($variations[$i]['White'], '_thumbnail_id', $att_ids[1]);
    }
    // add_post_meta($variations['Black'], '_thumbnail_id', $att_ids[0]);
    // add_post_meta($variations['White'], '_thumbnail_id', $att_ids[1]);

    // for ($i=0; $i < count($att_ids); $i++) { 
    //     add_post_meta($variations[$i], '_thumbnail_id', $att_ids[$i]);
    // }

    add_post_meta($post_id, '_thumbnail_id', $att_ids[0]);
    unset($att_ids[0]);
    add_post_meta($post_id, '_product_image_gallery', implode(',', $att_ids)); 
    
}

global $wpdb;

$stmt = $wpdb->get_results("SELECT * FROM casing WHERE status = 0 ORDER BY id DESC LIMIT 1"); // not random

foreach ($stmt as $res);

$product_data = array(
    "name" => $res->title,
    "sku" => "AE3416",
    "description" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ac erat maximus augue accumsan egestas. Quisque posuere augue quis libero molestie posuere.",
    "short_description" => "maximus augue accumsan egestas. Quisque posuere augue quis libero molestie posuere maximus augue accumsan egestas. Quisque posuere augue quis libero molestie posuere", 
    "categories" => array( $res->vendor, $res->product_type ),
    "available_attributes" => array( "color", "material" ), 
    "attachments" => array( $res->black, $res->white ),
    "variations" => array(
            array(
                    "attributes" => array(
                            "color" => "Black",
                            "material" => "Plastic"
                        ),
                    "price" => "17.99"
                ),
            array(
                    "attributes" => array(
                            "color" => "White",
                            "material" => "Plastic"
                        ),
                    "price" => "17.99"
                ),
            array(
                    "attributes" => array(
                            "color" => "Black",
                            "material" => "Rubber"
                        ),
                    "price" => "17.99"
                ),
            array(
                    "attributes" => array(
                            "color" => "White",
                            "material" => "Rubber"
                        ),
                    "price" => "17.99"
                )
        )
);

var_dump($product_data);

insert_product($product_data);

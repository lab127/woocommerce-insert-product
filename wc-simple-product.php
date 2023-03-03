<?php
/**	
- simple produk tanpa vaiasi
- menggunakan wp_defer_term_counting(); dan wp_defer_comment_counting();
- tunjukan lama eksekusi
- premade slug tabel post_name
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../wp-load.php';
include 'includes/functions.php';
include 'includes/attachments.php';

function insert_product ($product_data) 
{

	$post_date = randomDate('2018-01-01', date("Y-m-d"));

	$new_post = array( // Set up the basic post data to insert for our product
		'post_author'  => $product_data['author'],
		'post_content' => $product_data['description'],
		'post_excerpt' => $product_data['short_description'],
		'post_status'  => 'publish',
		'post_title'   => $product_data['name'],
		'post_name'    => getToken(6) . "-" . slugify($product_data['name']),
		'post_parent'  => '',
		'post_date'    => $post_date,
		'post_type'    => 'product'
	);

	$post_id = wp_insert_post($new_post); // Insert the post returning the new post id

	if (!$post_id) // If there is no post id something has gone wrong so don't proceed
	{
		return false;
	}

	update_post_meta( $post_id, '_sku', $product_data['sku']); // Set its SKU
	update_post_meta( $post_id,'_visibility','visible'); // Set the product to visible, if not it won't show on the front end
	update_post_meta( $post_id, '_price', $product_data['price'] );
	update_post_meta( $post_id, '_regular_price', $product_data['price'] );
	update_post_meta( $post_id, '_stock_status', 'instock');
	update_post_meta( $post_id, 'total_sales', '0');
	update_post_meta( $post_id, '_downloadable', 'no'); // digital product
	update_post_meta( $post_id, '_virtual', 'no'); // digital product
	update_post_meta( $post_id, '_purchase_note', "" );
	update_post_meta( $post_id, '_featured', "no" );
	update_post_meta( $post_id, '_weight', 3 );
	update_post_meta( $post_id, '_length', 6 );
	update_post_meta( $post_id, '_width', 3 );
	update_post_meta( $post_id, '_height', 1 );
	update_post_meta( $post_id, '_sale_price_dates_from', "" );
	update_post_meta( $post_id, '_sale_price_dates_to', "" );
	update_post_meta( $post_id, '_sold_individually', "" );
	update_post_meta( $post_id, '_manage_stock', "no" );
	update_post_meta( $post_id, '_backorders', "no" );
	update_post_meta( $post_id, '_stock', 100 );

	wp_set_object_terms($post_id, $product_data['categories'], 'product_cat'); // Set up its categories

	$attachment_id = insert_product_attachment($product_data['author'], $product_data['attachment'], $post_id, $product_data['name'], $post_date);

	add_post_meta($post_id, '_thumbnail_id', $attachment_id);
    
}

global $wpdb;

// $row = $wpdb->get_row("SELECT * FROM casing WHERE vendor = 'apple' AND status = 0");

$row = $wpdb->get_row("SELECT * FROM casing WHERE product_type = 'iphone_x' AND status = 0");

$product_types = array(
		"iphone_5" => "iPhone 5/5s/SE",
		"iphone_6" => "iPhone 6/6s",
		"iphone_6_plus" => "iPhone 6/6s Plus",
		"iphone_7" => "iPhone 7/8",
		"iphone_7_plus" => "iPhone 7/8 Plus",
		"iphone_x" => "iPhone X",
		"galaxy_note_8" => "Samsung Galaxy Note 8",
		"galaxy_s6" => "Samsung Galaxy S6",
		"galaxy_s6_edge" => "Samsung Galaxy S6 Edge",
		"galaxy_s6_edge_plus" => "Samsung Galaxy S6 Edge Plus",
		"galaxy_s7" => "Samsung Galaxy S7",
		"galaxy_s7_edge" => "Samsung Galaxy S7 Edge",
		"galaxy_s8" => "Samsung Galaxy S8",
		"galaxy_s8_plus" => "Samsung Galaxy S8 Plus",
		"galaxy_s9" => "Samsung Galaxy S9",
		"galaxy_s9_plus" => "Samsung Galaxy S9 Plus"
	);

foreach ($product_types as $key => $value) {
	if ($row->product_type == $key) {
		$product_type = $value;
	}
}

$product_vendors = array(
		"apple" => "Apple",
		"samsung" => "Samsung"
	);

foreach ($product_vendors as $key => $value) {
	if ($row->vendor == $key) {
		$vendor = $value;
	}
}

$product_title = str_replace(' ' . $row->product_type, '', $row->title);
$product_name = $product_title . ' ' . $product_type . ' Case';

$product_data = array(
	"author" => 2,
    "name" => $product_name,
    "sku" => $row->sku,
    "description" => "<p>This {$product_title} case provides a protective yet stylish shield to your {$product_type} against accidental impacts—on the back, sides, and corners.</p>

    	<p>With slim and sleek look, make sure you will get good experience in your hand. The image is fully printed on aluminum inlay attached to the case. Available in Hard case (Plastic) with high flexibility and cover to all buttons Finished with protective glossy coating for image clarity and durability.</p>
		<table width=\"100%\"><tbody>
		<tr>
		<td><strong>Designed for</strong></td>
		<td>{$vendor} {$product_type}</td>
		</tr>
		<tr>
		<td><strong>Model</strong></td>
		<td>Snap-fit</td>
		</tr>
		</tbody></table>",
    "short_description" => "<p>{$product_title} premium case gives you stylish shield to {$product_type} from accidental drops and scratches.</p>", 
    "categories" => array( $vendor, $product_type ),
    "price" => "12.99",
    "attachment" => 'tmp/' . $row->vendor . '/' . $row->product_type . '/' . $row->filename
);

// echo "<pre>";
// print_r($product_data);
$start = microtime(true);

wp_defer_term_counting( true );
wp_defer_comment_counting( true );

insert_product($product_data);
var_dump("\n", "execute ok -> {$product_name}");

$update = $wpdb->update(
		'casing',
		array('status' => 1),
		array('id' => $row->id)
	);

wp_defer_term_counting( false );
wp_defer_comment_counting( false );

$duration = microtime(true) - $start;
var_dump("\n", $duration);
// echo '<meta http-equiv="refresh" content="2">';

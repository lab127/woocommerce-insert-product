<?php

function insert_product_attachment ($author, $fileloc, $post_id, $filealt="images", $post_date=null) {

    global $wpdb;

    $filename = pathinfo($fileloc, PATHINFO_FILENAME); // filename
    $basename = pathinfo($fileloc, PATHINFO_BASENAME); // basename.ext

    $source = $fileloc; // This is going to be where the image is located, depending on the fileloc you pass in this may not be needed

    if (!empty($post_date)) {
        $totime = strtotime($post_date);
        $time = date('Y/m', $totime);
        $upload = wp_upload_dir($time);
    } else {
        $upload = wp_upload_dir();
    }

    $destination = $upload['path']. '/' . $basename; // Specify where we wish to upload the file, generally in the wp uploads directory
    $cek = $wpdb->get_var("SELECT count(ID) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_name = '".$filename."' ");

    if ($cek < 1) {
        
        // Move the file
        if (copy($source,$destination)) {
          @unlink($source);
        }

        $filetype = wp_check_filetype($destination); // Get the mime type of the file

        if (!empty($post_date)) {
            $attachment = array( // Set up our images post data
                'post_mime_type' => $filetype['type'],
                'post_title'     => $filename,
                'post_author'    => $author,
                'post_content'   => '',
                'post_date'   => $post_date
            );
        } else {
            $attachment = array( // Set up our images post data
                'post_mime_type' => $filetype['type'],
                'post_title'     => $filename,
                'post_author'    => $author,
                'post_content'   => ''
            );
        }

        $attach_id = wp_insert_attachment( $attachment, $destination, $post_id ); // Attach/upload image to the specified post id, think of this as adding a new post.
        // you must first include the image.php file
        // for the function wp_generate_attachment_metadata() to work
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $destination ); // Generate the necessary attachment data, filesize, height, width etc.

        wp_update_attachment_metadata( $attach_id, $attach_data ); // Add the above meta data data to our new image post
        echo '<div style="color:#23a127;">Attachment Sukses!</div>';
        
    } else {
        echo '<div style="color:#ff291c;">Attachment udah ada mas bro!</div>';
    }

    add_post_meta($attach_id, '_wp_attachment_image_alt', $filealt); // Add the alt text to our new image post

    return $attach_id; // Return the images id to use in the below functions
}

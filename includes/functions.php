<?php

function multiCurl($urls, $user_agent, $folder, $filename) {

    $filepaths = array();

    // cURL multi-handle
    $mh = curl_multi_init();

    // This will hold cURLS requests for each file
    $requests = array();

    $options = array(
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER    => true, 
        CURLOPT_USERAGENT      => $user_agent,
        CURLOPT_HEADER         => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_RETURNTRANSFER => true
    );

    //Corresponding filestream array for each file
    $fstreams = array();
    $i = 0;
    foreach ($urls as $key => $url) {
        // Add initialized cURL object to array
        $requests[$key] = curl_init($url);

        // Set cURL object options
        curl_setopt_array($requests[$key], $options);
        // Extract filename from URl and create appropriate local path
        // $path     = parse_url($url, PHP_URL_PATH);
        // $filename = pathinfo($path, PATHINFO_BASENAME); // Or whatever you want

        $filepath = $folder . $filename[$i];

        $filepaths []= $filepath;

        // Open a filestream for each file and assign it to corresponding cURL object
        $fstreams[$key] = fopen($filepath, 'w');
        curl_setopt($requests[$key], CURLOPT_FILE, $fstreams[$key]);

        // Add cURL object to multi-handle
        curl_multi_add_handle($mh, $requests[$key]);
        $i++;
    }

    // Do while all request have been completed
    do {
       curl_multi_exec($mh, $active);
    } while ($active > 0);

    
    // Collect all data here and clean up

    foreach ($requests as $key => $request) {

        //$returned[$key] = curl_multi_getcontent($request); // Use this if you're not downloading into file, also remove CURLOPT_FILE option and fstreams array
        curl_multi_remove_handle($mh, $request); //assuming we're being responsible about our resource management
        curl_close($request);                    //being responsible again.  THIS MUST GO AFTER curl_multi_getcontent();
        fclose($fstreams[$key]);
        
    }

    curl_multi_close($mh);

    return $filepaths;

}

// Find a randomDate between $start_date and $end_date
function randomDate($start_date, $end_date) {
    // Convert to timetamps
    $min = strtotime($start_date);
    $max = strtotime($end_date);

    // Generate random number using above bounds
    $val = rand($min, $max);

    // Convert back to desired date format
    return date('Y-m-d H:i:s', $val);
}

// https://stackoverflow.com/a/13733588
function crypto_rand_secure($min, $max) {
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length) {
    $token = "";
    $codeAlphabet = "abcdefghijklmnopqrstuvwxyz";
    // $codeAlphabet.= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }

    return $token;
}

function slugify($text) { 

    // 24 mei 2015: decode htmlspecialchars
    $text = htmlspecialchars_decode($text);

    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim
    $text = trim($text, '-');

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // lowercase
    $text = strtolower($text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;

}


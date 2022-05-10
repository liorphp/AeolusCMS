<?php

function redirect($url, $permanent = false) {
	if($permanent) {
            header('HTTP/1.1 301 Moved Permanently');
	}
	header('Location: '.$url);
	exit();
}

function redirectToHome($permanent = false) {
    redirect('/', $permanent);
}

function redirectToLogin($permanent = false) {
    $_SESSION['redirect'] = $_SERVER["REQUEST_URI"];
    redirect('/user/login', $permanent);
}

function redirectToAdmin($permanent = false) {
    redirect('/admin/', $permanent);
}

function encrypt_num($input) {
    $input = (string)$input;
 
    $inputlen = strlen($input);// Counts number characters in string $input
    $randkey = rand(1, 9); // Gets a random number between 1 and 9
 
    $i = 0;
    $inputchr = array();
    while ($i < $inputlen) {
        $inputchr[$i] = (ord($input[$i]) - $randkey);//encrpytion 
        $i++; // For the loop to function
    }
 
    $encrypted = implode('.', $inputchr) . '.' . (ord($randkey) + \intval(\AeolusCMS\App::getConfig('encrypt_param')));
    return base64_encode($encrypted);
}

function decrypt_num($input) {
    $input = base64_decode($input);
    $input_count = strlen($input);

    $dec = explode(".", $input);// splits up the string to any array
    $x = count($dec);
    $y = $x-1;// To get the key of the last bit in the array 

    $calc = \intval($dec[$y]) - \intval(\AeolusCMS\App::getConfig('encrypt_param'));
    $randkey = chr($calc);// works out the randkey number

    $i = 0;

     $real = '';
    while ($i < $y) {
        $array[$i] = $dec[$i]+$randkey; // Works out the ascii characters actual numbers
        $real .= chr($array[$i]); //The actual decryption

        $i++;
    }
 
    $input = $real;
    return $input;
}

function percentage($val1, $val2, $precision)  {
    $division = $val1 / $val2;

    $res = $division * 100;

    $res = round($res, $precision);

    return $res;
}

function js_str($s) {
    return addcslashes($s, "\0..\37\"\\");
}

function js_array($array) {
    $temp = array_map('js_str', $array);
    return '[' . implode(',', $temp) . ']';
}

function num_format($num, $afterDot = 0) {
    return number_format($num, $afterDot, "." ,",");
}

function showDate($date, $format = 'd/m/Y H:i', $default = '') {
    if ($date == '') {
        return $default;
    } elseif (!is_numeric($date)) {
        $date = strtotime($date);
    }

    return date($format, $date);
}

function current_date() {
    return date("Y-m-d H:i:s");
}

function timeToTimestamp($time) {
    return date('Y-m-d H:i:s', $time);
}

function sortByOrder($a, $b) {
    $a = (array)$a;
    $b = (array)$b;
    return $b['order'] - $a['order'];
}

function sortById($a, $b) {
    $a = (array)$a;
    $b = (array)$b;
    return $b['id'] - $a['id'];
}

function getUserIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function formatBytes($bytes, $precision = 2) {
    $unit = ["B", "KB", "MB", "GB"];
    $exp = floor(log($bytes, 1024)) | 0;
    return round($bytes / (pow(1024, $exp)), $precision).$unit[$exp];
}

function is_decimal( $val ) {
    return is_numeric( $val ) && floor( $val ) != $val;
}

function ssl_file_get_contents($url) {
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
    return file_get_contents($url, false, stream_context_create($arrContextOptions));
}

function arrayTrim(&$array) {
    $array = array_map('trim', $array);
}

function checkUserAccess($access) {
    return (\AeolusCMS\App::$user->getAccessNum() >= $access);
}

function removeEmptyDirs($path) {
    $dirs = glob($path . "/*", GLOB_ONLYDIR);

    foreach($dirs as $dir) {
        $files = glob($dir . "/*");
        $innerDirs = glob($dir . "/*", GLOB_ONLYDIR);
        if(empty($files)) {
            rmdir($dir);
        } elseif(!empty($innerDirs)) {
            removeEmptyDirs($dir);
        }
    }
}

function bot_detected() {
    return (
        isset($_SERVER['HTTP_USER_AGENT'])
        && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
    );
}

function utf_8_bom() {
    return chr(239) . chr(187) . chr(191);
}

function roundToNearestFraction( $number, $fractionAsDecimal ) {
    $factor = 1 / $fractionAsDecimal;
    return round( $number * $factor ) / $factor;
}

include_once 'aeolus.php';
include_once 'string.php';
include_once 'array.php';
include_once 'extra.php';
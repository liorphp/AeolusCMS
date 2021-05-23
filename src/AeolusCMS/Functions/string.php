<?php
function slug($str) {
    $str = strtolower(trim($str));
    str_replace(array('(', ')', '"', "'", '[', ']', ',', '#', '?', '–'), ' ', $str);
    $str = preg_replace('/[^A-Za-z0-9א-ת_\-]/', '-', $str);
    $str = preg_replace('/-+/', "-", $str);
    return trim($str, '-');
}

function checkUrlAlias($alias) {
    return !preg_match('/[^A-Za-z0-9א-ת_\-\/]/', $alias);
}

function random_string($length) {
    $key = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $key;
}

function only_numbers($str) {
    return preg_replace("/[^0-9]/", "", $str);
}

function format_phone($phone) {
    // note: making sure we have something
    if($phone == '') { return ''; }
    // note: strip out everything but numbers
    $phone = \only_numbers($phone);
    $length = strlen($phone);
    switch($length) {
        case 9:
            return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{4})/", "$1-$2$3", $phone);
            break;
        case 10:
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2$3", $phone);
            break;
        case 11:
            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1($2) $3-$4", $phone);
            break;
        default:
            return $phone;
            break;
    }
}

function check_il_phone_number($phone) {
    $phone = \only_numbers($phone);

    $pattern = '/^(050|052|053|054|055|057|058|02|03|04|08|09|072|073|076|077|078)-?\d{7,7}$/';

    if (!preg_match( $pattern, $phone )) {
        return false;
    } else {
        return true;
    }
}

function remove_utf8_bom($text) {
    $bom = pack('H*','EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}

function day_name($day_number) {
    $dayNames = array(
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    );

    if (isset($dayNames[$day_number])) {
        return $dayNames[$day_number];
    } else {
        return '';
    }
}
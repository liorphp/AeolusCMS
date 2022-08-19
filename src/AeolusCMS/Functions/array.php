<?php

function arrayFilterEmpty($arr){
    return ($arr !== NULL && $arr !== '');
}

function outputCSV($data, $file_name = 'file.csv', $remove = array()) {
    # output headers so that the file is downloaded rather than displayed
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$file_name");
    # Disable caching - HTTP 1.1
    header("Cache-Control: no-cache, no-store, must-revalidate");
    # Disable caching - HTTP 1.0
    header("Pragma: no-cache");
    # Disable caching - Proxies
    header("Expires: 0");

    # Start the ouput
    $output = fopen("php://output", "w");

    # Then loop through the rows
    foreach ($data as $row) {
        $row = (array)$row;
        foreach ($remove as $r) {
            if (isset($row[$r])) {
                unset($row[$r]);
            }
        }
        # Add the rows to the body
        fputcsv($output, $row); // here you can change delimiter/enclosure
    }
    # Close the stream off
    fclose($output);
}

function array_map_keys(array $array, callable $callable) : array {
    $map = [];
    foreach ($array as $key => $value) {
        $result = $callable($key, $value);
        $map[key($result)] = $result[key($result)];
    }
    return $map;
}

function array_to_x_editable_source($array) {
    $ret = array();
    foreach ($array as $key => $val) {
        $ret[] = "'".$key."' : '". \addslashes($val) ."'";
    }

    return '{' . implode(', ', $ret) . '}';
}
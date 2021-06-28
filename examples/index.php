<?php
error_reporting(E_ALL);
define('ROOT_PATH', dirname(__file__));
header('Content-Type: text/html; charset=utf-8');

require '../vendor/autoload.php';

global $activeModules;
$activeModules = array(
    'Home',
    'Admin',
    'Pages',
    'SEO'
);

$config = array(
    'db' => array(
        'type' => 'mysql',
        'host' => 'localhost',
        'name' => 'aeolus_cms',
        'user' => 'root',
        'pass' => '',
    )
);

try {
    $app = new \AeolusCMS\App($config);
} catch (\Exception $e) {

}
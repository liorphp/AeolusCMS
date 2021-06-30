<?php
namespace AeolusCMS\Helpers;

use AeolusCMS\App;
use AeolusCMS\Libs\View\View;

class Hooks_list {
    /* @var View $view */
    static protected $view = null;

    function __construct() {
        self::$view = &App::$view;
        $class_name = str_replace('_hooks', '', get_class($this));
        $method_names = preg_grep('/^'.$class_name.'_/', get_class_methods($this));

        foreach ($method_names as $method) {
            $do_action_name = preg_replace('/'.$class_name.'_/i', '', $method, 1);
            $class = new \ReflectionClass($class_name . '_hooks');
            
            $rfMethod = $class->getMethod($method);
            App::$hooks->add_action($do_action_name, $rfMethod, 10, $rfMethod->getNumberOfParameters());
        }
    }
    
    static function new_hook_class($class_name) {
        $class_name .=  '_hooks';
        if (class_exists($class_name)) {
            $cont_hook = new $class_name();
        }
    }
}
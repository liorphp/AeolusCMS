<?php
namespace AeolusCMS\Helpers;

class retAjax {
    const ALERT_TYPE_SUCCESS = 'success';
    const ALERT_TYPE_ERROR = 'error';
    const ALERT_TYPE_WARNING = 'warning';
    const ALERT_TYPE_INFO = 'info';
    const ALERT_TYPE_QUESTION = 'question';

    static private $status = true;
    static private $responseContentType = "application/x-json";
    
    static private $append = array();
    static private $pre_append = array();
    static private $replace = array();
    static private $remove_id = array();
    static private $js = array();
    static private $callbacks = array();
    static private $vars = array();
    static private $redirect = null;
    static private $html = '';
    static private $fatalError = false;
    static private $alert = array();
    static private $alertArray = array();
    static private $extra_array_ret = array();
    
    static public function hasFatalError($mode = true) {
        self::$fatalError = $mode;
    }

    static public function setStatus($status) {
        self::$status = $status;
    }

    static public function setAlert($alertText, $type = self::ALERT_TYPE_INFO, $alertTitle = '', $confirm_text = "<i class='fa fa-check'></i>") {
        self::$alert = array(
            'text' => $alertText,
            'type' => $type,
            'title' => $alertTitle,
            'confirm' => $confirm_text
        );
    }

    public static function setAlertOne($alertText, $type = self::ALERT_TYPE_INFO, $alertTitle = '', $confirm_text = "<i class='fa fa-check'></i>") {
        self::$alertArray[] = array(
            'text' => $alertText,
            'type' => $type,
            'title' => $alertTitle,
            'confirm' => $confirm_text
        );
    }

    static public function addAppend($key, $value) {
        self::$append[] = array('obj' => $key, 'html' => $value);
    }
    
    static public function addPreAppend($key, $value) {
        self::$pre_append[] = array('obj' => $key, 'html' => $value);
    }
    
    static public function addReplace($key, $value) {
        self::$replace[$key] = $value;
    }

    static public function addRemoveId($key) {
        self::$remove_id[] = $key;
    }
    
    static public function addJs($value, $order = 100) {
        self::$js[] = array('order' => $order, 'code' => $value);
    }
    
    static public function addCallback($value) {
        self::$callbacks[] = $value;
    }

    static public function addCallbackPageReload() {
        self::addCallback('page_reload');
    }

    static public function setHtml($html) {
        self::$html = $html;
    }

    static public function setVar($key, $value) {
        self::$vars[$key] = $value;
    }

    static public function addExtraArrayRet($array) {
        self::$extra_array_ret = array_merge(self::$extra_array_ret, $array);
    }
    
    static function setRedirect($url) {
        self::$redirect = $url;
    }

    static protected function setResponseContentType($type) {
        self::$responseContentType = $type;
    }

    static public function ajaxResult($array) {
        @\header("Cache-Control: no-cache, must-revalidate", true);
        @\header("Expires: Sat, 26 Jul 1997 05:00:00 GMT", true);
        @\header("Content-Type: ".self::$responseContentType."; charset=utf-8", true, 200);
        
        echo \json_encode($array);
        die;
    }

    static public function build() {
        $ret = array();

        if (self::$fatalError) {
            $ret['fatal_error'] = true;
        } else {
            $ret['status'] = self::$status;
        
            if (!empty(self::$append))
                $ret['append'] = self::$append;
    
            if (!empty(self::$remove_id))
                $ret['remove_id'] = self::$remove_id;
            
            if (!empty(self::$pre_append))
                $ret['pre_append'] = self::$pre_append;
            
            if (!empty(self::$replace))
                $ret['replace'] = self::$replace;
            
            if (!empty(self::$js)) {
                usort(self::$js, 'sortByOrder');
                $ret['js'] = self::$js;
            }
            
            if (!empty(self::$vars))
                $ret['vars'] = self::$vars;
            
            if (!empty(self::$callbacks))
                $ret['callbacks'] = self::$callbacks;

            if (self::$redirect != null) {
                $ret['redirect'] = self::$redirect;
            }

            if (!empty(self::$alert)) {
                $ret['alert'] = self::$alert;
            }

            if (!empty(self::$alertArray)) {
                $ret['alertsArray'] = self::$alertArray;
            }
    
            $ret['html'] = self::$html;
        }

        foreach (self::$extra_array_ret as $key => $value) {
            if (!isset($ret[$key])) {
                $ret[$key] = $value;
            }
        }
                
        self::ajaxResult($ret);
    }
}

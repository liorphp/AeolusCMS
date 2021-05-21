<?php
namespace AeolusCMS\Helpers;

class Encryption {
    static public function generate_random_hash() {
        return md5(uniqid(microtime() . rand(), true));
    }
 
    static private function safe_b64encode($string) {
        $data = \base64_encode($string);
        $data = \str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
 
    static private function safe_b64decode($string) {
        $data = \str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = \strlen($data) % 4;
        if ($mod4) {
            $data .= \substr('====', $mod4);
        }
        return \base64_decode($data);
    }

    static public  function encode($password, $salt) {
        if(!$password){return false;}
        $options = ['cost' => 12];

        return \trim(\password_hash($password . $salt, PASSWORD_DEFAULT, $options));
    }

    static public function decode($password, $hash) {
        return \password_verify($password, $hash);
    }
}
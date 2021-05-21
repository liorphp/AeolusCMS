<?php
namespace AeolusCMS;

class App {
    public function __construct($config = array()) {
        $this->setConfig($config);
        $this->init();
        $this->appStating();
    }

    private function init() {
        define('ROOT_PATH', dirname(__file__));
        header('Content-Type: text/html; charset=utf-8');
    }

    private function appStating(){
    }

    private function setConfig(array $config) {
    }
}
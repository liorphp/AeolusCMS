<?php
namespace AeolusCMS\MVC\Controllers\Home;

use AeolusCMS\App;
use AeolusCMS\Libs\Controllers\SiteController;

class HomeController extends SiteController {
    /* @var HomeModel $model */
    public $model;

    protected function preLoader() {
        //$this->model = \loadModel::loadHomeModel();
    }
    
    public function index() {
        App::$hooks->do_action('homepage_index');

        $this->view->render();
    }
}
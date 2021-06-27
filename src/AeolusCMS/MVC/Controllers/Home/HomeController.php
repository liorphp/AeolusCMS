<?php
namespace AeolusCMS\MVC\Controllers\Home;

use AeolusCMS\App;
use AeolusCMS\Libs\Controllers\SiteController;
use AeolusCMS\MVC\Models\HomeModel;
use AeolusCMS\MVC\Models\loadModel;

class HomeController extends SiteController {
    /* @var HomeModel $model */
    public $model;

    protected function preLoader() {
        /* @var HomeModel $this->model */
        $this->model = loadModel::load('Home');
    }
    
    public function index() {
        App::$hooks->do_action('homepage_index');

        $this->view->render();
    }
}
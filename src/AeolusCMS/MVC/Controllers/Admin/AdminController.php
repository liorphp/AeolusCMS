<?php
namespace AeolusCMS\MVC\Controllers\Admin;

use AeolusCMS\App;
use AeolusCMS\Libs\Controllers\AdmController;
use AeolusCMS\MVC\Models\AdminModel;
use AeolusCMS\MVC\Models\loadModel;

class AdminController extends AdmController {
    /* @var AdminModel $model */
    public $model;

    protected function preLoader() {
        $this->model = loadModel::load('Admin');
    }

    public function index() {
        if ($this->get->keyExist('am') && $this->get->keyExist('ac')) {

            $controller_name = $this->get->getAttribute('am');
            $action_name = $this->get->getAttribute('ac');

            App::$hooks->do_action('admin_page', array($controller_name, $action_name));

            App::$app_data->setAttribute('render_controller', $controller_name);
            $this->renderAdminController($controller_name, $action_name);

        } else {
            $this->view->setPageTitle('Admin Panel', true);

            App::$hooks->do_action('admin_dashBoard');

            $this->view->render('Admin/index');
        }
    }

    public function adminAjax_ajax() {
        $this->runAdminAjax($this->get->getAttribute('data'));
    }
}
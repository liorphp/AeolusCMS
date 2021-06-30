<?php
namespace AeolusCMS\Libs\Controllers;

use AeolusCMS\App;
use AeolusCMS\Helpers\retAjax;
use AeolusCMS\Libs\View\AdminView;

class AdmController extends Controller {
    protected function systemPreLoader() {
        if (!App::$user->isAdmin()) {
            redirectToHome();
        }
    }

    protected function setViewInstance() {
        $this->view = AdminView::getInstance();
    }

    protected function renderAdminController($controller_name, $action_name) {
        if ($controllerObjName = App::loadController($controller_name)) {

            /* @var Controller $controllerObj */
            $controllerObj = new $controllerObjName();
            $action_name = '_admin_' . $action_name;
            if (method_exists($controllerObj, $action_name)) {

                $controllerObj->$action_name();

            } else {
                redirectToAdmin();
            }

        } else {
            redirectToAdmin();
        }

        $this->view->render();
    }

    protected function runAdminAjax($data) {
        $dataParams = array_slice(explode('/', $data->url), 2);

        $controller_name = $dataParams[0];
        $action_name = $dataParams[1];

        if ($controllerObjName = App::loadController($controller_name)) {
            $dataParams = array_slice($dataParams, 2);

            $controllerObj = new $controllerObjName();
            $action_name = '_admin_ajax_' . $action_name;

            if (method_exists($controllerObj, $action_name)) {
                call_user_func_array(array($controllerObj, $action_name), $dataParams);
            } else {
                retAjax::build();
            }
        }
    }
}
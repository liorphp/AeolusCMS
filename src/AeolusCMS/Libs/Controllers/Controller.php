<?php
namespace AeolusCMS\Libs\Controllers;

use AeolusCMS\App;
use AeolusCMS\Helpers\dataObj;
use AeolusCMS\Libs\AeolusObj;
use AeolusCMS\Libs\View\AdminView;
use AeolusCMS\Libs\View\View;

class Controller extends AeolusObj {

    public $temp_db = null;
    public $cont_name = null;
    public $cont_action = null;
    static $vars = null;    
    public $title = '';
    /* @var View view */
    public $view = null;

    function __construct() {
        parent::__construct();
        self::$vars = new dataObj();
        $this->setViewInstance();
        $this->cont_name = App::$app_data->getAttribute('controller');
        $this->cont_action = App::$app_data->getAttribute('action');
        $this->view->setTplName($this->cont_action);
        $this->systemPreLoader();
        $this->preLoader();
    }

    protected function setViewInstance() {
        $this->view = View::getInstance();
    }

    protected function systemPreLoader() {}

    protected function setAdminView() {
        if (App::$user->isAdmin()) {
            $this->view = AdminView::getInstance();
        }
    }

    protected function preLoader() {}

    public function getControllerName() {
        return ltrim(get_class($this), 'Controller');
    }

    protected function checkUserAccessOnAdmin($access) {
        if (!\checkUserAccess($access)) {
            redirectToHome();
        }
    }
}
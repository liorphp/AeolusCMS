<?php
use AeolusCMS\MVC\Controllers\Home\HomeController;
use AeolusCMS\MVC\Models\HomeCustomModel;

class HomeCustomController extends HomeController {
    /* @var HomeCustomModel $this->model */

    public function test() {
        $this->view->setVar('unique_id', $this->model->getUniqueId());
        $this->view->render();
    }
}
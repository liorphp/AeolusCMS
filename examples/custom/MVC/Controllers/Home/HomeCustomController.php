<?php
use AeolusCMS\MVC\Controllers\Home\HomeController;

class HomeCustomController extends HomeController {
    /* @var HomeCustomModel $this->model */

    public function test() {
        $this->view->setVar('unique_id', $this->model->getUniqueId());
        $this->view->render();
    }
}
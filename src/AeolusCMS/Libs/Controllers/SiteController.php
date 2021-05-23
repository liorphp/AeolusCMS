<?php
namespace AeolusCMS\Libs\Controllers;

use AeolusCMS\Libs\View\SiteView;

class SiteController extends Controller {
    protected function setViewInstance() {
        $this->view = SiteView::getInstance();
    }
}
<?php
namespace AeolusCMS\Libs;

use AeolusCMS\App;
use AeolusCMS\Libs\DBObject\DBObject;
use AeolusCMS\Libs\View\View;

class Model extends AeolusObj {
    /* @var DBObject $dbo */
    var $dbo = null;

    /* @var View $view */
    var $view = null;

    public function __construct($use_view = true) {
        if ($use_view) {
            $this->view = &App::$view;
        } else {
            $this->view = new View();
        }

        parent::__construct();
    }

    public function dboFind($where = array(), $select = array()) {
        return $this->dbo->find($select, $where)->getResult();
    }
}


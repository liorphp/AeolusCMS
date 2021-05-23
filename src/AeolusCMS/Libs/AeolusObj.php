<?php
namespace AeolusCMS\Libs;

use AeolusCMS\App;
use AeolusCMS\Helpers\dataObj;

class AeolusObj {
    /* @var dataObj $post */
    protected $post = null;
    /* @var dataObj $get */
    protected $get = null;

    public function __construct() {
        $this->post = App::$post;
        $this->get = App::$get;
    }
}
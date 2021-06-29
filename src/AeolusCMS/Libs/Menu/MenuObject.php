<?php
namespace AeolusCMS\Libs\Menu;

use AeolusCMS\Libs\Objects\Interfaces\Menuable;

class MenuObject implements Menuable {
    private $_id = null;
    private $_content_id = null;
    private $_title = null;
    private $_url = null;
    private $_img = null;
    private $_has_child = false;
    private $_class = '';

    public function __construct($content_id, $title, $url, $id = null, $img = null, $has_child = false, $class = '') {
        $this->_title = $title;
        $this->_url = $url;
        $this->_id = $id;
        $this->_content_id= $content_id;
        $this->_img = $img;
        $this->_has_child = $has_child;
        $this->_class = $class;
    }

    public function getId() {
        return $this->_id;
    }

    public function getContentId() {
        return $this->_content_id;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function getImg() {
        return $this->_img;
    }

    public function hasChild() {
        return $this->_has_child;
    }

    public function getClass() {
        return $this->_class;
    }

    public function getObjectLangNAme() {
        return _t('Menu');
    }
}
<?php
namespace AeolusCMS\Libs\Menu;

class Menu {

    const MENU_PARAM_ID = 'id';
    const MENU_PARAM_NAME = 'name';
    const MENU_PARAM_LINK = 'link';
    const MENU_PARAM_ICON = 'icon';
    const MENU_PARAM_ORDER = 'order';
    const MENU_PARAM_ACCESS = 'access';
    const MENU_CONTENT_ID = 'content_id';
    const MENU_CONTENT_TYPE = 'content_type';
    const MENU_PARAM_SUB = 'sub';

    private $_items = array();

    public function crateCategory($id, $title, $link, $icon = '', $access = -1, $order = 9999, $content_id = null, $content_type = null) {
        if (!\checkUserAccess($access)) { return; }

        if (!isset($this->_items[$id])) {
            $this->_items[$id] = array(
                self::MENU_PARAM_ID => $id,
                self::MENU_PARAM_NAME => $title,
                self::MENU_PARAM_LINK => $link,
                self::MENU_PARAM_ICON => $icon,
                self::MENU_PARAM_ORDER => $order,
                self::MENU_PARAM_ACCESS => $access,
                self::MENU_CONTENT_ID => $content_id,
                self::MENU_CONTENT_TYPE => $content_type,
                self::MENU_PARAM_SUB => array()
            );
        }
    }

    public function crateItem($parent_id, $title, $link, $access = -1, $order = 9999) {
        if (!\checkUserAccess($access)) { return; }

        if (isset($this->_items[$parent_id])) {
            $this->_items[$parent_id][self::MENU_PARAM_SUB][] = array(
                self::MENU_PARAM_NAME => $title,
                self::MENU_PARAM_LINK => $link,
                self::MENU_PARAM_ORDER => $order,
                self::MENU_PARAM_ACCESS => $access
            );
        }
    }

    public function getMenuItems() {
        return $this->_items;
    }

}
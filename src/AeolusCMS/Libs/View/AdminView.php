<?php
namespace AeolusCMS\Libs\View;

use AeolusCMS\App;
use AeolusCMS\Libs\Menu\Menu;

class AdminView extends View {
    static public function registerViewCssJs() {
        App::$hooks->do_action('admin_register_view_css_js');
        static::registerControllerCssJs();
    }

    static public function renderHeader($layout) : string {
        $menu = new Menu();
        App::$hooks->do_action('menu_items', array(&$menu, 1));

        $short_links = array();
        App::$hooks->do_action('admin_short_links', array(&$short_links, 1));

        App::$hooks->do_action('admin_view_header_vars', array(&$header_vars, $layout));
        $header_vars = array_merge($header_vars, array(
            'page_title' => self::getPageTitle(),
            'body_classes' => parent::getBodyClasses(),
            'css_files' => self::showCss(),
            'current_pam' => App::$get->getAttribute('pam'),
            'current_am' => App::$get->getAttribute('am'),
            'current_ac' => App::$get->getAttribute('ac'),
            'admin_menu' => $menu->getMenuItems(),
            'short_links' => $short_links,
            'user_data' => App::$user
        ));

        return self::showBlock('_layouts/_admin/Header', $header_vars);
    }

    static public function renderFooter($layout) : string {
        $footer_vars = array(
            'hiddens' => static::renderHiddenDivsFooter(),
            'js_files' => static::showJs(),
        );
        return self::showBlock('_layouts/_admin/Footer', $footer_vars);
    }
}
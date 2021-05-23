<?php
namespace AeolusCMS\Libs\View;
use Application;
use Menu\Menu;

class AdminView extends View {

    static public function registerViewCssJs() {
        self::addCss('https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all', 1001);

        self::addCss(PUBLIC_ADMIN_URL . 'global/plugins/font-awesome/css/font-awesome.min.css', 1000);
        self::addCss(PUBLIC_ADMIN_URL . 'global/plugins/simple-line-icons/simple-line-icons.min.css', 1000);
        self::addCss(PUBLIC_ADMIN_URL . 'global/plugins/bootstrap/css/bootstrap-rtl.min.css', 1000);
        self::addCss(PUBLIC_ADMIN_URL . 'global/plugins/bootstrap-switch/css/bootstrap-switch-rtl.min.css', 1000);

        self::addCss(PUBLIC_ADMIN_URL . 'global/css/components-md-rtl.css', 890);
        self::addCss(PUBLIC_ADMIN_URL . 'global/css/plugins-md-rtl.min.css?0.1', 880);

        self::addCss(PUBLIC_ADMIN_URL . 'layouts/layout/css/layout-rtl.min.css', 590);
        self::addCss(PUBLIC_ADMIN_URL . 'layouts/layout/css/themes/darkblue-rtl.min.css?0.1', 580);
        self::addCss(PUBLIC_ADMIN_URL . 'layouts/layout/css/custom-rtl.min.css', 570);

        self::addCss(PUBLIC_ADMIN_URL . 'global/css/extra.css?0.1.1', 200);




        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/respond.min.js', 1005, 'lt IE 9');
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/excanvas.min.js', 1004, 'lt IE 9');
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/ie8.fix.min.js', 1003, 'lt IE 9');

        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/jquery.min.js', 900);
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/bootstrap/js/bootstrap.min.js', 790);
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/js.cookie.min.js', 785);
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/jquery-slimscroll/jquery.slimscroll.min.js', 780);
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/jquery.blockui.min.js', 770);
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/bootstrap-switch/js/bootstrap-switch.min.js', 650);
        self::addJs(PUBLIC_ADMIN_URL . 'global/plugins/bootbox/bootbox.min.js', 600);
        self::addJs('https://cdn.jsdelivr.net/npm/sweetalert2@7.32.2/dist/sweetalert2.all.min.js', 600);


        self::addJs(PUBLIC_ADMIN_URL . 'global/scripts/app.min.js?1', 505);
        self::addJs(PUBLIC_ADMIN_URL . 'layouts/layout/scripts/layout.min.js?1', 504);

        self::addJs('https://cdnjs.cloudflare.com/ajax/libs/voca/1.4.0/voca.min.js', 503);

        self::addJs(PUBLIC_APP_URL . 'app.js?1.2.14', 400);



        self::addCss('https://fonts.googleapis.com/earlyaccess/opensanshebrew.css', 400);
        self::addCss(PUBLIC_GENERAL_URL . 'css/app.css?1.2.3');

        static::registerControllerCssJs();
    }

    static public function renderHeader($layout) {
        $menu = new Menu();
        Application::$hooks->do_action('menu_items', array(&$menu, 1));

        $short_links = array();
        Application::$hooks->do_action('admin_short_links', array(&$short_links, 1));


        $header_vars = array(
            'page_title' => self::getPageTitle(),
            'css_files' => self::showCss(),
            'current_pam' => Application::$get->getAttribute('pam'),
            'current_am' => Application::$get->getAttribute('am'),
            'current_ac' => Application::$get->getAttribute('ac'),
            'admin_menu' => $menu->getMenuItems(),
            'short_links' => $short_links,
            'user_data' => Application::$user
        );
        return self::showBlock('_layouts/_admin/Header', $header_vars);
    }

    static public function renderFooter($layout) {
        $footer_vars = array(
            'hiddens' => static::renderHiddenDivsFooter(),
            'js_files' => static::showJs(),
        );
        return self::showBlock('_layouts/_admin/Footer', $footer_vars);
    }
}
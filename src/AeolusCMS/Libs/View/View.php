<?php
namespace AeolusCMS\Libs\View;

use AeolusCMS\App;
use AeolusCMS\Helpers\AeolusErrorsLog;
use AeolusCMS\Helpers\dataObj;
use AeolusCMS\Helpers\File;
use AeolusCMS\Wrappers\AeolusPhpFastCache;

class View {
    static protected $_empty_obj = true;

    static public $_header_scope = '_header';

    const TPL_EXT = '.twig';
    const NEW_LINE = "\n";

    /* @var DataObj $vars */
    static public $vars;
    static public $scope_vars;
    static public $page_title;
    static public $page_h1;
    static public $tpl_name;
    static public $tpl_override_full;
    static public $ret = array();
    static public $block = array();
    static protected  $js = array();
    static protected $css = array();
    static protected $header_codes = array();
    static protected $meta = array();
    static protected $head_links = array();

    static $hidden_divs_footer = array();
    static public $body_classes = array();

    static public function start() {
        static::$scope_vars = array();
        static::$vars = new dataObj();
        static::$js['all'] = array();
        static::$js['groups'] = array();
    }

    static public function getInstance(): View {
        if (static::$_empty_obj) {
            static::start();
            static::$_empty_obj = false;
        }
        return new static();
    }

    static public function isSiteView(): bool {
        return false;
    }

    static public function setHiddenDivsFooter($div) {
        static::$hidden_divs_footer[] = $div;
    }

    static public function setTplName($name) {
        static::$tpl_name = $name;
    }

    static public function setTplOverrideFull($name) {
        static::$tpl_override_full = $name;
    }

    static public function setPageTitle($title, $trans = false) {
        if ($trans) {
            $title = \_t($title);
        }
        static::$page_title = $title;
    }

    static public function getPageTitle($echo = false) {
        if ($echo) {
            echo static::$page_title;
        }
        return static::$page_title;
    }

    static public function setH1($h1, $trans = false) {
        if ($trans) {
            $h1 = \_t($h1);
        }
        static::$page_h1 = $h1;
    }

    static public function getH1($echo = false) {
        if ($echo) {
            echo static::$page_h1;
        }
        return static::$page_h1;
    }

    static public function setMeta($name, $content, $type = 'name') {
        static::$meta[$name] = array(
            'content' => $content,
            'type' => $type
        );
    }

    static public function getMeta(): array {
        $ret = array();
        foreach (self::$meta as $name => $content) {
            $ret[] = '<meta '. $content['type'] .'="'. $name .'" content="'. $content['content'] .'" />';
        }

        return $ret;
    }

    static public function setHeaderCode($code) {
        static::$header_codes[] = $code;
    }

    static public function getHeaderCode(): array {
        $ret = array();
        foreach (self::$header_codes as $code) {
            $ret[] = $code;
        }

        return $ret;
    }

    static public function setHeadLink($rel, $href) {
        static::$head_links[$rel] = $href;
    }

    static public function getHeadLink(): array {
        $ret = array();
        foreach (self::$head_links as $rel => $href) {
            $ret[] = '<link rel="'.$rel.'" href="'.$href.'" />';
        }

        return $ret;
    }

    static public function setVar($key, $value, $scope = FALSE) {
        if ($scope) {
            if (!isset(static::$scope_vars[$scope])) {
                static::$scope_vars[$scope] = new dataObj();
            }
            static::$scope_vars[$scope]->$key = $value;
        } else {
            static::$vars->$key = $value;
        }
    }

    static public function getVars($scope = '') {
        if ($scope != '')
            if (isset(static::$scope_vars[$scope])) {
                /* @var dataObj $scope_vars*/
                $scope_vars = static::$scope_vars[$scope];
                $tpl_vars = $scope_vars->getList(true);
            }
            else
                $tpl_vars = array();
        else
            $tpl_vars = static::$vars->getList(true);

        return $tpl_vars;
    }

    static public function renderHeader($layout): string {
        return static::renderBlankHeader();
    }

    static public function renderHiddenDivsFooter(): string {
        return implode(" ", static::$hidden_divs_footer);
    }

    static public function renderFooter($layout): string {
        return static::renderBlankFooter($layout);
    }

    static public function registerViewCssJs() {

    }

    static public function registerControllerCssJs() {
        $controller = App::$app_data->getAttribute('controller');
        $js_file =  CUSTOM_PATH . 'js_controllers/' . $controller . '.js';

        if (File::fileExists(ROOT_PATH . $js_file)) {
            self::addJs($js_file);
        }
    }

    static public function renderLayout($layout, $tpl = '', $scope = '', $just_middle = false) {
        static ::render($tpl, $scope, $just_middle, $layout);
    }

    static public function render($tpl = '', $scope = '', $just_middle = false, $layout = '') {
        $tpl_vars = static::getVars($scope);

        if (App::$header404) {
            \header("HTTP/1.0 404 Not Found");
        }

        static::registerViewCssJs();
        if ($just_middle !== true) {
            echo static::renderHeader($layout);
            \flush();
        }

        if ($tpl == '' && !$tpl = static::$tpl_override_full) {
            $controller = App::realControllerName(App::$app_data->getAttribute('controller'));
            $tpl = $controller .'/' . \strtolower(static::$tpl_name);
        }

        echo static::showBlock($tpl, $tpl_vars);

        if ($just_middle !== true) {
            echo static::renderFooter($layout);
        }
    }

    static public function renderBlankHeader() {
        return '';
    }

    static public function renderBlankFooter($layout) {
        return '';
    }

    static public function renderBlankOnlyMiddle() {
        static::renderBlank('', '', true, '');
    }

    static public function renderBlank($tpl = '', $scope = '', $just_middle = false, $layout = '') {
        $tpl_vars = static::getVars($scope);
        \extract($tpl_vars);

        static::registerViewCssJs();
        if (!$just_middle) {
            echo static::renderBlankHeader();
        }

        if ($tpl == '' && !$tpl = static::$tpl_override_full) {
            $controller = App::realControllerName(App::$app_data->getAttribute('controller'));
            $tpl = $controller .'/' . \strtolower(static::$tpl_name);
        }

        echo static::showBlock($tpl, $tpl_vars);

        if (!$just_middle) {
            echo static::renderBlankFooter($layout);
        }
    }

    static public function setTplHook($hook_name, $scope = '', $controller = null) {
        if (!$controller) {
            $controller = App::realControllerName(App::$app_data->getAttribute('render_controller'));
        }

        $files = glob(VIEWS_PATH . '*/hooks/' . $controller . '/' . $hook_name . '.tpl.php', GLOB_BRACE);
        $activeModules = App::getActiveModules();

        $tpl_vars = static::getVars($scope);
        $tpl = new StringParser();

        $files_ordered = array();

        $counter = 0;
        foreach($files as $file) {
            $file_name = \str_replace(VIEWS_PATH, '', $file);
            $file_name_dirs = \explode('/', $file_name);

            if (\in_array($file_name_dirs[0], $activeModules)) {
                $counter++;
                $key = (array_search($file_name_dirs[0], $activeModules) * 100000) + $counter;
                $files_ordered[$key] = $file;
            }
        }

        ksort($files_ordered);

        foreach ($files_ordered as $file) {
            $hook_tpl = File::Read($file);
            echo static::commentsWrap('Hook: ' . $hook_name, $tpl->parse($hook_tpl, $tpl_vars));
        }
    }

    static private function checkNotificationsSes() {
        if (!isset($_SESSION['notifications']))
            $_SESSION['notifications'] = array();
    }

    static function setNotification($msg, $type = 1, $name = '') {
        static::checkNotificationsSes();

        $notification = array();
        if ($name == '')
            $name = \microtime() . \rand(2, 999999);

        $notification['msg'] = $msg;
        $notification['type'] = $type; //1:Warning, 2:Success, 3:Info, 4:Error
        $notification['time'] = \time();

        $_SESSION['notifications'][$name] = $notification;
    }

    static function showNotifications(): string {
        static::checkNotificationsSes();

        $str = '';
        foreach ($_SESSION['notifications'] as $key => $value) {

            $type = 'alert-warning';
            if ($value['type'] == 2)
                $type = 'alert-success';
            elseif ($value['type'] == 3)
                $type = 'alert-info';
            elseif ($value['type'] == 4)
                $type = 'alert-danger';

            $str .= static::showBlock('notification', array('type' => $type, 'msg' => $value['msg']));
            unset($_SESSION['notifications'][$key]);
        }
        return $str;
    }

    static function addJs($src, $order = 0,  $group = 'all', $defer = false) {
        if ($group == 'all')
            $js_array = & static::$js[$group];
        else
            $js_array = & static::$js['groups'][$group];

        if (!isset($js_array))
            $js_array = array();

        $js_array[] = array('src' => $src, 'order' => $order, 'defer' => $defer);
    }

    private static function js_format($src, $attrs): string {
        return '<script src="'. $src .'" '. $attrs .'></script>';
    }

    static function showJs(): string {
        $js_files = '';

        $js_file_inserted = array();

        usort(static::$js['all'], 'sortByOrder');
        foreach (static::$js['all'] as $js_file) {
            if (!in_array($js_file['src'], $js_file_inserted)) {
                $attrs = '';
                if ($js_file['defer']) {
                    $attrs .= 'defer';
                }

                $js_file_inserted[] = $js_file['src'];
                $js_files .= static::js_format($js_file['src'], $attrs);
            }
        }

        foreach (static::$js['groups'] as $key => &$js_groups) {
            usort($js_groups, 'sortByOrder');
            $js_files .= '<!--[if '. $key .']>';
            foreach ($js_groups as $js_groups_file) {
                $attrs = '';
                if ($js_groups_file['defer']) {
                    $attrs .= 'defer';
                }
                $js_files .= static::js_format($js_groups_file['src'], $attrs);
            }
            $js_files .= '<![endif]-->';
        }

        return $js_files;
    }

    static function addCss($href, $order = 0, $media = '') {
        static::$css[] = array('href' => $href, 'media' => $media, 'order' => $order);
    }

    static function showCss(): string {
        \usort(static::$css, 'sortByOrder');
        $css_files = '';
        foreach (static::$css as $css_file) {
            $media = '';
            if ($css_file['media']) {
                $media = 'media="'. $css_file['media'] .'"';
            }
            $css_files .= '<link rel="stylesheet" type="text/css" href="'. $css_file['href'] .'" '. $media .' />';
        }

        return $css_files;
    }

    static function divWrap($html, $wrap = array()): string {
        if (!isset($wrap['type'])) $wrap['type'] = 'div';
        $extra = '';
        foreach ($wrap as $attr => $val) {
            if ($attr != 'type') {
                $extra .= ' ' . $attr . '="'. $val .'"';
            }
        }

        return '<'. $wrap['type'] . $extra .'>'. $html .'</'. $wrap['type'] .'>';
    }

    static function showBlock($block_name, $params = array(), $wrap = array(), $full_path = false, $use_comments_wrap = false) {
        try {
            $block_tpl = AeolusPhpFastCache::showKey('show_block_' . $block_name, function() use ($block_name, $full_path) {
                $path = '';

                if (!$full_path) {
                    $path = CUSTOM_VIEWS_PATH;
                }

                $block_file = $path . $block_name . self::TPL_EXT;
                return preg_replace('~>\s+<~', '><', File::Read($block_file));
            }, CACHE_TIME_LV11, array(AeolusPhpFastCache::CACHE_TAG_TEMPLATE));

            if (!empty($wrap)) {
                $block_tpl = static::divWrap($block_tpl, $wrap);
            }

            $params['_block_name'] = $block_name;

            $loader = new \Twig\Loader\ArrayLoader([
                $block_name => $block_tpl,
            ]);
            $twig = new \Twig\Environment($loader);

            $twig->addFilter(new \Twig\TwigFilter('encrypt_num','encrypt_num'));
            $twig->addFilter(new \Twig\TwigFilter('decrypt_num','decrypt_num'));
            $twig->addFilter(new \Twig\TwigFilter('showDate','showDate'));
            $twig->addFilter(new \Twig\TwigFilter('num_format','num_format'));

            $twig->addFunction(new \Twig\TwigFunction('showBlock',[App::$controllerObj->view, 'showBlock']));

            App::$hooks->do_action('twig_filters', array(&$twig, 1));

            $block = $twig->render($block_name, $params);

            if ($use_comments_wrap) {
                $block = static::commentsWrap('Block: ' . $block_name, $block);
            }

            return $block;
        } catch (\Exception $exception) {
            AeolusErrorsLog::sendException($exception, array(
                'block' => $block_name,
                'vars' => \get_defined_vars(),
            ));
            return '';
        }
    }

    static function showBlockCycle($items, $block_name, $wrap = array(), $full_path = false) {
        $html = '';
        foreach ((array)$items as $item_params) {
            $html .= static::showBlock($block_name, (array)$item_params, $wrap, $full_path);
        }
        return $html;
    }

    static function commentsWrap($title, $block, $show_force = false) {
        if (!SHOW_COMMENT_WRAP && !$show_force)
            return $block;

        $block = '<!-- Start '.$title.' -->' . static::NEW_LINE
                . $block .
                static::NEW_LINE . '<!-- End '.$title.' -->';
        return $block;
    }

    private static function facebookJs() {
        return '';
    }

    static public function addBodyClasses($classes = array()) {
        self::$body_classes = array_merge(self::$body_classes, $classes);
    }

    static public function getBodyClasses() {
        return \implode(" ", array_unique(self::$body_classes));
    }

    public function getContentFilesVer() {
        $ver = null;

        if (!$ver) {
            $ver = App::getSystemParameters()->getValue('content_files_ver');
        }

        return $ver;
    }
}
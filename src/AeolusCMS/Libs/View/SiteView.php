<?php
namespace AeolusCMS\Libs\View;

use AeolusCMS\App;

class SiteView extends View {

    static private $header_code = '';
    static private $footer_code = '';

    static public function isSiteView(): bool {
        return true;
    }

    static public function registerViewCssJs() {
        App::$hooks->do_action('site_register_view_css_js');
    }

    static public function renderHeader($layout = ''): string {
        $header_vars = array();

        App::$hooks->do_action('site_view_header_vars', array(&$header_vars, $layout));

        $header_vars = array_merge($header_vars, array(
            'header_scope' => self::getVars(View::$_header_scope),
            'page_title' => self::getPageTitle(),
            'page_h1' => self::getH1(),
            'head_tags' => self::getHeadTags(),
            'body_classes' => parent::getBodyClasses(),
        ));

        return self::showBlock('_layouts/Header' . $layout, $header_vars);
    }

    static private function getFooterVars($layout = '') {
        $footerCode = '';

        App::$hooks->do_action('site_view_footer_code_pre', array(&$footerCode, 1));

        $footerCode .= '<div class="hidden">' . static::renderHiddenDivsFooter() . '</div>';
        $footerCode .= static::showJs();
        //$footerCode .= App::$preferences->getAttribute('bottom_code', '');
        $footerCode .= self::$footer_code;

        App::$hooks->do_action('site_view_footer_code', array(&$footerCode, 1));

        $footer_vars = array(
            'footer_code' => $footerCode
        );

        App::$hooks->do_action('site_view_footer_vars', array(&$footer_vars, 1));

        return $footer_vars;
    }

    static public function renderFooter($layout = ''): string {
        return static::showBlock('_layouts/Footer' . $layout, self::getFooterVars($layout));
    }

    static function renderBlankHeader() {

        $header_vars = array();

        App::$hooks->do_action('site_view_header_vars', array(&$header_vars, ''));

        $header_vars = array_merge($header_vars, array(
            'header_scope' => self::getVars(View::$_header_scope),
            'page_title' => self::getPageTitle(),
            'page_h1' => self::getH1(),
            'head_tags' => self::getHeadTags(),
            'body_classes' => parent::getBodyClasses(),
        ));

        return static::showBlock('_general/header_blank', $header_vars);
    }

    static public function renderBlankFooter($layout) {
        return static::showBlock('_general/footer_blank' . $layout, self::getFooterVars($layout));
    }

    static public function getMeta(): array {
        App::$hooks->do_action('site_head_tags');
        return parent::getMeta();
    }

    public function appendHeaderCode(string $code) {
        self::$header_code .= $code;
    }

    public function appendFooterCode(string $code) {
        self::$footer_code .= $code;
    }

    private static function getHeadTags() {
        $code = '';

        $code .= self::showCss();
        $code .= \implode(" ", self::getMeta());
        $code .= \implode(" ", self::getHeadLink());
        $code .= \implode(" ", self::getHeaderCode());

        $code .= self::$header_code;

        return $code;
    }
}
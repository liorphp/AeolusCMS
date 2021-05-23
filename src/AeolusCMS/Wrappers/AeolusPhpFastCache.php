<?php
namespace AeolusCMS\Wrappers;

use Phpfastcache\CacheManager;

define('CACHE_TIME_LV0', 0);
define('CACHE_TIME_LV1', 60);
define('CACHE_TIME_LV2', 60 * 10);
define('CACHE_TIME_HALF_HOUR', 60 * 30);
define('CACHE_TIME_ONE_HOUR', 60 * 60);
define('CACHE_TIME_LV4', 60 * 60 * 3);
define('CACHE_TIME_LV5', 60 * 60 * 6);
define('CACHE_TIME_LV6', 60 * 60 * 12);
define('CACHE_TIME_ONE_DAY', 60 * 60 * 24);
define('CACHE_TIME_LV8', 60 * 60 * 24 * 2);
define('CACHE_TIME_WEEK', 60 * 60 * 24 * 7);
define('CACHE_TIME_LV10', 60 * 60 * 24 * 14);
define('CACHE_TIME_LV11', 60 * 60 * 24 * 30);

class AeolusPhpFastCache extends CacheManager {
    private static $_cache_key = '';

    const CACHE_TAG_TEMPLATE = 'template';
    const CACHE_TAG_HOME_PAGE = 'home_page';
    const CACHE_TAG_MENU = 'cache_tag_menu';
    const CACHE_TAG_BLOCK = 'cache_tag_block';
    const CACHE_TAG_ALBUM = 'cache_tag_album';
    const CACHE_TAG_ARTICLES = 'cache_articles';
    const CACHE_TAG_PAGES = 'cache_tag_pages';

    public static function getAppInstance($type = 'files', $config = null, $cache_key = null) {
        static $instance;

        if (!$instance) {
            $instance = parent::getInstance($type, $config);

            if ($cache_key) {
                self::$_cache_key = $cache_key;
            }
        }

        return $instance;
    }

    public static function showKey($key, $callback, $time = 300, $tags = array()) {
        $original_key = $key;

        $key = preg_replace("/[^_A-Za-z0-9]/", '-', $key);

        $key = self::$_cache_key . $key;
        $instance = self::getAppInstance();

        try {
            $ret = $instance->getItem($key);
            if ($val = $ret->get()) {
                return $val;
            } else {
                $val = call_user_func($callback);
                $ret->set($val);
                $ret->setTags($tags);
                $ret->expiresAfter($time);

                if ($tags) {
                    foreach ($tags as $tag) {
                        $ret->addTag(self::$_cache_key . $tag);
                    }
                }

                $instance->save($ret);
            }

            return $val;
        } catch (\Exception $e) {

        }

        return null;
    }

    public static function deleteItem($key) {
        $key = self::$_cache_key . $key;
        $instance = self::getAppInstance();

        $ret = null;
        try {
            $ret = $instance->deleteItem($key);
        } catch (\Exception $e) {

        }

        return $ret;
    }

    public static function deleteItemFromTag($tag) {
        $tag = self::$_cache_key . $tag;
        $instance = self::getAppInstance();

        try {
            return $instance->deleteItemsByTag($tag);
        } catch (\Exception $e) {

        }
    }
}
<?php
namespace AeolusCMS\Libs\DBObjects;

use AeolusCMS\App;
use AeolusCMS\Wrappers\AeolusPhpFastCache;

class DBOConfigs extends DBObject {
    protected $_table = 'configs';

    const KEY_CACHE = 'configs_data';

    const ATTR_ID = 'id';
    const ATTR_ALIAS = 'alias';
    const ATTR_VALUE = 'value';

    protected $_validColumns = array(
        self::ATTR_ID,
        self::ATTR_ALIAS,
        self::ATTR_VALUE
    );

    static public function getValue($alias) {
        $configs = AeolusPhpFastCache::showKey(self::KEY_CACHE, function() {
            return App::$db->from('configs')->fetchPairs(self::ATTR_ALIAS, self::ATTR_VALUE);
        }, CACHE_TIME_LV11);

        if (isset($configs[$alias])) {
            return $configs[$alias];
        } else {
            return null;
        }
    }

    static public function updateValue($alias, $value) {
        $alias = \trim($alias);

        if ($alias) {
            $ret = self::newInstance()->updateWhere(array(
                self::ATTR_VALUE => trim($value)
            ), array(
                self::ATTR_ALIAS => $alias
            ));

            AeolusPhpFastCache::deleteItem(self::KEY_CACHE);

            return $ret;
        } else {
            return false;
        }
    }
}
<?php
namespace AeolusCMS\MVC\Models;

use AeolusCMS\Libs\Model;
use AeolusCMS\Wrappers\AeolusPhpFastCache;

class HomeModel extends Model {
    public function getUniqueId() {
        return time();
    }

    public function deleteCache() {
        $ins = AeolusPhpFastCache::getAppInstance()->clear();
    }
}
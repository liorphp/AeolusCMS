<?php
namespace AeolusCMS\MVC\Models;

use AeolusCMS\Libs\Model;
use AeolusCMS\Wrappers\AeolusPhpFastCache;

class HomeModel extends Model {
    public function deleteCache() {
        $ins = AeolusPhpFastCache::getAppInstance()->clear();
    }
}
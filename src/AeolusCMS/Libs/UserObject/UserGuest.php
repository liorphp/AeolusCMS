<?php
namespace AeolusCMS\Libs\UserObject;

class UserGuest extends UserObject {
    const ACCESS_NUM = -1;

    public function isGuest(): bool {
        return true;
    }
}
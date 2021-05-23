<?php
namespace AeolusCMS\Libs\UserObject;

class UserObject {

    const ACCESS_NUM = 0;

    public function isAdmin(): bool {
        return false;
    }

    public function isGuest(): bool {
        return false;
    }

    public function getAccessNum(): int {
        return static::ACCESS_NUM;
    }
}
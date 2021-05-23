<?php
namespace AeolusCMS\Libs\UserObject;

class UserAdmin extends UserObject {
    const ACCESS_NUM = 70;

    public function isAdmin(): bool {
        return true;
    }
}
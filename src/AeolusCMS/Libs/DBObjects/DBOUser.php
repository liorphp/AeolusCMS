<?php
namespace AeolusCMS\Libs\DBObject;

class DBOUser extends DBObject {
    const TABLE_NAME = 'user';
    protected $_table = self::TABLE_NAME;

    const ATTR_ID = 'id';
    const ATTR_TYPE = 'type';
    const ATTR_PARENT = 'parent';
    const ATTR_USERNAME = 'username';
    const ATTR_PASSWORD = 'password';
    const ATTR_PASSWORD_HASH = 'password_hash';
    const ATTR_HASH = 'hash';
    const ATTR_REGISTER = 'registered';
    const ATTR_LAST_LOGIN = 'last_login';
    const ATTR_LAST_VISIT = 'last_visit';
    const ATTR_ACTIVE = 'active';
    const ATTR_VERIFY = 'verify';
    const ATTR_FIRST_NAME = 'first_name';
    const ATTR_LAST_NAME = 'last_name';
    const ATTR_PHONE = 'phone';
    const ATTR_FILES_DIR = 'files_dir';
    const ATTR_COMMENT = 'comment';
}
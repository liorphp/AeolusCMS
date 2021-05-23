<?php
namespace AeolusCMS\Libs\UserObject;

use AeolusCMS\Wrappers\AeolusPhpFastCache;

class getUser {
    const SESSION_NAME = 'user_data';

    /* @var UserObject $_userObject */
    private $_userObject = null;
    /* @var DBOUser $_userDbo */
    private $_userDbo = null;
    private $_userId = null;
    private $_userData = array();
    private $_userAttributes = array();

    public function __construct($user_id = 0) {
        if (!$user_id) {
            $this->_userId = $this->loggedInId();
        } else {
            $this->_userId = $user_id;
        }

        if ($this->_userId) {

            $this->_userDbo = new DBOUser();
            $this->_userData = $this->setUserData();
            $this->_userAttributes = $this->setUserAttributes();

            switch ($this->_userData->{DBOUser::ATTR_TYPE}) {
                case 0:
                    $this->_userObject = new UserRegular();
                    break;
                case 70:
                    $this->_userObject = new UserAdmin();
                    break;
                default:
                    $this->_userObject = new UserGuest();
            }
        } else {
            $this->_userObject = new UserGuest();
        }
    }

    public function setUserData($reset = false) {
        $key = 'user_data_' . $this->loggedInId();
        $userDbo = $this->_userDbo;
        $userId = $this->loggedInId();

        if ($reset) {
            AeolusPhpFastCache::deleteItem($key);
        }

        return  AeolusPhpFastCache::showKey($key, function() use ($userDbo, $userId) {
            return $userDbo->findById($userId)->getResult();
        }, CACHE_TIME_LV4, array('user_data'));
    }

    public function getDataArray() {
        return $this->_userData;
    }

    public function getData($name) {
        if (isset($this->_userData->{$name})) {
            return $this->_userData->{$name};
        } else {
            return null;
        }
    }

    public function getAttribute($name) {
        if (isset($this->_userAttributes[$name])) {
            return $this->_userAttributes[$name];
        } else {
            return null;
        }
    }

    public function loggedInId($reset = false) {
        if ($reset || $this->_userId === null) {
            if (isset($_SESSION['login_as_user'])) {
                $this->_userId = $_SESSION['login_as_user'];
            } else {
                $this->_userId = (isset($_SESSION[self::SESSION_NAME]['id'])) ? $_SESSION[self::SESSION_NAME]['id'] : 0;
            }
        }

        return $this->_userId;
    }

    public function getHash() {
        return $this->getData(DBOUser::ATTR_HASH);
    }

    public function getType() {
        return $this->getData(DBOUser::ATTR_TYPE);
    }

    public function getFirstName() {
        return $this->getData(DBOUser::ATTR_FIRST_NAME);
    }

    public function getLastName() {
        return $this->getData(DBOUser::ATTR_LAST_NAME);
    }

    public function getFullName() {
        $f_name = $this->getData(DBOUser::ATTR_FIRST_NAME);
        $l_name = $this->getData(DBOUser::ATTR_LAST_NAME);

        if ($f_name || $l_name) {
            return $this->getData(DBOUser::ATTR_FIRST_NAME) . ' ' . $this->getData(DBOUser::ATTR_LAST_NAME);
        } else {
            return $this->getUsername();
        }
    }

    public function getUsername() {
        return $this->getData(DBOUser::ATTR_USERNAME);
    }

    public function isActive(): bool {
        return (boolean)$this->getData(DBOUser::ATTR_ACTIVE);
    }

    public function isAdmin(): bool {
        return $this->_userObject->isAdmin();
    }

    public function isGuest(): bool {
        return $this->_userObject->isGuest();
    }

    public function getAccessNum(): int {
        return $this->_userObject->getAccessNum();
    }
}
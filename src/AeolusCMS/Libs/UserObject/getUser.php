<?php
namespace AeolusCMS\Libs\UserObject;

use AeolusCMS\App;
use AeolusCMS\Libs\DBObjects\DBOUsers;

class getUser {
    const SESSION_NAME = 'user_data_sess';

    /* @var UserObject $_userObject */
    private $_userObject = null;
    /* @var DBOUsers $_userDbo */
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

            $this->_userDbo = new DBOUsers();
            $this->_userData = $this->setUserData();
            $this->_userAttributes = array();

            if (!isset($this->_userData->{DBOUsers::ATTR_TYPE})) {
                $user_type = -1;
                $this->_userId = 0;
            } else {
                $user_type = $this->_userData->{DBOUsers::ATTR_TYPE};
            }

            switch ($user_type) {
                case UserRegular::ACCESS_NUM:
                    $this->_userObject = new UserRegular();
                    break;
                case UserAdmin::ACCESS_NUM:
                    $this->_userObject = new UserAdmin();
                    break;
                default: {
                    $custom_user_objects = App::getConfig('user_objects');
                    if ($custom_user_objects && isset($custom_user_objects[$user_type])) {

                        $custom_uo = $custom_user_objects[$user_type];
                        $custom_uo_file = CUSTOM_PATH . 'UserObject/' . $custom_uo . '.php';

                        if (file_exists($custom_uo_file)) {
                            require_once $custom_uo_file;

                            $user_object = 'Custom\\UserObject\\' . $custom_uo;
                            $this->_userObject = new $user_object();
                        }

                    } else {
                        $this->_userObject = new UserGuest();
                    }

                    break;
                }
            }
        } else {
            $this->_userObject = new UserGuest();
        }
    }

    public function setUserData($reset = false) {
        return $this->_userDbo->findById($this->loggedInId())->getResult();
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
        return $this->getData(DBOUsers::ATTR_HASH);
    }

    public function getType() {
        return $this->getData(DBOUsers::ATTR_TYPE);
    }

    public function getParent() {
        return $this->getData(DBOUsers::ATTR_PARENT);
    }

    public function getParentElseMe(): int {
        $ret = intval($this->getParent());
        if (!$ret) {
            $ret = intval($this->loggedInId());
        }

        return $ret;
    }

    public function getFirstName() {
        return $this->getData(DBOUsers::ATTR_FIRST_NAME);
    }

    public function getLastName() {
        return $this->getData(DBOUsers::ATTR_LAST_NAME);
    }

    public function getFullName() {
        $f_name = $this->getData(DBOUsers::ATTR_FIRST_NAME);
        $l_name = $this->getData(DBOUsers::ATTR_LAST_NAME);

        if ($f_name || $l_name) {
            return $this->getData(DBOUsers::ATTR_FIRST_NAME) . ' ' . $this->getData(DBOUsers::ATTR_LAST_NAME);
        } else {
            return $this->getUsername();
        }
    }

    public function getUsername() {
        return $this->getData(DBOUsers::ATTR_USERNAME);
    }

    public function isActive(): bool {
        return (boolean)$this->getData(DBOUsers::ATTR_ACTIVE);
    }

    public function isVerify(): bool {
        return (boolean)$this->getData(DBOUsers::ATTR_VERIFY);
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
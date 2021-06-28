<?php
namespace AeolusCMS\Libs\DBObject;

class DBOUserCustomProp extends DBObject {
    protected $_table = 'user_custom_prop';
    
	const ATTR_USER_ID = 'user_id';
	const ATTR_FIELD = 'field';
	const ATTR_VALUE = 'value';

	protected $_foreignKey = null;

	protected $_validColumns = array(
		self::ATTR_USER_ID,
		self::ATTR_FIELD,
		self::ATTR_VALUE,
	);

	public function insert($attribute, $ignore = false) {
	    self::deleteWhere(array(
	        self::ATTR_USER_ID => $attribute[self::ATTR_USER_ID],
	        self::ATTR_FIELD => $attribute[self::ATTR_FIELD]
        ));

	    return parent::insert($attribute, $ignore);
    }
}
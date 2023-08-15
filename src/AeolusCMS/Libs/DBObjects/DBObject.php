<?php
namespace AeolusCMS\Libs\DBObjects;

use AeolusCMS\App;
use AeolusCMS\Wrappers\AeolusFluentPDO;

class DBObject {

    /* @var AeolusFluentPDO $_db */
    protected $_db;

    protected $_table;
    protected $_foreignKey = 'id';
    protected $_validColumns = array();

    protected $_result;
    protected $_countResult = 0;
    private $_id = 0;

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    public function __construct() {
        $this->_db = &App::$db;
    }

    static public function newInstance() {
        return new static();
    }

    public function reset() {
        return new self();
    }

    public function getCountResult() {
        return $this->_countResult;
    }

    static public function getTableName() {
        return self::newInstance()->getTable();
    }

    public function getTable() {
        return $this->_table;
    }

    protected function _setResult($result) {
        $this->_result = $result;

        if (\is_object($result)) {
            $this->_countResult = 1;
        }
        elseif ((\is_array($result) || \is_object($result)) && !empty($result)) {
            $this->_countResult = \count($result);
        } else {
            $this->_countResult = 0;
        }
    }

    public function getResult($as_array = false) {
        if ($as_array) {
            return (array)$this->_result;
        } else {
            return $this->_result;
        }
    }

    public function getFirst() {
        if (is_array($this->_result) && !empty($this->_result)) {
            $resObj = $this->getResult();
            $firstRes = array_pop($resObj);
            $this->_setResult($firstRes);
            if ($this->getForeignKey()) {
                $this->_id = $firstRes->{$this->getForeignKey()};
            }
        }

        return $this;
    }

    protected function filterKeys(&$params) {
        if (is_array($params) && !empty($params) &&!empty($this->_validColumns)) {
            foreach ($params as $key => $value) {
                if (!in_array($key, $this->_validColumns)) {
                    unset($params[$key]);
                }
            }
        }
    }

    protected function filterParams(&$params) {
        if (is_array($params) && !empty($params) &&!empty($this->_validColumns)) {
            foreach ($params as $key => $value) {
                if (!in_array($value, $this->_validColumns)) {
                    unset($params[$key]);
                }
            }
        }
        return $params;
    }

    protected function validParam(&$name) {
        if (!\strtolower($name) == 'rand()') {
            if (!empty($this->_validColumns) && !\in_array($name, $this->_validColumns)) {
                $name = null;
            }
        }
    }

    public function returnValidColumns() {
        return $this->_validColumns;
    }

    public function find($select = array(), $where = array(), $orderBy = '', $orderType = self::ORDER_ASC, $limit = array(), $groupBy = '') {
        $pdo = $this->_db->from($this->getTable());

        $this->filterParams($select);
        $this->filterKeys($where);
        $this->validParam($orderBy);
        $this->validParam($groupBy);

        if (!empty($select)) {
            $pdo->select(null)->select($select);
        }
        if (!empty($where)) {
            $pdo->where($where);
        }
        if ($orderBy != '') {
            if ($orderType != self::ORDER_ASC && $orderType != self::ORDER_DESC) {
                $orderType = self::ORDER_ASC;
            }
            $pdo->orderBy($orderBy . ' ' . $orderType);
        }
        if (!empty($limit)) {
            $pdo->limit($limit[0]);
            if (\count($limit) == 2) {
                $pdo->offset($limit[1]);
            }
        }
        if ($groupBy != '') {
            $pdo->groupBy($groupBy);
        }

        $this->_setResult($pdo->fetchAll($this->getForeignKey()));

        return $this;
    }

    public function findById($id) {
        $this->_setResult($this->_db->from($this->getTable(), $id)->fetch());

        return $this;
    }

    public function update($set) {
        $this->filterKeys($set);
        if ($this->updateId($set, $this->getForeign())) {
            $results = $this->getResult();
            foreach ($results as &$row) {
                foreach ($set as $key => $value) {
                    $row->{$key} = $value;
                }
            }
            $this->_setResult($results);
        }
        return $this;
    }

    public function updateId($set, $id) {
        $this->filterKeys($set);
        return $this->_db->update($this->getTable())->set($set)->where(array($this->getForeignKey() => $id))->execute();
    }

    public function updateWhere($set, $where) {
        $this->filterKeys($set);
        $this->filterKeys($where);

        return $this->_db->update($this->getTable())->set($set)->where($where)->execute();
    }

    public function deleteAll() {
        $this->_db->deleteFrom($this->getTable())->where(array(
            $this->getForeignKey() => $this->getForeign()
        ))->execute();
        return $this->reset();
    }

    public function deleteOne() {
        if ($this->_id) {
            $this->deleteId($this->_id);
        } elseif ($this->_countResult == 1) {
            $this->deleteWhere($this->_result);
        }
        return $this->reset();
    }

    public function deleteId($id) {
        return $this->_db->deleteFrom($this->getTable(), $id)->execute();
    }

    public function deleteWhere($where) {
        if (!empty($where)) {
            return $this->_db->deleteFrom($this->getTable())->where($where)->execute();
        }
        return 0;
    }

    public function insert($attribute, $ignore = false) {
        $this->filterKeys($attribute);

        $query = $this->_db->insertInto($this->getTable(), $attribute);
        if ($ignore) {
            $query->ignore();
        }

        if ($this->_foreignKey) {
            return $query->execute();
        } else {
            return $query->executeWithoutId();
        }
    }

    public function count($where = array()) {
        return $this->_db->from($this->getTable())->where($where)->count();
    }

    public function getAttributes() {
        if ($this->_countResult == 0) {
            return $this->_result;
        } else {
            return null;
        }
    }

    public function getAttribute($name) {
        if (isset($this->_result->{$name})) {
            return $this->_result->{$name};
        } else {
            return null;
        }
    }

    public function getForeign() {
        $indexes = array();
        $foreignKey = $this->getForeignKey();
        foreach ($this->getResult() as $row) {
            $indexes[] = $row->{$foreignKey};
        }
        return $indexes;
    }

    public function getForeignKey() {
        return $this->_foreignKey;
    }

    public function getRowData($id) {
        static $rows = array();

        if (!isset($rows[$id])) {
            $rows[$id] = $this->findById($id)->getResult();
        }

        return $rows[$id];
    }

    public function getDbInstance() {
        return $this->_db;
    }

    public function truncateTable() {
        $query = "TRUNCATE TABLE " . $this->getTable();
        $stmt = App::$db->getPdo()->prepare($query);
        $stmt->execute();
    }
}
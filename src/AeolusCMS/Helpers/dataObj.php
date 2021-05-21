<?php
namespace AeolusCMS\Helpers;

class dataObj {
    private $data = NULL;
    private $default_value = NULL;
    private $default_temp = NULL;
    private $changes = 0;
    
    public function __construct($arr = array()) {
        $this->data = new \stdClass();

        if (!empty($arr)) {
            $this->fromArray($arr);
        }
    }
    
    public function __get($name) {
        $data = (array) $this->getList();
        if (isset($data[$name])) {
            return $this->data->$name;
        }
        else {
            if ($this->default_temp) {
                $tmp = $this->default_temp;
                $this->default_temp = '';
                return $tmp;
            } else {
                return $this->getDefaultValue();
            }
        }
    }

    public function __set($key, $value) {
        $this->data->$key = $value;
        $this->objChanged();
    }

    public function getAttribute($key, $default = null) {
        $this->default_temp = $default;
        return $this->{$key};
    }

    public function setAttribute($key, $val) {
        $this->{$key} = $val;
    }

    public function appendAttribute($key, $val) {
        if (!isset($this->{$key})) {
            $this->{$key} = '';
        }

        $this->{$key} .= $val;
    }

    private function objChanged() {
        $this->changes++;
    }

    public function getChange(): int {
        return $this->changes;
    }
    
    public function setDefaultValue($val) {
        $this->default_value = $val;
    }
    
    public function getDefaultValue() {
        return $this->default_value;
    }
    
    public function fromArray($arr) {
        foreach ($arr as $key => $value) {
            $this->data->$key = $value;
        }
        $this->objChanged();
    }
    
    public function merge($obj) {
        $this->data = (object) \array_merge((array) $this->getList(), (array) $obj);
        $this->objChanged();
    }
    
    public function getList($to_array = false) {
        if ($to_array)
            return (array) $this->data;
        return $this->data;
    }
    
    public function keyExist($key): bool {
        $data = $this->getList(true);
            return isset($data[$key]);
    }
}
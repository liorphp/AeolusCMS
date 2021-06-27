<?php
use AeolusCMS\MVC\Models\HomeModel;

class HomeCustomModel extends HomeModel {
    public function getUniqueId() {
        return 'CU' . time();
    }
}
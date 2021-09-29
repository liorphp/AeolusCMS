<?php
namespace AeolusCMS\MVC\Models;

use AeolusCMS\Helpers\File;
use AeolusCMS\Libs\Model;

class loadModel {
    static $models = array();

    public static function load($model_name) : Model {
        if (!in_array($model_name, self::$models)) {
            self::$models[$model_name] = self::getModelObject($model_name);
        }
        return self::$models[$model_name];
    }

    private static function getModelObject($model_name) {
        static $overridesModels = array();

        if (!isset($overridesModels[$model_name])) {
            $model_override_file = CUSTOM_MODEL_PATH . $model_name . 'Custom' . 'Model.php';
            if (File::fileExists($model_override_file)) {
                require_once $model_override_file;
                $overridesModels[$model_name] = $model_name . 'Custom' . 'Model';
            } else {
                $overridesModels[$model_name] = 'AeolusCMS\\MVC\\Models\\' . $model_name. 'Model';
            }
        }

        return new $overridesModels[$model_name]();
    }
}
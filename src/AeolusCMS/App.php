<?php
namespace AeolusCMS;

use AeolusCMS\Helpers\Cookie;
use AeolusCMS\Helpers\dataObj;
use AeolusCMS\Helpers\File;
use AeolusCMS\Helpers\Hooks;
use AeolusCMS\Helpers\Hooks_list;
use AeolusCMS\Libs\Controllers\Controller;
use AeolusCMS\Libs\UserObject\getUser;
use AeolusCMS\Libs\View\View;
use AeolusCMS\Wrappers\AeolusFluentPDO;
use AeolusCMS\Wrappers\AeolusPhpFastCache;


class App {
    static $cache;

    /* @var DataObj $app_data */
    static $app_data;

    /* @var DataObj $get */
    public static $get;

    /* @var DataObj $post */
    public static $post;

    private static $config;

    static AeolusFluentPDO $db;
    static Hooks $hooks;
    static getUser $user;
    static string $url;
    static bool $header404 = false;
    private static $activeModules = array();

    /* @var View $view */
    public static $view;

    /* @var boolean $ajaxMode */
    public static $ajaxMode = false;

    /* @var Controller $controllerObj */
    public static $controllerObj;

    private static $user_hash = null;

    public function __construct($config = array()) {
        $this->setConfig($config);
        $this->init();
        $this->appStarting();

        if (!is_null(self::$controllerObj)) {
            $action = self::$app_data->getAttribute('action');
            if (!method_exists(self::$controllerObj, $action)) {
                $req_controller = (self::$app_data->getAttribute('controller') == self::$config['url']['default_controller']) ? false : true;
                $this->ShowErrorPage($req_controller);
            } elseif (self::$app_data->getAttribute('parameter_3') != NULL) {
                self::$controllerObj->{self::$app_data->getAttribute('action')}(self::$app_data->getAttribute('parameter_1'), self::$app_data->getAttribute('parameter_2'), self::$app_data->getAttribute('parameter_3'));
            } elseif (self::$app_data->getAttribute('parameter_2') != NULL) {
                self::$controllerObj->{self::$app_data->getAttribute('action')}(self::$app_data->getAttribute('parameter_1'), self::$app_data->getAttribute('parameter_2'));
            } elseif (self::$app_data->getAttribute('parameter_1') != NULL) {
                self::$controllerObj->{self::$app_data->getAttribute('action')}(self::$app_data->getAttribute('parameter_1'));
            } else {
                if (self::$app_data->getAttribute('action') == '') self::$app_data->setAttribute('action', self::$config['url']['default_action']);
                try {
                    self::$controllerObj->{$action}();
                } catch (\Exception $e) {

                }
            }
        } else {
            $continue = true;
            self::$hooks->do_action('appliction_no_controller', array(&$continue));

            if ($continue) {
                $this->ShowErrorPage();
            }
        }

        $this->appEnding();
    }

    private function init() {
        self::$app_data = new dataObj();

        ini_set("log_errors", 1);
        ini_set('log_errors_max_len', 1024);
        ini_set("error_log", self::$config['runtime_dir'] . 'errors/'. date('Y-m-d') .'.log');

        global $activeModules;
        self::setActiveModules($activeModules);

        self::$cache = AeolusPhpFastCache::getAppInstance();
        $this->dbConnection();
        self::$hooks = new Hooks();
        Cookie::init();

        self::$user = new getUser();


        $this->loadInit();
        $this->splitUrl();
    }

    private function appStarting(){
        self::$hooks->do_action('application_header');
    }

    private function appEnding() {
        self::$hooks->do_action('application_footer');
        unset($_SESSION["redirect"]);
    }

    private function setConfig(array $config) {
        session_set_cookie_params(604800);
        session_start();
        header('Content-Type: text/html; charset=utf-8');

        define('SHOW_COMMENT_WRAP', true);

        define('LIBRARY_PATH', dirname(__file__));

        define('CONTROLLER_PATH', LIBRARY_PATH . '/MVC/Controllers/');
        define('VIEWS_PATH', LIBRARY_PATH . '/MVC/Views/');

        define('CUSTOM_PATH', ROOT_PATH . '/custom/');
        define('CUSTOM_CONTROLLER_PATH', CUSTOM_PATH . 'MVC/Controllers/');
        define('CUSTOM_MODEL_PATH', CUSTOM_PATH . 'MVC/Models/');
        define('CUSTOM_VIEWS_PATH', CUSTOM_PATH . 'MVC/Views/');
        define('CUSTOM_HOOKS_PATH', CUSTOM_PATH . 'Hooks/');

        $defaults = array(
            'encrypt_param' => 85,
            'url' => array(
                'default_controller' => 'home',
                'default_action' => 'index'
            ),
            'runtime_dir' => ROOT_PATH . '/runtime/'
        );

        self::$config = array_merge($defaults, $config);

        require_once LIBRARY_PATH . '/Functions/functions.php';

        spl_autoload_register(function ($class) {
            $class_sep = explode("\\", $class);
            if ($class_sep[0] == 'Custom') {
                array_shift($class_sep);
                $class = \implode('\\', $class_sep);

                $file = CUSTOM_PATH . str_replace('\\', '/', $class) . '.php';

                if (is_readable($file)) {
                    require_once($file);
                } else {
                    throw new \Exception('AutoLoad: The file ' . $class . '.php is missing in the Custom folder', 2);
                }
            }
        });
    }

    static function getConfig($key) {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        } else {
            return null;
        }
    }

    private function dbConnection() {
        try {
            $options = array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4");
            $db = new \PDO(self::$config['db']['type'] . ':host=' . self::$config['db']['host'] . ';dbname=' . self::$config['db']['name'], self::$config['db']['user'], self::$config['db']['pass'], $options);
            self::$db  = new AeolusFluentPDO($db);
        } catch (\Exception $e) {

        }
    }

    private function loadInit() {
        $activeModules = self::$activeModules;

        $hook_list_app = AeolusPhpFastCache::showKey('init_modules_hooks', function() use($activeModules) {
            $hook_list_array = array();

            if (file_exists(CUSTOM_HOOKS_PATH . 'app_hooks.php')) {
                $hook_list_array[] = array(
                    'file' => CUSTOM_HOOKS_PATH . 'app_hooks.php',
                    'class_name' => 'app',
                );
            }

            if (defined('SYSTEM_NAME')) {
                if (file_exists(CUSTOM_HOOKS_PATH . SYSTEM_NAME . '_hooks.php')) {
                    $hook_list_array[] = array(
                        'file' => CUSTOM_HOOKS_PATH . SYSTEM_NAME . '_hooks.php',
                        'class_name' => SYSTEM_NAME,
                    );
                }
            }

            foreach($activeModules as $module) {
                $url = CONTROLLER_PATH . $module . '/init.php';
                if (file_exists($url)) {
                    $hook_list_array[] = array(
                        'file' => $url,
                        'class_name' => $module,
                    );
                }

                $url = CUSTOM_CONTROLLER_PATH . $module . '/init.php';
                if (file_exists($url)) {
                    $hook_list_array[] = array(
                        'file' => $url,
                        'class_name' => $module . 'Custom',
                    );
                }
            }
            return $hook_list_array;
        }, CACHE_TIME_LV11);

        foreach($hook_list_app as $hook) {
            require_once $hook['file'];
            Hooks_list::new_hook_class($hook['class_name']);
        }
    }

    private function splitUrl() {
        self::$url = urldecode(htmlspecialchars(trim($_SERVER['REQUEST_URI'],  '/')));

        if (isset($_GET['url'])) {
            $url = htmlspecialchars(rtrim($_GET['url'], '/'));

            $url = explode('/', $url);

            self::$app_data->setAttribute('controller', (isset($url[0]) ? $url[0] : self::$config['url']['default_controller']));
            self::$app_data->setAttribute('action', (isset($url[1]) ? $url[1] : self::$config['url']['default_action']));
            self::$app_data->setAttribute('parameter_1', (isset($url[2]) ? $url[2] : null));
            self::$app_data->setAttribute('parameter_2', (isset($url[3]) ? $url[3] : null));
            self::$app_data->setAttribute('parameter_3', (isset($url[4]) ? $url[4] : null));
        } else {
            $url = array();
            self::$app_data->setAttribute('controller', self::$config['url']['default_controller']);
            self::$app_data->setAttribute('action', self::$config['url']['default_action']);
        }

        if ($this->isAjax()) {
            self::$ajaxMode = true;
            self::$app_data->action .= '_ajax';
        }


        self::$post = new dataObj();
        self::$post->fromArray($_POST);
        $_POST = array();

        self::$get = new dataObj();
        self::$get->fromArray($_GET);
        $_GET = array();

        $_REQUEST = $_ENV = array();


        if (!$controller_name = self::loadController(self::$app_data->getAttribute('controller'))) {
            $controller_name = self::loadController(self::$config['url']['default_controller'] );

            self::$app_data->setAttribute('controller', self::$config['url']['default_controller']);
            self::$app_data->setAttribute('action', 'error');

            self::setHeader404();
        }

        self::$controllerObj = new $controller_name();
    }

    private function setActiveModules($modules) {
        self::$activeModules = $modules;
    }

    static function realControllerName($controllerName) {
        $activeModules = array_map('strtolower', self::$activeModules);
        $key = array_search(strtolower($controllerName), $activeModules);

        if ($key !== false) {
            return self::$activeModules[$key];
        } else {
            return null;
        }
    }

    static function loadController($controllerName) {
        static $controllers = array();

        if ($controllerName = self::realControllerName($controllerName)) {
            if (!isset($controllers[$controllerName])) {

                $namespace = 'AeolusCMS\\MVC\\Controllers\\' . $controllerName . '\\';

                $controller_override_file = CUSTOM_CONTROLLER_PATH . $controllerName . '/' . $controllerName . 'Custom' . 'Controller.php';
                if (File::fileExists($controller_override_file)) {
                    require_once $controller_override_file;
                    $controllers[$controllerName] = $controllerName . 'Custom' . 'Controller';
                } else {
                    $controllers[$controllerName] = $namespace . $controllerName . 'Controller';
                }
            }

            return $controllers[$controllerName];
        } else {
            return false;
        }
    }

    private static function setHeader404() {
        self::$header404 = true;
    }

    private function ShowErrorPage($req_controller = true) {
        self::$hooks->do_action('application_error_page');

        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        header("Status: 404 Not Found");
        $_SERVER['REDIRECT_STATUS'] = 404;

        $args = array(
            'controller' => self::$app_data->getAttribute('controller'),
            'action' => self::$app_data->getAttribute('action'),
            'parameter_1' => self::$app_data->getAttribute('parameter_1'),
            'parameter_2' => self::$app_data->getAttribute('parameter_2'),
            'parameter_3' => self::$app_data->getAttribute('parameter_3')
        );

        self::$app_data->setAttribute('controller', self::$config['url']['error_controller']);
        self::$app_data->setAttribute('action', self::$config['url']['error_action']);

        $controller_name = self::loadController(self::$config['url']['error_controller']);

        $cont = new $controller_name();
        $cont->{self::$config['url']['error_action']}($args);
    }

    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    public static function getUserHash() {
        return self::$user_hash;
    }

    public static function setUserHash(string $user_hash) {
        self::$user_hash = $user_hash;
    }

    public static function setNoPHPLimits() {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }
}
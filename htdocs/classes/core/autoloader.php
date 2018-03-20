<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 自動載入類別
 */
class Autoloader {

    /**
     * 類別引用路徑
     * @var array
     */
    public static $classes = array();
    /**
     * 類別引用正則
     * @var array
     */
    public static $pattens = array();

    public static $include_path = array();

    /**
     * 判斷是否為合法類別名稱
     * @param  string  $class 類別名稱
     * @return boolean
     */
    public static function is_classname($class) {
        return preg_match('#^[a-zA-Z_][a-zA-Z0-9_]*$#', $class);
    }

    /**
     * 加入引用路徑
     * @param string $class 類別名稱
     * @param string $path  路徑
     */
    public static function add($class, $path) {
        if (self::is_classname($class)) {
            self::$classes[$class] = str_replace('/', DS, $path);
        } else {
            self::$pattens[$class] = $path;
        }
    }

    /**
     * 加入引用路徑列表
     * @param array $classes 引用路徑列表
     */
    public static function add_classes($classes) {
        if (is_array($classes)) {
            foreach ($classes as $name => $path) {
                self::add($name, $path);
            }
        }
    }

    /**
     * 加入預設引用路徑
     * @param string $path 路徑
     */
    public static function add_include_path($path) {
        self::$include_path[] = str_replace('/', DS, $path);
        return set_include_path(get_include_path().PATH_SEPARATOR.$path);
    }

    /**
     * 尋找引用類別路徑
     * @param  string $class 類別名稱
     * @return string        路徑
     */
    public static function find($class) {
        if (isset(self::$classes[$class])) {
            return self::$classes[$class];
        }

        foreach (self::$include_path as $path) {
            if (is_file($path = realpath($path.DS.strtolower($class).'.php'))) {
                return $path;
            }
        }

        foreach (self::$pattens as $name => $path) {
            $name = '#^'.$name.'$#iuD';
            if (preg_match($name, $class) and is_file($path = call_user_func(is_callable($path) ? 'preg_replace_callback' : 'preg_replace', $name, $path, strtolower($class)))) {
                return $path;
            }
        }

        return false;
    }

    /**
     * 載入類別
     * @param  string $class 類別名稱
     */
    public static function load($class) {
        if (($path = self::find($class))) {
            @include($path);
        }
    }

    /**
     * 註冊自動載入函式
     */
    public static function register() {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            spl_autoload_register(array(__CLASS__,'load'), true, true);
        } else {
            spl_autoload_register(array(__CLASS__,'load'), true);
        }
    }

}


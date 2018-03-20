<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 輸入類別
 */
class Input {

    /**
     * 從$_GET取值，並驗證型別
     * @param  string   $type    型別
     * @param  string   $key     鍵值
     * @param  mixed    $default 預設值
     * @return mixed             型別驗證通過為$_GET[$key]，否則為$default
     */
    public static function get($type, $key, $default=null) {
        return isset($_GET[$key]) ? self::validate_var($type, $_GET[$key], $default) : $default;
    }

    /**
     * 從$_POST取值，並驗證型別
     * @param  string   $type    型別
     * @param  string   $key     鍵值
     * @param  mixed    $default 預設值
     * @return mixed             型別驗證通過為$_POST[$key]，否則為$default
     */
    public static function post($type, $key, $default=null) {
        return isset($_POST[$key]) ? self::validate_var($type, $_POST[$key], $default) : $default;
    }

    /**
     * 從$_COOKIE取值，並驗證型別
     * @param  string   $type    型別
     * @param  string   $key     鍵值
     * @param  mixed    $default 預設值
     * @return mixed             型別驗證通過為$_COOKIE[$key]，否則為$default
     */
    public static function cookie($type, $key, $default=null) {
        return isset($_COOKIE[$key]) ? self::validate_var($type, $_COOKIE[$key], $default) : $default;
    }

    /**
     * 依序從$_GET, $_POST, $_COOKIE取值，並驗證型別
     * @param  string   $type    型別
     * @param  string   $key     鍵值
     * @param  mixed    $default 預設值
     * @return mixed             型別驗證通過為self::_request[$key]，否則為$default
     */
    public static function request($type, $key, $default=null) {
        // not using $_REQUEST because php.ini might be different
        $var = self::_request($key);
        return !is_null($var) ? self::validate_var($type, $var, $default) : $default;
    }

    /**
     * 依序從$_GET, $_POST, $_COOKIE取得非空值
     * @param  string   $key     鍵值
     * @return mixed
     */
    private static function _request($key) {
        // request_order = GPC
        if (isset($_GET[$key])) {
            return $_GET[$key];
        } elseif (isset($_POST[$key])) {
            return $_POST[$key];
        } elseif (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return null;
        }
    }

    /**
     * 驗證型別
     * @param  string   $type    型別
     * @param  mixed    $var     預驗證的值
     * @param  mixed    $default 預設值
     * @return mixed             型別驗證通過為$var，否則為$default
     */
    public static function validate_var($type, $var, $default=null) {
        switch(strtolower($type)) {
            case 's': ## 字串
                return !is_null($var) ? (string)$var : $default;
            case 'i': ## 整數
                return preg_match('/^\d+$/', (string)$var) ? (int)$var : $default;
            case 'f': ## 浮點數
                return preg_match('/^[-+]?(\d+\.?\d*|\.\d+)$/', (string)$var) ? (float)$var : $default;
            case 'a': ## 陣列
                return is_array($var) ? $var : $default;
            case 'ai': ## 整數陣列
                return is_array($var) ? array_map('intval', array_filter($var, create_function('$v', 'return preg_match("/^\d+$/", $v);'))) : $default;
            case 'af': ## 浮點數陣列
                return is_array($var) ? array_map('floatval', array_filter($var, create_function('$v', 'return preg_match("//^[-+]?(\d+\.?\d*|\.\d+)$/", $v);'))) : $default;
            default:
                return $var;
        }
    }

}

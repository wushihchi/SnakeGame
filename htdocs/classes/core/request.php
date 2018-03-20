<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 請求類別
 */
class Request {

    /**
     * 請求方法
     *
     * get 或 post
     * @var string
     */
    public static $method;

    /**
     * 請求網址
     *
     * 不包含?之後的部分
     * @var string
     */
    public static $uri;

    /**
     * 查詢字串
     *
     * $_SERVER['QUERY_STRING'] 的替名
     * @var string
     */
    public static $query;

    /**
     * 請求路徑
     *
     * <pre>
     * 不同於$uri，$path僅包含從安裝目錄開始的部分
     * 例如安裝目錄為www/frame/
     * 請求網址為/frame/foo/bar
     * $uri為/frame/foo/bar
     * $path為foo/bar
     * </pre>
     *
     * @var string
     */
    public static $path;

    /**
     * 請求網址的副檔名部分
     * @var string
     */
    public static $ext;

    /**
     * 請求路徑分段
     * @var array
     */
    public static $segment;

    /**
     * 請求的格式
     * @var array
     */
    public static $accept;

    /**
     * 請求的語系
     * @var array
     */
    public static $accept_language;

    public static $route;
    // public static $ip;
    // public static $user_agent;
    // public static $is_ajax;

    /**
     * 初始化
     */
    public static function init() {
        self::$method = strtolower($_SERVER['REQUEST_METHOD']);
        self::$uri = strtok($_SERVER['REQUEST_URI'], '?');
        self::$query = $_SERVER['QUERY_STRING'];

        self::$path =
            (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] :
            (isset($_SERVER['ORIG_PATH_INFO']) ?
                # 修正微軟IIS ORIG_PATH_INFO包含腳本名稱
                str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['ORIG_PATH_INFO']) :
            (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0 ?
                str_replace($_SERVER['SCRIPT_NAME'], '', strtok($_SERVER['REQUEST_URI'], '?')) :
                str_replace(dirname($_SERVER['SCRIPT_NAME']), '', strtok($_SERVER['REQUEST_URI'], '?'))
            )));
        self::$path = trim(self::$path, '/');
        self::$segment = explode('/', self::$path);
        if (count(self::$segment)) {
            $last = array_pop(self::$segment);
            if (($pos = strrpos($last, '.')) !== false and strlen($last)-1 != $pos) {
                array_push(self::$segment, substr($last, 0, $pos));
                self::$ext = substr($last, $pos+1);
            } else {
                array_push(self::$segment, $last);
            }
        }

        if(! empty($_SERVER['HTTP_ACCEPT'])) {
            self::$accept = self::parse_accpet($_SERVER['HTTP_ACCEPT']);
        }
        if(! empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            self::$accept_language = self::parse_accpet($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
    }

    /**
     * 解析請求的accept字串
     * @param  string $accpet accept字串
     * @return array
     */
    public static function parse_accpet($accpet) {
        preg_match_all('#([^,;]+)(?:;q=(1|0\.[0-9]+))?#i', $accpet, $matches);
        $rank = array();
        foreach ($matches[2] as $k => $v) {
            $rank[$matches[1][$k]] = empty($v) ? 1 : (float)$v;
        }
        arsort($rank, SORT_NUMERIC);
        return array_keys($rank);
    }

}
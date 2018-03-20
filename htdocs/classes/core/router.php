<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 路由器
 */
class Router {

    /**
     * 路由列表
     * @var array
     */
    public static $routes = array();

    /**
     * 加入路由
     * @param string $name     路由名稱
     * @param string $uri      匹配網址格式
     * @param array  $defaults 預設參數
     * @param array  $matches  匹配條件
     */
    public static function add($name, $uri, array $defaults=array(), array $matches=array()) {
        self::$routes[$name] = new Route($name, $uri, $defaults, $matches);
    }

    /**
     * 加入路由列表
     * @param array $routes 路由列表
     */
    public static function add_routes($routes) {
        if(is_array($routes)) {
            foreach ($routes as $name => $route) {
                is_string($route) and $route = array($route);
                if(is_array($route)) {
                    list($uri, $matches, $defaults) = array_pad($route, 3, array());
                    self::$routes[$name] = new Route($name, $uri, $matches, $defaults);
                }
            }
        }
    }

    /**
     * 尋找路由
     * @param  string $path   請求路徑
     * @param  string $method 請求方法
     * @return array          路由參數
     */
    public static function routing($path, $method) {
        $route = false;
        foreach (self::$routes as $name=> $r) {
            if($route = $r->match($path, $method)) {
                break;
            }
        }
        return $route;
    }

}


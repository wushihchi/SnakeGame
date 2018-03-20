<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 路由
 */
class Route {

    /**
     * 路由名稱
     * @var string
     */
    public $name;

    /**
     * 路由網址匹配格式
     * @var string
     */
    public $uri;

    /**
     * 預設參數
     * @var array
     */
    public $defaults;

    /**
     * 匹配條件
     * @var array
     */
    public $matches;

    /**
     * 正則
     * @var [type]
     */
    public $pattern;

    /**
     * @param string $name     路由名稱
     * @param string $uri      匹配網址格式
     * @param array  $defaults 預設參數
     * @param array  $matches  匹配條件
     */
    public function __construct($name, $uri, array $defaults=array(), array $matches=array()) {
        #todo: 需要檢查controller有沒有設定
        $this->name     = $name;
        $this->uri      = $uri;
        $this->defaults = $defaults + array('module' => '', 'controller' => '', 'action' => '');
        $this->matches  = $matches + array('method' => 'any|get|put|post|delete');
        $this->pattern  = self::compile($uri, $this->matches);
    }

    /**
     * 比對匹配請求網址
     * @param  string $uri    請求網址
     * @param  string $method 請求方法
     * @return mixed
     */
    public function match($uri, $method='any') {
        if( ! preg_match('#^'.$this->pattern.'$#uD', $method.' '.trim($uri, '/'), $matches)) {
            return false;
        }

        $matches += $this->defaults;

        $route = array(
            'name' => $this->name,
            'module' => '',
            'controller' => '',
            'action' => '',
            'params'=>array()
        );

        // class 不分大小寫,這裡可以不用轉換
        foreach ($matches as $key => $value) {
            if (in_array($key, array('module', 'controller'), true)) {
                $route[$key] = str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($value))));
            } elseif ($key === 'action') {
                $route[$key] = strtolower($value);
            } elseif (is_string($key)) {
                $route['params'][$key] = $value;
            }
        }

        return $route;
    }


    #todo: methods 可以再想其他辦法比對
    /**
     * 編譯正則
     * @param  string $uri     匹配網址格式
     * @param  array  $matches 匹配條件
     * @return string          正則
     */
    public static function compile($uri, array $matches=array()) {
        $regex = preg_replace('#[.\+*?[^\]${}=!|]#', '\\\\$0', $uri);
        $regex = str_replace(array('(', ')', '<', '>'), array('(?:', ')?', '(?P<', '>[^/]+)'), $regex);

        if ( ! empty($matches)) {
            $search = $replace = array();
            foreach ($matches as $key => $value) {
                $search[]  = "<$key>[^/]+";
                $replace[] = "<$key>$value";
            }
            $regex = str_replace($search, $replace, $regex);
        }

        return '('.$matches['method'].') '.$regex;
    }
}

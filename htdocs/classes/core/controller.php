<?php
/**
 * 框架核心
 * @package Core
 */


/**
 * 控制器
 *
 * 此為抽象類別(abstract)
 */
abstract class Controller {

    /**
     * 呼叫動作函式
     *
     * @param  string $action 動作
     * @param  array  $params 參數
     * @param  string $method 方法
     * @return mixed          回傳字串/陣列或視圖(View)
     */
    public function invoke($action='index', array $params=array(), $method='get') {

        if (method_exists($this, $method.'_'.$action)) {
            $func = $method.'_'.$action;
        } elseif (method_exists($this, 'action_'.$action)) {
            $func = 'action_'.$action;
        } else {
            throw new HttpNotFound($action.' not found');
        }

        $func = new ReflectionMethod($this, $func);

        if( ! $func->isPublic()) {
            throw new HttpNotFound($action.' is not public');
        }

        if (count($params) < $func->getNumberOfRequiredParameters()) {
            throw new HttpNotFound('too few args '.$func->getNumberOfRequiredParameters());
        }

        $func_args =  $func->getParameters();

        $args = array();
        foreach ($func_args as $arg) {
            $name = $arg->getName();
            if (isset($params[$name])) {
                $args[$name] = $params[$name];
            } elseif ($arg->isOptional()) {
                $args[$name] = $arg->getDefaultValue();
            } else {
                throw new HttpNotFound('missing arg '.$name);
            }
        }

        ob_start();

        $this->before();
        $response = $this->after($func->invokeArgs($this, $args));

        if (defined('DDEBUG')) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }

        return $response;
    }

    /**
     * 前置函式，將在呼叫動作前執行
     */
    protected function before() {}

    /**
     * 後置函式，將在呼叫動作後執行
     */
    protected function after($response) {
        return $response;
    }

}

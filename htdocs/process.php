<?php

try {
//------------------------------------------------------------------------------

    # routing
    Request::init();
	
    ## 必須返回　controller
    if (( ! $route = Router::routing(Request::$path, Request::$method)) and empty($route['controller'])) {
        throw new HttpNotFound("No route was found.");
    }
	
    Request::$route = $route;
	
    $module     = $route['module'];
    $controller = $route['controller'];
    $action     = strlen($route['action']) ? $route['action'] : 'index';
    $class      = $module.(strlen($module) && strlen($controller) ? '_' : '').$controller.CONTROLLER_POSTFIX;
	
	//2015-12-17	Ricky	先加入此段避免引入不到class
	if(is_file(CONTROLLER_PATH.$module."/".$class.".class.php")){
		require CONTROLLER_PATH.$module."/".$class.".class.php";
	}
	
    if (!class_exists($class)) {
        throw new HttpNotFound('class not found');
    }

    $ctl = new $class;

    $res = $ctl->invoke($action, $route['params'], Request::$method);

    if ($res instanceof Response) {
        $response = $res;
    } elseif ($res instanceof View) {
        $response = Response::make($res->getBody(), 200, $res->getHeaders());
    } else {
        $response = Response::make((string)$res);
    }
//------------------------------------------------------------------------------
} catch (HttpExeception $e) {
    switch ($e->getCode()) {
        case 302:
            $response = Response::make(null, $e->getCode(), $e->getHeaders());
            break;
        case 404:
            $view = PHP_View::make('error/404.php', array('message' => $e->getMessage()));
            $response = Response::make($view->getBody(), 404);
            break;
        default:
            $code = isset(Response::$statuses[$e->getCode()]) ? $e->getCode() : 500;
            $view = PHP_View::make('error/httperror.php', array(
                'code'    => $code,
                'status'  => Response::$statuses[$code],
                'message' => $e->getMessage(),
            ));
            $response = Response::make($view->getBody(), $code);
    }
//------------------------------------------------------------------------------
} catch (SqlError $e) {
    $view = PHP_View::make('error/sqlerror.php', array(
        'code' => $e->getCode(),
        'type' => 'Sql Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'query' => $e->query,
        'param' => $e->param
    ));

    $response = Response::make($view->getBody(), 500);
//------------------------------------------------------------------------------
} catch (Exception $e) {
    $view = PHP_View::make('error/showerror.php', array(
        'code' => $e->getCode(),
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ));

    $response = Response::make($view->getBody(), 500);
}
//------------------------------------------------------------------------------
$response->send();

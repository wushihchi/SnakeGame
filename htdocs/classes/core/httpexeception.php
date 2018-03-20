<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * HTTP 異常類
 */
class HttpExeception extends Exception {
	protected $headers = array();

	final public function getHeaders() {
		return $this->headers;
	}
}

/**
 * HTTP 重新導向
 */
class HttpRedirect extends HttpExeception {
	public function __construct($uri) {
		$this->headers['Location'] = $uri;
		parent::__construct(null, 302);
	}
}

/**
 * HTTP 重新導回
 */
class HttpRedirectBack extends HttpRedirect {
	public function __construct() {
		$uri = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'];
		parent::__construct($uri);
	}
}

/**
 * HTTP 禁止訪問
 */
class HttpForbidden extends HttpExeception {
	public function __construct($message='Forbidden') {
		parent::__construct($message, 403);
	}
}

/**
 * HTTP 找不到
 */
class HttpNotFound extends HttpExeception {
	public function __construct($message='Not Found') {
		parent::__construct($message, 404);
	}
}

/**
 * HTTP 內部服務器錯誤
 */
class HttpServerError extends HttpExeception {
	public function __construct($message='Internal Server Error') {
		parent::__construct($message, 500);
	}
}

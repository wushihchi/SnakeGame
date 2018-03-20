<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 響應類別
 */
class Response {

    /**
     * HTTP 狀態表
     * @var array
     */
    public static $statuses = array(
        200 => 'OK',

        302 => 'Found',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        408 => 'Request Timeout',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout'
    );

    /**
     * HTTP 狀態碼
     * @var integer
     */
    protected $code = 200;

    /**
     * HTTP 標頭
     * @var array
     */
    protected $headers = array('Content-Type' => 'text/html; charset=utf-8');

    /**
     * HTTP 內容
     * @var string
     */
    protected $body = '';

    /**
     * 產生一個響應物件
     * @param  string  $body    內容
     * @param  integer $code    狀態碼
     * @param  array   $headers 標頭
     * @return Response         響應物件
     */
    public static function make($body='', $code=200, array $headers=array()) {
        return new self($body, $code, $headers);
    }

    /**
     * @param  string  $body    內容
     * @param  integer $code    狀態碼
     * @param  array   $headers 標頭
     */
    public function __construct($body='', $code=200, array $headers=array()) {
        $this->body     = $body;
        $this->code     = intval($code);
        $this->headers  = $headers + $this->headers;
    }

    /**
     * 取得 / 設定狀態碼
     *
     * <pre>
     * $r = Response::make();
     * echo $r->code(); // 200
     * $r->code(404);
     * echo $r->code(); // 404
     * </pre>
     *
     * @param  integer $code    狀態碼
     * @return mixed
     */
    public function code($code=200) {
        if (func_num_args()) {
            $this->code = intval($code);
            return $this;
        } else {
            return $this->code;
        }
    }

    /**
     * 取得 / 設定標頭
     * @param  string $name    標頭名稱
     * @param  string $content 標頭內容
     * @return mixed
     */
    public function header($name, $content=null) {
        switch (func_num_args()) {
            case 2:
                $this->headers[$name] = $content;
                return $this;
            case 1:
                return isset($this->headers[$name]) ? $this->headers[$name] : null ;
        }
    }

    /**
     * 取得 / 設定標頭陣列
     * @param  array  $headers 標頭陣列
     * @return mixed
     */
    public function headers(array $headers=array()) {
        if (func_num_args()) {
            $this->headers = $headers + $this->headers; // overwrite
            return $this;
        } else {
            return $this->headers;
        }
    }

    /**
     * 取得 / 設定內容
     * @param  string $body 內容
     * @return mixed
     */
    public function body($body=null) {
        if (func_num_args()) {
            $this->body = $body;
            return $this;
        } else {
            return $this->__toString();
        }
    }

    /**
     * 發送響應標頭
     */
    public function send_headers() {
        if ( ! headers_sent()) {

            if (isset(self::$statuses[$this->code])) {
                header($_SERVER['SERVER_PROTOCOL'] . ' ' . $this->code.' '.self::$statuses[$this->code]);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            }

            foreach ($this->headers as $name => $value) {
                is_string($name) and strlen($name) and header("$name: $value");
            }

        }
    }

    /**
     * 發送響應內容
     */
    public function send() {
        $this->send_headers();
        if ( ! empty($this->body) and $this->code/100%10 != 3) {
            echo $this->__toString();
        }
    }

    public function __toString() {
        return (string)$this->body;
    }

}
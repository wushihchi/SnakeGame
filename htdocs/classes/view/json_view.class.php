<?php

class Json_View implements View {

    public $headers = array('Content-Type' => 'application/json; charset=utf-8');
    public $code;
    public $data;

    public static function make($data, $code=200) {
        return new static($data, $code);
    }

    public function __construct($data, $code=200) {
        $this->code = $code;
        $this->data = $data;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getBody() {
        $output = array( 'code' => $this->code );
        $output['status'] = isset(Response::$statuses[$this->code]) ? Response::$statuses[$this->code] : 'Undefined';
        $output['data'] = $this->data;

        return json_encode($output);
    }

    public function __toString() {
        return $this->getBody();
    }
}

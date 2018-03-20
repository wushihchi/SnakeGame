<?php

class PHP_View implements View {

    protected $headers = array('Content-Type' => 'text/html; charset=utf-8');
    protected $tpl;
    protected $data;

    public static function make($tpl,array $data=array()) {
        return new static($tpl, $data);
    }

    public function __construct($tpl, $data) {
        $this->tpl  = $tpl;
        $this->data = $data;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getBody() {
        if (! is_file(TPL_PATH.$this->tpl)) {
            throw new Exception(__CLASS__.'::'.__FUNCTION__.": Template '{$this->tpl}' not found.");
        }
        extract($this->data, EXTR_REFS);
        ob_start();
        include(TPL_PATH.$this->tpl);
        return ob_get_clean();
    }

    public function __toString() {
        return $this->getBody();
    }
}

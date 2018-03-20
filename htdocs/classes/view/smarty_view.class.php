<?php


class Smarty_View implements View {

    protected $headers = array('Content-Type' => 'text/html; charset=utf-8');
    protected $smarty;
    protected $tpl;
    protected $data;

    public static function make($tpl,array $data=array()) {
        return new static($tpl, $data);
    }

    public function __construct($tpl, $data) {
        $this->tpl  = $tpl;
        $this->data = $data;
        $this->smarty  = new Smarty;
        $this->smarty->template_dir = TPL_PATH;
        $this->smarty->compile_dir = '/tmp/';
        $this->smarty->compile_id = $_SERVER['HTTP_HOST'];
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getBody() {
        if (! is_file(TPL_PATH.$this->tpl)) {
            throw new Exception("Smarty template '{$this->tpl}' not found.");
        }
        $this->smarty->assign($this->data);
        return $this->smarty->fetch($this->tpl);
    }

    public function __toString() {
        return $this->getBody();
    }

}

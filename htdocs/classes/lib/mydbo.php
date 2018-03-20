<?php

class SqlError extends RuntimeException
{
    public $query;
    public $param;

    public function __construct($message, $code = 591, $query = '', array $param = array())
    {
        $this->message = $message;
        $this->code = $code;
        $this->query = $query;
        $this->param = $param;

        foreach ($this->getTrace() as $b) {
            if (isset($b['file']) and $b['file'] != __FILE__) {
                $this->file = $b['file'];
                $this->line = $b['line'];
                break;
            }
        }
    }
}

class MyDBO
{
    public $dbhost;
    public $dbuser;
    public $dbpass;
    public $dbname;
    public $dbport = 3306;
    public $socket;
    public $charset = 'utf8';
    public $readonly = false;


    /**
     * @var parse type regex
     */
    const PARSE_REG = '#%([ifsa])#iu';


    private $mysqli;

    /**
     * @var mysqli_result
     */
    private $result;

    private $last_query = '';
    private $last_param = array();


    public function __construct($host, $user, $pass, $name, $port = 3306, $socket = null)
    {
        switch (func_num_args()) {
            case 6:
                $this->socket = $socket;
            case 5:
                $this->dbport = $port;
            case 4:
                $this->dbhost = $host;
                $this->dbuser = $user;
                $this->dbpass = $pass;
                $this->dbname = $name; 
        }
    }

    public function connect()
    {
        if( ! $this->mysqli){
            $this->mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname, $this->dbport);
            $this->mysqli->connect_error and $this->trigger_error($this->mysqli->connect_error, $this->mysqli->connect_errno);

            empty($this->charset) and $this->charset = 'utf8';

            $this->mysqli->set_charset($this->charset) or $this->trigger_error();
            $this->mysqli->query("SET NAMES '{$this->charset}'") or $this->trigger_error();
            $this->mysqli->query("SET CHARACTER_SET_CLIENT={$this->charset}") or $this->trigger_error();
            $this->mysqli->query("SET CHARACTER_SET_RESULTS={$this->charset}") or $this->trigger_error();
        }

        return $this->mysqli;
    }

    public function close()
    {
        if($this->result) {
            if ($this->result instanceof mysqli_result) {
                @mysqli_free_result($this->result);
            }
            $this->result = null;
        }

        if($this->mysqli) {
            @$this->mysqli->close();
            $this->mysqli = null;
        }
    }

    /**
     * 591 = sql
     *
     * @param  string  $errMsg
     * @param  integer $errNo
     */
    private function trigger_error($errMsg = '', $errNo = 591, $query = '', array $param = array())
    {
        switch (func_num_args()) {
            case 0:
                $errMsg = $this->error;
            case 1:
                $errNo = $this->mysqli ? $this->errno : 591;
            case 2:
                $query = $this->last_query;
            case 3:
                $param = $this->last_param;
        }
        throw new SqlError($errMsg, $errNo, $query, $param);
    }

    /**
     * 判斷是否為 SELECT 語法
     * @param  string  $query
     * @return boolean
     */
    public function is_select($query)
    {
        return (bool)preg_match('#^[\s(]*(SELECT|SHOW)#i', $query);
    }

    /**
     * @param  string $query query string
     * @param  array  $param param to bind
     * @return int           insert_id | rows
     */
    public function query($query, array $param = array())
    {
        $this->last_query = $query;
        $this->last_param = $param;
        $param and $query = $this->prepare($query, $param);
        return $this->raw_query($query);
    }

    /**
     * @param  string $query query string
     * @return int           insert_id | rows
     */
    private function raw_query($query)
    {
        if ($this->readonly and ! $this->is_select($query)) {
            $this->trigger_error('Database is readonly!');
        }

        $this->mysqli or $this->connect();
        $this->result and $this->result->free();
        $this->result = null;

        if ( ! $result = $this->mysqli->query($query)) {
            $this->trigger_error();
        }

        $this->last_query = '';
        $this->last_param = array();

        if ($result instanceof mysqli_result) {
            $this->result = $result;
            return $result->num_rows;
        } else {
            $insert_id = $this->mysqli->insert_id;
            return $insert_id ? $insert_id : $this->mysqli->affected_rows;
        }
    }

    /**
     * @param  string $query query string
     * @param  array  $param param to bind
     * @return string        parsed query string
     */
    public function prepare($query, array $param)
    {
        $this->mysqli or $this->connect();

        $prepared = $types = '';

        $segment = preg_split(self::PARSE_REG, $query, -1, PREG_SPLIT_DELIM_CAPTURE);

        $num_required = floor(count($segment) / 2);
        if ( $num_required != count($param) ) {
            $this->trigger_error( sprintf("prepare_query: too few param(%d / %d).", count($param), $num_required), 0, $query, $param);
        }

        foreach ($segment as $i => $type) {
            if ( $i % 2 ) {
                $prepared .= $this->parse($type, array_shift($param));
            } else {
                $prepared .= $type;
            }
        }

        return $prepared;
    }

    /**
     * @param  string $type
     * @param  string $var
     * @return string
     */
    private function parse($type, $var)
    {
        $type = strtolower($type);

        if ($type == 'a') {
            return (! is_array($var) or empty($var)) ? "(NULL)" : "('".implode("', '", array_map(array($this, 'real_escape_string'), $var))."')";
        }

        if (is_null($var)) {
            return 'NULL';
        }

        switch ($type) {
            case 'i':
                return intval($var);
            case 'f':
                return floatval($var);
            case 's':
            default:
                return "'".$this->mysqli->real_escape_string($var)."'";
        }
    }

    /**
     * fetch a row
     *
     * @param  string  $column     specified column
     * @param  integer $resulttype
     * @return mixed
     */
    public function fetch($column = null, $resulttype = MYSQLI_ASSOC)
    {
        $ret = false;
        if ($this->result) {
            if ($row = $this->result->fetch_array($resulttype)) {
                if ($column !== null) {
                    $ret = isset($row[$column]) ? $row[$column] : null;
                } else {
                    $ret = $row;
                }
            } else {
                $this->result->free();
                $this->result = null;
            }
        }
        return $ret;
    }

    /**
     * fetch all result
     *
     * @param  string $column specified column
     * @return array
     */
    public function fetch_all($column = null, $resulttype = MYSQLI_ASSOC)
    {
        $ret = false;

        if ($this->result) {
            $ret = array();
            while (($row = $this->fetch($column, $resulttype)) !== false) {
                $ret[] = $row;
            }
        }

        return $ret;
    }

    /**
     * fetch result as key index array
     *
     * @param  string $column key
     * @return array
     */
    public function fetch_idx($column)
    {
        $ret = false;

        if ($this->result) {
            $ret = array();
            while (($row = $this->fetch()) !== false) {
                $ret[$row[$column]] = $row;
            }
        }

        return $ret;
    }

    /**
     * fetch result as key=>value pairs
     *
     * @param  string $key
     * @param  string $value
     * @return array
     */
    public function fetch_pair($key, $value)
    {
        $ret = false;

        if ($this->result) {
            $ret = array();
            while (($row = $this->fetch()) !== false) {
                $ret[$row[$key]] = $row[$value];
            }
        }

        return $ret;
    }

    //------------------------------------------------------------------------------
    ## SELECT helper

    private function select_helper($func, $query, array $param)
    {
        if ( ! $this->is_select($query)) {
            $this->trigger_error($func . ': SELECT query is required.', 0, $query, $param);
        }
        $this->query($query, $param);
    }

    public function select_one($query, array $param = array())
    {
        $this->select_helper(__FUNCTION__, $query, $param);
        if ($ret = $this->fetch()) {
            $this->result->free();
            $this->result = null;
        }
        return $ret;
    }

    public function select_all($query, array $param = array())
    {
        $this->select_helper(__FUNCTION__, $query, $param);
        return $this->fetch_all();
    }

    public function select_idx($column, $query, array $param = array())
    {
        $this->select_helper(__FUNCTION__, $query, $param);
        return $this->fetch_idx($column);
    }

    public function select_col($column, $query, array $param = array())
    {
        $this->select_helper(__FUNCTION__, $query, $param);
        return $this->fetch_all($column);
    }

    public function select_pair($key, $value, $query, array $param = array())
    {
        $this->select_helper(__FUNCTION__, $query, $param);
        return $this->fetch_pair($key, $value);
    }

}


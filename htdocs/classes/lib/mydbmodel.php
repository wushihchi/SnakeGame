<?php
// require(CLASS_PATH.'lib/mydbo.php');
/**
 * IPLDBModel 抽象類
 *
 */
abstract class MyDBModel extends DBModel {

    /**
     * 取得一筆資料
     *
     * @param  array    $conditions     WHERE條件
     * @param  array    $fields         欄位(可選)
     * @return array
     */
    final public function get(array $conditions, array $fields = array()) {
        return $this->select_one($this->_get($conditions, $fields));
    }

    /**
     * 取得多筆資料
     *
     * @param  array    $conditions     WHERE條件
     * @param  array    $options        選項(可選)
     *
     * @return array
     */
    final public function find(array $conditions, array $options = array()) {
        return $this->select_all($this->_find($conditions, $options));
    }


    /**
     * 取得多筆資料 - 索引
     *
     * @param  string   $col            索引欄位
     * @param  array    $conditions     WHERE條件
     * @param  array    $options        選項(可選)
     *
     * @return array
     */
    final public function find_idx($col, array $conditions, array $options = array()) {
        return $this->select_idx($col, $this->_find($conditions, $options));
    }

    /**
     * 取得多筆資料 - 單一欄位
     *
     * @param  string   $col            欄位
     * @param  array    $conditions     WHERE條件
     * @param  array    $options        選項(可選)
     *
     * @return array
     */
    final public function find_col($col, array $conditions, array $options = array()) {
        $options['field'] = $col;
        return $this->select_col($col, $this->_find($conditions, $options));
    }

    /**
     * 取得多筆資料 - 鍵值對子
     *
     * @param  string   $key            鍵欄位
     * @param  string   $value          值欄位
     * @param  array    $conditions     WHERE條件
     * @param  array    $options        選項(可選)
     *
     * @return array
     */
    final public function find_pair($key, $value, array $conditions, array $options = array()) {
        $options['field'] = "$key, $value";
        return $this->select_pair($key, $value, $this->_find($conditions, $options));
    }

    /**
     * 取得資料筆數
     *
     * @param  array    $conditions     WHERE條件
     * @return integer
     */
    final public function count(array $conditions) {
        $result = $this->select_one($this->_count($conditions));
        return $result['count'];
    }

    /**
     * 寫入一筆資料
     *
     * @param  array    $sets           資料陣列
     * @return integer
     */
    final public function insert(array $sets) {
        return $this->query($this->_insert($sets));
    }

    /**
     * 取代一筆資料
     *
     * @param  array    $sets           資料陣列
     * @return integer
     */
    final public function replace(array $sets) {
        return $this->query($this->_replace($sets));
    }
	
	//2015-12-18	Ricky	新增update與delete
	/**
     * 更新一筆資料
     *
     * @param  array    $sets           資料陣列
     * @param  array    $condition      條件陣列
     * @return integer
     */
    final public function update(array $sets,array $condition) {
        return $this->query($this->_update($sets,$condition));
		//return $this->_update($sets,$condition);
    }
	
	/**
     * 刪除一筆資料
     *
     * @param  array    $condition     條件陣列
     * @return integer
     */
    final public function delete_data(array $condition) {
        return $this->query($this->_delete($condition));
    }

//----------------------------------------------------------------------------------------------------------------------

    protected static $DBInfo = array();

    protected static function getDB($dbname)
    {
        if ( ! self::$DBInfo) {
            self::$DBInfo = ConfigFile::load('dbinfo.ini');
        }

        if (empty(self::$DBInfo[$dbname])) {
            throw new SqlError(sprintf("DB '%s' not found.", $dbname));
        }
        
        return self::$DBInfo[$dbname];
    }

    private $dbs;
    private $dbm;

    final public function __construct() {
        if (empty($this->dbname) or empty($this->tablename)) {
            preg_match("/^([a-z][a-z0-9]*)_([a-z0-9][a-z0-9_]*)+_[a-z][a-z0-9]*$/i", get_class($this), $match);
        }

        if (empty($this->dbname)) {
            $this->dbname = $match[1];
        }

        if (empty($this->tablename)) {
            $this->tablename = trim($match[2], '_');
        }

        $info = self::getDB($this->dbname);
		
        isset($info['port']) or $info['port'] = null;

        $this->dbm = new MyDBO($info['host'], $info['user'], $info['pass'], $this->dbname, $info['port']);
        $this->dbs = new MyDBO($info['host'], $info['user'], $info['pass'], $this->dbname, $info['port']);
        $this->dbs->readonly = true;

        if(isset($info['charset'])) {
            $this->dbm->charset = $info['charset'];
            $this->dbs->charset = $info['charset'];
        }
    }

    final public function __destruct() {
        $this->dbm->close();
        $this->dbs->close();
    }

    final protected function select_one() {
        return call_user_func_array(array($this->dbs, __FUNCTION__), func_get_args());
    }

    final protected function select_all() {
        return call_user_func_array(array($this->dbs, __FUNCTION__), func_get_args());
    }

    final protected function select_idx() {
        return call_user_func_array(array($this->dbs, __FUNCTION__), func_get_args());
    }

    final protected function select_col() {
        return call_user_func_array(array($this->dbs, __FUNCTION__), func_get_args());
    }

    final protected function select_pair() {
        return call_user_func_array(array($this->dbs, __FUNCTION__), func_get_args());
    }

    final protected function query() {
        return call_user_func_array(array($this->dbm, 'query'), func_get_args());
    }

    public function _escape_string($var) {
        $this->dbs->connect()->real_escape_string($var);
    }

}

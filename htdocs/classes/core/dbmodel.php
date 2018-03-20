<?php

abstract class DBModel {

    protected $dbname = '';
    protected $tablename = '';
    protected $schema = array();
    protected $default = array();

    /**
     * 取得一筆資料
     * @param  array    $conditions     WHERE條件
     * @param  array    $fields         欄位(可選)
     * @return array
     */
    final public function _get(array $conditions, array $fields = array()) {
		
		$conditions = $this->_condition($this->_escape_sets($conditions));
		
        $sql = 'SELECT %s FROM `%s` WHERE %s LIMIT 1';
        $sql = sprintf($sql, $this->_field($fields), $this->tablename, $conditions);

        return $sql;
    }

    /**
     * 取得多筆資料
     *
     * @param  array    $conditions     WHERE條件
     * @param  array    $options        選項(可選)
     */
    final public function _find(array $conditions, array $options = array()) {
        $conditions = $this->_condition($this->_escape_sets($conditions));

        $field = isset($options['field']) ? $this->_field($options['field']) : '*';
        $order = isset($options['order']) ? $this->_order($options['order']) : '';
        $limit = isset($options['limit']) ? $this->_limit($options['limit']) : '';

        $sql = 'SELECT %s FROM `%s` WHERE %s %s %s';
        $sql = sprintf($sql, $field, $this->tablename, $conditions, $order, $limit);

        return $sql;
    }

    /**
     * 取得資料筆數
     *
     * @param  array    $conditions     WHERE條件
     */
    final public function _count(array $conditions) {
        $conditions = $this->_condition($this->_escape_sets($conditions));

        $sql = 'SELECT COUNT(*) as `count` FROM `%s` WHERE %s';
        $sql = sprintf($sql, $this->tablename, $conditions);

        return $sql;
    }

    private function _into(array $sets) {
        $sets = $this->_sets($this->_escape_sets($sets) + $this->default);
        return sprintf(' INTO `%s` SET %s', $this->tablename, $sets);
    }

    /**
     * 寫入一筆資料
     *
     * @param  array    $sets           資料陣列
     */
    final public function _insert(array $sets) {
        return 'INSERT' . $this->_into($sets);
    }

    /**
     * 取代一筆資料
     *
     * @param  array    $sets           資料陣列
     */
    final public function _replace(array $sets) {
        return 'REPLACE' . $this->_into($sets);
    }

    /**
     * 更新資料
     *
     * @param  array    $sets           資料陣列
     * @param  array    $conditions     WHERE條件
     */
    final public function _update(array $sets, array $conditions) {
		
        $conditions = $this->_condition($this->_escape_sets($conditions));
        $sets = $this->_sets($this->_escape_sets($sets) + $this->default);

        $sql = 'UPDATE `%s` SET %s WHERE %s';
        $sql = sprintf($sql, $this->tablename, $sets, $conditions);
        return $sql;
    }

    /**
     * 刪除資料
     *
     * @param  array    $conditions     WHERE條件
     */
    final public function _delete(array $conditions) {
        $conditions = $this->_condition($this->_escape_sets($conditions));

        $sql = 'DELETE FROM `%s` WHERE %s';
        $sql = sprintf($sql, $this->tablename, $conditions);
        return $sql;
    }

    final public function __call($func, $args) {
        $fields = empty($this->schema) ? '[a-z][a-z0-9]*' : implode('|', array_keys($this->schema));

        if (preg_match("/^_?(get|find|count)_by_($fields)$/i", $func, $match)) {
            array_unshift($args, array($match[2] => array_shift($args)));
            return call_user_func_array(array($this, $match[1]), $args);
        } elseif (preg_match("/^_delete_by_($fields)$/i", $func, $match)) {
            array_unshift($args, array($match[1] => array_shift($args)));
            return call_user_func_array(array($this, '_delete'), $args);
        }
    }

//------------------------------------------------------------------------------

    final public function _field($field) {
        if (!is_array($field)) {
            if (preg_match('/^\s*\w+\s*(,\s*\w+\s*)*$/i', $field)) {
                $field = array_map('trim', explode(',', $field));
            } else {
                return '*';
            }
        }
        return $this->_fieldArr($field);
    }

    private function _fieldArr(array $fields = array()) {
        if (!empty($this->schema)) {
            $fields = array_uintersect(array_keys($this->schema), $fields, 'strcasecmp');
        }
        return empty($fields) ? '*' : '`' . implode('`, `', $fields) . '`';
    }

    final public function _condition(array $conditions = array()) {
        $var = array();
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $var[] = "`$key` in ('" . implode("', '", $value) . "')";
            } else {
                $var[] = "`$key` = '$value'";
            }
        }
        return $var ? implode(' AND ', $var) : '1';
    }

    final public function _sets(array $sets) {
        $var = array();
        foreach ($sets as $key => $value) {
            $var[] = "`$key` = '$value'";
        }
        return implode(', ', $var);
    }

    private function _keymap(array $pair) {
        if (!empty($this->schema)) {
            $pair = array_change_key_case($pair);
            $map = array_change_key_case(array_combine(array_keys($this->schema), array_keys($this->schema)));

            $newpair = array();
            foreach ($pair as $key => $value) {
                if (array_key_exists($key, $map)) {
                    $newpair[$map[$key]] = $value;
                }
            }
            return $newpair;
        } else {
            return $pair;
        }
    }

    final public function _order($order) {
        $w = '(\w+)(?:\s+(asc|desc))?';

        if (!preg_match("/^\s*$w\s*(?:,\s*$w\s*)*$/i", $order)) {
            return '';
        }

        preg_match_all("/$w/i", $order, $match);

        $order = array_combine($match[1], $match[2]);
        $order = $this->_keymap($order);

        $pair = array();
        foreach ($order as $k => $v) {
            $pair[] = sprintf('`%s` %s', $k, strcasecmp($v, 'desc') ? 'ASC' : 'DESC');
        }

        return 'ORDER BY ' . implode(', ', $pair);
    }

    final public function _limit($limit) {
        if (preg_match('/^(?:\s*([0-9]+)\s*,)?\s*([1-9][0-9]*)\s*$/', (string)$limit, $match)) {
            if (empty($match[1])) {
                $limit = sprintf('LIMIT %d', $match[2]);
            } else {
                $limit = sprintf('LIMIT %d, %d', $match[1], $match[2]);
            }
        } else {
            $limit = '';
        }
        return $limit;
    }

    final public function _escape_sets(array $sets) {
		
        $sets = array_change_key_case($sets);
		
		//重寫陣列索引
        if (!empty($this->schema)) {
            $newsets = array_intersect_ukey($this->schema, $sets, 'strcasecmp');
        } else {
            $newsets = array_fill_keys(array_keys($sets), 'string');
        }
		
		//塞值
        foreach ($newsets as $key => $type) {
			
            $value = $sets[strtolower($key)];

            if (is_array($value)) {
                $newsets[$key] = array();
                foreach ($value as $v) {
                    $newsets[$key][] = $this->_escape($v, $type);
                }
            } else {
                $newsets[$key] = $this->_escape($value, $type);
            }
        }
        return $newsets;
    }

    /**
     * 參數跳脫
     *
     * @param string   $var
     * @param string   $type
     * @return string
     */
    final public function _escape($var, $type = 's') {	
		//坑爹阿....補上每一個break 避免不知道為什麼把值變成空的
        switch($type) {
            case 'i':
				break;
            case 'int':
                $var = (int)$var;
                break;
            case 'b':
				break;
            case 'bool':
                $var = (bool)$var;
                break;
            case 'f':
				break;
            case 'float':
                $var = (float)$var;
                break;
            case 'd':
				break;
            case 'date':
                $var = date('Y-m-d', strtotime($var));
                break;
            case 'dt':
				break;
            case 'datetime':
                $var = date('Y-m-d H:i:s', strtotime($var));
                break;
            case 't':
				break;
            case 'time':
                $var = date('H:i:s', strtotime($var));
                break;
            case 's':
				break;
            case 'str':
				break;
            case 'string':
				break;
            default:
                $var = $this->_escape_string($var);
                break;
        }
        return $var;
    }

    public function _escape_string($var) {
        return addslashes((string)$var);
    }



}

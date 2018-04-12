<?php

/**
 * 管理者帳號
 *
 */
class Angeldb_User_Model extends MyDBModel
{
    protected $schema = array(
        'user_id'      => 'int',
        'user_name'    => 'string',
        'user_email'   => 'string',
        'user_pwd'     => 'string',
        'user_level'   => 'int',
        'showsysmsg'   => 'bool',
    );

    public function userListMsg()
    {
    	$sql = "SELECT * FROM `User` WHERE `user_level` != 9 ORDER BY `user_name` ASC";
    	return $this->select_all($sql); 
    }

}

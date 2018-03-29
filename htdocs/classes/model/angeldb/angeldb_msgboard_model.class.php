<?php

/**
 * 管理者帳號
 *
 */
class Angeldb_MsgBoard_Model extends MyDBModel
{
    protected $schema = array(
        'msg_id'       => 'int',
        'pmsg_id'      => 'int',
        'user_id'      => 'int',
        'msg_content'  => 'string',
        'private_fg'   => 'bool',
        'receiver'     => 'int',
        'msg_dtm'      => 'datetime',
    );

    public function replyMsg() {
    	$sql = "SELECT *  FROM `MsgBoard` WHERE `pmsg_id` > 0 ORDER BY `msg_id` DESC";
    	return $this->select_all($sql); 

    }

}
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

    public function replyMsg()
    {
    	$sql = "SELECT *  FROM `MsgBoard` WHERE `pmsg_id` > 0 ORDER BY `msg_id` DESC";
    	return $this->select_all($sql); 
    }

    public function msgList($iType,$iUserID,$iShowSysMsg,$iLimit1,$iLimit2)
    {
        switch ($iType) {
            case 'private':
                $sql = "SELECT *  FROM `MsgBoard` WHERE `pmsg_id` = 0 and (user_id = ". $iUserID ." or receiver = ". $iUserID .") ORDER BY `msg_id` DESC limit ".$iLimit1.",".$iLimit2;
                //return $sql;
                break;
            default://all
                if($iShowSysMsg == 0){
                    $sWhereStr = " and user_id <> 23";
                }
                $sql = "SELECT *  FROM `MsgBoard` WHERE `pmsg_id` = 0 and (((user_id = ". $iUserID ." or receiver = ". $iUserID .") and private_fg = 1) or private_fg = 0) ".$sWhereStr." ORDER BY `msg_id` DESC limit ".$iLimit1.",".$iLimit2;
                //return $sql;
                break;
        }
        
        return $this->select_all($sql); 
    }

    public function msgCnt($iType,$iUserID,$iShowSysMsg)
    {
        switch ($iType) {
            case 'private':
                $sql = "SELECT COUNT(*) AS CNT FROM `MsgBoard` WHERE `pmsg_id` = 0 and (user_id = ". $iUserID ." or receiver = ". $iUserID .") ORDER BY `msg_id` DESC";
                //return $sql;
                break;
            default://all
                if($iShowSysMsg == 0){
                    $sWhereStr = " and user_id <> 23";
                }
                $sql = "SELECT COUNT(*) AS CNT FROM `MsgBoard` WHERE `pmsg_id` = 0 and (((user_id = ". $iUserID ." or receiver = ". $iUserID .") and private_fg = 1) or private_fg = 0) ".$sWhereStr." ORDER BY `msg_id` DESC";
                //return $sql;
                break;
        }
        //return $sql;
        return $this->select_all($sql); 
    }

}
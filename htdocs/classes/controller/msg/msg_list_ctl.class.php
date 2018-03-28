<?php

class Msg_List_Ctl extends Controller
{
    public function get_index()
    {

        return Smarty_View::make('msg/l_msg.html',array('pagename'=>'msg'));
    }

    public function get_msglist()
    {
        if($this->chkSession()){
            $oMsgModel = new Angeldb_MsgBoard_Model;
            $oUserModel = new Angeldb_User_Model;

            $iPage = $_SESSION['msgpage'];
            $iPageSize = $_SESSION['msgpagesize'];

            $iMsgCnt = $oMsgModel->count(array('pmsg_id' => 0), array('field'=>'msg_id'));

            $iPageCnt = ceil($iMsgCnt/$iPageSize);

            //$iLimit1 = $iPageSize*$iPage;
            $iLimit2 = $iPageSize*($iPage-1);
            
            $aMsgList = $oMsgModel->find(array('pmsg_id' => 0), array(
                'field' => 'msg_id, user_id, msg_content, msg_dtm',
                'order' => 'msg_dtm desc',
                'limit' => $iLimit2.','.$iPageSize
            ));

            $aReplyMsgList = $oMsgModel->replyMsg();

            //return json_encode($aMsgList);
            $aMsgUser = $oMsgModel->find_col('user_id',array(),array('field' => 'user_id'));
            $aUserNameList = $oUserModel->find_pair(
                'user_id',
                'user_name',
                array('user_id'=>$aMsgUser),
                array()
            );
            $aUserLevelList = $oUserModel->find_pair(
                'user_id',
                'user_level',
                array('user_id'=>$aMsgUser),
                array()
            );

            
            foreach ($aMsgList as $iKey => $aValue) {
                $aMsgList[$iKey]['user_name'] = $aUserNameList[$aMsgList[$iKey]['user_id']];
                $aMsgList[$iKey]['user_level'] = $aUserLevelList[$aMsgList[$iKey]['user_id']];
            }

            foreach ($aReplyMsgList as $iKey => $aValue) {
                $aReplyMsgList[$iKey]['user_name'] = $aUserNameList[$aReplyMsgList[$iKey]['user_id']];
                $aReplyMsgList[$iKey]['user_level'] = $aUserLevelList[$aReplyMsgList[$iKey]['user_id']];
            }

            // echo '<pre>';
            // print_r($aReplyMsgList);
            //exit;
            //return json_encode($aMsgList);
            unset($oMsgModel);
            unset($oUserModel);

            return Smarty_View::make('msg/l_msg.html', array(
                'msglist'       => $aMsgList,
                'replymsglist'  => $aReplyMsgList,
                'msgCnt'        => $iMsgCnt,
                'session_id'    => $_SESSION['user_id'],
                'session_name'  => $_SESSION['user_name'],
                'session_level' => $_SESSION['user_level'],
                'page'          => $iPage,
                'pagesize'      => $iPageSize,
                'pagecnt'       => $iPageCnt
            ));
        }else{
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
    }

    public function post_insert()
    {
        if($this->chkSession()){
            $_SESSION['msgpage'] = 1;
            $sMsgContent = str_replace("'","''",Input::post('s', 'msgcontent'));
            $iUsrId = Input::post('i', 'user_id');
            $iPMsgId = Input::post('i', 'pmsg_id');

            $oModel = new Angeldb_MsgBoard_Model;

            if($iPMsgId != null){
                $primary_key = $oModel->insert(array(
                    'user_id'     => $iUsrId,
                    'pmsg_id'     => $iPMsgId,
                    'msg_content' => $sMsgContent,
                    'msg_dtm'     => gmdate('Y-m-d H:i:s', strtotime('+8 hours')),
                ));
            }else{
                $primary_key = $oModel->insert(array(
                    'user_id'     => $iUsrId,
                    'msg_content' => $sMsgContent,
                    'msg_dtm'     => gmdate('Y-m-d H:i:s', strtotime('+8 hours')),
                ));
            }

            unset($oModel);
            if($primary_key){
                return true;
            }else{
                return false;
            }
            //return $primary_key;
        }else{
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
    }

    public function post_update()
    {
        if($this->chkSession()){
            $oModel = new Angeldb_MsgBoard_Model;
            $sMsgContent = Input::post('s', 'msgcontent');
            $iMsgId = Input::post('i', 'msg_id');

            $iUpdateCnt = $oModel->update(
                array('msg_content' => $sMsgContent),
                array('msg_id' => $iMsgId)
            );
            unset($oModel);

            if($iUpdateCnt > 0){
                return true;
            }else{
                return false;
            }
            
        }else{
            return Smarty_View::make('login/login.html',array('pagename' => 'login'));
        }
    }

    public function post_delete()
    {
        if($this->chkSession()){
            $oModel = new Angeldb_MsgBoard_Model;
            $iMsgId = Input::post('i', 'msg_id');
            $iDeleteCnt = $oModel->delete_data(array('msg_id' => $iMsgId));
            unset($oModel);

            if($iDeleteCnt>0){
                return true;
            }else{
                return false;
            }
        }else{
            return Smarty_View::make('login/login.html',array('pagename'=> 'login'));
        }
    }

    private function chkSession()
    {
        session_start();

        if ($_SESSION['user_id'] == '') {
            $_SESSION['pagename'] = 'login';
            return false;
        } else {
            $_SESSION['pagename'] = 'msg';
            if (!isset($_SESSION['msgpage'])) {
                $_SESSION['msgpage'] = 1;
            }
            if (!isset($_SESSION['msgpagesize'])) {
                $_SESSION['msgpagesize'] = 5;
            }
            
            return true;
        }
    }

    public function post_changepage()
    {
        session_start();
        $_SESSION['msgpage'] = Input::post('i', 'page');
        return true;
    }

}

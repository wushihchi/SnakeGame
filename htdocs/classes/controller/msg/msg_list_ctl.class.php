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
                'field' => 'msg_id, user_id, msg_content, private_fg, receiver, msg_dtm',
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

            //第三個參數,放user_id或是receiver結果都一樣,就先不管了
            $aReceiver = $oMsgModel->find_col('receiver',array(),array('field' => 'user_id'));
            //return $aReceiver;
            $aReceiverNameList = $oUserModel->find_pair(
                'user_id',
                'user_name',
                array('user_id'=>$aReceiver),
                array()
            );
            //return json_encode($aReceiverNameList);
            echo '<pre>';
            print_r($aMsgList);
            //exit;
            foreach ($aMsgList as $iKey => $aValue) {

                $sPrivate = "";
                if($aMsgList[$iKey]['private_fg'] == true){
                    $sPrivate = "[悄悄話]";
                }

                $aMsgList[$iKey]['user_level'] = $aUserLevelList[$aMsgList[$iKey]['user_id']];
                if($aMsgList[$iKey]['private_fg'] == true &&
                    !($aMsgList[$iKey]['receiver'] == $_SESSION['user_id'] ||
                    $aMsgList[$iKey]['user_id'] == $_SESSION['user_id']) &&
                    $_SESSION['user_level'] != 3 &&
                    $_SESSION['user_level'] != 0
                ){
                    $aMsgList[$iKey]['user_name'] = "悄悄話";
                    $aMsgList[$iKey]['msg_content'] = "悄悄話";
                    $aMsgList[$iKey]['receiver_nm'] = "";
                    $aMsgList[$iKey]['user_level_nm'] = "";
                    $aMsgList[$iKey]['allow_reply'] = false;
                }else{
                    if($aMsgList[$iKey]['user_id'] == 0){
                        $aMsgList[$iKey]['user_name'] = "使用者已刪除";
                    }else{
                        $aMsgList[$iKey]['user_name'] = $aUserNameList[$aMsgList[$iKey]['user_id']];
                    }

                    if($aReceiverNameList[$aMsgList[$iKey]['receiver']] != ''){
                        $aMsgList[$iKey]['receiver_nm'] = " to ".$aReceiverNameList[$aMsgList[$iKey]['receiver']];
                    }
                    
                    $aMsgList[$iKey]['user_level_nm'] = $this->getLevelName($aMsgList[$iKey]['user_level']).'  '.$sPrivate;
                    $aMsgList[$iKey]['allow_reply'] = true;
                }
                //$aMsgList[$iKey]['receiver_nm'] = $aReceiverNameList[$aMsgList[$iKey]['receiver']];
                if($aMsgList[$iKey]['user_id'] == $_SESSION['user_id']){
                    $aMsgList[$iKey]['className'] = 'box box-blue';
                }else{
                    $aMsgList[$iKey]['className'] = 'box box-gray';
                }


            }
            
            foreach ($aReplyMsgList as $iKey => $aValue) {
                $sPrivate = "";
                if($aReplyMsgList[$iKey]['private_fg'] == true){
                    $sPrivate = "[悄悄話]";
                }

                $aReplyMsgList[$iKey]['user_level'] = $aUserLevelList[$aReplyMsgList[$iKey]['user_id']];
                if($aReplyMsgList[$iKey]['private_fg'] == true &&
                    !($aReplyMsgList[$iKey]['receiver'] == $_SESSION['user_id'] ||
                    $aReplyMsgList[$iKey]['user_id'] == $_SESSION['user_id']) &&
                    $_SESSION['user_level'] != 3 &&
                    $_SESSION['user_level'] != 0
                ){
                    $aReplyMsgList[$iKey]['user_name'] = "悄悄話";
                    $aReplyMsgList[$iKey]['msg_content'] = "悄悄話";
                    $aReplyMsgList[$iKey]['receiver_nm'] = "";
                    $aReplyMsgList[$iKey]['user_level_nm'] = "";
                }else{
                    if($aReplyMsgList[$iKey]['user_id'] == 0){
                        $aReplyMsgList[$iKey]['user_name'] = "使用者已刪除";
                    }else{
                        $aReplyMsgList[$iKey]['user_name'] = $aUserNameList[$aReplyMsgList[$iKey]['user_id']];
                    }

                    // if($aReceiverNameList[$aReplyMsgList[$iKey]['receiver']] != ''){
                    //     $aReplyMsgList[$iKey]['receiver_nm'] = " to ".$aReceiverNameList[$aReplyMsgList[$iKey]['receiver']];
                    // }
                    
                    $aReplyMsgList[$iKey]['user_level_nm'] = $this->getLevelName($aReplyMsgList[$iKey]['user_level']).'  '.$sPrivate;
                }

                //$aReplyMsgList[$iKey]['user_name'] = $aUserNameList[$aReplyMsgList[$iKey]['user_id']];
                
                //$aReplyMsgList[$iKey]['user_level_nm'] = $this->getLevelName($aReplyMsgList[$iKey]['user_level']);
                //$aReplyMsgList[$iKey]['receiver_nm'] = $aReceiverNameList[$aReplyMsgList[$iKey]['receiver']];
                if($aReplyMsgList[$iKey]['user_id'] == $_SESSION['user_id']){
                    $aReplyMsgList[$iKey]['className'] = 'box box-blue';
                }else{
                    $aReplyMsgList[$iKey]['className'] = 'box box-gray';
                }
            }
            


            $aUserSelectList = $oUserModel->userListMsg();
            $sUserSelectListStr = "";
            foreach ($aUserSelectList as $iKey => $aValue) {
                $sUserSelectListStr = $sUserSelectListStr."<option value='".$aUserSelectList[$iKey]['user_id']."'>".$aUserSelectList[$iKey]['user_name'].'</option>';
            }

            // echo '<pre>';
            // print_r($sUserSelectListStr);
            // exit;
            //return json_encode($aMsgList);
            unset($oMsgModel);
            unset($oUserModel);

            if ($iPage == 1) {
                $iPrePage = 1;
            }else{
                $iPrePage = $iPage - 1;
            }

            if ($iPage == $iPageCnt){
                $iNextPage = $iPageCnt;
            }else{
                $iNextPage = $iPage + 1;
            }

            return Smarty_View::make('msg/l_msg.html', array(
                'msglist'            => $aMsgList,
                'replymsglist'       => $aReplyMsgList,
                'msgCnt'             => $iMsgCnt,
                'userselectliststr'  => $sUserSelectListStr,
                'session_id'         => $_SESSION['user_id'],
                'session_name'       => $_SESSION['user_name'],
                'session_level'      => $_SESSION['user_level'],
                'session_level_nm'   => $this->getLevelName($_SESSION['user_level']),
                'page'               => $iPage,
                'pagesize'           => $iPageSize,
                'pagecnt'            => $iPageCnt,
                'prepage'            => $iPrePage,
                'nextpage'           => $iNextPage
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
            $bPrivateFg = Input::post('b', 'private_fg');
            $iReceiver = Input::post('i', 'receiver');

            if($bPrivateFg != true) $bPrivateFg = false;

            $oModel = new Angeldb_MsgBoard_Model;

            if($iPMsgId != null){
                $primary_key = $oModel->insert(array(
                    'user_id'     => $iUsrId,
                    'pmsg_id'     => $iPMsgId,
                    'msg_content' => $sMsgContent,
                    'private_fg'  => $bPrivateFg,
                    'receiver'    => $iReceiver,
                    'msg_dtm'     => gmdate('Y-m-d H:i:s', strtotime('+8 hours')),
                ));
            }else{
                $primary_key = $oModel->insert(array(
                    'user_id'     => $iUsrId,
                    'msg_content' => $sMsgContent,
                    'private_fg'  => $bPrivateFg,
                    'receiver'    => $iReceiver,
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

    public function getLevelName($iLevel)
    {
        switch ($iLevel)
        {
            case 0:
                return '最高權限';
                break;  
            case 3:
                return '管理者';
                break;  
            case 5:
                return '一般使用者';
                break;  
            case 9:
                return '停權中';
                break;  
            default:
                return '一般使用者';
                break;
        }
    }
}

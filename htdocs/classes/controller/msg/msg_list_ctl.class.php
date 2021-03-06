<?php

class Msg_List_Ctl extends Controller
{
    public function get_index()
    {
        return Smarty_View::make('msg/l_msg.html',array('pagename' => 'msg'));
    }

    public function get_msglist()
    {
        $oInclude = new Include_Func();
        if($oInclude->chkSession()){

            $_SESSION['pagename'] = 'msg';

            if (!isset($_SESSION['msgtab'])) {
                $_SESSION['msgtab'] = 'all';
            }

            if (!isset($_SESSION['msgpage'])) {
                $_SESSION['msgpage'] = 1;
            }
            if (!isset($_SESSION['msgpagesize'])) {
                $_SESSION['msgpagesize'] = 5;
            }

            $sMsgtab = $_SESSION['msgtab'];

            $oMsgModel = new Angeldb_MsgBoard_Model;
            $oUserModel = new Angeldb_User_Model;

            $iPage = $_SESSION['msgpage'];
            $iPageSize = $_SESSION['msgpagesize'];

            $iLimit2 = $iPageSize*($iPage-1);

            switch ($sMsgtab) {
                case 'all':
                    if($_SESSION['user_level']<3){
                        $iMsgCnt = $oMsgModel->count(array('pmsg_id' => 0), array('field'=>'msg_id'));
                        $aMsgList = $oMsgModel->find(array('pmsg_id' => 0), array(
                            'field' => 'msg_id, user_id, msg_content, private_fg, receiver, msg_dtm',
                            'order' => 'msg_dtm desc',
                            'limit' => $iLimit2.','.$iPageSize
                        ));

                    }else{
                        $iMsgCntAry = $oMsgModel->msgCnt('all',$_SESSION['user_id'],$_SESSION['showsysmsg']);
                        $iMsgCnt = $iMsgCntAry[0]['CNT'];
                        //return print_r($iMsgCntAry);
                        $aMsgList = $oMsgModel->msgList('all',$_SESSION['user_id'],$_SESSION['showsysmsg'],$iLimit2,$iPageSize);
                    }
                    //return $iMsgCnt;
                    break;
                
                case 'system':
                    $iMsgCnt = $oMsgModel->count(array('user_id' => 23), array('field'=>'msg_id'));
                    $aMsgList = $oMsgModel->find(array('user_id' => 23), array(
                        'field' => 'msg_id, user_id, msg_content, private_fg, receiver, msg_dtm',
                        'order' => 'msg_dtm desc',
                        'limit' => $iLimit2.','.$iPageSize
                    ));
                    break;
                
                case 'private':
                    $iMsgCntAry = $oMsgModel->msgCnt('private',$_SESSION['user_id'],$_SESSION['showsysmsg']);
                    $iMsgCnt = $iMsgCntAry[0]['CNT'];
                    //return print_r($iMsgCntAry);
                    $aMsgList = $oMsgModel->msgList('private',$_SESSION['user_id'],$_SESSION['showsysmsg'],$iLimit2,$iPageSize);
                    break;
                
                default:
                    # code...
                    break;
            }
            
            if($iPageSize>$iMsgCnt){
                $iPageCnt = 1;
            }else{
                $iPageCnt = ceil($iMsgCnt/$iPageSize);
            }

            $aReplyMsgList = $oMsgModel->replyMsg();
            
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
            
            $aReceiverNameList = $oUserModel->find_pair(
                'user_id',
                'user_name',
                array('user_id'=>$aReceiver),
                array()
            );

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
                    
                    $aMsgList[$iKey]['user_level_nm'] = $oInclude->getLevelName($aMsgList[$iKey]['user_level']).'  '.$sPrivate;
                    $aMsgList[$iKey]['allow_reply'] = true;
                }

                if($aMsgList[$iKey]['user_id'] == $_SESSION['user_id']){
                    $aMsgList[$iKey]['className'] = 'box box-blue';
                }elseif($aMsgList[$iKey]['user_id'] == '23'){
                    $aMsgList[$iKey]['className'] = 'box box-yellow';
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

                    $aReplyMsgList[$iKey]['user_level_nm'] = $oInclude->getLevelName($aReplyMsgList[$iKey]['user_level']).'  '.$sPrivate;
                }

                if($aReplyMsgList[$iKey]['user_id'] == $_SESSION['user_id']){
                    $aReplyMsgList[$iKey]['className'] = 'box box-blue';
                }elseif($aReplyMsgList[$iKey]['user_id'] == '23'){
                    $aReplyMsgList[$iKey]['className'] = 'box box-yellow';
                }else{
                    $aReplyMsgList[$iKey]['className'] = 'box box-gray';
                }
            }
            

            $aUserSelectList = $oUserModel->userListMsg();
            $sUserSelectListStr = "";
            foreach ($aUserSelectList as $iKey => $aValue) {
                if($aUserSelectList[$iKey]['user_level'] != '2' &&
                $aUserSelectList[$iKey]['user_name']){
                    $sUserSelectListStr = $sUserSelectListStr."<option value='".$aUserSelectList[$iKey]['user_id']."'>".$aUserSelectList[$iKey]['user_name'].'</option>';
                }
                
            }

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

            if($iPage <= 5){
                $iShowPageStart = 1;
                
                if($iPageCnt > 10){
                    $iShowPageEnd = 10;
                }else{
                    $iShowPageEnd = $iPageCnt;
                }
            }else{
                if($iPage + 5 > $iPageCnt){
                    $iShowPageStart = $iPageCnt - 9;
                    $iShowPageEnd = $iPageCnt;
                }else{
                    $iShowPageStart = $iPage - 4;
                    $iShowPageEnd = $iPage + 5;
                }
                
            }

            return Smarty_View::make('msg/l_msg.html', array(
                'msglist'            => $aMsgList,
                'replymsglist'       => $aReplyMsgList,
                'msgCnt'             => $iMsgCnt,
                'userselectliststr'  => $sUserSelectListStr,
                'session_id'         => $_SESSION['user_id'],
                'session_name'       => $_SESSION['user_name'],
                'session_level'      => $_SESSION['user_level'],
                'session_level_nm'   => $oInclude->getLevelName($_SESSION['user_level']),
                'page'               => $iPage,
                'showpagestart'      => $iShowPageStart,
                'showpageend'        => $iShowPageEnd,
                'pagesize'           => $iPageSize,
                'pagecnt'            => $iPageCnt,
                'prepage'            => $iPrePage,
                'nextpage'           => $iNextPage,
                'msgtab'             => $_SESSION['msgtab']
            ));
        }else{
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
        unset($oInclude);
    }

    public function post_insert()
    {
        $oInclude = new Include_Func();
        if($oInclude->chkSession()){
            
            $sMsgContent = str_replace("'","''",Input::post('s', 'msgcontent'));
            $iUsrId = Input::post('i', 'user_id');
            $iPMsgId = Input::post('i', 'pmsg_id');
            $bPrivateFg = Input::post('b', 'private_fg');
            $iReceiver = Input::post('i', 'receiver');

            //$sMsgContent = str_replace(chr(13).chr(10),"<BR>",$sMsgContent);

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
                $_SESSION['msgpage'] = 1;
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
        unset($oInclude);
    }

    public function post_update()
    {
        $oInclude = new Include_Func();
        if($oInclude->chkSession()){
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
        unset($oInclude);
    }

    public function post_delete()
    {
        $oInclude = new Include_Func();
        if($oInclude->chkSession()){
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
        unset($oInclude);
    }

    public function post_changepage()
    {
        session_start();
        $_SESSION['msgpage'] = Input::post('i', 'page');
        return true;
    }

    public function post_changetab(){
        session_start();
        $_SESSION['msgtab'] = Input::post('s', 'tbname');
        $_SESSION['msgpage'] = 1;
        return true;
    }
}

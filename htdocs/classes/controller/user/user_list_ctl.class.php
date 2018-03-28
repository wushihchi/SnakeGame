<?php

class User_List_Ctl extends Controller
{
    public function get_index()
    {
        //return Smarty_View::make('user/l_user.html',array('pagename'=>'user'));
    }

    public function get_userlist()
    {
        if ($this->chkSession() && ($_SESSION['user_level'] == 3 || $_SESSION['user_level'] == 0)) {
            $oModel = new Angeldb_User_Model;

            $iPage = $_SESSION['userpage'];
            $iPageSize = $_SESSION['userpagesize'];

            $iUserCnt = $oModel->count(array(), array('field' => 'msg_id'));

            $iPageCnt = ceil($iUserCnt/$iPageSize);
            //return '$iUserCnt=['.$iUserCnt.']$iPageSize=['.$iPageSize.']$iPageCnt=['.$iPageCnt.']'; 
            //$iLimit1 = $iPageSize*$iPage;
            $iLimit2 = $iPageSize*($iPage-1);
            //return '$iLimit1=['.$iLimit1.']$iLimit2=['.$iLimit2.']'; 
            $aUserList = $oModel->find(array(), array(
                'field' => 'user_id, user_name, user_email, user_pwd, user_level',
                'limit' => $iLimit2.','.$iPageSize
            ));

            unset($oModel);

            return Smarty_View::make('user/l_user.html', array(
                'userlist'      => $aUserList,
                'pagename'      => $_SESSION['pagename'],
                'session_id'    => $_SESSION['user_id'],
                'session_name'  => $_SESSION['user_name'],
                'session_level' => $_SESSION['user_level'],
                'page'          => $iPage,
                'pagesize'      => $iPageSize,
                'pagecnt'       => $iPageCnt
            ));
        } else {
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
    }

    public function post_update()
    {
        if ($this->chkSession()) {
            $oModel = new Angeldb_User_Model;

            $sUserLevel = Input::post('s', 'UserLevel');
            $sUserId = Input::post('s', 'UserId');

            $iUpdateCnt = $oModel->update(
                array('user_level'=> $sUserLevel),
                array('user_id'=> $sUserId)
            );
            unset($oModel);

            if ($iUpdateCnt>0) {
                return true;
            } else {
                return false;
            }
            
        } else {
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
    }

    public function post_delete()
    {
        if ($this->chkSession()) {
            $oUserModel = new Angeldb_User_Model;

            $sUserId = Input::post('s', 'UserId');

            $iDeleteCnt = $oUserModel->delete_data(array('user_id' => $sUserId));
            unset($oUserModel);

            //使用者刪除,留言人跟著變成0
            $oMsgModel = new Angeldb_MsgBoard_Model;
            $iUpdateCnt = $oMsgModel->update(
                array('user_id'=> 0),
                array('user_id'=> $sUserId)
            );

            unset($oMsgModel);
            
            if ($iDeleteCnt > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return Smarty_View::make('login/login.html',array('pagename' => 'login'));
        }
    }

    public function post_disable()
    {
        if($this->chkSession()){
            $oModel = new Angeldb_User_Model;
            $sUserId = Input::post('s', 'UserId');

            $iUpdateCnt = $oModel->update(
                array('user_level' => '9'),
                array('user_id' => $sUserId)
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

    public function post_enable()
    {
        $oModel = new Angeldb_User_Model;
        $sUserId = Input::post('s', 'UserId');

        $iUpdateCnt = $oModel->update(
            array('user_level' => '5'),
            array('user_id' => $sUserId)
        );
        
        unset($oModel);

        if($iUpdateCnt > 0){
            return true;
        }else{
            return false;
        }
    }

    private function chkSession()
    {
        session_start();
        if ($_SESSION['user_id'] == '') {
            $_SESSION['pagename'] = 'login';

            return false;
        } else {
            $_SESSION['pagename'] = 'user';
            if (!isset($_SESSION['userpage'])) {
                $_SESSION['userpage'] = 1;
            }
            if (!isset($_SESSION['userpagesize'])) {
                $_SESSION['userpagesize'] = 5;
            }

            return true;
        }
    }

    public function post_changepage()
    {
        session_start();

        $iPage = Input::post('i', 'page');
        $_SESSION['userpage'] = $iPage;
        return true;
    }

}

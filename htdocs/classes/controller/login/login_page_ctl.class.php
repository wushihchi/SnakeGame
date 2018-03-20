<?php

class Login_Page_Ctl extends Controller
{
    public function get_index()
    {
        session_start();
        $_SESSION['pagename']='login';
        return Smarty_View::make('login/login.html', array('pagename'=>'login'));
    }

    public function post_login()
    {
        $oModel = new Snakegame_Player_Model;
        $sPlayerEmail = Input::post('s','playeremail');
        $sPlayerPwd = Input::post('s','playerpwd');
        $aEmailExist = $oModel->get(
            array('player_email'=>$sPlayerEmail), 
            array('field'=>'player_id'
        ));

        if ($aEmailExist==null) {
            unset($oModel);
            return false;
        }

        $aPlayerList = $oModel->get(
            array('player_email'=>$sPlayerEmail, 'player_pwd'=>md5($sPlayerPwd)),
            array('field'=>'player_id', 'player_nm')
        );
        
        if ($aPlayerList==null) {
            unset($oModel);
            //return '密碼輸入錯誤，請確認!';
            return false;
        }

        session_start();

        $_SESSION['player_id']    = $aPlayerList['player_id'];
        $_SESSION['player_nm']  = $aPlayerList['player_nm'];

        unset($oModel);

        return true;
    }

    public function get_logout()
    {
        session_start();
        session_destroy();

        $_SESSION['pagename']='login';
        return Smarty_View::make('login/login.html', array('pagename'=>$_SESSION['pagename']));
    }

}


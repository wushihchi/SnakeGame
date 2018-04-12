<?php

class Login_Page_Ctl extends Controller
{
    public function get_index()
    {
        session_start();
        $_SESSION['pagename']='login';
        return Smarty_View::make('login/login.html',array('pagename'=>'login'));
    }

    public function post_login()
    {
        $oModel = new Angeldb_User_Model;
        $sUserEmail = Input::post('s', 'userEmail');

        $aEmailExist = $oModel->get(
            array('user_email'=>$sUserEmail), 
            array('field'=>'user_id'
        ));

        if ($aEmailExist==null) {
            unset($oModel);
            //return '您尚未註冊，請先註冊!';
            return false;
        }

        $sUserPwd = Input::post('s', 'userPwd');
        $sUserEmail = Input::post('s', 'userEmail');
        $aUserList = $oModel->get(
            array('user_email' => $sUserEmail, 'user_pwd' => md5($sUserPwd)),
            array('field' => 'user_id', 'user_name', 'user_level','showsysmsg')
        );

        //return json_encode($aUserList);
        
        if ($aUserList==null) {
            unset($oModel);
            //return '密碼輸入錯誤，請確認!';
            return false;
        }

      
        session_start();

        $_SESSION['user_id']     = $aUserList['user_id'];
        $_SESSION['user_level']  = $aUserList['user_level'];
        $_SESSION['user_name']   = $aUserList['user_name'];
        $_SESSION['showsysmsg']  = $aUserList['showsysmsg'];

        unset($oModel);

        return true;
    }

    public function get_logout()
    {
        session_start();
        session_destroy();

        $_SESSION['pagename'] = 'login';
        return Smarty_View::make('login/login.html', array('pagename' => $_SESSION['pagename']));
    }

    public function post_chkpermission(){
        $oModel = new Angeldb_User_Model;
        $sUserEmail = Input::post('s', 'userEmail');

        $aUserList = $oModel->get(
            array('user_email' => $sUserEmail),
            array('field' => 'user_id', 'user_level')
        );
        //return $aUserList['user_level'];
        if($aUserList['user_level'] == 9){
            session_start();
            session_destroy();

            $_SESSION['pagename'] = 'login';
            return false;
        }

        return true;
    }

}


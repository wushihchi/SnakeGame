<?php

class Regist_Page_Ctl extends Controller
{
    public function get_index()
    {
        session_start();
        $_SESSION['pagename'] = 'regist';
        return Smarty_View::make('regist/regist.html',array());
    }

    public function post_insert()
    {
        $oModel = new Angeldb_User_Model;

        $sUserName = str_replace("'", "''", Input::post('s', 'UserName'));
        $sUserEmail = str_replace("'", "''", Input::post('s', 'UserEmail'));
        $sUserPwd = str_replace("'", "''", Input::post('s', 'UserPwd'));

        $primary_key = $oModel->insert(array(
            'user_name'  => $sUserName,
            'user_email' => $sUserEmail,
            'user_pwd'   => md5($sUserPwd),
            'user_level' => '5',
        ));
        unset($oModel);
        if ($primary_key) {
            return true;
        }
        
    }

    public function post_checkemailexist()
    {
        $oModel = new Angeldb_User_Model;

        $sUserEmail = str_replace("'", "''", Input::post('s', 'userEmail'));

        $aChkUserEmail = $oModel->get(
            array('user_email' => $sUserEmail), 
            array('field' => 'user_id')
        );

        unset($oModel);

        //return json_encode($aChkUserEmail); 
        if ($aChkUserEmail != null) {
            return false;
        } else {
            return true;
        }

        
    }

    public function post_checknameexist()
    {
        $oModel = new Angeldb_User_Model;
        $sUserName = str_replace("'", "''", Input::post('s', 'userName'));

        $aChkUserName = $oModel->get(
            array('user_name' => $sUserName), 
            array('field' => 'user_id')
        );
        
        unset($oModel);
        if ($aChkUserName != null) {
            return false;
        } else {
            return true;
        }

        
    }

    public function post_ispermit(){

        $sStr = Input::post('s', 'str');
        
        if(!(stripos($sStr,"'") === false)) return false;
        if(!(stripos($sStr,"<") === false)) return false;
        if(!(stripos($sStr,">") === false)) return false;
        if(!(stripos($sStr,"delete") === false)) return false;
        if(!(stripos($sStr,"update") === false)) return false;
        if(!(stripos($sStr,"select") === false)) return false;
        if(!(stripos($sStr,"create") === false)) return false;
        if(!(stripos($sStr,"or") === false)) return false;
        if(!(stripos($sStr,"=") === false)) return false;
           
        return true;
    }

}

<?php

class User_Personal_Ctl extends Controller
{
    public function get_index()
    {
        //return Smarty_View::make('user/l_personal.html',array('pagename'=>'personal'));
    }

    public function get_personalpage()
    {
        if ($this->chkSession()) {
            $oModel = new Angeldb_User_Model;

            $aUserInfo = $oModel->find(array('user_id' => $_SESSION['user_id']), array(
                'field' => 'user_id, user_name, user_email, user_pwd, user_level'
            ));

            foreach ($aUserInfo as $iKey => $aValue) {
                $aUserInfo[$iKey]['user_level_nm'] = $this->getLevelName($aUserInfo[$iKey]['user_level']);
            }

            unset($oModel);

            return Smarty_View::make('user/l_personal.html', array(
                'userinfo'         => $aUserInfo,
                'pagename'         => $_SESSION['pagename'],
                'session_id'       => $_SESSION['user_id'],
                'session_name'     => $_SESSION['user_name'],
                'session_level'    => $_SESSION['user_level'],
                'session_level_nm' => $this->getLevelName($_SESSION['user_level'])
            ));
        } else {
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
    }

    public function post_chkoldpwd(){
        if ($this->chkSession()) {
            $oModel = new Angeldb_User_Model;

            $sUserPwd = Input::post('s', 'userPwd');

            $aInfo = $oModel->get(
                array('user_id' => $_SESSION['user_id'], 'user_pwd' => md5($sUserPwd)),
                array('field' => 'user_id')
            );

            if($aInfo != null){
                return true;
            }else{
                return false;
            }
        } else {
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
    }

    public function post_changepwd(){
        if ($this->chkSession()) {
            $oModel = new Angeldb_User_Model;

            $sUserPwd = Input::post('s', 'userPwd');

            $iUpdateCnt = $oModel->update(
                array('user_pwd' => md5($sUserPwd)),
                array('user_id'  => $_SESSION['user_id'])
            );
            
            unset($oModel);

            if($iUpdateCnt > 0){
                return true;
            }else{
                return false;
            }
        } else {
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
    }

    private function getLevelName($iLevel)
    {
        switch ($iLevel)
        {
            case 0:
                return '最高權限';
                break;    
            case 2:
                return '系統';
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

}

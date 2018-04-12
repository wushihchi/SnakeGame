<?php

class User_Personal_Ctl extends Controller
{
    public function get_index()
    {
        //return Smarty_View::make('user/l_personal.html',array('pagename'=>'personal'));
    }

    public function get_personalpage()
    {
        $oInclude = new Include_Func();
        if ($oInclude->chkSession()) {
            $oModel = new Angeldb_User_Model;

            $aUserInfo = $oModel->find(array('user_id' => $_SESSION['user_id']), array(
                'field' => 'user_id, user_name, user_email, user_pwd, user_level,showsysmsg'
            ));

            foreach ($aUserInfo as $iKey => $aValue) {
                $aUserInfo[$iKey]['user_level_nm'] = $oInclude->getLevelName($aUserInfo[$iKey]['user_level']);
            }

            unset($oModel);

            return Smarty_View::make('user/l_personal.html', array(
                'userinfo'         => $aUserInfo,
                'pagename'         => $_SESSION['pagename'],
                'session_id'       => $_SESSION['user_id'],
                'session_name'     => $_SESSION['user_name'],
                'session_level'    => $_SESSION['user_level'],
                'session_level_nm' => $oInclude->getLevelName($_SESSION['user_level'])
            ));
        } else {
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
        unset($oInclude);

    }

    public function post_chkoldpwd()
    {
        $oInclude = new Include_Func();
        if ($oInclude->chkSession()) {
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
        unset($oInclude);
    }

    public function post_changepwd()
    {
        $oInclude = new Include_Func();
        if ($oInclude->chkSession()) {
            $oModel = new Angeldb_User_Model;

            $sUserPwd = Input::post('s', 'userPwd');
            //return $sUserPwd;
            $iUpdateCnt = $oModel->update(
                array('user_pwd' => md5($sUserPwd)),
                array('user_id'  => $_SESSION['user_id'])
            );
            
            unset($oModel);
            return $iUpdateCnt;
            if($iUpdateCnt > 0){
                return true;
            }else{
                return false;
            }
        } else {
            return Smarty_View::make('login/login.html',array('pagename'=>'login'));
        }
        unset($oInclude);
    }

    public function post_showsysmsg()
    {
        $oInclude = new Include_Func();
        if($oInclude->chkSession()){
            $oModel = new Angeldb_User_Model;
            $bShowSysMsg = Input::post('b', 'open');
            //return $bShowSysMsg;
            $iUpdateCnt = $oModel->update(
                array('showsysmsg' => $bShowSysMsg),
                array('user_id' => $_SESSION['user_id'])
            );
            

            unset($oModel);

            if($iUpdateCnt > 0){
                $_SESSION['showsysmsg'] = $bShowSysMsg;
                return true;
            }else{
                return false;
            }
            
        }else{
            return Smarty_View::make('login/login.html', array('pagename' => 'login'));
        }
        unset($oInclude);
    }

}

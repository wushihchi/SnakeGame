<?php

class Regist_Page_Ctl extends Controller
{
    public function get_index()
    {
        session_start();
        $_SESSION['pagename']='regist';
        return Smarty_View::make('regist/regist.html', array());
    }

    //玩家註冊
    public function post_insert()
    {
        $oModel = new Snakegame_Player_Model;
        $sPlayerNm = str_replace("'", "''", $_POST["playernm"]);

        $primary_key = $oModel->insert(array(
            'player_nm'  => $sPlayerNm,
            'player_email' => $_POST["playeremail"],
            'player_pwd' => md5($_POST["playerpwd"]),
        ));
        unset($oModel);
        if ($primary_key) {
            return true;
        }
        
    }

    //確認Email是否已註冊
    public function post_checkemailexist()
    {
        $oModel = new Snakegame_Player_Model;
        $sPlayerEmail = str_replace("'", "''", $_POST["playeremail"]);
        $aChkPlayerEmail = $oModel->get(
            array('player_email' => $sPlayerEmail), 
            array('field' => 'player_id')
        );
        
        unset($oModel);
        if ($aChkPlayerEmail!=null) {
            return false;
        } else {
            return true;
        }
    }

    //確認暱稱是否已存在
    public function post_checknameexist()
    {
        $oModel = new Snakegame_Player_Model;
        $sPlayerNm = str_replace("'", "''", $_POST["playernm"]);
        $aChkPlayerNm = $oModel->get(
            array('player_nm' => $sPlayerNm), 
            array('field' => 'player_id')
        );
        
        unset($oModel);
        if ($aChkPlayerNm!=null) {
            return false;
        } else {
            return true;
        }
    }

}

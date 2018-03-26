<?php

class Regist_Page_Ctl extends Controller
{
    public function get_index()
    {
        return Smarty_View::make('regist/regist.html', array());
    }

    //玩家註冊
    public function post_insert()
    {
        $oModel = new Snakegame_Player_Model;
        $sPlayerNm = str_replace("'", "''", Input::post('s', 'playernm'));
        $sPlayerEmail = str_replace("'", "''", Input::post('s', 'playeremail'));
        $sPlayerPwd = str_replace("'", "''", Input::post('s', 'playerpwd'));

        $primary_key = $oModel->insert(array(
            'player_nm'    => $sPlayerNm,
            'player_email' => $sPlayerEmail,
            'player_pwd'   => md5($sPlayerPwd),
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
        $sPlayerEmail = str_replace("'", "''", Input::post('s','playeremail'));
        $aChkPlayerEmail = $oModel->get(
            array('player_email' => $sPlayerEmail), 
            array('field' => 'player_id')
        );
        
        unset($oModel);
        if ($aChkPlayerEmail != null) {
            return false;
        } else {
            return true;
        }
    }

    //確認暱稱是否已存在
    public function post_checknameexist()
    {
        $oModel = new Snakegame_Player_Model;
        $sPlayerNm = str_replace("'", "''", Input::post('s', 'playernm'));
        $aChkPlayerNm = $oModel->get(
            array('player_nm' => $sPlayerNm), 
            array('field' => 'player_id')
        );
        
        unset($oModel);
        if ($aChkPlayerNm != null) {
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

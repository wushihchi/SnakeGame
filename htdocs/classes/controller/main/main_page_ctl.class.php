<?php

class Main_Page_Ctl extends Controller
{
    public function get_index()
    {
        if ($this->chkSession()) {
            //取得最高分           
            $oModel = new Snakegame_Score_Model;
            
            $aScoreList = $oModel->find(array('player_id' => $_SESSION['user_id']), array(
                'field' => 'score_id, score,player_id,play_dtm',
                'limit' => '10',
                'order' => 'score desc'
            ));

            //return json_encode($aScoreList);
            unset($oModel);

            return Smarty_View::make('main/mainframe.html', array(
                'scorelist'        => $aScoreList,
                'session_id'       => $_SESSION['user_id'],
                'session_name'     => $_SESSION['user_name'],
                'session_level'    => $_SESSION['user_level'],
                'session_level_nm' => $this->getLevelName($_SESSION['user_level']),
            ));
            //return Smarty_View::make('main/mainframe.html', array('pagename'=>$_SESSION['pagename'],'player_id'=>$_SESSION['player_id'],'player_nm'=>$_SESSION['player_nm']));
        } else {
            return Smarty_View::make('login/login.html');
        }


       
    }

    public function get_iframepage()
    {

        if ($this->chkSession()) {
            return Smarty_View::make('main/main.html', array(
                'pagename'  => $_SESSION['pagename'],
                'user_id'   => $_SESSION['user_id'],
                'user_name' => $_SESSION['user_name'])
        );
        } else {
            return Smarty_View::make('login/login.html');
        }


       
    }
    public function get_score()
    {
         if ($this->chkSession()) {
            //取得最高分
            $oModel = new Snakegame_Score_Model;
           
            $aScoreList = $oModel->find(array('player_id' => $_SESSION['user_id']), array(
                'field' => 'score_id, score,player_id,play_dtm',
                'limit' => '5',
                'order' => 'score desc'
            ));

            unset($oModel);

            return Smarty_View::make('main/score.html', array('scorelist'=> $aScoreList,'topscore' => $iTopScore));
        } else {
            return Smarty_View::make('login/login.html');
        }
        
    }

    public function get_rank()
    {
         if ($this->chkSession()) {
            //取得最高分
            $oModel = new Snakegame_Score_Model;
            
            $aScoreList = $oModel->find(array(), array(
                'field' => 'score_id, score,player_id,play_dtm',
                'limit' => '10',
                'order' => 'score desc'
            ));

            //$oPlayerModel = new Snakegame_Player_Model;
            $oUserModel = new Angeldb_User_Model;

            $aPlayerAry = $oModel->find_col('player_id', array(), array('field' => 'player_id'));
            $aPlayerList = $oUserModel->find_pair(
                'user_id',
                'user_name',
                array('user_id'=> $aPlayerAry),
                array()
            );

            foreach ($aScoreList as $iKey => $aValue) {
                $sTempPlayerNm = $aPlayerList[$aScoreList[$iKey]['player_id']];
                if ($aScoreList[$iKey]['player_id'] != $_SESSION['user_id']) {
                    $sTempPlayerNm = $this->maskString($sTempPlayerNm);
                }
                $aScoreList[$iKey]['user_name'] = $sTempPlayerNm;                
            }

            //return json_encode($aScoreList);
            unset($oModel);
   
            return Smarty_View::make('main/rank.html', array(
                'scorelist' => $aScoreList,
                'topscore'  => $iTopScore,
                'user_id'   => $_SESSION['user_id']
            ));

        } else {
            return Smarty_View::make('login/login.html');
        }
        
    }

    public function post_save()
    {
        $oModel = new Snakegame_Score_Model;
        $iScore = Input::post('i', 'score');
        $sPlayerId = Input::post('s', 'player_id');
        $primary_key = $oModel->insert(array(
            'score'     => $iScore,
            'player_id' => $sPlayerId,
            'play_dtm'  => gmdate('Y-m-d H:i:s', strtotime('+8 hours')),
        ));

        $oUserModel = new Angeldb_User_Model;
        $sUserName = $oUserModel->find(array('user_id' => $sPlayerId), array(
            'field' => 'user_id, user_name'
        ));

        $oMsgModel = new Angeldb_MsgBoard_Model;
        $sMsgContent = "玩家 <font style=''color:blue;''>".$sUserName[0]['user_name']."</font> 剛剛在貪食蛇遊戲中，創下了 <font style=''color:red;''>".$iScore."</font> 的驚人紀錄!!";
        $primary_key = $oMsgModel->insert(array(
            'user_id'     => '23',
            'msg_content' => $sMsgContent,
            'private_fg'  => false,
            'receiver'    => 0,
            'msg_dtm'     => gmdate('Y-m-d H:i:s', strtotime('+8 hours')),
        ));

        unset($oModel);
        if ($primary_key) {
            return true;
        }
    }


    private function chkSession()
    {
        session_start();

        if ($_SESSION['user_id']=='') {
            $_SESSION['pagename'] = 'login';
            return false;
        } else {
            $_SESSION['pagename'] ='main';
            
            return true;
        }
    }

    private function maskString($s)
    {
        $masknum = 3;
        $len = strlen($s);
        /*
        if($masknum<0) {
            $masknum = $len + $masknum;
        }*/
        if ($len < 3) {
            return $s;
        } elseif ($len < $masknum + 1) {
            return substr($s, 0, 1).str_repeat('*',$len - 2).substr($s, -1);
        }

        $right = ($len - $masknum) >> 1;
        if ($right == 0) {
            $rightStr = "";
        } else {
            //$rightStr = substr($s,-$right);
            $rightStr = str_repeat('*', $right);
        }

        $left = $len - $right - $masknum;
        if ($left == 0) {
            $leftStr = "";
        } else {
            $leftStr = substr($s, 0, $left);
        }
        
        return $leftStr.str_repeat('*', $len-$right-$left).$rightStr;
    }

    public function getLevelName($iLevel)
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
}


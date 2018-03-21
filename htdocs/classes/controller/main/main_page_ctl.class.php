<?php

class Main_Page_Ctl extends Controller
{
    public function get_index()
    {
        if ($this->chkSession()) {
            //取得最高分
            $oModel = new Snakegame_Score_Model;
            
            $aScoreList = $oModel->find(array('player_id' => $_SESSION['player_id']), array(
                'field' => 'score_id, score,player_id,play_dtm',
                'limit' => '10',
                'order' => 'score desc'
            ));

            //return json_encode($aScoreList);
            unset($oModel);

            return Smarty_View::make('main/main.html', array(
                'scorelist'      => $aScoreList,
                'player_id'      => $_SESSION['player_id'],
                'player_nm'      => $_SESSION['player_nm']
            ));
            //return Smarty_View::make('main/main1.html', array('pagename'=>$_SESSION['pagename'],'player_id'=>$_SESSION['player_id'],'player_nm'=>$_SESSION['player_nm']));
        } else {
            return Smarty_View::make('login/login.html');
        }


       
    }
    public function get_score()
    {
         if ($this->chkSession()) {
            //取得最高分
            $oModel = new Snakegame_Score_Model;
            
            $aScoreList = $oModel->find(array('player_id' => $_SESSION['player_id']), array(
                'field' => 'score_id, score,player_id,play_dtm',
                'limit' => '5',
                'order' => 'score desc'
            ));

            $iTopScore = $aScoreList[0]['score'];
            
            //return json_encode($aScoreList);
            unset($oModel);

            return Smarty_View::make('main/score.html', array('scorelist'=> $aScoreList,'topscore'=> $iTopScore));
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

            $oPlayerModel = new Snakegame_Player_Model;

            $aPlayerAry = $oModel->find_col('player_id', array(), array('field' => 'player_id'));
            $aPlayerList = $oPlayerModel->find_pair(
                'player_id',
                'player_nm',
                array('player_id'=>$aPlayerAry),
                array()
            );

            //$temp = $this->maskString("star");
            //return $temp;
            
            foreach ($aScoreList as $iKey => $aValue) {
                $sTempPlayerNm = $aPlayerList[$aScoreList[$iKey]['player_id']];
                if ($aScoreList[$iKey]['player_id'] != $_SESSION['player_id']) {
                    $sTempPlayerNm = $this->maskString($sTempPlayerNm);
                }
                $aScoreList[$iKey]['player_nm'] = $sTempPlayerNm;                
            }

            unset($oModel);

            return Smarty_View::make('main/rank.html', array(
                'scorelist' => $aScoreList,
                'topscore'  => $iTopScore,
                'player_id' => $_SESSION['player_id']
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
            'play_dtm'  => date ("Y-m-d H:i:s", mktime(date('H')+7, date('i'), date('s'), date('m'), date('d'), date('Y'))),
        ));

        unset($oModel);
        if ($primary_key) {
            return true;
        }
    }

    private function chkSession()
    {
        session_start();
        if ($_SESSION['player_id'] == '') {
            $_SESSION['pagename'] = 'main';

            return false;
        } else {
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
}


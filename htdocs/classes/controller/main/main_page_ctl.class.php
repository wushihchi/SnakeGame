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

    public function post_save()
    {
        $oModel = new Snakegame_Score_Model;

        $primary_key = $oModel->insert(array(
            'score'  => $_POST["score"],
            'player_id' => $_POST["player_id"],
            'play_dtm'   => date ("Y-m-d H:i:s", mktime(date('H')+7, date('i'), date('s'), date('m'), date('d'), date('Y'))),
        ));

        unset($oModel);
        if ($primary_key) {
            return true;
        }
    }

    private function chkSession()
    {
        session_start();
        if ($_SESSION['player_id']=='') {
            $_SESSION['pagename']='main';

            return false;
        } else {
            return true;
        }
    }
}


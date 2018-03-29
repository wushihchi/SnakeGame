<?php

class Include_Func_Ctl
{
    public function chkSession()
    {
        session_start();

        if ($_SESSION['user_id']=='') {
            $_SESSION['pagename'] = 'login';
            return false;
        } else {
            return true;
        }
    }

    public function maskString($s)
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


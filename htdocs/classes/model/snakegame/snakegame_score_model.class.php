<?php

/**
 * 管理者帳號
 *
 */
class Snakegame_Score_Model extends MyDBModel
{
    protected $schema = array(
        'score_id'     => 'int',
        'score'        => 'int',
        'player_id'    => 'int',
        'play_dtm'     => 'datetime',
    );

}

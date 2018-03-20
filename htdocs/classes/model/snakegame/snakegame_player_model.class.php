<?php

/**
 * 管理者帳號
 *
 */
class Snakegame_Player_Model extends MyDBModel
{
    protected $schema = array(
        'player_id'       => 'int',
        'player_nm'      => 'string',
        'player_email'   => 'string',
        'player_pwd'  => 'string',
    );

}

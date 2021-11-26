<?php

namespace App\Common;


class BattleChara
{
    //表情包头像组
    const chara_head = array(
        0 => array(
            'name' => 'AC娘',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        1 => array(
            'name' => '鹦鹉鸡',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        2 => array(
            'name' => '咪子鱼',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        3 => array(
            'name' => '小黑猫',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        4 => array(
            'name' => '麻将脸',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        5 => array(
            'name' => '小恐龙',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        6 => array(
            'name' => 'TD猫',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        7 => array(
            'name' => '小豆泥',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        8 => array(
            'name' => '小企鹅',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),
        9 => array(
            'name' => '元元',
            'wait' => '',
            'against' => '',
            'attack' => '',
            'win' => '',
            'lose' => '',
        ),





    );

    //骰子结果文字
    const dice_message = array(
        0 => '%name突然失手了！掷出了「%rand_num」点。',
        1 => '%name突然失手了！掷出了「%rand_num」点。',
        2 => '%name突然失手了！掷出了「%rand_num」点。',
        3 => '%name突然失手了！掷出了「%rand_num」点。',
        4 => '%name突然失手了！掷出了「%rand_num」点。',
        5 => '%name突然失手了！掷出了「%rand_num」点。',
        6 => '%name突然失手了！掷出了「%rand_num」点。',
        7 => '%name突然失手了！掷出了「%rand_num」点。',
        8 => '%name突然失手了！掷出了「%rand_num」点。',
        9 => '%name突然失手了！掷出了「%rand_num」点。',
        10 => '%name突然失手了！掷出了「%rand_num」点。',

    );

    public static function CharaHead($chara_index, $action)
    {
        return self::chara_head[$chara_index][$action];
    }

    public static function CharaAttackMessage($chara_index, $rand_num)
    {
        $chara_name = self::chara_head[$chara_index];
        $message = self::dice_message[intval($rand_num / 10)];
        str_replace('%name', $chara_name, $message);
        str_replace('%rand_num', strval($rand_num), $message);
        return $message;
    }

    public static function CharaRandNum($chara_index)
    {
        switch ($chara_index) {
            case 9: //元元可以掷出最大101点
                return random_int(1, 101);
            default:
                return random_int(1, 100);
        }
    }
}

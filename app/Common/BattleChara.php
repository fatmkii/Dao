<?php

namespace App\Common;


class BattleChara
{
    //表情包头像组
    const chara_head = array(
        0 => array(
            'name' => 'AC娘',
            'wait' => 'https://www.z4a.net/images/2021/11/27/39.png',
            'against' => 'https://www.z4a.net/images/2021/11/27/13.png',
            'attack' => 'https://www.z4a.net/images/2021/11/27/1008.png',
            'win' => 'https://www.z4a.net/images/2021/11/27/2025.png',
            'lose' => 'https://www.z4a.net/images/2021/11/27/2008.png',
        ),
        1 => array(
            'name' => '鹦鹉鸡',
            'wait' => 'https://www.z4a.net/images/2021/11/27/ia_100000057.jpg',
            'against' => 'https://www.z4a.net/images/2021/11/27/QQ20210429164531.gif',
            'attack' => 'https://www.z4a.net/images/2021/11/27/B3KA7QG01_KGWBBQQ4.gif',
            'win' => 'https://www.z4a.net/images/2021/11/27/PCUSE29K4AXLVUUU3U.gif',
            'lose' => 'https://www.z4a.net/images/2021/11/26/456.jpg',
        ),
        2 => array(
            'name' => '咪子鱼',
            'wait' => 'https://www.z4a.net/images/2021/11/27/2ba6ff0ac5e63aa8bbc792556c012e5c.gif',
            'against' => 'https://www.z4a.net/images/2021/11/27/5e265cbd108fb51123d21c44854f1f2c.jpg',
            'attack' => 'https://www.z4a.net/images/2021/11/27/f75309bbd6822edaff5fe3a64e106ffe.jpg',
            'win' => 'https://www.z4a.net/images/2021/11/27/ec03418d685798d3ff035140ae186346.jpg',
            'lose' => 'https://www.z4a.net/images/2021/11/27/02dffe18efae81e3534c560ff5abfda7.jpg',
        ),
        3 => array(
            'name' => '小黑猫',
            'wait' => 'https://www.z4a.net/images/2021/11/27/ia_100004472.jpg',
            'against' => 'https://www.z4a.net/images/2021/11/27/ia_100004548.jpg',
            'attack' => 'https://www.z4a.net/images/2021/11/27/ia_100004462.jpg',
            'win' => 'https://www.z4a.net/images/2021/11/27/ia_100004540.jpg',
            'lose' => 'https://www.z4a.net/images/2021/11/27/ia_100004448.jpg',
        ),
        4 => array(
            'name' => '小白猫',
            'wait' => 'https://z3.ax1x.com/2021/04/29/gkMVyj.jpg',
            'against' => 'https://z3.ax1x.com/2021/04/29/gkMFfS.jpg',
            'attack' => 'https://z3.ax1x.com/2021/08/16/ffB0xO.png',
            'win' => 'https://z3.ax1x.com/2021/04/29/gkMnwq.jpg',
            'lose' => 'https://z3.ax1x.com/2021/04/29/gkMASg.jpg',
        ),
        5 => array(
            'name' => '麻将脸',
            'wait' => 'https://www.z4a.net/images/2021/11/27/063.png',
            'against' => 'https://www.z4a.net/images/2021/11/27/075.png',
            'attack' => 'https://www.z4a.net/images/2021/11/27/231-2.gif',
            'win' => 'https://www.z4a.net/images/2021/11/27/057.png',
            'lose' => 'https://www.z4a.net/images/2021/11/27/153.png',
        ),
        6 => array(
            'name' => '小恐龙',
            'wait' => 'https://www.z4a.net/images/2021/11/27/5415f6faaf51f3deffd1d00183eef01f3a297973.jpg',
            'against' => 'https://www.z4a.net/images/2021/11/27/QQ20210501234624.png',
            'attack' => 'https://www.z4a.net/images/2021/11/27/7a1ae0c4b74543a93a7a93a909178a82b9011471.jpg',
            'win' => 'https://www.z4a.net/images/2021/11/27/QQ20210501230752.jpg',
            'lose' => 'https://www.z4a.net/images/2021/11/27/QQ20210501230904.gif',
        ),
        7 => array(
            'name' => 'TD猫',
            'wait' => 'https://www.z4a.net/images/2021/11/27/QQ20210822211433.gif',
            'against' => 'https://www.z4a.net/images/2021/11/27/QQ20210822211656.gif',
            'attack' => 'https://www.z4a.net/images/2021/11/27/QQ20210822211037.gif',
            'win' => 'https://www.z4a.net/images/2021/11/27/QQ20210822211059.gif',
            'lose' => 'https://www.z4a.net/images/2021/11/27/QQ20210822211530.gif',
        ),
        8 => array(
            'name' => '小豆泥',
            'wait' => 'https://www.z4a.net/images/2021/11/27/QQ20210417143904.png',
            'against' => 'https://www.z4a.net/images/2021/12/01/QQ20211201220044.gif',
            'attack' => 'https://www.z4a.net/images/2021/11/27/008lY0qrly1gw4ct6dut2g306o06ogme.gif',
            'win' => 'https://www.z4a.net/images/2021/11/27/QQ20210814204640.jpg',
            'lose' => 'https://www.z4a.net/images/2021/12/01/QQ20211201220048.gif',
        ),
        9 => array(
            'name' => '小企鹅',
            'wait' => 'https://www.z4a.net/images/2021/11/27/0c350dec0ef4e3cd.jpg',
            'against' => 'https://www.z4a.net/images/2021/11/27/3d3fe9940b1439d1.jpg',
            'attack' => 'https://www.z4a.net/images/2021/11/27/dbc360ee560af5f1.gif',
            'win' => 'https://www.z4a.net/images/2021/11/27/d0c57162364ac318.jpg',
            'lose' => 'https://www.z4a.net/images/2021/11/27/d0a77206afd99fbd.jpg',
        ),
        10 => array(
            'name' => '元元',
            'wait' => 'https://www.z4a.net/images/2021/11/27/008i3skNly1gq437fg55kj308c07s0t3.jpg',
            'against' => 'https://www.z4a.net/images/2021/11/27/008i3skNly1gq436vah7gj308c07uaad.jpg',
            'attack' => 'https://www.z4a.net/images/2021/11/27/Rp87kt.png',
            'win' => 'https://www.z4a.net/images/2021/11/27/2mtBTg.jpg',
            'lose' => 'https://www.z4a.net/images/2021/11/27/RpGEX4.jpg',
        ),





    );

    //骰子结果文字
    const dice_message = array(
        0 => '%name突然脚下踩到了乐高！掷出了「%rand_num」点。',
        1 => '%name用手手轻轻拨了一下骰子，掷出了「%rand_num」点。',
        2 => '%name被对方的美貌诱惑了，掷出了「%rand_num」点。',
        3 => '%name有点心不在焉，掷出了「%rand_num」点。',
        4 => '%name不紧不慢地掷出了「%rand_num」点。',
        5 => '%name决定打完这场就回老家结婚，掷出了「%rand_num」点。',
        6 => '%name使出全力一击，掷出了「%rand_num」点。',
        7 => '%name决定向岛神祈祷，掷出了「%rand_num」点。',
        8 => '%name发动了右手的王之力！掷出了「%rand_num」点。',
        9 => '%name在此刻欧皇附体！掷出了「%rand_num」点。',
        10 => '%name发动屁元特攻，造成了成吨伤害！掷出了「%rand_num」点。',

    );

    public static function CharaHeadIndex()
    {
        $index = [];
        foreach (self::chara_head as $key => $value) {
            array_push($index, ['value' => $key, 'text' => $value['name']]);
        }
        return $index;
    }
    public static function CharaHead(int $chara_id, string $action)
    {
        return self::chara_head[$chara_id][$action];
    }

    public static function CharaName(int $chara_id)
    {
        return self::chara_head[$chara_id]['name'];
    }

    public static function CharaAttackMessage($chara_id, $rand_num)
    {
        $chara_name = self::chara_head[$chara_id]['name'];
        $message = self::dice_message[intval(($rand_num - 1) / 10)];
        $message = str_replace('%name', $chara_name, $message);
        $message = str_replace('%rand_num', strval($rand_num), $message);
        return $message;
    }

    public static function CharaRandNum($chara_id)
    {
        switch ($chara_id) {
            case 9: //元元可以掷出最大101点
                return random_int(1, 101);
            default:
                return random_int(1, 100);
        }
    }
}

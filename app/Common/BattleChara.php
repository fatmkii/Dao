<?php

namespace App\Common;


class BattleChara
{
    //表情包索引表
    //注意角色序号的数据库字段是tinyint(4)，最大255
    const chara_index = array(
        //group：0=共通组；1=咒术组；
        //message：0=共通组；其他=特别；
        0 => array(
            'name' => 'AC娘',
            'group' => 0,
            'head' => 0,
            'message' => 0,
        ),
        1 => array(
            'name' => '鹦鹉鸡',
            'group' => 0,
            'head' => 1,
            'message' => 0,
        ),
        2 => array(
            'name' => '咪子鱼',
            'group' => 0,
            'head' => 2,
            'message' => 0,
        ),
        3 => array(
            'name' => '小黑猫',
            'group' => 0,
            'head' => 3,
            'message' => 0,
        ),
        4 => array(
            'name' => '小白猫 ',
            'group' => 0,
            'head' => 4,
            'message' => 0,
        ),
        5 => array(
            'name' => '麻将脸',
            'group' => 0,
            'head' => 5,
            'message' => 0,
        ),
        6 => array(
            'name' => '小恐龙',
            'group' => 0,
            'head' => 6,
            'message' => 0,
        ),
        7 => array(
            'name' => 'TD猫',
            'group' => 0,
            'head' => 7,
            'message' => 0,
        ),
        8 => array(
            'name' => '小豆泥',
            'group' => 0,
            'head' => 8,
            'message' => 0,
        ),
        9 => array(
            'name' => '小企鹅',
            'group' => 0,
            'head' => 9,
            'message' => 0,
        ),
        10 => array(
            'name' => '元元',
            'group' => 0,
            'head' => 10,
            'message' => 0,
        ),
        100 => array(
            'name' => '虎杖悠仁',
            'group' => 1,
            'head' => 100,
            'message' => 100,
        ),
        101 => array(
            'name' => '伏黑惠',
            'group' => 1,
            'head' => 101,
            'message' => 101,
        ),
        102 => array(
            'name' => '钉崎野蔷薇',
            'group' => 1,
            'head' => 102,
            'message' => 102,
        ),
        103 => array(
            'name' => '五条悟',
            'group' => 1,
            'head' => 103,
            'message' => 103,
        ),
        104 => array(
            'name' => '七海建人',
            'group' => 1,
            'head' => 104,
            'message' => 104,
        ),
        105 => array(
            'name' => '夏油杰',
            'group' => 1,
            'head' => 105,
            'message' => 105,
        ),
        106 => array(
            'name' => '乙骨忧太',
            'group' => 1,
            'head' => 106,
            'message' => 106,
        ),
        107 => array(
            'name' => '狗卷棘',
            'group' => 1,
            'head' => 107,
            'message' => 107,
        ),
        108 => array(
            'name' => '两面宿傩',
            'group' => 1,
            'head' => 108,
            'message' => 108,
        ),
        109 => array(
            'name' => '伏黑甚尔',
            'group' => 1,
            'head' => 109,
            'message' => 109,
        ),
        110 => array(
            'name' => '禅院直哉',
            'group' => 1,
            'head' => 110,
            'message' => 110,
        ),
        111 => array(
            'name' => '屁元',
            'group' => 1,
            'head' => 111,
            'message' => 111,
        ),
        199 => array(
            'name' => '芥见下下',
            'group' => 1,
            'head' => 199,
            'message' => 199,
        ),
    );

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
        100 => array(
            'name' => '虎杖悠仁',
            'wait' => 'https://z3.ax1x.com/2021/04/29/gFjiGD.jpg',
            'against' => 'https://z3.ax1x.com/2021/04/29/gFjJLn.gif',
            'attack' => 'https://z3.ax1x.com/2021/04/28/giOTG4.gif',
            'win' => 'https://z3.ax1x.com/2021/05/11/gdFaCV.jpg',
            'lose' => 'https://z3.ax1x.com/2021/05/11/gdFN40.jpg',
        ),
        101 => array(
            'name' => '伏黑惠',
            'wait' => 'https://z3.ax1x.com/2021/04/29/gFjFRe.jpg',
            'against' => 'https://z3.ax1x.com/2021/04/28/giOjZ6.jpg',
            'attack' => 'https://z3.ax1x.com/2021/05/26/2Cnk8K.png',
            'win' => 'https://z3.ax1x.com/2021/07/30/WXKuQI.jpg',
            'lose' => 'https://z3.ax1x.com/2021/05/11/gdi26g.jpg',
        ),
        102 => array(
            'name' => '钉崎野蔷薇',
            'wait' => 'https://s4.ax1x.com/2022/01/27/7jgoYq.jpg',
            'against' => 'https://z3.ax1x.com/2021/04/29/gFjkxH.jpg',
            'attack' => 'https://s4.ax1x.com/2022/01/27/7jgTf0.jpg',
            'win' => 'https://z3.ax1x.com/2021/05/16/ggCxRH.png',
            'lose' => 'https://z3.ax1x.com/2021/07/30/WXK3TS.jpg',
        ),
        103 => array(
            'name' => '五条悟',
            'wait' => 'https://z3.ax1x.com/2021/05/09/gYIjVP.jpg',
            'against' => 'https://z3.ax1x.com/2021/07/30/WXKR61.jpg',
            'attack' => 'https://z3.ax1x.com/2021/04/29/gFjWFK.jpg',
            'win' => 'https://z3.ax1x.com/2021/05/16/ggPpQA.png',
            'lose' => 'https://www.z4a.net/images/2021/04/29/QQ20210429165856.png',
        ),
        104 => array(
            'name' => '七海建人',
            'wait' => 'https://z3.ax1x.com/2021/07/30/WXKOXt.jpg',
            'against' => 'https://z3.ax1x.com/2021/04/28/giOyGQ.jpg',
            'attack' => 'https://z3.ax1x.com/2021/04/29/gFjEMd.jpg',
            'win' => 'https://s4.ax1x.com/2022/01/27/7jWnQf.jpg',
            'lose' => 'https://s4.ax1x.com/2022/01/27/7jWuy8.jpg',
        ),
        105 => array(
            'name' => '夏油杰',
            'wait' => 'https://z3.ax1x.com/2021/04/29/gFj1zQ.jpg',
            'against' => 'https://z3.ax1x.com/2021/05/09/gYorZt.jpg',
            'attack' => 'https://z3.ax1x.com/2021/04/28/giO5IU.png',
            'win' => 'https://z3.ax1x.com/2021/04/28/giO62j.jpg',
            'lose' => 'https://www.z4a.net/images/2021/12/01/iFonQ.jpg',
        ),
        106=> array(
            'name' => '乙骨忧太',
            'wait' => 'https://s4.ax1x.com/2022/01/27/7j2rB4.jpg',
            'against' => 'https://s4.ax1x.com/2022/01/27/7j26E9.jpg',
            'attack' => 'https://s4.ax1x.com/2022/01/27/7j2sHJ.jpg',
            'win' => 'https://z3.ax1x.com/2021/04/28/giODIS.jpg',
            'lose' => 'https://s4.ax1x.com/2022/01/27/7j2cNR.jpg',
        ),
        107=> array(
            'name' => '狗卷棘',
            'wait' => 'https://z3.ax1x.com/2021/04/29/gFjdiT.jpg',
            'against' => 'https://z3.ax1x.com/2021/06/18/RpGxgO.gif',
            'attack' => 'https://z3.ax1x.com/2021/06/18/RpGv8K.gif',
            'win' => 'https://z3.ax1x.com/2021/07/30/WXKhm6.jpg',
            'lose' => 'https://z3.ax1x.com/2021/05/16/ggP9sI.png',
        ),
        108 => array(
            'name' => '两面宿傩',
            'wait' => 'https://s4.ax1x.com/2022/01/09/7FRzbF.jpg',
            'against' => 'https://z3.ax1x.com/2021/04/29/gFjsy9.jpg',
            'attack' => 'https://z3.ax1x.com/2021/04/29/gFjBz4.jpg',
            'win' => 'https://z3.ax1x.com/2021/07/30/WXKokD.jpg',
            'lose' => 'https://z3.ax1x.com/2021/04/29/gFjuIf.jpg',
        ),
        109 => array(
            'name' => '伏黑甚尔',
            'wait' => 'https://q7501.bvimg.com/10368/efa0f2a34638954a.jpg',
            'against' => 'https://z3.ax1x.com/2021/05/09/gYoVaV.gif',
            'attack' => 'https://z3.ax1x.com/2021/05/09/gYomPU.gif',
            'win' => 'https://q7501.bvimg.com/10368/cab90b41784dab0d.gif',
            'lose' => 'https://q7501.bvimg.com/10368/269e3bc4c680bdac.gif',
        ),
        110 => array(
            'name' => '禅院直哉',
            'wait' => 'https://q7501.bvimg.com/10368/069fc0c61de0c8e4.jpg',
            'against' => 'https://q7501.bvimg.com/10368/8a587dd853bdf8d2.gif',
            'attack' => 'https://z3.ax1x.com/2021/06/18/RpJ8P0.gif',
            'win' => 'https://z3.ax1x.com/2021/05/16/ggC7s1.jpg',
            'lose' => 'https://z3.ax1x.com/2021/05/09/gYoZ5T.gif',
        ),
        111 => array(
            'name' => '屁元',
            'wait' => 'https://z3.ax1x.com/2021/06/18/Rp87kt.png',
            'against' => 'https://z3.ax1x.com/2021/06/18/Rp8HtP.jpg',
            'attack' => 'https://z3.ax1x.com/2021/05/09/gYotPO.jpg',
            'win' => 'https://z3.ax1x.com/2021/05/26/2Cntbj.jpg',
            'lose' => 'https://z3.ax1x.com/2021/06/18/RpGEX4.jpg',
        ),
        199 => array(
            'name' => '芥见下下',
            'wait' => 'https://z3.ax1x.com/2021/05/11/gdFVHA.jpg',
            'against' => 'https://z3.ax1x.com/2021/04/29/gFjrQJ.jpg',
            'attack' => 'https://www.z4a.net/images/2021/12/01/Snipaste_2021-12-01_22-14-55.jpg',
            'win' => 'https://www.z4a.net/images/2021/05/10/D5XNu.png',
            'lose' => 'https://www.z4a.net/images/2021/05/10/D5sNh.png',
        ),
    );

    //表情包骰子结果文字
    const chara_message = array(
        0 => array(
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
        ),
        100 => array(
            0 => '%name看电影分心被咒核熊揍了，掷出了「%rand_num」点。',
            1 => '%name进入假死状态，掷出了「%rand_num」点。',
            2 => '%name遇到小葵跟踪，拼命逃跑掷出了「%rand_num」点。',
            3 => '%name被要求拎着一大堆购物袋，掷出了「%rand_num」点。',
            4 => '%name今天打小钢珠的运气不好也不差，掷出了「%rand_num」点。',
            5 => '%name使出了径庭拳，掷出了「%rand_num」点。',
            6 => '%name全身心注入咒力，掷出了「%rand_num」点。',
            7 => '%name使出了投掷铅球30m的水平，掷出了「%rand_num」点。',
            8 => '%name打出了本垒打！掷出了「%rand_num」点。',
            9 => '%name绝境之中领悟了黑闪！掷出了「%rand_num」点。',
        ),
        101 => array(
            0 => '%name继承了某个男人的霉运，掷出了「%rand_num」点。',
            1 => '%name被虎杖和钉崎狠狠整蛊，掷出了「%rand_num」点。',
            2 => '%name吃到超甜的草莓大福被齁得面目扭曲，掷出了「%rand_num」点。',
            3 => '%name一边要求虎杖和钉崎请吃三万一顿的饭一边掷出了「%rand_num」点。',
            4 => '%name用脱兔淹没%opponent，掷出了「%rand_num」点。',
            5 => '%name摸了摸小白小黑的头，掷出了「%rand_num」点。',
            6 => '%name钻进影子盯着%opponent，掷出了「%rand_num」点。',
            7 => '%name用海胆刺狠狠扎了%opponent，掷出了「%rand_num」点。',
            8 => '%name整个影子动物园发动攻击！掷出了「%rand_num」点。',
            9 => '%name布瑠部由良由良，异界神将魔虚罗！掷出了「%rand_num」点。',
        ),
        102 => array(
            0 => '%name早上起来找不到校服裙子了，抓狂地掷出了「%rand_num」点。',
            1 => '%name因为虎杖伏黑都没看出自己新换的发型，不爽地掷出了「%rand_num」点。',
            2 => '%name又被老师套路了以为去逛街结果出任务，气得掷出了「%rand_num」点。',
            3 => '%name和虎杖争执寿司吃哪家没争过，在回转寿司店生无可恋的掷出了「%rand_num」点。',
            4 => '%name观察%opponent颜值，得出结论长得像土豆，失去兴趣地掷出了「%rand_num」点。',
            5 => '%name和虎杖伏黑猜拳决定谁去买饮料结果连续平局20次，掷出了「%rand_num」点。',
            6 => '%name买的新裙子被真希夸好看，开心掷出了「%rand_num」点。',
            7 => '%name发现伏黑惠和女生在聊天，戏瘾大发假装抓奸掷出了「%rand_num」点。',
            8 => '%name黑五买到心水很久的限定款包包，掷出了「%rand_num」点。',
            9 => '%name把钉子甩出黑闪怒扎%opponent，掷出了「%rand_num」点。',     
        ),
        103 => array(
            0 => '%name不慎踩到御门疆，掷出了「%rand_num」点。',
            1 => '%name执行任务遇到刀疤男人，掷出了「%rand_num」点。',
            2 => '%name被烂橘子抓去开会，掷出了「%rand_num」点。',
            3 => '%name想吃的甜品卖完了，掷出了「%rand_num」点。',
            4 => '%name今天的咒灵有点多，心不在焉地掷出了「%rand_num」点。',
            5 => '%name学生今天又弄脏了一件衬衫，一边想着怎么整蛊他们一边掷出了「%rand_num」点。',
            6 => '%name今天的大喜利玩得很开心掷出了「%rand_num」点。',
            7 => '%name吃到了新口味喜久福，开心地掷出了「%rand_num」点。',
            8 => '%name烂橘子被迫乖乖听话，心情格外舒畅地掷出了「%rand_num」点。',
            9 => '%name终于打开御门疆啦！重获自由后帅气地掷出了「%rand_num」点。',
        ),
        104 => array(
            0 => '%name临时被通知周末也要加班，生无可恋地掷出了「%rand_num」点。',
            1 => '%name在枕头边发现了一些脱落的头发，郁闷地掷出了「%rand_num」点。',
            2 => '%name没买到喜欢的面包，烦躁地掷出了「%rand_num」点。',
            3 => '%name收到了来自某白毛的奇怪的画，生气地掷出了「%rand_num」点。',
            4 => '%name没睡好熬出了黑眼圈，不慌不忙地掷出了「%rand_num」点。',
            5 => '%name今天下班后去喝酒玩骰子，掷出了「%rand_num」点。',
            6 => '%name收到了这个月的工资，开心地掷出了「%rand_num」点。',
            7 => '%name休息日在家悠闲地看书，掷出了「%rand_num」点。',
            8 => '%name吃到了喜欢的西班牙橄榄蒜虾，掷出了「%rand_num」点。',
            9 => '%name获得连休10天，激动地掷出了「%rand_num」点。', 
        ),
        105 => array(
            0 => '%name被一群猴子绊倒，掷出了「%rand_num」点。',
            1 => '%name执行任务遇到刀疤男人，掷出了「%rand_num」点。',
            2 => '%name吃了一个味道奇怪的咒灵球，掷出了「%rand_num」点。',
            3 => '%name和同班同学一起心不在焉听夜蛾训话，掷出了「%rand_num」点。',
            4 => '%name高专任务结束后顺路去吃了荞麦面，掷出了「%rand_num」点。',
            5 => '%name今天超市除臭剂打折，买了很多，掷出了「%rand_num」点。',
            6 => '%name陪菜美一起逛街shopping吃甜品，掷出了「%rand_num」点。',
            7 => '%name对近期盘星教发展发表重要讲话，掷出了「%rand_num」点。',
            8 => '%name对乙姓男子的咒灵一见钟情，掷出了「%rand_num」点。',
            9 => '%name骗到了猴子的钱，掷出了「%rand_num」点。',
        ),
        106 => array(
            0 => '%name不小心踩到自己鞋带在教室里摔倒，掷出了「%rand_num」点。',
            1 => '%name奇怪的丑男同学想要对自己做奇怪的事情，掷出了「%rand_num」点。',
            2 => '%name吃到了讨厌的牛排油脂，掷出了「%rand_num」点。',
            3 => '%name在非洲出差，见不到二年级同学，掷出了「%rand_num」点。',
            4 => '%name把自己的海胆头梳成中分，并掷出了「%rand_num」点。',
            5 => '%name听说有人要抢自己老婆，大为震撼掷出了「%rand_num」点。',
            6 => '%name在非洲吃到了好吃的肉卷，高兴的掷出了「%rand_num」点。',
            7 => '%name复制了狗卷棘的咒言术，用大喇叭说%opponent必须输，掷出了「%rand_num」点。',
            8 => '%name跟里香深情告白放闪光弹，闪瞎了大家并掷出了「%rand_num」点。',
            9 => '%name严正声明%opponent真失礼并指出自己这可是纯爱，掷出了「%rand_num」点。', 
        ),
        107 => array(
            0 => '%name喉咙药用完了，无法发出声音，掷出了「%rand_num」点。',
            1 => '%name饭团语没有被岛神理解，伤心的掷出了「%rand_num」点。',
            2 => '%name遇到了比自己强大的%opponent，咒言被反噬了！掷出了「%rand_num」点。',
            3 => '%name被迫参加了讨厌的晨会，掷出了「%rand_num」点。',
            4 => '%name不小心吃到了讨厌的鱼子，掷出了「%rand_num」点。',
            5 => '%name一边打游戏一边随便的掷出了「%rand_num」点。',
            6 => '%name拉下衣领，唇齿轻启，掷出了「%rand_num」点。',
            7 => '%name举起扩音器，用咒言向岛神祈祷，掷出了「%rand_num」点。',
            8 => '%name发明了新的恶作剧道具【魔鬼辣椒饭团】，骗了「%rand_num」人。',
            9 => '%name在油管上投稿的熊猫吃肉丸视频播放破亿，掷出了「%rand_num」点。',
        ),
        108 => array(
            0 => '%name不小心被关进容器里，掷出了「%rand_num」点。',
            1 => '%name被缝合脸挑衅，不爽地掷出了「%rand_num」点。',
            2 => '%name吃到了不好吃的菜，掷出了「%rand_num」点。',
            3 => '%name被威胁办事，很不爽地掷出了「%rand_num」点。',
            4 => '%name大业遥遥无期，心不在焉地掷出了「%rand_num」点。',
            5 => '%name好久没有出来露脸，对jjxx警告地掷出了「%rand_num」点。',
            6 => '%name今天又见到了海胆，心情还不错地掷出了「%rand_num」点。',
            7 => '%name伏黑惠打败了特级咒灵，这样就很好，掷出了「%rand_num」点。',
            8 => '%name伏黑惠召唤出来魔虚罗，很满意地掷出了「%rand_num」点。',
            9 => '%name完成大业如有神助般，掷出了「%rand_num」点。',
        ),
        109 => array(
            0 => '%name被禅院家扔进咒灵堆，掷出了「%rand_num」点。',
            1 => '%name出任务碰到两个难缠的高中生，不爽地掷出了「%rand_num」点。',
            2 => '%name赌狗赌狗一无所有，被孔老叔无情嘲笑，掷出了「%rand_num」点。',
            3 => '%name路过盘星教被里面冲出来的猴子撞到，掷出了「%rand_num」点。',
            4 => '%name卖身嫖资没谈好，和客人打起来了，掷出了「%rand_num」点。',
            5 => '%name陪富婆睡了一晚，掷出了「%rand_num」点。',
            6 => '%name威胁夏油杰给钱否则砍掉他的刘海，勒索了「%rand_num」元。',
            7 => '%name用胸肌弹飞了%opponent，掷出了「%rand_num」点。',
            8 => '%name准备打完回去和老婆结婚，掷出了「%rand_num」点。',
            9 => '%name想起自己还有个儿子，掷出了「%rand_num」点。',
        ),
        110 => array(
            0 => '%name被煮饭的女人偷袭，奄奄一息地掷出了「%rand_num」点。',
            1 => '%name听说甚尔君结婚了，悲愤地掷出了「%rand_num」点。',
            2 => '%name听到家主之位没了，心不在焉掷出了「%rand_num」点。',
            3 => '%name又被下人说了坏话，生气地掷出了「%rand_num」点。',
            4 => '%name见到了甚尔君，但他根本不认识自己，心不在焉地掷出了「%rand_num」点。',
            5 => '%name今天又见到了甚尔君一家三口，默默悲伤地掷出了「%rand_num」点。',
            6 => '%name做白日梦梦见和甚尔君打了招呼，掷出了「%rand_num」点。',
            7 => '%name今天又偷看了甚尔君的色图，掷出了「%rand_num」点。',
            8 => '%name知道自己将来是家主，得意地掷出了「%rand_num」点。',
            9 => '%name运气大爆棚人气排名就在甚尔君后面，掷出了「%rand_num」点。',
        ),
        111 => array(
            0 => '%name和自己玩瞪眼游戏，被吓得掷出了「%rand_num」点。',
            1 => '%name被腌入味的大哥99殴打，掷出了「%rand_num」点。',
            2 => '%name头上被套了塑料袋，看不清的情况下掷出了「%rand_num」点。',
            3 => '%name元元在玉米地巡逻，朝着里面lj的情侣掷出了「%rand_num」点。',
            4 => '%name被单身狗的现实打击，掷出了「%rand_num」点。',
            5 => '%name出门散心，掷出了「%rand_num」点。',
            6 => '%name听说隔壁开了个老八秘制小汉堡，犹豫中掷出了「%rand_num」点。',
            7 => '%name前方出现华莱士！掷出了「%rand_num」点。',
            8 => '%name华莱士还有十分钟关门，加快脚步，掷出了「%rand_num」点。',
            9 => '%name吃到了正宗华莱士，感到肚内一阵汹涌，喷出了「%rand_num」点。',
        ),
        199 => array(
            0 => '%name屁股痔疮突发，掷出了「%rand_num」点。',
            1 => '%name被要求连画三期彩图，掷出了「%rand_num」点。',
            2 => '%name被编辑一直催稿，掷出了「%rand_num」点。',
            3 => '%name又收到交税的消息，掷出了「%rand_num」点。',
            4 => '%name今天又被咒割咒解骂了，掷出了「%rand_num」点。',
            5 => '%name eva剧场版上映了，花出「%rand_num」元。',
            6 => '%name又可以画新鲜的老叔了，掷出了「%rand_num」点。',
            7 => '%name见到喜欢的前辈，追星成功，掷出了「%rand_num」点。',
            8 => '%name这周jump合刊，公费休刊，快乐地掷出了「%rand_num」点。',
            9 => '%name连着两个月不用更新，快乐地扭着屁股掷出了「%rand_num」点。',
        ),
    );

    public static function CharaHeadIndex()
    {
        //这个方法没在用
        $index = [];
        foreach (self::chara_head as $key => $value) {
            array_push($index, ['value' => $key, 'text' => $value['name']]);
        }
        return $index;
    }
    public static function CharaHead(int $chara_id, string $action)
    {
        $head_index = self::chara_index[$chara_id]['head'];
        return self::chara_head[$head_index][$action];
    }

    public static function CharaName(int $chara_id)
    {
        return self::chara_index[$chara_id]['name'];
    }

    public static function CharaAttackMessage($chara_id, $chara_id_opponent, $rand_num)
    {
        $message_index = self::chara_index[$chara_id]['message'];
        $chara_name = self::chara_head[$chara_id]['name'];
        $chara_name_opponent = self::chara_head[$chara_id_opponent]['name'];
        $message = self::chara_message[$message_index][intval(($rand_num - 1) / 10)];
        $message = str_replace('%name', $chara_name, $message);
        $message = str_replace('%opponent', $chara_name_opponent, $message);
        $message = str_replace('%rand_num', strval($rand_num), $message);
        return $message;
    }

    public static function CharaRandNum($chara_id)
    {
        switch ($chara_id) {
            case 10: //元元可以掷出最大101点
                return random_int(1, 101); 
            case 3:
                return random_int(80, 100);             
            default:
                return random_int(1, 100);
        }
    }
}

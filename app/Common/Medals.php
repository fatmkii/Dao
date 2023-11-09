<?php

namespace App\Common;

class Medals
{
    //徽章的具体名称、描述、拿到徽章的数据阈值
    const DATA = array(
        //拥有的olo数量
        1 => array(
            'name' => '好的开始是成功的一半',
            'describe' => '拥有10w个olo',
            'threshold' => 100000,
            'varname' => 'olo',
        ),
        2 => array(
            'name' => '千仓万箱',
            'describe' => '拥有50w个olo',
            'threshold' => 500000,
            'varname' => 'olo',
        ),
        3 => array(
            'name' => '财富自由',
            'describe' => '拥有100w个olo',
            'threshold' => 1000000,
            'varname' => 'olo',
        ),
        4 => array(
            'name' => '锅中首富',
            'describe' => '拥有500w个olo',
            'threshold' => 5000000,
            'varname' => 'olo',
        ),

        //通过大乱斗获得的olo数量
        11 => array(
            'name' => '一鸣惊人',
            'describe' => '通过大乱斗获得5w个olo',
            'threshold' => 50000,
            'varname' => 'battle_olo_in',
        ),
        12 => array(
            'name' => '常胜将军',
            'describe' => '通过大乱斗获得10w个olo',
            'threshold' => 100000,
            'varname' => 'battle_olo_in',
        ),
        13 => array(
            'name' => '赌仙在世',
            'describe' => '通过大乱斗获得50w个olo',
            'threshold' => 500000,
            'varname' => 'battle_olo_in',
        ),
        14 => array(
            'name' => '今夜做梦也会笑',
            'describe' => '通过大乱斗获得100w个olo',
            'threshold' => 1000000,
            'varname' => 'battle_olo_in',
        ),
        15 => array(
            'name' => '赌场收割者',
            'describe' => '通过大乱斗获得1000w个olo',
            'threshold' => 10000000,
            'varname' => 'battle_olo_in',
        ),

        //通过大乱斗失去的olo数量
        21 => array(
            'name' => '出师不利',
            'describe' => '由于大乱斗失去5w个olo',
            'threshold' => 50000,
            'varname' => 'battle_olo_out',
        ),
        22 => array(
            'name' => '一败涂地',
            'describe' => '由于大乱斗失去10w个olo',
            'threshold' => 100000,
            'varname' => 'battle_olo_out',
        ),
        23 => array(
            'name' => '家徒四壁',
            'describe' => '由于大乱斗失去50w个olo',
            'threshold' => 500000,
            'varname' => 'battle_olo_out',
        ),
        24 => array(
            'name' => '倾家荡产',
            'describe' => '由于大乱斗失去100w个olo',
            'threshold' => 1000000,
            'varname' => 'battle_olo_out',
        ),
        25 => array(
            'name' => '空中飞人',
            'describe' => '由于大乱斗失去1000w个olo',
            'threshold' => 10000000,
            'varname' => 'battle_olo_out',
        ),

        //通过红包获得的olo数量 
        31 => array(
            'name' => '天降横财',
            'describe' => '通过红包获得5w个olo',
            'threshold' => 50000,
            'varname' => 'hongbao_olo_in',
        ),
        32 => array(
            'name' => '时来运转',
            'describe' => '通过红包获得10w个olo',
            'threshold' => 100000,
            'varname' => 'hongbao_olo_in',
        ),
        33 => array(
            'name' => '鸿运当头',
            'describe' => '通过红包获得50w个olo',
            'threshold' => 500000,
            'varname' => 'hongbao_olo_in',
        ),
        34 => array(
            'name' => '天选之子',
            'describe' => '通过红包获得100w个olo',
            'threshold' => 1000000,
            'varname' => 'hongbao_olo_in',
        ),
        35 => array(
            'name' => '金玉满堂',
            'describe' => '通过红包获得500w个olo',
            'threshold' => 5000000,
            'varname' => 'hongbao_olo_in',
        ),

        //通过红包失去的olo数量
        41 => array(
            'name' => '元元之民',
            'describe' => '派发红包合计5w个olo',
            'threshold' => 50000,
            'varname' => 'hongbao_olo_out',
        ),
        42 => array(
            'name' => '乐善好施',
            'describe' => '派发红包合计10w个olo',
            'threshold' => 100000,
            'varname' => 'hongbao_olo_out',
        ),
        43 => array(
            'name' => '行善积德',
            'describe' => '派发红包合计50w个olo',
            'threshold' => 500000,
            'varname' => 'hongbao_olo_out',
        ),
        44 => array(
            'name' => '有钱就是任性',
            'describe' => '派发红包合计100w个olo',
            'threshold' => 1000000,
            'varname' => 'hongbao_olo_out',
        ),
        45 => array(
            'name' => '普度众生',
            'describe' => '派发红包合计500w个olo',
            'threshold' => 5000000,
            'varname' => 'hongbao_olo_out',
        ),

        //发起大乱斗没人接的次数 
        51 => array(
            'name' => '无人在意',
            'describe' => '发起大乱斗没人接',
            'threshold' => 1,
            'varname' => 'battle_ignored',
        ),
        52 => array(
            'name' => '透明人',
            'describe' => '发起大乱斗100次没人接',
            'threshold' => 100,
            'varname' => 'battle_ignored',
        ),


        //删楼次数
        61 => array(
            'name' => '手滑了一下',
            'describe' => '删了一个楼',
            'threshold' => 1,
            'varname' => 'delete_posts_num',
        ),
        62 => array(
            'name' => '你是五嬷吗',
            'describe' => '进行了100次删楼',
            'threshold' => 100,
            'varname' => 'delete_posts_num',
        ),

        //使olo数量达到0
        71 => array(
            'name' => '归零！',
            'describe' => 'olo归零',
            'threshold' => 0,
            'varname' => null,
        ),

        //回帖数量
        81 => array(
            'name' => '轻轻松松',
            'describe' => '发表了1000个回帖',
            'threshold' => 1000,
            'varname' => 'posts_num',
        ),
        82 => array(
            'name' => '小菜一碟',
            'describe' => '发表了5000个回帖',
            'threshold' => 5000,
            'varname' => 'posts_num',
        ),
        83 => array(
            'name' => '火锅劳模',
            'describe' => '发表了1w个回帖',
            'threshold' => 10000,
            'varname' => 'posts_num',
        ),
        84 => array(
            'name' => '决战紫禁之巅',
            'describe' => '发表了5w个回帖',
            'threshold' => 50000,
            'varname' => 'posts_num',
        ),
        85 => array(
            'name' => '火锅有你了不起',
            'describe' => '发表了10w个回帖',
            'threshold' => 100000,
            'varname' => 'posts_num',
        ),

        //抢到某楼的10000层
        91 => array(
            'name' => '万中有一',
            'describe' => '抢到第10000楼',
            'threshold' => 0,
            'varname' => null,
        ),

        //大乱斗掷出101点
        101 => array(
            'name' => '元元的爱',
            'describe' => '大乱斗掷出101点',
            'threshold' => 0,
            'varname' => null,
        ),

        //大乱斗平局次数
        111 => array(
            'name' => '双赢才是最好的',
            'describe' => '大乱斗平局',
            'threshold' => 1,
            'varname' => 'battle_draw',
        ),
        //大乱斗平局次数
        112 => array(
            'name' => '火锅之神也被你薅秃了',
            'describe' => '大乱斗平局100次',
            'threshold' => 100,
            'varname' => 'battle_draw',
        ),

        //红包抢到0个olo
        121 => array(
            'name' => '小火锅的负责人是个骗子',
            'describe' => '红包抢到0个olo',
            'threshold' => 0,
            'varname' => null,
        ),


        //申请一个定制饼干
        131 => array(
            'name' => '个人财产+1',
            'describe' => '申请1个定制饼干',
            'threshold' => 0,
            'varname' => null,
        ),

        //饼干等级
        141 => array(
            'name' => '我要变强',
            'describe' => '饼干升到5级',
            'threshold' => 5,
            'varname' => 'user_lv',
        ),
        142 => array(
            'name' => '潮到出水',
            'describe' => '饼干升到10级',
            'threshold' => 10,
            'varname' => 'user_lv',
        ),
        143 => array(
            'name' => '表情包大户',
            'describe' => '饼干升到20级',
            'threshold' => 20,
            'varname' => 'user_lv',
        ),
        144 => array(
            'name' => '神仙饼干',
            'describe' => '饼干升到40级',
            'threshold' => 40,
            'varname' => 'user_lv',
        ),

        //小火锅周年活动
        151 => array(
            'name' => '小火锅二周年',
            'describe' => '我们要一直在一起 — 至少十年吧！',
            'threshold' => null,
            'varname' => null,
        ),
        //23年国庆
        152 => array(
            'name' => '国庆节快乐',
            'describe' => '伟大的祖国节日快乐',
            'threshold' => null,
            'varname' => null,
        ),


        //通过打赏获得的olo数量 
        161 => array(
            'name' => '得来全不费功夫',
            'describe' => '通过打赏获得10w个olo',
            'threshold' => 100000,
            'varname' => 'reward_olo_in',
        ),
        162 => array(
            'name' => '得道多助',
            'describe' => '通过打赏获得50w个olo',
            'threshold' => 500000,
            'varname' => 'reward_olo_in',
        ),
        163 => array(
            'name' => '全凭本事',
            'describe' => '通过打赏获得100w个olo',
            'threshold' => 1000000,
            'varname' => 'reward_olo_in',
        ),
        164 => array(
            'name' => '嘀——全款已到账',
            'describe' => '通过打赏获得200w个olo',
            'threshold' => 2000000,
            'varname' => 'reward_olo_in',
        ),

        //通过打赏失去的olo数量 
        171 => array(
            'name' => '种瓜得瓜',
            'describe' => '打赏出去10w个olo',
            'threshold' => 100000,
            'varname' => 'reward_olo_out',
        ),
        172 => array(
            'name' => '手有余香',
            'describe' => '打赏出去50w个olo',
            'threshold' => 500000,
            'varname' => 'reward_olo_out',
        ),
        173 => array(
            'name' => '春蚕蜡炬',
            'describe' => '打赏出去100w个olo',
            'threshold' => 1000000,
            'varname' => 'reward_olo_out',
        ),
        174 => array(
            'name' => '羽化飞升',
            'describe' => '打赏出去200w个olo',
            'threshold' => 2000000,
            'varname' => 'reward_olo_out',
        ),


        //管理员荣誉徽章
        181 => array(
            'name' => '皇家御用画宗',
            'describe' => '大触快教教我画画',
            'threshold' => null,
            'varname' => null,
        ),

        //管理员荣誉徽章
        182 => array(
            'name' => '小火锅守护者',
            'describe' => '我来组成小火锅锅底！',
            'threshold' => null,
            'varname' => null,
        ),

        //小火锅终末旅行
        183 => array(
            'name' => '小火锅终末旅行',
            'describe' => '既有开始，必有终结',
            'threshold' => null,
            'varname' => null,
        ),


        //表情包萌
        201 => array(
            'name' => 'ac娘',
            'describe' => '\\\\ ac娘，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        202 => array(
            'name' => '鹦鹉鸡',
            'describe' => '\\\\ 鹦鹉鸡，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        203 => array(
            'name' => '咪子鱼',
            'describe' => '\\\\ 咪子鱼，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        204 => array(
            'name' => '小黑猫',
            'describe' => '\\\\ 小黑猫，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        205 => array(
            'name' => '麻将脸',
            'describe' => '\\\\ 麻将脸，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        206 => array(
            'name' => '小恐龙',
            'describe' => '\\\\ 小恐龙，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        207 => array(
            'name' => 'TD猫',
            'describe' => '\\\\ TD猫，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        208 => array(
            'name' => '小豆泥',
            'describe' => '\\\\ 小豆泥，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        209 => array(
            'name' => '小企鹅',
            'describe' => '\\\\ 小企鹅，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        210 => array(
            'name' => '小黄脸',
            'describe' => '\\\\ 小黄脸，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        211 => array(
            'name' => 'FUFU',
            'describe' => '\\\\ FUFU，出道！//',
            'threshold' => 500, //这里指500票，50000个olo
            'varname' => 'emoji_contest',
        ),
        212 => array(
            'name' => '小火锅第一届表情包萌',
            'describe' => '全军出击！',
            'threshold' => 100, //这里指100票，50000个olo
            'varname' => 'emoji_contest_total',
        ),
        213 => array(
            'name' => '燃起来了！',
            'describe' => '擦出爱的火花',
            'threshold' => 1000, //这里指1000票，100000个olo
            'varname' => 'emoji_contest_total',
        ),
        214 => array(
            'name' => '为冠军的小豆泥献上喝彩',
            'describe' => '尊贵的小豆泥陛下火遍全球天下第一帝锅',
            'threshold' => 0, //本命是小豆泥的全部饼干
            'varname' => null,
        ),

        //连续赢得大乱斗
        221 => array(
            'name' => '喜鹊落枝头',
            'describe' => '连续赢得2次大乱斗',
            'threshold' => 3,
            'varname' => 'battle_win_con',
        ),
        222 => array(
            'name' => '春风得意马蹄疾',
            'describe' => '连续赢得5次大乱斗',
            'threshold' => 5,
            'varname' => 'battle_win_con',
        ),
        223 => array(
            'name' => '福无双至今日至',
            'describe' => '连续赢得8次大乱斗',
            'threshold' => 8,
            'varname' => 'battle_win_con',
        ),
        224 => array(
            'name' => '紫气东来保你发财',
            'describe' => '连续赢得10次大乱斗',
            'threshold' => 10,
            'varname' => 'battle_win_con',
        ),

        //连续输掉大乱斗
        231 => array(
            'name' => '脚踩瓜皮',
            'describe' => '连续输掉2次大乱斗',
            'threshold' => 3,
            'varname' => 'battle_lose_con',
        ),
        232 => array(
            'name' => '凉水塞牙',
            'describe' => '连续输掉5次大乱斗',
            'threshold' => 5,
            'varname' => 'battle_lose_con',
        ),
        233 => array(
            'name' => '点背不能怨社会',
            'describe' => '连续输掉8次大乱斗',
            'threshold' => 8,
            'varname' => 'battle_lose_con',
        ),
        234 => array(
            'name' => '人生不如意十有八九',
            'describe' => '连续输掉10次大乱斗',
            'threshold' => 10,
            'varname' => 'battle_lose_con',
        ),

        //银行存款合计
        241 => array(
            'name' => '日进斗金',
            'describe' => '粮仓存粮5w',
            'threshold' => 50000,
            'varname' => null,
        ),
        242 => array(
            'name' => '堆金积玉',
            'describe' => '粮仓存粮10w',
            'threshold' => 100000,
            'varname' => null,
        ),
        243 => array(
            'name' => '富甲一方',
            'describe' => '粮仓存粮30w',
            'threshold' => 300000,
            'varname' => null,
        ),
        244 => array(
            'name' => '腰缠万贯',
            'describe' => '粮仓存粮50w',
            'threshold' => 500000,
            'varname' => null,
        ),
        245 => array(
            'name' => '富有四海',
            'describe' => '粮仓存粮100w',
            'threshold' => 1000000,
            'varname' => null,
        ),


        //银行提前支取累计
        251 => array(
            'name' => '今朝有米今朝吃',
            'describe' => '明日无米啃枯枝（提前开仓累计30w）',
            'threshold' => 300000,
            'varname' => 'withdraw_penalty',
        ),

        //表情包出道萌
        261 => array(
            'name' => '小火锅第一届出道萌',
            'describe' => '出道萌投票100票',
            'threshold' => 100, //这里指100票，10000个olo
            'varname' => 'emoji_contest_total',
        ),
        262 => array(
            'name' => '成为大家的爱豆',
            'describe' => '出道萌投票1000票',
            'threshold' => 1000, //这里指1000票，100000个olo
            'varname' => 'emoji_contest_total',
        ),


    );
}

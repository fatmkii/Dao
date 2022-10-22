<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\myModel;
use App\Models\Thread;
use App\Common\ResponseCode;
use App\Events\NewPostBroadcast;
use App\Models\Battle;

class Post extends myModel
{
    use HasFactory;

    protected $binggan = '';
    protected $user_id = 0;

    protected $fillable = [
        'forum_id',
        'thread_id',
        'floor',
        'random_head',
        'created_by_admin',
        'content',
        'created_IP',
        'created_binggan',
    ];

    protected $hidden = [
        'created_IP',
        'created_binggan',
        'updated_at',
        'forum_id',
        // 'thread_id',
    ];

    protected $appends = [
        'is_your_post',
        'battle_data',
        'hongbao_data'
    ];

    protected $casts = [];

    protected $guarded = [];

    // /**
    //  * 模型属性的默认值
    //  *
    //  * @var array
    //  */
    // protected $attributes = [
    //     'random_head' => random_int(0, 39)
    // ];

    protected static function booted()
    {
        // static::saving(function ($post) {
        //     $thread = $post->thread;
        //     if ($post->floor % 200 == 0) { //如果是刚好是翻页楼或者是第0楼，则清除全部页面的缓存（以刷新页码）
        //         for ($i = 1; $i <= ceil(($post->floor + 1) / 200); $i++) {
        //             Cache::forget('threads_cache_' . $thread->id . '_' . $i);
        //         }
        //     } else {
        //         Cache::forget('threads_cache_' . $thread->id . '_' . ceil(($post->floor + 1) / 200));
        //     }
        // });
    }

    public static function create(array $array)
    {
        //post需求的属性和其默认值
        $attrs_default = [
            'random_head' =>  random_int(0, 39),
        ];

        $post = new Post;
        $post->setSuffix(intval($array['thread_id'] / 10000));

        //统一赋值
        foreach ($array as $attr => $value) {
            $post->$attr = $value;
        }

        //array参数未给出的就用默认值
        foreach ($attrs_default as $attr => $default) {
            if (!isset($array[$attr])) {
                $post->$attr = $default;
            }
        }


        if (isset($array['floor'])) {
            //如果有给出floor，说明是新主题的0楼，直接写入
            $post->floor = $array['floor'];
        } else {
            //更新thread的posts_num
            $posts_num = POST::Suffix(intval($array['thread_id'] / 10000))->where('thread_id', $array['thread_id'])->count();
            Thread::where('id', $array['thread_id'])->update(['posts_num' => $posts_num]);
            $post->floor = $posts_num;
        }
        $post->save();

        return $post;
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getCreatedBingganHashAttribute()
    {
        return hash('sha256', $this->created_binggan . $this->thread_id);
    }

    public function getNicknameAttribute($nickname)
    {
        switch ($this->is_deleted) {
            case 0:
                return $nickname;
            case 1:
                if ($this->created_by_admin != 2) {
                    return '****';
                } else {
                    return $nickname;
                }
            case 2:
                if ($this->created_by_admin != 2) {
                    return '****';
                } else {
                    return $nickname;
                }
            default:
                return $nickname;
        }
    }

    public function getCreatedByAdminAttribute($created_by_admin)
    {
        switch ($this->is_deleted) {
            case 0:
                return $created_by_admin;
            case 1:
                if ($created_by_admin != 2) {
                    return 0;
                } else {
                    return $created_by_admin;
                }
            case 2:
                if ($created_by_admin != 2) {
                    return 0;
                } else {
                    return $created_by_admin;
                }
            default:
                return $created_by_admin;
        }
    }

    public function getContentAttribute($content)
    {
        switch ($this->is_deleted) {
            case 0:
                return $content;
            case 1:
                return '*此贴已被作者删除*';
            case 2:
                return '*此贴已被管理员删除*';
            default:
                return $content;
        }
    }

    public function setBinggan($binggan)
    {
        $this->binggan = $binggan;
    }

    public function setUserID($user_id)
    {
        $this->user_id = $user_id;
    }

    public static function Binggan($binggan)
    {
        $instance = new static;
        $instance->setBinggan($binggan);

        return $instance->newQuery();
    }


    public function getIsYourPostAttribute()
    {
        if ($this->binggan) {
            if ($this->binggan == $this->created_binggan) {
                return true;
            } else {
                return false;
            }
        } else {
            return null;
        }
    }

    public function getBattleDataAttribute()
    {
        if ($this->battle_id) {
            $battle = Battle::find($this->battle_id);
            if (!$battle) {
                return response()->json([
                    'code' => ResponseCode::BATTLE_NOT_FOUND,
                    'message' => ResponseCode::$codeMap[ResponseCode::BATTLE_NOT_FOUND],
                ]);
            }
            $battle_messages  = $battle->BattleMessages()->get();

            //如果有提供binggan，为battle输入binggan，用来判断is_your_battle（为前端提供是否是用户自己帖子的判据）
            if ($this->binggan) {
                $battle->setBinggan($this->binggan);
            }
            return [
                'battle' => $battle,
                'battle_messages' => $battle_messages,
            ];
        } else {
            return null;
        }
    }

    public function getHongbaoDataAttribute()
    {
        if ($this->hongbao_id) {
            $hongbao = HongbaoPost::withTrashed()->find($this->hongbao_id);
            if ($hongbao) {
                //如果有提供user_id，为HongbaoPost输入user_id，用来判断hongbao结果
                if ($this->user_id) {
                    $hongbao->setUserID($this->user_id);
                }
                if ($hongbao->olo_hide) {
                    //隐藏olo总额
                    $hongbao->makeHidden('olo_total');
                }
                if (in_array($hongbao->key_word_type, [2, 3])) {
                    //如果是抢答红包或者暗号红包，则隐藏红包口令
                    $hongbao->makeHidden('key_word');
                }
                return $hongbao;
            } else {
                return null;
            }
        } else {
            return null;
            // $hongbao = HongbaoPost::withTrashed()->where('thread_id', $this->thread_id)->where('post_id', $this->id)->first();
            // if ($hongbao) {
            //     //如果有提供user_id，为HongbaoPost输入user_id，用来判断hongbao结果
            //     if ($this->user_id) {
            //         $hongbao->setUserID($this->user_id);
            //     }
            //     return $hongbao;
            // } else {
            //     return null;
            // }
        }
    }

    public function broadcast()
    {
        broadcast(new NewPostBroadcast($this->thread_id, $this->id, $this->floor))->toOthers();
        return $this;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\myModel;
use App\Models\Thread;
use Illuminate\Support\Facades\Cache;
use App\Common\ResponseCode;
use App\Models\Battle;

class Post extends myModel
{
    use HasFactory;

    protected $binggan = '';

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
        'is_anonymous',
        'updated_at',
        'forum_id',
    ];

    protected $appends = [
        'is_your_post',
        'battle_data',
    ];

    protected $casts = [];

    protected $guarded = [];

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
}

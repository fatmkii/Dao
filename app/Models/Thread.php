<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\myModel;
use App\Models\Post;
use App\Models\Forum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Thread extends myModel
{
    use HasFactory;

    public $hidden = [
        'created_binggan',
        'created_IP',
        'is_anonymous',
        'is_deleted',
        'forum',
    ];
    protected $fillable = [
        'forum_id', 'title', 'content', 'created_binggan', 'created_IP',
        'anti_jingfen', 'sub_id', 'nissin_date', 'author_name'
    ];
    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    protected $casts = [];


    public function Forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function Posts()
    {
        // Posts数据库表分表。根据Tread->id万位以上数字作为Post表后缀
        // 例如thread->id是2xxxx的post在表post_2表里。
        // suffix方法写在myModel类里
        // $posts = Post::suffix(intval($this->id / 10000));
        $posts = new Post;
        $posts->setSuffix(intval($this->id / 10000));
        return new HasMany($posts->newQuery(), $this, 'thread_id', 'id');
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i');
    }

}

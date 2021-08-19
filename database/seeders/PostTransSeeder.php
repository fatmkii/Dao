<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Database\QueryException;

class PostTransSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $threads_old = DB::table('threads_old')->where('fid', 40)->get();
        foreach ($threads_old as $thread_old) {
            $thread_month = Carbon::parse($thread_old->created_at)->month;
            $posts_old = DB::table('replys_2021_0' . $thread_month)->where('tid', $thread_old->id)->get();
            try {
                DB::beginTransaction();
                //发主题帖（Thread）
                $thread = new Thread;
                $thread->id = $thread_old->id - 1260000;
                $thread->created_binggan = $thread_old->user_id;
                $thread->forum_id = 419;
                $thread->nickname = $thread_old->username;
                $thread->created_ip = $thread_old->user_ip;
                $thread->posts_num = $thread_old->reply_count;
                if ($thread_old->subname == null) {
                    $thread->sub_title = "[闲聊]";
                } else {
                    $thread->sub_title = "[" . $thread_old->subname . "]";
                }
                $thread->random_heads_group = 2;
                $thread->nissin_date = Carbon::now()->addDays(7);
                $thread->title = $thread_old->title;
                $thread->anti_jingfen = 0;
                $thread->created_at = $thread_old->created_at;
                $thread->updated_at = $thread_old->updated_at;
                $thread->save();
                $post = new Post;
                $post->setSuffix(0);
                $post->created_binggan = $thread_old->user_id;
                $post->forum_id = 419;
                $post->thread_id = $thread->id;
                $post->content = $thread_old->content;
                $post->nickname = $thread_old->username;
                $post->created_by_admin = 0;
                $post->created_ip = $thread_old->user_ip;
                $post->random_head = $thread_old->bbsbigimage;
                $post->floor = 0;
                $post->created_at = $thread_old->created_at;
                $post->updated_at = $thread_old->updated_at;
                $post->save();
                DB::commit();
            } catch (QueryException $e) {
                DB::rollback();
                echo '失败:主题=' . $thread->id . "\n";
            }
            foreach ($posts_old as $post_old) {
                //执行追加新主题流程
                try {
                    DB::beginTransaction();
                    $post = new Post;
                    $post->setSuffix(0);
                    $post->created_binggan = $thread_old->user_id;
                    $post->forum_id = 419;
                    $post->thread_id = $thread->id;
                    $post->content = $post_old->content;
                    $post->nickname = $post_old->username;
                    $post->created_by_admin = $thread_old->id_admin;
                    $post->created_ip = $post_old->user_ip;
                    $post->random_head = $post_old->bbsbigimage;
                    $post->floor = $post_old->floor;
                    $post->is_deleted = $post_old->deleted_at == null ? 0 : 1;
                    $post->created_at = $post_old->created_at;
                    $post->updated_at = $post_old->updated_at;
                    $post->save();
                    DB::commit();
                } catch (QueryException $e) {
                    DB::rollback();
                    echo '失败:主题=' . $thread->id . "帖子=" . $post->id . "\n";
                }
            }
            echo "完成：板块=40 主题=" . $thread->id . "\n";
        }
        $threads_old = DB::table('threads_old')->where("id", ">", 1268284)->where('fid', 1)->get();
        foreach ($threads_old as $thread_old) {
            $thread_month = Carbon::parse($thread_old->created_at)->month;
            $posts_old = DB::table('replys_2021_0' . $thread_month)->where('tid', $thread_old->id)->get();
            try {
                DB::beginTransaction();
                //发主题帖（Thread）
                $thread = new Thread;
                $thread->id = $thread_old->id - 1260000;
                $thread->created_binggan = $thread_old->user_id;
                $thread->forum_id = 419;
                $thread->nickname = $thread_old->username;
                $thread->created_ip = $thread_old->user_ip;
                $thread->posts_num = $thread_old->reply_count;
                if ($thread_old->subname == null) {
                    $thread->sub_title = "[闲聊]";
                } else {
                    $thread->sub_title = "[" . $thread_old->subname . "]";
                }
                $thread->random_heads_group = 2;
                $thread->nissin_date = Carbon::now()->addYears(3);
                $thread->title = $thread_old->title;
                $thread->anti_jingfen = 0;
                $thread->created_at = $thread_old->created_at;
                $thread->updated_at = $thread_old->updated_at;
                $thread->save();
                $post = new Post;
                $post->setSuffix(0);
                $post->created_binggan = $thread_old->user_id;
                $post->forum_id = 419;
                $post->thread_id = $thread->id;
                $post->content = $thread_old->content;
                $post->nickname = $thread_old->username;
                $post->created_by_admin = 0;
                $post->created_ip = $thread_old->user_ip;
                $post->random_head = $thread_old->bbsbigimage;
                $post->floor = 0;
                $post->created_at = $thread_old->created_at;
                $post->updated_at = $thread_old->updated_at;
                $post->save();
                DB::commit();
            } catch (QueryException $e) {
                DB::rollback();
                echo '失败:主题=' . $thread->id . "\n";
            }
            foreach ($posts_old as $post_old) {
                //执行追加新主题流程
                try {
                    DB::beginTransaction();
                    $post = new Post;
                    $post->setSuffix(0);
                    $post->created_binggan = $thread_old->user_id;
                    $post->forum_id = 419;
                    $post->thread_id = $thread->id;
                    $post->content = $post_old->content;
                    $post->nickname = $post_old->username;
                    $post->created_by_admin = $thread_old->id_admin;
                    $post->created_ip = $post_old->user_ip;
                    $post->random_head = $post_old->bbsbigimage;
                    $post->floor = $post_old->floor;
                    $post->is_deleted = $post_old->deleted_at == null ? 0 : 1;
                    $post->created_at = $post_old->created_at;
                    $post->updated_at = $post_old->updated_at;
                    $post->save();
                    DB::commit();
                } catch (QueryException $e) {
                    DB::rollback();
                    echo '失败:主题=' . $thread->id . "帖子=" . $post->id . "\n";
                }
            }
            echo "完成：板块=1 主题=" . $thread->id . "\n";
        }
        $threads_old = DB::table('threads_old')->where('fid', 2811)->get();
        foreach ($threads_old as $thread_old) {
            $thread_month = Carbon::parse($thread_old->created_at)->month;
            $posts_old = DB::table('replys_2021_0' . $thread_month)->where('tid', $thread_old->id)->get();
            try {
                DB::beginTransaction();
                //发主题帖（Thread）
                $thread = new Thread;
                $thread->id = $thread_old->id - 1260000;
                $thread->created_binggan = $thread_old->user_id;
                $thread->forum_id = 2811;
                $thread->nickname = $thread_old->username;
                $thread->created_ip = $thread_old->user_ip;
                $thread->posts_num = $thread_old->reply_count;
                if ($thread_old->subname == null) {
                    $thread->sub_title = "[闲聊]";
                } else {
                    $thread->sub_title = "[" . $thread_old->subname . "]";
                }
                $thread->random_heads_group = 2;
                $thread->nissin_date = Carbon::now()->addDays(7);
                $thread->title = $thread_old->title;
                $thread->anti_jingfen = 0;
                $thread->created_at = $thread_old->created_at;
                $thread->updated_at = $thread_old->updated_at;
                $thread->save();
                $post = new Post;
                $post->setSuffix(0);
                $post->created_binggan = $thread_old->user_id;
                $post->forum_id = 419;
                $post->thread_id = $thread->id;
                $post->content = $thread_old->content;
                $post->nickname = $thread_old->username;
                $post->created_by_admin = 0;
                $post->created_ip = $thread_old->user_ip;
                $post->random_head = $thread_old->bbsbigimage;
                $post->floor = 0;
                $post->created_at = $thread_old->created_at;
                $post->updated_at = $thread_old->updated_at;
                $post->save();
                DB::commit();
            } catch (QueryException $e) {
                DB::rollback();
                echo '失败:主题=' . $thread->id . "\n";
            }
            foreach ($posts_old as $post_old) {
                //执行追加新主题流程
                try {
                    DB::beginTransaction();
                    $post = new Post;
                    $post->setSuffix(0);
                    $post->created_binggan = $thread_old->user_id;
                    $post->forum_id = 419;
                    $post->thread_id = $thread->id;
                    $post->content = $post_old->content;
                    $post->nickname = $post_old->username;
                    $post->created_by_admin = $thread_old->id_admin;
                    $post->created_ip = $post_old->user_ip;
                    $post->random_head = $post_old->bbsbigimage;
                    $post->floor = $post_old->floor;
                    $post->is_deleted = $post_old->deleted_at == null ? 0 : 1;
                    $post->created_at = $post_old->created_at;
                    $post->updated_at = $post_old->updated_at;
                    $post->save();
                    DB::commit();
                } catch (QueryException $e) {
                    DB::rollback();
                    echo '失败:主题=' . $thread->id . "帖子=" . $post->id . "\n";
                }
            }
            echo "完成：板块=2811 主题=" . $thread->id . "\n";
        }
    }
}

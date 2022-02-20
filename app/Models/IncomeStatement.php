<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Thread;
use Carbon\Carbon;

class IncomeStatement extends myModel
{
    use HasFactory;

    protected $table = 'income_statement';

    protected $fillable = [
        'id',
        'created_at',
        'olo',
        'user_id',
        'binggan',
        'user_id_target',
        'binggan_target',
        'content',
        'thread_id',
        'thread_title',
        'post_id',
        'floor',
    ];

    protected $hidden = [
        'id',
        'user_id',
        'binggan',
        'user_id_target',
        'binggan_target',
        'post_id',
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Thread()
    {
        return $this->belongsTo(Thread::class);
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    //查询收支表
    public static function incomeData($user_id, $date, $page)
    {
        $limit = 50; //每页50;
        $offset = $page * $limit - $limit;
        $date = Carbon::parse($date);
        $income_table = 'income_statement_' . $date->year;

        //用子查询join
        $sql_child = DB::table($income_table)
            ->select('id')
            ->where('user_id', $user_id)
            ->whereDate('created_at', $date->toDateString())
            ->offset($offset)->limit($limit);
        $income = IncomeStatement::suffix($date->year)
            ->joinSub($sql_child, 'sql_child', function ($join) use ($income_table) {
                $join->on($income_table . '.id', '=', 'sql_child.id');
            })
            ->get();


        //查询最大页数
        $income_num = DB::table($income_table)
            ->select('id')
            ->where('user_id', $user_id)
            ->whereDate('created_at', $date->toDateString())
            ->count();
        $lastPage = ceil($income_num / $limit);

        return array($income, $lastPage);
    }
}

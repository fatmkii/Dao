<?php

namespace App\Jobs;

use App\Models\IncomeStatement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessIncomeStatement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $action = 'normal'; //一般记录'normal'，回帖记录'post'(因为要聚合)
    protected $IncomeStatement;
    // [
    //     'created_at' => ,
    //     'olo'=> ,
    //     'user_id'=>,
    //     'binggan'=>,
    //     'user_id_target'=>,
    //     'binggan_target'=>,
    //     'content'=>,
    //     'thread_id'=>,
    //     'thread_title'=>,
    //     'post_id'=>,
    //     'floor'=>,
    // ]

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $action, array $IncomeStatement)
    {
        $this->IncomeStatement = $IncomeStatement;
        $this->action = $action;
        if (array_key_exists('content', $this->IncomeStatement) && strlen($this->IncomeStatement['content']) > 255) {
            //数据库最长记录255
            $this->IncomeStatement['content'] = substr($this->IncomeStatement['content'], 0, 255);
        }
        if (array_key_exists('thread_title', $this->IncomeStatement) && strlen($this->IncomeStatement['thread_title']) > 255) {
            //数据库最长记录255
            $this->IncomeStatement['thread_title'] = substr($this->IncomeStatement['thread_title'], 0, 255);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $carbon_date = Carbon::parse($this->IncomeStatement['created_at']);
        switch ($this->action) {
            case 'normal':
                $model = new IncomeStatement($this->IncomeStatement);
                $model->setsuffix($carbon_date->year);
                $model->save();
                break;
            case 'post':
                //用当天的0:0:0作为回帖的聚合记录时间
                $post_date = $carbon_date;
                $post_date->hour = 0;
                $post_date->minute = 0;
                $post_date->second = 0;

                $model = IncomeStatement::suffix($carbon_date->year)
                    ->where('user_id', $this->IncomeStatement['user_id'])
                    ->where('created_at', $post_date)
                    ->where('content', '回帖')
                    ->first();
                if ($model) {
                    $model->olo += 10;
                } else {
                    $statement_temp = $this->IncomeStatement;
                    $statement_temp['created_at'] = $post_date;
                    $model = new IncomeStatement($statement_temp);
                    $model->setsuffix($carbon_date->year);
                }
                $model->save();
                break;
        }
    }
}

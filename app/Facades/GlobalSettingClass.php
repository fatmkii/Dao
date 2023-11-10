<?php

namespace App\Facades;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class GlobalSettingClass
{
    const TABLENAME = 'global_setting';


    public function get(string $key)
    {
        $value = json_decode(DB::table(self::TABLENAME)->where('key', $key)->value('value'));
        return $value;
    }

    public function get_all()
    {
        $data = DB::table(self::TABLENAME)->get();
        $keyed = $data->mapWithKeys(function ($item) {
            return [$item->key =>  json_decode($item->value)];
        });
        return $keyed;
    }

    public function set(string $key, $value)
    {
        if (gettype($value) == 'array') {
            $value = json_encode($value);
        }

        DB::table(self::TABLENAME)
            ->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
    }

    public function get_tax(string $name, Carbon $time = null)
    {
        //各种税率整合

        $use_festival_check = true;//快速判断是否需要判断节日优惠税率的开关
        $is_festival = false; 
        $start = '2023/11/11 00:00:00';
        $end = '2023/11/12 00:00:00';

        if ($use_festival_check) {
            if ($time == null) {
                $time = Carbon::now();
            }
            if ($time->between($start, $end)) {
                $is_festival = true;
            }
        }

        switch ($name) {
            case 'normal': {
                    //一般税率1.07（打赏、红包等），活动时候1.02
                    $tax_rate = $is_festival ? 1.02 : 1.07;
                    break;
                }
            case 'battle': {
                    //税率4%，也就是奖金是1.96倍。活动时候1.98
                    $tax_rate = $is_festival ? 1.98 : 1.96;
                    break;
                }
        }


        return $tax_rate;
    }
}

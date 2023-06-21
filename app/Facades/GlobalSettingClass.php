<?php

namespace App\Facades;

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
}

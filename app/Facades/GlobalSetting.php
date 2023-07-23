<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * @method static mixed app\Facades\GlobalSettingClass.php get(string $key)
 * @method static mixed app\Facades\GlobalSettingClass.php get_all()
 * @method static void app\Facades\GlobalSettingClass.php set(string $key, $value)
 * @method static float app\Facades\GlobalSettingClass.php get_tax(string $name, Carbon $time = null)
 * 
 */
class GlobalSetting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'GlobalSetting';
    }
}

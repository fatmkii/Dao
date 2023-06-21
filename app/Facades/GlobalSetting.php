<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * @method static  app\Facades\GlobalSettingClass.php get(string $key)
 * @method static app\Facades\GlobalSettingClass.php get_all()
 * @method static app\Facades\GlobalSettingClass.php set(string $key, $value)
 * 
 */
class GlobalSetting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'GlobalSetting';
    }
}

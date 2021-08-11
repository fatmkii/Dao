<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class myModel extends EloquentModel
{
    protected $suffix = null;

    // 设置表后缀
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        if ($suffix !== null) {
            $this->setTable($this->getTable() . '_' . $suffix);
        }
    }

    // 提供一个静态方法设置表后缀
    public static function suffix($suffix)
    {
        $instance = new static;
        $instance->setSuffix($suffix);

        return $instance->newQuery();
    }
}

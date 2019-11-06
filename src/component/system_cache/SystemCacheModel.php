<?php

namespace app\admin\component\system_cache;

use magein\php_tools\think\Model;

class SystemCacheModel extends Model
{
    protected $table = 'system_cache';

    protected $readonly = [
        'id',
        'create_time',
    ];


    /**
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    protected function getStoreTextAttr($value, $data)
    {
        return SystemCacheLogic::instance()->transStore($data['store']);
    }
}
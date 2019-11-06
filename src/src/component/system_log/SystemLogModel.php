<?php

namespace app\admin\component\system_log;

use magein\php_tools\think\Model;
use think\Request;

class SystemLogModel extends Model
{
    protected $table = 'system_log';

    protected $readonly = [
        'id',
        'create_time',
    ];

    protected $insert = [
        'uid',
        'ip',
        'action',
    ];

    /**
     * @param $value
     * @param $data
     * @return int
     */
    protected function setUidAttr($value, $data)
    {
        if (!$value) {
            $value = defined('UID') ? UID : 0;
        }

        return $value;
    }

    /**
     * @return mixed
     */
    protected function setIpAttr()
    {
        return Request::instance()->ip();
    }

    /**
     * @return string|string[]|null
     */
    protected function setActionAttr()
    {
        return preg_replace('/.html/', '', Request::instance()->baseUrl());
    }
}
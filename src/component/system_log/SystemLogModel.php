<?php

namespace app\admin\component\system_log;

use magein\php_tools\think\Model;
use think\Request;
use think\Response;

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
        'post',
        'get',
        'controller',
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
     * @param $value
     * @return mixed
     */
    protected function setIpAttr($value)
    {
        return $value ?: Request::instance()->ip();
    }

    /**
     * @param $value
     * @return false|string
     */
    protected function setPostAttr($value)
    {
        if (empty($value)) {
            $value = Request::instance()->post();
        }

        return $value ? json_encode($value) : '';
    }

    /**
     * @param $value
     * @return false|string
     */
    protected function setGetAttr($value)
    {
        if (empty($value)) {
            $value = Request::instance()->get();
        }

        return $value ? json_encode($value) : '';
    }

    /**
     * @param $value
     * @return string|string[]|null
     */
    protected function setActionAttr($value)
    {
        if (empty($value)) {
            $value = Request::instance()->action();
        }

        $value = preg_replace('/.html/', '', $value);

        return $value;
    }

    /**
     * @param $value
     * @return string|string[]|null
     */
    protected function setControllerAttr($value)
    {
        if (empty($value)) {
            $value = Request::instance()->controller();
        }

        return $value;
    }
}
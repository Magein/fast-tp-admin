<?php

namespace app\admin\controller;

use app\admin\component\system_ip\SystemIpConstant;
use app\admin\component\system_ip\SystemIpLogic;

/**
 * 系统日志管理
 * Class Log
 * @package app\admin\controller
 */
class Ip extends Main
{

    /**
     * @param string $type
     * @param string $className
     * @param string $namespace
     * @return null
     */
    protected function getClass($type = 'logic', $className = '', $namespace = 'app\admin\component')
    {
        return parent::getClass($type, SystemIpLogic::class, $namespace);
    }

    /**
     * @return array
     */
    protected function getTips()
    {
        return [
            [
                'message' => 'ip地址采用通配符的方式判断 如192.168.*.*，是否开启ip地址验证，由配置文件控制',
                'color' => 'green'
            ]
        ];
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            'id',
            'title',
            'ip',
            'remark',
            'create_time',
        ];
    }

    protected function form()
    {
        return [
            'title',
            'ip',
            'remark',
        ];
    }

    /**
     * @param $result
     * @param null $class
     * @param array $data
     * @param int $wait
     * @param array $header
     */
    protected function operationAfter($result, $class = null, $data = [], $wait = 3, array $header = [])
    {
        \think\Cache::rm(SystemIpConstant::SYSTEM_ALLOW_LOGIN_IP_LIST_NAME);

        parent::operationAfter($result, $class, $data, $wait, $header);
    }
}

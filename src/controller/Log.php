<?php

namespace app\admin\controller;


use app\admin\component\system_log\SystemLogLogic;

/**
 * Class Log
 * @package app\admin\controller
 */
class Log extends Main
{
    protected $isShow = true;

    /**
     * @var array
     *
     */
    protected $leftTopButtons = [];

    /**
     * @var array
     */
    protected $operationButtons = [];

    /**
     * @param array $data
     * @return array
     */
    protected function search($data = [])
    {
        return [
            'uid',
            'controller',
            'action',
            ['field' => 'create_time', 'type' => 'datetime', 'format' => 'date', 'range' => '~', 'express' => 'range']
        ];
    }

    /**
     * @param string $type
     * @param string $className
     * @param string $namespace
     * @return null
     */
    protected function getClass($type = 'logic', $className = '', $namespace = 'app\admin\component')
    {
        return parent::getClass($type, SystemLogLogic::class, $namespace);
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            'id',
            'uid',
            'controller',
            'action',
            'ip',
            'create_time',
        ];
    }
}

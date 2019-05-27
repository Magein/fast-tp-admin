<?php

namespace app\admin\controller;


use app\admin\component\system_log\SystemLogLogic;

/**
 * Class Log
 * @package app\admin\controller
 */
class Log extends Main
{
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
            'action',
            'content',
            'ip',
            'create_time',
        ];
    }
}

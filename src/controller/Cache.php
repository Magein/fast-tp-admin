<?php

namespace app\admin\controller;

use app\admin\component\system_cache\SystemCacheLogic;
use think\Request;

class Cache extends Main
{

    /**
     * @var int
     */
    protected $operationColWidth = 160;

    /**
     * @return array
     */
    protected function getOperationButton()
    {
        $this->operationButtons['clear'] = [
            'title' => '清除',
            'url' => 'clear',
            'param' => ['name' => '__name__', 'store' => '__store__'],
            'type' => 'other'
        ];

        return $this->operationButtons;
    }

    /**
     * @param string $type
     * @param string $className
     * @param string $namespace
     * @return null
     */
    protected function getClass($type = 'logic', $className = '', $namespace = 'app\admin\component')
    {
        return parent::getClass($type, SystemCacheLogic::class, $namespace);
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            'title',
            'key',
            'store',
            'description',
        ];
    }

    /**
     * @return array
     */
    protected function form()
    {
        return [
            'title',
            'key',
            'store',
            'description',
        ];
    }

    public function clear()
    {
        $name = Request::instance()->param('name');

        $store = Request::instance()->param('store');

        if (empty($name) || empty($store)) {
            $this->error('参数错误');
        }

        \think\Cache::store($store)->rm($name);

        $this->success('已清除完毕');
    }
}
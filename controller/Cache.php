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
            'param' => ['key' => '__key__', 'store' => '__store__'],
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
            'store_text',
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
            ['store', SystemCacheLogic::instance()->transStore()],
            'description',
        ];
    }

    public function clear()
    {
        $key = Request::instance()->param('key');

        $store = Request::instance()->param('store');

        if (empty($key) || empty($store)) {
            $this->error('参数错误');
        }

        \think\Cache::store($store)->rm($key);

        $this->success('已清除完毕');
    }
}
<?php

namespace app\admin\controller;

use app\admin\component\system_cache\SystemCacheLogic;
use think\Request;

class Cache extends Main
{

    /**
     * @var int
     */
    protected $operationColWidth = 70;

    /**
     * @param array $data
     * @return array
     */
    protected function search($data = [])
    {
        return [
            'title',
            'tag',
            'key',
        ];
    }

    /**
     * @return array
     */
    protected function getOperationButton()
    {
        return [
            $this->setButtonConfirm('清除', 'clear', '请再次确定是否删除？', ['key' => '__key__', 'store' => '__store__', 'tag' => '__tag__'])
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
        return parent::getClass($type, SystemCacheLogic::class, $namespace);
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            'title',
            ['field' => 'tag', 'width' => 350],
            ['field' => 'key', 'width' => 190],
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
            ['store', SystemCacheLogic::instance()->transStore()],
            ['field' => 'tag', 'required' => false],
            ['field' => 'key', 'required' => false],
            'description',
        ];
    }

    public function clear()
    {
        $tag = Request::instance()->param('tag');
        $key = Request::instance()->param('key');

        $store = Request::instance()->param('store', 'file');

        if (empty($store)) {
            $store = 'file';
        }

        if ($tag) {
            \think\Cache::store($store)->clear($tag);
        }

        if ($key) {
            \think\Cache::store($store)->rm($key);
        }

        $this->operationAfter(true, '已清除完毕');
    }
}
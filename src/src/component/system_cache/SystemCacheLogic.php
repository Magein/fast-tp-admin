<?php

namespace app\admin\component\system_cache;

use app\admin\component\system_cache\SystemCacheConstant;
use magein\php_tools\think\Logic;


class SystemCacheLogic extends Logic
{
    protected $fields = [
        'id',
        'title',
        'tag',
        'key',
        'store',
        'description',
        'create_time',
    ];

    /**
     * @return SystemCacheModel|\think\Model
     */
    protected function model()
    {
        return new SystemCacheModel();
    }


    /**
     * @param bool $withTrashed
     * @return array
     */
    public function getTitle($withTrashed = false)
    {
        return $this->setWithTrashed($withTrashed)->column('id,title');
    }

    /**
     * @param null $store
     * @return array|mixed|string
     */
    public function transStore($store = null)
    {
        $data = [
            SystemCacheConstant::STORE_TYPE_FILE => 'file',
            SystemCacheConstant::STORE_TYPE_MEMCACHE => 'memcache',
            SystemCacheConstant::STORE_TYPE_REDIS => 'redis',
        ];

        if (null !== $store) {
            return isset($data[$store]) ? $data[$store] : '';
        }

        return $data;
    }

}
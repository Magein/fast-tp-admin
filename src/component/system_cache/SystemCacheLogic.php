<?php

namespace app\admin\component\system_cache;

use magein\php_tools\think\Logic;


class SystemCacheLogic extends Logic
{
    protected $fields = [
        'id',
        'title',
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

}
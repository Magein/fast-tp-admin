<?php

namespace app\admin\component\system_config;

use magein\php_tools\think\Logic;

class SystemConfigLogic extends Logic
{
    protected $fields = [
        'id',
        'name',
        'value',
        'create_time',
    ];

    /**
     * @return SystemConfigModel|\think\Model
     */
    protected function model()
    {
        return new SystemConfigModel();
    }

    /**
     * @param bool $withTrashed
     * @return array
     */
    public function getName($withTrashed = false)
    {
        return $this->setWithTrashed($withTrashed)->getFileStorageList('id,name');
    }

    public function getValue()
    {
        return $this->getFileStorageList('name,value');
    }
}
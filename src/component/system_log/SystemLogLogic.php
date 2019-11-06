<?php

namespace app\admin\component\system_log;

use magein\php_tools\think\Logic;


class SystemLogLogic extends Logic
{
    protected $fields = [
        'id',
        'uid',
        'controller',
        'action',
        'ip',
        'create_time',
    ];

    /**
     * @return SystemLogModel|\think\Model
     */
    protected function model()
    {
        return new SystemLogModel();
    }

    /**
     * @param $uid
     * @return bool|false|int
     */
    public function create($uid)
    {
        return $this->save(
            [
                'uid' => $uid
            ]
        );
    }
}
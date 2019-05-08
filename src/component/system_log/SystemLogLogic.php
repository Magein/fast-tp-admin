<?php

namespace app\admin\component\system_log;

use magein\php_tools\think\Logic;


class SystemLogLogic extends Logic
{
    protected $fields = [
        'id',
        'uid',
        'action',
        'content',
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
     * @param $content
     * @param string $uid
     * @return bool|false|int
     */
    public function create($content, $uid = '')
    {
        return $this->save([
            'content' => $content,
            'uid' => $uid
        ]);
    }
}
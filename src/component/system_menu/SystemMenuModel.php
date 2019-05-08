<?php

namespace app\admin\component\system_menu;

use magein\php_tools\think\Model;

class SystemMenuModel extends Model
{
    protected $table = 'system_menu';

    protected $readonly = [
        'id',
        'create_time',
    ];

    protected $auto = [
        'node'
    ];

    /**
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    protected function getStatusTextAttr($value, $data)
    {
        return SystemMenuLogic::instance()->transStatus($data['status']);
    }

    /**
     * @param $value
     * @param $data
     * @return mixed|string
     */
    protected function setNodeAttr($value, $data)
    {
        if (!isset($data['pid'])) {
            return false;
        }

        static $records = [];

        if (empty($records)) {
            $records = SystemMenuLogic::instance()->getLevelList();
        }

        return $value = isset($records[$data['pid']]) ? $records[$data['pid']]['node'] : '';
    }

    /**
     * @param $value
     * @param $data
     * @return array
     */
    protected function getNodeAttr($value, $data)
    {
        if ($data['node']) {
            return explode('-', $data['node']);
        }

        return [];
    }
}
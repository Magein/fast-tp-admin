<?php

namespace app\admin\component\system_menu;

use magein\php_tools\common\TreeStructure;
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
        return '';
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

    /**
     * @param $value
     * @return bool|string
     */
    protected function setUrlAttr($value)
    {
        if ($value) {

            $value = trim($value, '/');

            if (!preg_match('/^admin/', $value)) {
                $value = 'admin/' . $value;
            }

            return $value;
        }

        return false;
    }
}
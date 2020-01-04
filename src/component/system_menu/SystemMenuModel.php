<?php

namespace app\admin\component\system_menu;

use magein\php_tools\common\TreeStructure;
use magein\php_tools\common\Variable;
use magein\php_tools\think\Model;

class SystemMenuModel extends Model
{
    protected $table = 'system_menu';

    protected $readonly = [
        'id',
        'create_time',
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
     * @return bool|string
     */
    protected function setUrlAttr($value)
    {
        if ($value) {

            $value = trim($value, '/');

            if (!preg_match('/^admin/', $value)) {
                $value = 'admin/' . $value;
            }

            $value = str_replace('/^admin/', '', $value);

            $value = trim($value, '/');

            $value = lcfirst($value);

            $value = (new Variable())->transToCamelCase($value);

            return $value;
        }

        return false;
    }
}
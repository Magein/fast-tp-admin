<?php

namespace app\admin\component\system_role;

use magein\php_tools\think\Model;

class SystemRoleModel extends Model
{
    protected $table = 'system_role';

    protected $readonly = [
        'id',
        'create_time',
    ];

    protected $insert = [
        'uid',
        'auth',
    ];

    /**
     * @return int|null
     */
    protected function setUidAttr()
    {
        return defined('UID') ? UID : 0;
    }

    /**
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    protected function getStatusTextAttr($value, $data)
    {
        return SystemRoleLogic::instance()->transStatus($data['status']);
    }

    /**
     * @param $value
     * @return array|string
     */
    protected function setAuthAttr($value)
    {
        if (is_array($value)) {

            $value = array_filter($value);

            $value = array_unique($value);

            $value = implode(',', $value);
        }

        return $value ? $value : '';
    }

    /**
     * @param $value
     * @param $data
     * @return array
     */
    protected function getAuthAttr($value, $data)
    {
        if ($data['auth']) {
            return explode(',', $data['auth']);
        }

        return [];
    }
}
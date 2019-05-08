<?php

namespace app\admin\component\system_user;

use magein\php_tools\common\Password;
use magein\php_tools\think\Model;
use app\admin\component\system_role\SystemRoleLogic;

class SystemUserModel extends Model
{
    protected $table = 'system_user';

    protected $readonly = [
        'id',
        'create_time',
    ];

    protected $insert = [
        'password'
    ];

    /**
     * 设置密码
     * @param $value
     * @return string
     */
    protected function setPasswordAttr($value)
    {
        if (empty($value)) {
            $value = 123456;
        }

        return (new Password())->encrypt($value);
    }

    /**
     * @param $value
     * @param $data
     * @return string
     */
    protected function setRoleAttr($value, $data)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return $value;
    }

    /**
     * @param $value
     * @param $data
     * @return array
     */
    protected function getRoleAttr($value, $data)
    {
        if ($data['role']){
            return explode(',',$data['role']);
        }

        return [];
    }

    /**
     * @param $value
     * @param $data
     * @return string
     */
    protected function getRoleTextAttr($value, $data)
    {
        static $records = null;

        if (empty($records)) {
            $records = SystemRoleLogic::instance()->getTitle();
        }

        $text = '';
        if ($data['role']) {
            $roleId = explode(',', $data['role']);
            if ($roleId) {
                foreach ($roleId as $item) {
                    $text .= (isset($records[$item]) ? $records[$item] : '') . ',';
                }
            }
        }

        return trim($text, ',');
    }

    /**
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    protected function getStatusTextAttr($value, $data)
    {
        return SystemUserLogic::instance()->transStatus($data['status']);
    }
}
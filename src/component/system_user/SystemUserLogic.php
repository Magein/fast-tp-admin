<?php

namespace app\admin\component\system_user;

use magein\php_tools\think\Logic;
use think\Request;


class SystemUserLogic extends Logic
{
    protected $fields = [
        'id',
        'username',
        'nickname',
        'phone',
        'email',
        'role',
        'status',
        'login_at',
        'login_ip',
        'login_time',
        'remark',
        'create_time',
    ];

    /**
     * @return SystemUserModel|\think\Model
     */
    protected function model()
    {
        return new SystemUserModel();
    }

    /**
     * @param mixed $status
     * @return array|mixed|string
     */
    public function transStatus($status = null)
    {
        $data = [
            SystemUserConstant::STATUS_FORBID => '禁用',
            SystemUserConstant::STATUS_OPEN => '启用',
        ];

        if (null !== $status) {
            return isset($data[$status]) ? $data[$status] : '';
        }

        return $data;
    }

    /**
     * @param $username
     * @return array|bool|null
     */
    public function getByUsername($username)
    {
        if (empty($username)) {
            return false;
        }
        $this->fields[] = 'password';

        return $this->setCondition(['username' => $username])->setFields($this->fields)->find();
    }

    /**
     * 更新登录数据
     * @param $id
     * @return bool|false|int
     */
    public function updateLoginData($id)
    {
        if (empty($id)) {
            return false;
        }
        return $this->model()->save(
            [
                'login_ip' => Request::instance()->ip(),
                'login_at' => time(),
                'login_time' => ['inc', '1']
            ],
            [
                'id' => $id,
            ]
        );
    }
}
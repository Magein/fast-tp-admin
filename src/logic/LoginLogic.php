<?php

namespace app\admin\logic;

use think\Session;
use traits\think\Instance;

class LoginLogic
{
    use Instance;

    public function __construct()
    {
        Session::init(
            [
                'expire' => 86400,
                // 'id' => 'admin-user-' . date('Ymd'),
                'prefix' => 'user_',
            ]
        );
    }

    /**
     * @param null $data
     * @return $this
     */
    public function setLogin($data = null)
    {
        unset($data['password']);

        Session::set('user', $data);

        return $this;
    }

    /**
     * 获取登录信息
     * @return mixed
     */
    public function getLogin()
    {
        return Session::get('user');
    }
}
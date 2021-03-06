<?php

namespace app\admin\logic;

use think\Session;
use traits\think\Instance;

class LoginLogic
{
    use Instance;

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

    /**
     * @return null
     */
    public function id()
    {
        $info = $this->getLogin();

        if (isset($info['id']) && $info['id']) {
            return $info['id'];
        }

        return null;
    }
}
<?php

namespace app\admin\behavior;

use app\admin\logic\LoginLogic;
use think\exception\HttpResponseException;
use think\Request;

class CheckLogin
{
    /**
     * @param Request $param
     * @return bool
     */
    public function run(&$param)
    {
        if ($param->controller() == 'Login') {
            return true;
        }

        $loginInfo = LoginLogic::instance()->getLogin();
        if (empty($loginInfo)) {
            throw new HttpResponseException(redirect('admin/login/index'));
        }

        !defined('UID') && define('UID', $loginInfo['id']);

        return true;
    }
}
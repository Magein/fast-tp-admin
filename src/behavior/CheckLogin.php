<?php

namespace app\admin\behavior;

use app\admin\component\system_log\SystemLogLogic;
use app\admin\logic\LoginLogic;
use think\Config;
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

        $id = LoginLogic::instance()->id();
        if (empty($id)) {
            throw new HttpResponseException(redirect('admin/login/index'));
        }

        !defined('UID') && define('UID', $id);

        $system_log = Config::get('system_action_log');

        if ($system_log) {
            $controller = strtolower($param->controller());
            $action = strtolower($param->action());

//            var_dump($controller);
//            var_dump($action);
//            die();

            if (in_array($controller . '/' . $action, $system_log) || in_array($controller . '/*', $system_log)) {
                SystemLogLogic::instance()->create(UID);
            }
        }

        return true;
    }
}
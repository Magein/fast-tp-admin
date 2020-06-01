<?php


namespace app\admin\controller;

use app\admin\logic\LoginLogic;
use app\admin\component\system_ip\SystemIpLogic;
use app\admin\component\system_config\SystemConfigLogic;
use app\admin\component\system_log\SystemLogLogic;
use app\admin\component\system_user\SystemUserConstant;
use app\admin\component\system_user\SystemUserLogic;
use magein\render\admin\Cdn;
use think\Controller;
use think\Request;
use think\Env;

/**
 * Class Login
 * @package app\admin\controller
 */
class Login extends Controller
{
    use Cdn;

    /**
     * @var null
     */
    protected $login_user_data = null;

    public function index()
    {
        $loginInfo = LoginLogic::instance()->getLogin();

        if ($loginInfo) {
            $this->redirect($this->loginSuccessRedirect());
        }

        $config = SystemConfigLogic::instance()->getValue();
        $this->assign('config', $config);
        $this->assign('cdn', $this->cdn());

        $this->assign('username', cookie('user_login_user_name'));
        $this->assign('password', cookie('user_login_user_pass'));

        return $this->fetch('admin@base/main/login');
    }

    public function login()
    {
        $username = Request::instance()->param('username');
        $password = Request::instance()->param('password');
        $remember_me = Request::instance()->param('remember_me');
        $redirect = Request::instance()->param('redirect');

        $username = trim($username);
        $password = trim($password);

        if (empty($username) || empty($password)) {
            $this->error('请输入账号密码');
        }

        /**
         * 验证IP
         */
        if (Env::get('admin.check_ip')) {
            if (false === SystemIpLogic::instance()->checkIp()) {
                $this->error('登录失败，错误代码：1001',
                    [
                        'ip' => Request::instance()->ip(),
                        'check_ip' => Env::get('admin.check_ip')
                    ]);
            }
        }

        $record = SystemUserLogic::instance()->getByUsername($username, $password);
        if (empty($record)) {
            $this->error('用户不存在');
        }

        if ($record['id'] == 1 && !Env::get('admin.allow_super_admin_login')) {
            $this->error('不允许超级管理员账号登录');
        }

        if ($record['status'] == SystemUserConstant::STATUS_FORBID) {
            $this->error('用户已被禁止登录');
        }

        // 更新登录信息
        if (false === SystemUserLogic::instance()->updateLoginData($record['id'])) {
            $this->error('登录失败');
        };

        if ($remember_me) {
            cookie('user_login_user_name', $username);
            cookie('user_login_user_pass', $password);
        }

        $this->login_user_data = $record;
        $this->setLogin();

        SystemLogLogic::instance()->create($record['id']);

        if ($redirect) {
            $url = $redirect;
        } else {
            $url = $this->loginSuccessRedirect();
        }

        $this->success('登录成功', $url);
    }

    protected function setLogin()
    {
        (new LoginLogic())->setLogin($this->login_user_data);
    }

    protected function loginSuccessRedirect()
    {
        $redirect = \think\Config::get('login_success_redirect');

        if (empty($redirect)) {
            $redirect = 'admin/index/index';
        }

        return $redirect;
    }

    public function logout()
    {
        LoginLogic::instance()->setLogin();

        $this->success('success', 'index');
    }
}

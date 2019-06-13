<?php

namespace app\admin\controller;


use app\admin\logic\LoginLogic;
use magein\php_tools\common\Password;
use magein\php_tools\common\Phone;
use magein\render\admin\RenderForm;
use magein\render\admin\component\Property;
use app\admin\component\system_role\SystemRoleLogic;
use app\admin\component\system_user\SystemUserLogic;
use app\admin\component\system_user\SystemUserValidate;
use magein\render\admin\Constant;
use think\Request;

/**
 * 系统用户管理控制器
 * Class User
 * @package app\admin\controller
 */
class User extends Main
{

    /**
     * @param string $type
     * @param string $className
     * @param string $namespace
     * @return null
     */
    protected function getClass($type = 'logic', $className = '', $namespace = 'app\admin\component')
    {
        if ($type == Constant::CLASS_TYPE_VALIDATE) {

            /**
             * unique规则，表明，字段，排除主键值，
             *
             * 下面得到的查询条件是
             *
             * $data['username']=xxx,
             * $data['id']=['neq',传递的值]
             */

            $id = Request::instance()->param('id');
            $rules = [
                'username' => 'require|length:1,30|alphaDash|unique:system_user,username,' . $id,
            ];
            $validate = new SystemUserValidate();
            $validate->rule($rules);
            return $validate;
        }

        return parent::getClass($type, SystemUserLogic::class, $namespace);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function search($data = [])
    {
        return [
            'username',
            'phone',
            ['status', SystemUserLogic::instance()->transStatus()]
        ];
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            'username',
            'nickname',
            'phone',
            'email',
            'status_text',
            'role_text',
            'remark',
        ];
    }

    protected function form()
    {
        return [
            ['role', 'checkbox', SystemRoleLogic::instance()->getTitle()],
            'username',
            'nickname',
            'phone',
            ['field' => 'email', 'required' => false],
            ['status', 'radio', SystemUserLogic::instance()->transStatus()],
            ['field' => 'remark', 'required' => false]
        ];
    }

    protected function save($data = [], $validate = null)
    {
        if (!Phone::instance()->checkPhone($data['phone'])) {
            $this->error('请输入正确的手机号码');
        }

        return parent::save($data);
    }

    public function info()
    {
        if (Request::instance()->isPost()) {
            $data = Request::instance()->post();
            unset($data['role']);
            $this->save($data);
        }

        $id = Request::instance()->param('id');
        $data = SystemUserLogic::instance()->get($id);
        $render = new RenderForm($data, $this->getWord());
        $render->setHidden('id');
        $render->setTexts(
            [
                'username',
                'nickname',
                'phone',
                'email',
            ]
        );

        $this->setFormItems($render);

        return $this->fetch(self::PUBLIC_FORM_MODAL);
    }

    public function password()
    {
        $id = Request::instance()->param('id');

        $data = SystemUserLogic::instance()->setFields(['password'])->get($id);

        if (empty($data)) {
            $this->error('参数错误');
        }

        if (Request::instance()->isPost()) {
            $oldPassword = Request::instance()->post('old_password');
            $password = Request::instance()->post('password');

            if (empty($oldPassword) || empty($password)) {
                $this->error('请输入旧密码与新密码');
            }

            if ($password == $oldPassword) {
                $this->error('新密码不能与旧密码相同');
            }

            $passwordLogic = new Password();

            if ($passwordLogic->encrypt($oldPassword) !== $data['password']) {
                $this->error('您的密码不正确，请重试');
            }

            if ($passwordLogic->encrypt($password) === $data['password']) {
                $this->success('更新成功');
            }

            $result = SystemUserLogic::instance()->updateField('password', $password, $id);

            if ($result) {
                LoginLogic::instance()->setLogin();
            }

            $this->operationAfter($result, [], SystemUserLogic::instance());
        }


        $form = new RenderForm();

        $form->setHidden('id', $id);
        $form->append((new Property())->setField('old_password')->setType('password')->setTitle('旧密码'));
        $form->append((new Property())->setField('password')->setType('password')->setTitle('新密码'));
        $this->setFormItems($form);

        return $this->fetch(self::PUBLIC_FORM_MODAL);
    }
}

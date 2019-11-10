<?php

namespace app\admin\controller;

use app\admin\component\system_menu\SystemMenuLogic;
use app\admin\component\system_role\SystemRoleConstant;
use app\admin\component\system_role\SystemRoleLogic;
use think\Request;

class Role extends Main
{
    protected $operationColWidth = 160;

    /**
     * @param string $type
     * @param string $className
     * @param string $namespace
     * @return null
     */
    protected function getClass($type = 'logic', $className = '', $namespace = 'app\admin\component')
    {
        return parent::getClass($type, SystemRoleLogic::class, $namespace);
    }

    /**
     * @return array
     */
    protected function getOperationButton()
    {
        $button = parent::getOperationButton();

        return [
                $this->setButton('授权', 'access')
            ] + $button;
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            'id',
            'title',
            'desc',
            'status_text',
            'create_time'
        ];
    }

    protected function form()
    {
        return [
            'title',
            'desc',
            [
                'field' => 'status',
                'type' => 'radio',
                'option' => SystemRoleLogic::instance()->transStatus(),
                'value' => SystemRoleConstant::STATUS_OPEN
            ],
        ];
    }

    public function access()
    {
        if (Request::instance()->isPost()) {
            $this->saveMenu();
        }

        $id = Request::instance()->param('id');

        /**
         * 设置当前角色已经设置的授权信息，用于回填
         */
        $auth = [];
        if ($id) {
            $record = SystemRoleLogic::instance()->get($id);
            if ($record) {
                $auth = $record['auth'];
            }
        }

        $result = SystemMenuLogic::instance()->getAuthList($auth);

        // 设置角色id，传递到前段用于后续ajax提交
        $this->assign('id', $id);
        $this->assign('data', json_encode($result, JSON_UNESCAPED_UNICODE));

        return $this->fetch('admin@base/main/access');
    }

    private function saveMenu()
    {
        $roleId = Request::instance()->param('role_id');
        $menuId = Request::instance()->param('menu_id/a');

        $result = SystemRoleLogic::instance()->setMenu($roleId, $menuId);

        if (false === $result) {
            $this->error('保存失败');
        }

        \think\Cache::store('file')->clear('ADMIN_USER_AUTH_MENU_ID_LIST');

        $this->success('保存成功');
    }
}
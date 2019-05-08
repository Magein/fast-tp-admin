<?php

namespace app\admin\controller;

use app\admin\component\system_menu\SystemMenuConstant;
use app\admin\component\system_menu\SystemMenuLogic;
use think\Request;

class Menu extends Basic
{

    protected $title = '系统菜单';

    /**
     * @param string $type
     * @param string $className
     * @param string $namespace
     * @return null
     */
    protected function getClass($type = 'logic', $className = '', $namespace = 'app\admin\component')
    {
        return parent::getClass($type, SystemMenuLogic::class, $namespace);
    }

    /**
     * @param array $condition
     * @param string $query_type
     * @return array|mixed
     */
    protected function getList($condition = [], $query_type = 'paginate')
    {
        $menus = SystemMenuLogic::instance()->getLevelList();

        return array_values($menus);
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            ['field' => 'id', 'width' => 60],
            ['field' => 'icon', 'width' => 60, 'templet' => '#menuIcon'],
            'title',
            'url',
            ['field' => 'sort', 'edit' => 'text'],
        ];
    }

    protected function operationAfter($result, $data = [], $class = null)
    {
        \think\Cache::store('file')->rm(SystemMenuConstant::SYSTEM_MENU_TREE_LIST_NAME);
    }

    public function save($data = [], $validate = null)
    {

        if (empty($data)) {
            $data = Request::instance()->post();
        }

        $children = $data['children'];

        unset($data['children']);

        $pid = parent::save($data);

        $result = true;


        if ($pid && $children) {

            foreach ($children as $key => $item) {

                $icon = '';

                $url = explode('/', $data['url']);

                array_pop($url);

                switch ($key) {
                    case 'add':
                        $url[] = 'add';
                        $title = '新增';
                        $icon = 'layui-icon layui-icon-add-1';
                        break;
                    case 'edit':
                        $url[] = 'edit';
                        $title = '编辑';
                        $icon = 'layui-icon layui-icon-edit';
                        break;
                    case 'del':
                        $url[] = 'del';
                        $title = '删除';
                        $icon = 'layui-icon layui-icon-delete';
                        break;
                }

                if (empty($title)) {
                    continue;
                }

                $data = [
                    'pid' => $pid,
                    'title' => $title,
                    'url' => implode('/', $url),
                    'icon' => $icon
                ];

                $record = SystemMenuLogic::instance()->setCondition(['url' => $data['url']])->find();

                if ($record) {
                    continue;
                }

                $result = parent::save($data);

                if (false === $result) {
                    break;
                }
            }
        }

        if ($result) {
            $this->success('操作成功');
        }

        $this->error('操作失败');
    }

    public function add()
    {
        if (Request::instance()->isPost()) {

            $this->save(Request::instance()->post());
        }

        $this->assign('menus', SystemMenuLogic::instance()->getLevelList());

        // 兼容回填
        $this->assign('data', []);

        return $this->fetch('form');
    }

    public function edit()
    {
        if (Request::instance()->isPost()) {
            $this->save();
        }

        $id = Request::instance()->param('id');

        $data = SystemMenuLogic::instance()->get($id);
        $this->assign('data', $data);

        $this->assign('menus', SystemMenuLogic::instance()->getLevelList());

        return $this->fetch('form');
    }

    /**
     * @param $id
     * @return bool|int|void
     */
    public function del($id)
    {
        $result = SystemMenuLogic::instance()->delete($id);

        $this->operationAfter($result);

        if ($result) {
            $this->success('操作成功');
        }

        $this->error('操作失败');
    }
}

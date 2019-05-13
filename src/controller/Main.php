<?php

namespace app\admin\controller;

use app\admin\logic\LoginLogic;
use app\admin\component\system_config\SystemConfigLogic;
use app\admin\component\system_menu\SystemMenuLogic;
use app\admin\component\system_role\SystemRoleLogic;
use magein\php_tools\object\QueryResult;
use magein\php_tools\think\Logic;
use magein\render\admin\FastBuild;
use magein\render\admin\RenderForm;
use think\Controller;
use think\Hook;
use think\Request;
use think\View;

class Main extends Controller
{
    use FastBuild;

    /**
     * 渲染方式：表单路径
     */
    const PUBLIC_FORM_MODAL = 'admin@public/form/modal';

    /**
     * 渲染方式：表单路径
     */
    const PUBLIC_FORM_OPEN = 'admin@public/form/open';

    /**
     * 渲染方式：表格路径
     */
    const PUBLIC_TABLE = 'admin@public/index';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * 初始化
     */
    public function _initialize()
    {
        $this->init();
    }

    protected function init()
    {
        /**
         * 如果没有在配置文件中注册验证登录的行为，则自动调用
         */
        if (!defined('UID')) {
            $param = Request::instance();
            $result = Hook::exec('app\admin\behavior\CheckLogin', 'run', $param);
            if (false === $result) {
                $this->error('行为异常');
            }
        }

        /**
         * 登录信息
         */
        $user = LoginLogic::instance()->getLogin();
        $this->assign('user', $user);

        $menuId = '';
        if ($user['role']) {
            $menuId = SystemRoleLogic::instance()->getAuthByIds($user['role']);
            if ($menuId) {
                $menuId = explode(',', $menuId);
            }
        }

        $path = Request::instance()->path();

        $path = strtolower($path);

        $menus = SystemMenuLogic::instance()->getList();

        /**
         * 根据用户角色信息，过滤显示的菜单
         */
        $allow = [];
        if ($menuId) {
            foreach ($menus as $key => $item) {
                if (!in_array($item['id'], $menuId)) {
                    unset($menus[$key]);
                    continue;
                }

                if ($item['pid'] != 0) {
                    $allow[$item['id']] = $item['url'];
                }
            }
        } else {
            foreach ($menus as $key => $item) {
                if ($item['pid'] != 0) {
                    $allow[$item['id']] = $item['url'];
                }
            }
        }

        /**
         * 验证访问的路径是否在 用户角色权限内
         */
        $key = array_search($path, $allow);

//        if (false === $key) {
//            $this->error('您没有获取访问改路径的权限');
//        }

        $node = [];
        if (isset($menus[$key])) {
            $node = $menus[$key]['node'];
            // 自动注册页面标题
            $this->title = $this->title ?: $menus[$key]['title'];
        }


        if ($node) {
            foreach ($menus as $key => $item) {
                if ($item['pid'] == 0) {
                    continue;
                }
                $value = isset($item['node'][0]) ? $item['node'][0] : '';

                if ($value && in_array($value, $node)) {
                    continue;
                }
                unset($menus[$key]);
            }
        }

        $menus = SystemMenuLogic::instance()->getListByNode($menus);

        $config = SystemConfigLogic::instance()->getValue();

        View::share(
            [
                // 面包削
                'title' => $this->title,
                // 页面提示
                'tips' => $this->getTips(),
                // 菜单
                'menus' => $menus,

                // 配置信息
                'config' => $config,

                // 当前访问路径
                'path' => $path,

                // 访问的节点信息
                'active_id' => isset($node[0]) ? $node[0] : 1,
            ]
        );
    }

    public function index()
    {

        $limit = Request::instance()->get('limit');
        if ($limit) {
            $this->limit = $limit;
        }

        /**
         * 处理搜索的条件信息
         */
        $condition = $this->getCondition();
        /**
         * 搜索域信息
         */
        $render = $this->buildForm($this->search(), $condition);
        $this->assign('search', $render->getItems());

        /**
         * 获取头部信息在获取数据前执行，以便于处理获取器的值
         */
        $header = $this->buildTable();
        $this->assign('header', array_values($header));

        $queryResult = $this->getList($condition);
        if ($queryResult instanceof QueryResult) {
            $page = $queryResult->getPage();
            $list = $queryResult->getList();
        } else {
            $page = [];
            $list = $queryResult;
        }

        $this->assign('page', array_values($page));
        $this->assign('list', array_values($list));

        // 表格列中的操作按钮
        $this->assign('operationButtons', $this->getOperationButton());

        // 左上角操作按钮
        $this->assign('leftTopButtons', $this->getLeftTopButton());

        return $this->fetch(self::PUBLIC_TABLE);
    }


    /**
     * 设置表单项
     * @param $form
     */
    protected function setFormItems($form)
    {
        if ($form instanceof RenderForm) {
            $items = $form->getItems();
        } elseif (is_array($form)) {
            $items = $form;
        } else {
            $items = [];
        }

        $this->assign('formItems', $items);
    }

    /**
     * @return string
     */
    protected function formTemplate()
    {
        if (Request::instance()->param('open')) {
            return self::PUBLIC_FORM_OPEN;
        }

        return self::PUBLIC_FORM_MODAL;
    }

    /**
     * 新增页面
     * @return mixed
     */
    public function add()
    {
        if (Request::instance()->isPost()) {
            $this->save(Request::instance()->post());
        }

        $this->setFormItems($this->buildForm($this->form()));
        $this->assign('data', []);

        return $this->fetch($this->formTemplate());
    }

    /**
     * 编辑页面
     * @return mixed
     */
    public function edit()
    {
        if (Request::instance()->isPost()) {
            $this->save(Request::instance()->post());
        }

        $id = Request::instance()->param('id');
        $data = $this->getData($id);
        $render = $this->buildForm($this->form(), $data);
        $render->setHidden('id', $id);
        $this->setFormItems($render);

        // 把获取到的值传递到前段，以适应其他的各种交互操作
        $this->assign('data', $data);

        return $this->fetch($this->formTemplate());
    }

    /**
     * 操作数据后的动作
     * @param mixed $result
     * @param array $data
     * @param Logic $class
     */
    protected function operationAfter($result, $data = [], $class = null)
    {
        if ($result) {
            $this->success('操作成功');
        } else {
            $this->error($class->getError() ?: '操作失败');
        }
    }
}
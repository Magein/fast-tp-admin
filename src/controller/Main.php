<?php

namespace app\admin\controller;

use app\admin\constant\StaticResourceConstant;
use app\admin\logic\LoginLogic;
use app\admin\component\system_config\SystemConfigLogic;
use app\admin\component\system_menu\SystemMenuLogic;
use app\admin\component\system_role\SystemRoleLogic;
use magein\php_tools\common\TreeStructure;
use magein\php_tools\common\Variable;
use magein\php_tools\object\QueryResult;
use magein\php_tools\think\Logic;
use magein\render\admin\Cdn;
use magein\render\admin\FastBuild;
use magein\render\admin\RenderForm;
use think\Controller;
use think\Hook;
use think\Request;
use think\View;

class Main extends Controller
{
    use FastBuild;
    use Cdn;

    /**
     * 基础文件路径
     */
    const VIEW_BASE = 'admin@base/';

    /**
     *  基础文件路径
     */
    const VEW_BASE_MAIN = 'admin@base/main/';

    /**
     * 基础渲染模板路径
     */
    const VIEW_BASE_RENDER = 'admin@base/render/';

    /**
     * 渲染方式：表单路径
     */
    const PUBLIC_FORM_MODAL = 'admin@base/render/form/modal';

    /**
     * 渲染方式：表单路径
     */
    const PUBLIC_FORM_OPEN = 'admin@base/render/form/open';

    /**
     * 渲染方式：表格路径
     */
    const PUBLIC_TABLE = 'admin@base/render/index';

    /**
     * 当前访问页面标题
     * @var string
     */
    protected $title = '';

    /**
     * 当前访问的路径信息
     * @var string
     */
    protected $path = '';

    /**
     * 当前访问菜单
     * @var array
     */
    protected $active_menu = ['node' => [], 'url' => ''];

    /**
     * 当前用户登录信息
     * @var array
     */
    protected $user = [];

    /**
     * 系统配置信息
     * @var array
     */
    protected $config = [];

    /**
     * 初始化
     */
    public function _initialize()
    {
        $this->checkLogin();

        $this->path();

        $this->config();

        $this->init();
    }

    /**
     * 检测用户登录信息
     */
    protected function checkLogin()
    {
        if (!defined('UID')) {
            $param = Request::instance();
            $result = Hook::exec('app\admin\behavior\CheckLogin', 'run', $param);
            if (false === $result) {
                $this->error('行为异常');
            }
        }

        $this->user = LoginLogic::instance()->getLogin();
    }

    /**
     * 设置系统变量
     * @return array
     */
    protected function config()
    {
        return $this->config = SystemConfigLogic::instance()->getValue();
    }

    /**
     * 系统初始化
     */
    protected function init()
    {
        $menus = $this->checkMenus();

        if ($menus) {
            $menus = TreeStructure::instance()->tree($menus);
        }

        // 顶部菜单
        $top = TreeStructure::instance()->getParent();
        // 左侧菜单
        if ($this->active_menu['node']) {
            $left = $menus[$this->active_menu['node'][0]]['child'];
        } else {
            $left = array_shift($menus)['child'];
        }

        View::share(
            [
                'user' => $this->user,
                // 面包削
                'title' => $this->title,
                // 页面提示
                'tips' => $this->getTips(),
                // 菜单
                'top' => $top,
                'left' => $left,
                // 配置信息
                'config' => $this->config,
                // 当前访问的菜单
                'active_menu' => $this->active_menu,
                // 静态资源文件
                'cdn' => $this->cdn(),
                // 上传路径
                'upload' => $this->upload()
            ]
        );
    }

    /**
     * 上传路径
     * @return array
     */
    protected function upload()
    {
        return [
            'UEditor' => url('plugin/editor'),
            'file' => url('plugin/upload'),
        ];
    }

    /**
     * 当前访问的路径
     * @return string
     */
    protected function path()
    {
        $request = Request::instance();

        $module = $request->module();
        $controller = $request->controller();
        $action = $request->action();

        $controller = (new Variable())->transToUnderline($controller);
        $this->path = $module . '/' . $controller . '/' . $action;

        return $this->path;
    }

    /**
     * @return array|mixed
     */
    protected function checkMenus()
    {
        /**
         * 获取系统的菜单列表，以及获取系统菜单的链接
         */
        $menus = SystemMenuLogic::instance()->getList();
        $menu_url = [];
        if ($menus) {
            foreach ($menus as $key => $item) {
                $menu_url[] = $item['url'];
                if ($item['url'] == $this->path && $item['node']) {
                    // 这是页面标题
                    $this->title = $this->title ?: $item['title'];
                    // 这里的待优化，为了兼容xxx/add 这种新窗口打开后，左侧菜单没有选中的状态
                    $this->active_menu = count($item['node']) > 2 ? null : $item;
                }
            }
        }

        /**
         * 根据用户角色获取用户角色对应的菜单ID
         */
        $menu_ids = $this->getUserAuth();

        if ($menu_ids) {
            // 重置掉菜单url，用于后续的验证访问权限
            $menu_url = [];
            foreach ($menus as $key => $item) {
                if (in_array($item['id'], $menu_ids)) {
                    $menu_url[] = $item['url'];
                    continue;
                }
                unset($menus[$key]);
            }
        }

        $this->checkAuth($menu_url);

        $this->getActiveMenu($menus);

        return $menus;
    }

    /**
     * 获取用户拥有的菜单ID
     * @param array $role_id
     * @return mixed
     */
    protected function getUserAuth($role_id = [])
    {
        if (empty($role_id)) {
            $role_id = isset($this->user['role_id']) ? $this->user['role_id'] : [];
        }

        $menu_ids = SystemRoleLogic::instance()->getMenuIdByRoleIds($role_id);

        return $menu_ids;
    }

    /**
     * @param $menu_url
     * @return bool
     */
    protected function checkAuth($menu_url)
    {
        if (empty($menu_url)) {
            return true;
        }

        if ($this->user['id'] == 1) {
            return true;
        }

        if (!in_array($this->path, $menu_url)) {
            $this->error('您尚未获得访问该路劲的权限');
        }

        return true;
    }

    /**
     * 获取当前访问的菜单信息，用户在前段高亮显示菜单
     * @param array $menus
     * @return string
     */
    protected function getActiveMenu($menus)
    {
        $removeAction = function ($path) {
            $path = substr($path, 0, strrpos($path, '/'));
            $path = str_replace('/', '/', $path);
            return $path;
        };

        if (empty($this->active_menu)) {
            $path = $removeAction($this->path);
            foreach ($menus as $key => $item) {
                $url = $removeAction($item['url']);
                if ($url == $path && $item['node']) {
                    $this->active_menu = $item;
                    break;
                }
            }
        }

        return $this->active_menu;
    }

    public function index()
    {

        $pageSize = Request::instance()->get('page_size', \think\Config::get('paginate.list_rows'));
        if ($pageSize) {
            $this->limit = $pageSize;
        }

        /**
         * 处理搜索的条件信息
         */
        $searchData = Request::instance()->get();
        $condition = $this->getCondition($searchData);

        $searchItem = $this->dateSearch($this->searchTime, $this->search());
        /**
         * 搜索域信息
         */
        $render = $this->buildForm($searchItem, $searchData);
        $this->assign('formItems', $render->getItems());

        /**
         * 获取头部信息在获取数据前执行，以便于处理获取器的值
         */
        $header = $this->buildTable();
        $this->assign('header', array_values($header));

        $queryResult = $this->getList($condition);

        if (Request::instance()->param('debug')) {
            halt($queryResult);
        }

        if ($queryResult instanceof QueryResult) {
            $page = $queryResult->getPage();
            $list = $queryResult->getList();
        } else {
            $page = [];
            $list = $queryResult;
        }

        $page['var_page'] = \think\Config::get('paginate.var_page');

        $this->assign('page', $page);
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
        if (Request::instance()->param('modal')) {
            return self::PUBLIC_FORM_MODAL;
        }
        return self::PUBLIC_FORM_OPEN;
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
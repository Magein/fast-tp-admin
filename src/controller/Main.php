<?php

namespace app\admin\controller;

use app\admin\component\system_menu\SystemMenuConstant;
use app\admin\logic\LoginLogic;
use app\admin\component\system_config\SystemConfigLogic;
use app\admin\component\system_menu\SystemMenuLogic;
use app\admin\component\system_role\SystemRoleLogic;
use magein\php_tools\common\TreeStructure;
use magein\php_tools\common\Variable;
use magein\php_tools\object\QueryResult;
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
     * pid是当前菜单的上级ID
     * ppid是当前菜单的上上级ID
     *
     * pid ppid都是用于左侧二级菜单的展示，默认打开还是关闭
     * @var array
     */
    protected $active_menu = ['node' => [], 'url' => '', 'pid' => 0, 'ppid' => 0];

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
    protected function _initialize()
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
        // 缓冲中获取菜单
        if (\think\Config::get('system_menu_use_cache') || true) {
            $system_menus = \think\Cache::store('file')->get(SystemMenuConstant::SYSTEM_MENU_LIST);
        }

        if (empty($system_menus)) {
            $system_menus = $this->checkMenus();
            \think\Cache::store('file')->set(SystemMenuConstant::SYSTEM_MENU_LIST, $system_menus);
        }

        $menu_url = $menus = $top = [];
        if ($system_menus) {
            /**
             * 根据用户角色获取用户角色对应的菜单ID
             */
            $menu_ids = $this->getUserAuth();
            if ($menu_ids) {
                foreach ($system_menus as $key => $item) {
                    if (in_array($item['id'], $menu_ids)) {
                        $menu_url[] = $item['url'];
                        continue;
                    }
                    unset($system_menus[$key]);
                }
            }

            $menus = TreeStructure::instance()->tree($system_menus, function ($item) {
                if ($item['url'] === $this->path) {
                    $this->active_menu = $item;
                }
                return $item;
            });

            // 顶部菜单
            $top = TreeStructure::instance()->getParent();
        }

        $this->checkAuth($menu_url);
        $this->getActiveMenu();

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
                //当前访问路径
                'path' => $this->path,
                // 配置信息
                'config' => $this->config,
                // 当前访问的菜单
                'active_menu' => $this->active_menu,
                // 静态资源文件
                'cdn' => $this->cdn(),
                // 上传路径
                'upload' => $this->upload(),
                // 右上角的导航信息
                'set_right_top_navigation' => $this->setRightTopNavigation()
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
     * // 右上角的导航信息
     * @return string
     */
    protected function setRightTopNavigation()
    {
        return '';
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
        $action = $request->action(true);

        $controller = (new Variable())->transToUnderline($controller);
        $action = (new Variable())->transToUnderline($action);
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
        $menus = TreeStructure::instance()->floor($menus, function ($item, $data) {
            if ($item['pid'] == 0) {
                $item['node'][] = $item['id'];
            } else {
                $item['node'] = $data[$item['pid']]['node'];
                $item['node'][] = $item['id'];
            }

            $item['title'] = trim($item['title'], '|--');

            return $item;
        }, 4);

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
            $role_id = isset($this->user['role']) ? $this->user['role'] : [];
        }

        if ($this->user['id'] == 1) {
            return [];
        }

        $cache_name = 'user_auth_menu_id_list_' . $this->user['id'];

        $menu_ids = \think\Cache::store('file')->tag('ADMIN_USER_AUTH_MENU_ID_LIST')->get($cache_name);

        if (empty($menu_ids)) {

            $menu_ids = SystemRoleLogic::instance()->getMenuIdByRoleIds($role_id);

            \think\Cache::store('file')->tag('ADMIN_USER_AUTH_MENU_ID_LIST')->set($cache_name, $menu_ids);
        }

        return $menu_ids;
    }

    /**
     * 检测权限
     * @param array $menu_url
     * @return bool
     */
    protected function checkAuth($menu_url)
    {
        if (empty($menu_url)) {
            return true;
        }

        if ($this->path == 'admin/index/index') {
            return true;
        }

        if ($this->user['id'] == 1) {
            return true;
        }

        $skip_auth_check = \think\Config::get('skip_auth_check') ?: [];

        if (is_array($skip_auth_check)) {
            $skip_auth_check = array_merge($skip_auth_check, [
                'user/info',
                'user/password',
            ]);
            $path = preg_replace('/admin/', '', $this->path);
            if (in_array(trim($path, '/'), $skip_auth_check)) {
                return true;
            }
        }

        if (!in_array($this->path, $menu_url)) {
            $this->error('您尚未获得访问该路径的权限');
        }

        return true;
    }

    /**
     * 获取当前访问的菜单信息，用户在前段高亮显示菜单
     * @return array
     */
    protected function getActiveMenu()
    {
        $removeAction = function ($path) {
            $path = substr($path, 0, strrpos($path, '/'));
            $path = str_replace('/', '/', $path);
            return $path;
        };

        if (count($this->active_menu['node']) > 3) {
            $this->active_menu['url'] = $removeAction($this->active_menu['url']) . '/index';
            $node = $this->active_menu['node'];
            $this->active_menu['ppid'] = $node[1];
        } else {
            $this->active_menu['ppid'] = 0;
        }

        if (isset($this->active_menu['title'])) {
            $this->title = $this->title ?: $this->active_menu['title'];
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
        $this->assign('list', $list ? array_values($list) : []);
        $this->assign('height', $this->height);
        $this->assign('tableStyle', $this->tableStyle);

        // 表格列中的操作按钮
        $this->assign('operationButtons', $this->getOperationButton());

        // 左上角操作按钮
        $this->assign('leftTopButtons', $this->getLeftTopButton());

        // 下载按钮
        $this->assign('downloadButton', $this->downLoadButton);

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
}
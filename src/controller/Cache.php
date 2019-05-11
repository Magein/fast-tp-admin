<?php

namespace app\admin\controller;

use app\admin\component\system_ip\SystemIpConstant;
use app\admin\component\system_menu\SystemMenuConstant;
use magein\render\admin\Constant;
use think\Request;

class Cache extends Main
{
    /**
     * @var array
     */
    protected $leftTopButtons = [];

    /**
     * @var bool
     */
    protected $hiddenCheckbox = true;

    /**
     * @var array
     */
    protected $operationButtons = [
        'clear' => [
            'title' => '清除',
            'url' => 'clear',
            'param' => ['name' => '__name__', 'store' => '__store__'],
            'type' => 'other'
        ],
    ];

    /**
     * @return array
     */
    protected function getTips()
    {
        return [
            [
                'color' => 'green',
                'message' => '缓存管理需要开发人员在代码中配置',
            ]
        ];
    }

    /**
     * @param array $condition
     * @param string $query_type
     * @return array|mixed
     */
    protected function getList($condition = [], $query_type = Constant::QUERY_TYPE_PAGINATE)
    {
        return [
            [
                'store' => 'file',
                'name' => SystemMenuConstant::SYSTEM_MENU_TREE_LIST_NAME,
                'title' => '系统菜单',
                'description' => '系统中所有的菜单缓存',
            ],
            [
                'store' => 'file',
                'name' => SystemIpConstant::SYSTEM_ALLOW_LOGIN_IP_LIST_NAME,
                'title' => '登录IP',
                'description' => '允许登录系统的IP地址',
            ]
        ];
    }

    /**
     * @return array
     */
    protected function header()
    {
        return [
            ['field' => 'name', 'title' => '缓存值'],
            ['field' => 'title', 'title' => '缓存名称'],
            ['field' => 'store', 'title' => '驱动'],
            ['field' => 'description', 'title' => '描述'],
        ];
    }

    public function clear()
    {
        $name = Request::instance()->param('name');

        $store = Request::instance()->param('store');

        if (empty($name) || empty($store)) {
            $this->error('参数错误');
        }

        \think\Cache::store($store)->rm($name);

        $this->success('已清除完毕');
    }
}
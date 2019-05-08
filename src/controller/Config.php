<?php

namespace app\admin\controller;

use app\admin\component\system_config\SystemConfigLogic;
use think\Request;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:05
 */
class Config extends Basic
{

    protected $title = '系统参数';

    /**
     * 显示系统常规配置
     */
    public function index()
    {
        $data = SystemConfigLogic::instance()->getValue();

        return view('', [
            'data' => $data,
        ]);
    }

    public function save($data = [], $validate = null)
    {
        $data = Request::instance()->post();

        $param = [];
        if ($data) {
            foreach ($data as $key => $item) {
                $param[] = [
                    'name' => $key,
                    'value' => $item,
                ];
            }
        }

        $records = SystemConfigLogic::instance()->getName();

        foreach ($param as $item) {

            $name = $item['name'];

            $key = array_search($name, $records);

            if ($key) {
                $item['id'] = $key;
            }
            SystemConfigLogic::instance()->save($item);
        }

        $this->success('success');
    }
}

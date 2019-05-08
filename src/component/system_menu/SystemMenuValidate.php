<?php

namespace app\admin\component\system_menu;

use think\Validate;

class SystemMenuValidate extends Validate
{
    protected $rule = [
        'pid' => 'require|integer',
        'title' => 'require|length:1,60',
        'icon' => 'length:1,60',
        'target' => 'length:1,30',
        'sort' => 'integer|between:1,127',
        'status' => 'integer|in:0,1',
    ];

    protected $message = [
        'pid.require' => '请输入父类ID',
        'pid.integer' => '父类ID格式错误',
        'title.require' => '请输入菜单名称',
        'title.length' => '菜单名称长度不正确,允许的长度1~60',
        'icon.length' => '图标长度不正确,允许的长度1~60',
        'target.length' => '打开方式长度不正确,允许的长度1~30',
        'sort.integer' => '排序格式错误',
        'sort.between' => '排序取值范围在 1~127',
        'status.integer' => '状态格式错误',
        'status.in' => '状态取值范围在 0~1',
    ];
}
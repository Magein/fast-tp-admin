<?php

namespace app\admin\component\system_role;

use think\Validate;

class SystemRoleValidate extends Validate
{
    protected $rule = [
        'title' => 'require|length:1,50',
        'desc' => 'require|length:1,255',
        'status' => 'require|integer|between:1,127',
    ];

    protected $message = [
        'title.require' => '请输入名称',
        'title.length' => '名称长度不正确,允许的长度1~50',
        'desc.require' => '请输入描述',
        'desc.length' => '描述长度不正确,允许的长度1~255',
        'status.require' => '请输入状态',
        'status.integer' => '状态格式错误',
        'status.between' => '状态取值范围在 1~127',
    ];
}
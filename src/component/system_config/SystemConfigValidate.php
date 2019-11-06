<?php

namespace app\admin\component\system_config;

use think\Validate;

class SystemConfigValidate extends Validate
{
    protected $rule = [
        'name' => 'require|length:1,255',
        'value' => 'require|length:1,255',
    ];

    protected $message = [
        'name.require' => '请输入参数名称',
        'name.length' => '参数名称长度不正确,允许的长度1~255',
        'value.require' => '请输入参数值',
        'value.length' => '参数值长度不正确,允许的长度1~255',
    ];
}
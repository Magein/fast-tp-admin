<?php

namespace app\admin\component\system_log;

use think\Validate;

class SystemLogValidate extends Validate
{
    protected $rule = [
        'uid' => 'require|integer',
        'action' => 'require|length:1,50',
        'content' => 'require|length:1,255',
        'ip' => 'require|length:1,30',
    ];

    protected $message = [
        'uid.require' => '请输入管理员',
        'uid.integer' => '管理员格式错误',
        'action.require' => '请输入行为',
        'action.length' => '行为长度不正确,允许的长度1~50',
        'content.require' => '请输入行为描述',
        'content.length' => '行为描述长度不正确,允许的长度1~255',
        'ip.require' => '请输入IP地址',
        'ip.length' => 'IP地址长度不正确,允许的长度1~30',
    ];
}
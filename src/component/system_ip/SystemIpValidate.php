<?php

namespace app\admin\component\system_ip;

use think\Validate;

class SystemIpValidate extends Validate
{
    protected $rule = [
        'title' => 'require|length:1,30',
        'ip' => 'require|length:1,18',
        'remark' => 'require|length:1,30',
    ];

    protected $message = [
        'title.require' => '请输入名称',
        'title.length' => '名称长度不正确,允许的长度1~30',
        'ip.require' => '请输入IP地址',
        'ip.length' => 'IP地址长度不正确,允许的长度1~18',
        'remark.require' => '请输入备注',
        'remark.length' => '备注长度不正确,允许的长度1~30',
    ];
}
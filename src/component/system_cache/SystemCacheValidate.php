<?php

namespace app\admin\component\system_cache;

use think\Validate;

class SystemCacheValidate extends Validate
{
    protected $rule = [
        'title' => 'require|length:1,255',
        'key' => 'require|length:1,255',
        'store' => 'require|integer|between:1,127',
        'description' => 'require|length:1,255',
    ];

    protected $message = [
        'title.require' => '请输入名称',
        'title.length' => '名称长度不正确,允许的长度1~255',
        'key.require' => '请输入键',
        'key.length' => '键长度不正确,允许的长度1~255',
        'store.require' => '请输入驱动方式',
        'store.integer' => '驱动方式格式错误',
        'store.between' => '驱动方式取值范围在 1~127',
        'description.require' => '请输入描述',
        'description.length' => '描述长度不正确,允许的长度1~255',
    ];
}
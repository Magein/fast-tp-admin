<?php

namespace app\admin\component\system_user;

use think\Validate;

class SystemUserValidate extends Validate
{
    protected $rule = [
        'username' => 'require|length:1,30',
        'nickname' => 'require|length:1,30',
        'phone' => 'require|length:11',
        'email' => 'require|email|length:1,30',
        'role' => 'length:1,255',
        'status' => 'integer|in:0,1',
        'remark' => 'length:1,255',
    ];

    protected $message = [
        'username.require' => '请输入登录账号',
        'username.length' => '登录账号长度不正确,允许的长度1~30',
        'username.unique' => '账号已经存在，请不要重复添加',
        'username.alphaDash' => '账号为是字母、数字、下划线、破折号的组合',
        'password.length' => '登录密码长度不正确,允许的长度1~50',
        'nickname.require' => '请输入昵称',
        'nickname.length' => '昵称长度不正确,允许的长度1~30',
        'phone.require' => '请输入手机号码',
        'phone.length' => '手机号码不正确',
        'email.require' => '请输入邮箱地址',
        'email.email' => '邮箱地址格式错误',
        'email.length' => '邮箱地址长度不正确,允许的长度1~30',
        'role.require' => '请输入角色',
        'role.length' => '角色长度不正确,允许的长度1~255',
        'status.integer' => '状态格式错误',
        'status.in' => '状态值错误',
        'remark.length' => '备注长度不正确,允许的长度1~255',
    ];
}
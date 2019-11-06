<?php

namespace app\admin\component\system_user;

class SystemUserDictionary
{
    public $word = [
        'username' => '登录账号',
        'password' => '登录密码',
        'nickname' => '昵称',
        'phone' => '手机号码',
        'email' => '邮箱地址',
        //多个用逗号隔开
        'role' => '角色',
        'role_text' => '角色',
        //0 禁用 forbid 1 启用 open
        'status' => '状态',
        'status_text' => '状态',
        'login_at' => '登录时间',
        'login_ip' => '登录IP',
        'login_time' => '登录次数',
        'remark' => '备注',
    ];
}
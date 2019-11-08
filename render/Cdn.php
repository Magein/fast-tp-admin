<?php

namespace magein\render\admin;


use think\Config;

trait Cdn
{
    protected function cdn()
    {
        // 配置文件中的
        $resource = Config::get('resource') ?: [];


        $js = [
            // 不稳定的CDN
            'layui_all_js' => 'https://cdn.90so.net/layui/2.4.5/layui.all.js',
            'require_js' => 'https://cdn.staticfile.org/require.js/2.3.6/require.min.js',
            'admin_main_js' => '/static/admin/js/main.js',
            'region_js' => '/static/js/region.js',
            // 多选框组件
            'form_select_js' => '/static/plugin/formSelect/formSelects-v4.js',
        ];

        $css = [
            // 稳定的CDN
            'font_awesome' => 'https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.css',
            // 不稳定的CDN
            'layui_css' => 'https://cdn.90so.net/layui/2.4.5/css/layui.css',
            'console_css' => '/static/theme/css/console.css',
            'jquery_ztree_css' => 'https://cdn.staticfile.org/zTree.v3/3.5.40/css/zTreeStyle/zTreeStyle.css',
            'form_select_css' => '/static/plugin/formSelect/formSelects-v4.css',
        ];

        $login = [
            'login_css' => '/static/theme/css/login.css',
        ];

        $require = [
            'require_css_js' => 'https://cdn.staticfile.org/require-css/0.1.10/css.min.js',
            'jquery_js' => 'https://cdn.staticfile.org/jquery/2.2.1/jquery.min.js',
            'jquery_ztree_js' => 'https://cdn.staticfile.org/zTree.v3/3.5.40/js/jquery.ztree.all.min',
            'jquery_migrate_js' => 'https://cdn.staticfile.org/jquery-migrate/1.3.0/jquery-migrate.min',
            'jquery_cookie_js' => 'https://cdn.staticfile.org/jquery-cookie/1.4.1/jquery.cookie.min',
        ];

        $resource['js'] = array_merge($js, $resource['js'] ?? []);
        $resource['css'] = array_merge($css, $resource['css'] ?? []);
        $resource['require'] = array_merge($require, $resource['require'] ?? []);
        $resource['login'] = array_merge($login, $resource['login'] ?? []);

        return $resource;
    }
}
<?php

namespace magein\render\admin;


use think\Config;

trait Cdn
{
    protected function cdn()
    {
        // 配置文件中的
        $resource = Config::get('resource') ?: [];

        return array_merge([
            // 不稳定的CDN
            'layui_css' => 'https://cdn.90so.net/layui/2.4.5/css/layui.css',
            'layui_all_js' => 'https://cdn.90so.net/layui/2.4.5/layui.all.js',
            // 稳定的CDN
            'font_awesome' => 'https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.css',
            'require_js' => 'https://cdn.staticfile.org/require.js/2.3.6/require.min.js',
            'require_css' => 'https://cdn.staticfile.org/require-css/0.1.10/css.min.js',
            'jquery' => 'https://cdn.staticfile.org/jquery/2.2.1/jquery.min.js',
            'jquery_ztree_js' => 'https://cdn.staticfile.org/zTree.v3/3.5.40/js/jquery.ztree.all.min',
            'jquery_ztree_css' => 'https://cdn.staticfile.org/zTree.v3/3.5.40/css/zTreeStyle/zTreeStyle.css',
            'jquery_migrate' => 'https://cdn.staticfile.org/jquery-migrate/1.3.0/jquery-migrate.min',
        ], $resource);
    }
}
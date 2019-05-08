<?php

namespace app\admin\controller;

use app\common\extend\Upload;
use magein\php_tools\extra\UEditor;
use think\Request;
use think\response\Json;

class Plugins
{

    public function editor()
    {
        $params = Request::instance()->param();
        $ue = new UEditor();
        $result = $ue->init($params);
        return Json::create($result);
    }

    /**
     * 字体图标选择器
     * @return \think\response\View
     */
    public function icon()
    {
        $field = input('field');
        return view('', ['field' => $field]);
    }

}

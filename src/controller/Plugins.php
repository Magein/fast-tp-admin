<?php

namespace app\admin\controller;

use magein\php_tools\extra\Upload;
use magein\php_tools\extra\UEditor;
use think\Request;
use think\response\Json;
use traits\controller\Jump;

class Plugins
{
    use Jump;

    public function editor()
    {
        $params = Request::instance()->param();
        $ue = new UEditor();
        $result = $ue->init($params);
        return Json::create($result);
    }

    /**
     * 文件上传
     */
    public function upload()
    {
        $result = Upload::instance()->image();
        if ($result) {
            $this->success('success', '', $result);
        }

        $this->error(Upload::instance()->getError());
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

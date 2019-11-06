<?php

namespace app\admin\controller;

use magein\php_tools\extra\Upload;
use magein\php_tools\extra\UEditor;
use magein\render\admin\Cdn;
use think\Request;
use think\response\Json;
use traits\controller\Jump;

class Plugin
{
    use Jump;
    use Cdn;

    /**
     * @return \think\Response|Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
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
        $field = Request::instance()->param('field');
        $size = Request::instance()->param('size');
        $ext = Request::instance()->param('ext');

        $result = Upload::instance()->file($field, $size * 1024, $ext);

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
        return view('admin@base/main/icon',
            [
                'field' => $field,
                'cdn' => $this->cdn()
            ]);
    }

}

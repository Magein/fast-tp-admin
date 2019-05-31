<?php

namespace app\admin\controller;


class Index extends Main
{
    /**
     * @return mixed
     */
    public function index()
    {
        return $this->fetch(self::VIEW_BASE . 'main/index');
    }
}
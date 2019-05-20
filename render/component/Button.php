<?php

namespace magein\render\admin\component;

use magein\php_tools\traits\ObjectInit;

class Button
{
    use ObjectInit;

    /**
     * 标题
     * @var string
     */
    private $title = '';

    /**
     * url
     * @var string
     */
    private $url = '';

    /**
     * 类型
     * @var string
     */
    private $type = '';

    /**
     * @var array
     */
    private $param = [];

    /**
     * @var string
     */
    private $icon = '';

    /**
     * @var array
     */
    private $attrs = [];

    /**
     * 页面打开
     */
    const TYPE_OPEN = 'open';

    /**
     * 模态对话框
     */
    const TYPE_MODAL = 'modal';

    /**
     * 重定向
     */
    const TYPE_REDIRECT = 'redirect';

    /**
     * Button constructor.
     * @param $title
     * @param string $url
     * @param string $type
     * @param array $param
     * @param string $icon
     * @param array $attrs
     */
    public function __construct($title = '按钮', $url = '', $type = 'open', $param = ['id' => '__id'], $icon = '', $attrs = [])
    {
        $this->setTitle($title);
        $this->setUrl($url);
        $this->setType($type);
        $this->setParam($param);
        $this->setIcon($icon);
        $this->setAttrs($attrs);
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }


    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getParam(): array
    {
        return $this->param;
    }

    /**
     * @param array $param
     * @return $this
     */
    public function setParam(array $param)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttrs(): array
    {
        return $this->attrs;
    }

    /**
     * @param array $attrs
     * @return $this
     */
    public function setAttrs(array $attrs)
    {
        $this->attrs = $attrs;

        return $this;
    }
}
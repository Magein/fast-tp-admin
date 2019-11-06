<?php

namespace magein\render\admin;

use magein\render\admin\component\Property;

class RenderForm
{
    /**
     * 要渲染的表单项
     * @var array
     */
    private $items = [];

    /**
     * 表单项中需要回填的数据
     * @var array
     */
    private $data = [];

    /**
     * 字典信息，用于描述表单项
     * @var array
     */
    private $dictionary = [];

    /**
     * FastForm constructor.
     * @param array $data
     * @param null $dictionary
     */
    public function __construct($data = [], $dictionary = null)
    {
        $this->setData($data);

        $this->setDictionary($dictionary);
    }

    /**
     * 这里是预留的信息，后续用于在后端生成html表单项
     * @return array
     */
    public function render()
    {
        return $this->items;
    }

    /**
     * 设置title
     * @param $dictionary
     */
    public function setDictionary($dictionary)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * 组合好后用于重置选项，如继承类，子类中不需要父类中那么多选项，调用items后，unset掉后在调用此方法重置即可
     * @param $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Property $property
     * @return array
     */
    public function append(Property $property)
    {
        $field = $property->getField();

        if (empty($field)) {
            return [];
        }

        /**
         * 设置默认的标题
         */
        if (empty($property->getTitle())) {
            $title = isset($this->dictionary[$field]) ? $this->dictionary[$field] : $property->getField();
            $property->setTitle($title);
        }

        /**
         * 设置默认值，如果传递的数据中包含该字段的值，则覆盖
         */
        if (isset($this->data[$field])) {
            $property->setValue($this->data[$field]);
        }

        if (empty($property->getPlaceholder())) {
            $property->setPlaceholder('请输入' . $property->getTitle());
        }

        $properties = $property->toArray();

        /**
         * 这里可以优化，通过传递参数是否加载传递的源数据
         *
         * 暂时全部传递
         */
        $properties['origin'] = $property->getOrigin();
        $properties['attrs'] = $property->getAttrs();

        $this->items[$field] = $properties;

        return $properties;
    }

    /**
     * @param $type
     * @param $field
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return Property
     */
    private function properties($type, $field, $value = '', $required = true, $title = '', $attrs = [])
    {
        $property = new Property();
        $property->setType($type);
        $property->setField($field);
        $property->setValue($value);
        $property->setTitle($title);
        $property->setRequired($required);
        $property->setAttrs($attrs);
        $this->append($property);

        return $property;
    }

    /**
     * @param $field
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setText($field, $value = '', $required = true, $title = '', $attrs = [])
    {
        $this->properties('text', $field, $value, $required, $title, $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setPassword($field, $value = '', $required = true, $title = '', $attrs = [])
    {
        $this->properties('password', $field, $value, $required, $title, $attrs);

        return $this;
    }


    /**
     * @param $field
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setDateTime($field, $value = '', $required = true, $title = '', $attrs = [])
    {
        $attrs['data-format'] = 'datetime';

        $this->properties('datetime', $field, $value, $required, $title, $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setDate($field, $value = '', $required = true, $title = '', $attrs = [])
    {
        $attrs['data-format'] = 'date';

        $this->properties('datetime', $field, $value, $required, $title, $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setTime($field, $value = '', $required = true, $title = '', $attrs = [])
    {
        $attrs['data-format'] = 'time';

        $this->properties('datetime', $field, $value, $required, $title, $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param string $dateFormat
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setDateTimeRange($field, $dateFormat = '', $value = '', $required = true, $title = '', $attrs = [])
    {
        $attrs['data-format'] = $dateFormat ? $dateFormat : 'datetime';
        $attrs['data-range'] = '~';

        $this->properties('datetime', $field, $value, $required, $title, $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @param array $attrs
     * @return $this
     */
    public function setHidden($field, $value = '', $attrs = [])
    {
        $this->properties('hidden', $field, $value, false, '', $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @param array $attrs
     * @return $this
     */
    public function setEditor($field, $value = '', $attrs = [])
    {
        $this->properties('editor', $field, $value, false, '', $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @param array $attrs
     * @return $this
     */
    public function setFile($field, $value = '', $attrs = [])
    {
        $this->properties('file', $field, $value, false, '', $attrs);

        return $this;
    }

    /**
     * @param $field
     * @param array $options
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setRegion($field, $options = [], $value = '', $required = true, $title = '', $attrs = [])
    {
        $property = new Property();
        $property->setType('region');
        $property->setField($field);
        $property->setTitle($title);
        $property->setOption($options);
        $property->setValue($value);
        $property->setRequired($required);
        $property->setAttrs($attrs);

        $this->append($property);

        return $this;
    }

    /**
     * @param $field
     * @param array $options
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setRadio($field, $options = [], $value = '', $required = true, $title = '', $attrs = [])
    {
        $property = new Property();
        $property->setType('radio');
        $property->setField($field);
        $property->setTitle($title);
        $property->setOption($options);
        $property->setValue($value);
        $property->setRequired($required);
        $property->setAttrs($attrs);

        $this->append($property);

        return $this;
    }

    /**
     * @param $field
     * @param array $options
     * @param mixed $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setCheckbox($field, $options = [], $value = '', $required = true, $title = '', $attrs = [])
    {
        $property = new Property();
        $property->setType('checkbox');
        $property->setField($field);
        $property->setTitle($title);
        $property->setOption($options);
        $property->setValue($value);
        $property->setRequired($required);
        $property->setAttrs($attrs);

        $this->append($property);

        return $this;
    }

    /**
     * @param $field
     * @param array $options
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setSelect($field, $options = [], $value = '', $required = true, $title = '', $attrs = [])
    {
        $property = new Property();
        $property->setType('select');
        $property->setField($field);
        $property->setTitle($title);
        $property->setOption($options);
        $property->setValue($value);
        $property->setRequired($required);
        $property->setAttrs($attrs);

        $this->append($property);

        return $this;
    }

    /**
     * @param $field
     * @param array $options
     * @param string $value
     * @param bool $required
     * @param string $title
     * @param array $attrs
     * @return $this
     */
    public function setSelectChecked($field, $options = [], $value = '', $required = true, $title = '', $attrs = [])
    {
        $property = new Property();
        $property->setType('select-checked');
        $property->setField($field);
        $property->setTitle($title);
        $property->setOption($options);
        $property->setValue($value);
        $property->setRequired($required);
        $property->setAttrs($attrs);

        $this->append($property);

        return $this;
    }

    /**
     * @param $fields
     * @return $this
     */
    public function setTexts($fields)
    {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        if ($fields) {
            foreach ($fields as $field) {
                $this->setText($field);
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param string|string $title
     * @param bool $required
     * @param mixed $value
     * @param array $attrs
     * @return $this
     */
    public function setTextArea($field, $value = '', $required = true, $title = '', $attrs = [])
    {
        $this->properties('textArea', $field, $value, $required, $title, $attrs);

        return $this;
    }
}
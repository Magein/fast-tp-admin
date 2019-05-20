<?php

namespace magein\render\admin;

use magein\render\admin\component\Button;
use magein\render\admin\component\Property;
use magein\php_tools\common\Variable;
use magein\php_tools\object\QueryResult;
use magein\php_tools\think\Dictionary;
use magein\php_tools\think\Logic;
use think\Request;
use think\Validate;

trait FastBuild
{
    /**
     * 数据列表
     * @var array
     */
    protected $list = [];

    /**
     * 分页参数
     * @var array
     */
    protected $page = [];

    /**
     * 追加查询数据中不存在的数据信息
     * @var array
     */
    protected $append = [];

    /**
     * 自动开启判断表头信息中是否包含 _text字段信息，包含的话则自动追加append参数且需要在model中生成
     * @var bool
     */
    protected $autoAppend = true;

    /**
     * 关联预查询的字段信息，需要在model中生成
     * @var array
     */
    protected $with = [];

    /**
     * 这里是页面中编辑，删除等使用的键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 隐藏全选框
     * @var bool
     */
    protected $hiddenCheckbox = false;

    /**
     * 展示为图片的字段信息
     * @var array
     */
    protected $images = [];

    /**
     * 左上角的按钮
     * @var array
     */
    protected $leftTopButtons = [
        'add' => [
            'title' => '新增',
            'url' => 'add',
            'type' => 'modal',
            'param' => [],
            'icon' => 'layui-icon layui-icon-add-1',
            'cla' => '',
            'attrs' => ''
        ],
        'del' => [
            'title' => '批量删除',
            'url' => 'del',
            'type' => 'del',
            'param' => [],
            'icon' => 'layui-icon layui-icon-delete',
            'cla' => 'layui-btn layui-btn-sm layui-btn-danger',
            'attrs' => ''
        ],
    ];

    /**
     * @var array
     */
    protected $operationButtons = [
        'edit' => [
            'title' => '编辑',
            'url' => 'edit',
            'type' => '',
            'param' => ['id' => '__id__'],
            'icon' => '',
            'cla' => '',
            'attrs' => ''
        ],
        'del' => [
            'title' => '删除',
            'url' => 'del',
            'type' => 'del',
            'param' => ['id' => '__id__'],
            'icon' => '',
            'cla' => 'layui-btn-danger',
            'attrs' => ''
        ]
    ];

    protected $tips = [];

    /**
     * @var int
     */
    protected $operationColWidth = 120;

    /**
     * @var array
     */
    protected $condition = [];

    /**
     * @var int
     */
    protected $limit = 15;

    /**
     * @var string
     */
    protected $order = 'id desc';

    /**
     * @var array
     */
    protected $attr = [];

    /**
     * 获取对应的类
     * 可选项: logic(业务类),dictionary(字典类),validate(验证类)
     * @param string $type
     * @param string 对应的类名
     * @param string 命名空间
     * @return null
     */
    protected function getClass($type = 'logic', $className = '', $namespace = 'app\component')
    {
        if (empty($className)) {
            $className = static::class;
        }

        // 兼容linux下的路径
        $className = preg_replace('/\\\/', '/', $className);

        // 兼容使用直接传递逻辑类的命令空间模式
        $class = preg_replace('/Logic/', '', basename($className));

        $variable = new Variable();
        // 目录使用下划线方式
        $dirName = $variable->transToUnderline($class);

        // 类型使用帕斯卡方式
        $class = $variable->transToPascal($class);

        // 获取命名空间
        $namespace .= '\\' . $dirName . '\\' . $class . ucfirst($type);

        if (class_exists($namespace)) {
            return new $namespace();
        }

        return null;
    }

    /**
     * 获取业务类
     * @param $className
     * @return Logic|null
     */
    protected function getLogic($className = '')
    {
        return $this->getClass(Constant::CLASS_TYPE_LOGIC, $className);
    }

    /**
     * @param $className
     * @return Validate|null
     */
    protected function getValidate($className = '')
    {
        return $this->getClass(Constant::CLASS_TYPE_VALIDATE, $className);
    }

    /**
     * @param $className
     * @return Dictionary|null
     */
    protected function getDictionary($className = '')
    {
        return $this->getClass(Constant::CLASS_TYPE_DICTIONARY, $className);
    }

    /**
     * 获取字典的数据信息
     * @return array
     */
    protected function getWord()
    {
        static $dictionary;

        if (empty($dictionary)) {
            $dictionary = $this->getDictionary();
        }

        if ($dictionary) {
            return $dictionary->word;
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getLeftTopButton()
    {
        return $this->leftTopButtons;
    }

    /**
     * @return array
     */
    protected function getOperationButton()
    {
        return $this->operationButtons;
    }

    /**
     * @param string $title
     * @param string $url
     * @param string $type
     * @param array $param
     * @param string $icon
     * @param string $cla
     * @param array $attrs
     * @return array
     */
    protected function setButton($title = '按钮', $url = '', $type = Button::TYPE_OPEN, $param = ['id' => '__id__'], $icon = '', $cla = '', $attrs = [])
    {
        $toString = '';
        if ($attrs) {
            foreach ($attrs as $key => $item) {
                $toString .= $key . '="' . $item . '"';
            }
        }

        return [
            'title' => $title,
            'url' => $url,
            'type' => $type,
            'param' => $param,
            'icon' => $icon,
            'cla' => $cla,
            'attrs' => $toString
        ];
    }

    /**
     * @param string $title
     * @param string $url
     * @param array $param
     * @param string $icon
     * @param string $cla
     * @param array $attrs
     * @return array
     */
    protected function setButtonModal($title = '按钮', $url = '', $param = ['id' => '__id__'], $icon = '', $cla = '', $attrs = [])
    {
        return $this->setButton($title, $url, Button::TYPE_MODAL, $param, $icon, $cla, $attrs);
    }

    protected function imageTemplate($field, $data)
    {
        return <<<EOF
<div>
{{#  if(typeof d.$field ==="string"){ }}
{{#  var images=d.$field.split(',')}}
{{#  layui.each(images, function(index, item){ }}
<img src="{{item}}" class="table-image"/>
{{#  }); }}
{{#  } }}
</div>
EOF;
    }

    /**
     * @return array
     */
    protected function getTips()
    {
        return $this->tips;
    }

    /**
     * 获取数据列表
     * @param array $condition
     * @param string $query_type
     * @return QueryResult
     */
    protected function getList($condition = [], $query_type = Constant::QUERY_TYPE_PAGINATE)
    {
        $classLogic = $this->getLogic();

        $items = [];
        $page = [];

        if ($classLogic && method_exists($classLogic, $query_type)) {

            if ($this->with) {
                $classLogic->setWith($this->with);
            }

            if ($this->append) {
                $classLogic->setAppendAttr($this->append);
            }

            if ($condition) {
                $classLogic->setCondition($condition);
            }

            $classLogic->setOrder($this->order);

            $items = call_user_func_array([$classLogic, $query_type], [$this->limit]);

            $page = $classLogic->getPageParams();
        }

        return new QueryResult($items ?: [], $page);
    }

    /**
     * @param string $id
     * @return array|bool
     */
    public function getData($id = '')
    {
        $data = [];

        if ($id) {

            $class = $this->getLogic();

            if ($this->with) {
                $class->setWith($this->with);
            }

            if ($this->append) {
                $class->setAppendAttr($this->append);
            }

            $data = $class->get($id);
        }

        return $data;
    }

    /**
     * 要渲染的头部信息
     * @return array
     */
    protected function header()
    {
        return [];
    }

    /**
     * 构建表格数据
     * @param null $callback 处理表头的回调函数
     * @return array
     */
    protected function buildTable($callback = null)
    {
        $headers = $this->header();

        if ($headers) {

            // 获取字典
            $dictionary = $this->getDictionary();

            $property = 'word';
            if ($dictionary && property_exists($dictionary, $property)) {
                $word = $dictionary->$property;
            } else {
                $word = [];
            }

            $word = array_merge((new Dictionary())->word, $word);

            foreach ($headers as &$item) {

                if (!is_array($item)) {
                    $item = [
                        'field' => $item
                    ];
                }

                $field = $item['field'];

                if ($this->autoAppend && preg_match('/_text/', $field)) {
                    $this->append[] = $field;
                }

                $title = isset($item['title']) ? $item['title'] : '';

                if (empty($title)) {
                    $title = isset($word[$field]) ? $word[$field] : '';
                }

                if ($this->images && in_array($item['field'], $this->images)) {
                    $item['templet'] = $this->imageTemplate($field, $this->images);
                }

                if ($callback) {
                    $item = call_user_func($callback, $item);
                }

                $item['title'] = $title;

            }
            unset($item);
        }

        if (false === $this->hiddenCheckbox) {
            array_unshift($headers, [
                'type' => 'checkbox',
                'field' => $this->primaryKey
            ]);
        }

        if ($this->operationButtons) {
            $headers[] = [
                'fixed' => 'right',
                'title' => '操作',
                'toolbar' => '#operationButtons',
                'width' => $this->operationColWidth,
            ];
        }

        return $headers;
    }

    /**
     * @return array
     */
    protected function form()
    {
        return [];
    }

    /**
     * @param $data
     * @return Property
     */
    protected function property($data)
    {
        if ($data instanceof Property) {
            return $data;
        }

        $property = new Property();

        // 识别一个字符串
        if (is_string($data)) {
            $data = [
                'field' => $data
            ];
        } else {

            if (!isset($data['field'])) {
                /**
                 * 下面是懒到极致的写法
                 *
                 * 不建议用，不限制用
                 */
                $field = isset($data[0]) ? $data[0] : '';
                $type = 'select';
                $option = [];

                /**
                 * 兼容
                 * ['scene',[1=>'公司',2=>'家']]
                 * ['intro','textArea']
                 * ['name','text']
                 */
                $second = isset($data[1]) ? $data[1] : '';
                if ($second) {
                    if (is_array($second)) {
                        $option = $second;
                    } elseif (is_string($second)) {
                        $type = $second;
                    }
                }

                /**
                 * 兼容
                 * ['scene','select',[1=>'公司',2=>'家']]
                 * ['scene','radio',[1=>'公司',2=>'家']]
                 */
                $third = isset($data[2]) ? $data[2] : [];
                if ($third) {
                    $option = $third;
                }

                $data = [
                    'type' => $type,
                    'option' => $option,
                    'field' => $field
                ];
            }
        }

        // 合并设置的默认属性
        $data = array_merge($this->attr, $data);

        $property->init($data);

        return $property;
    }

    /**
     * @param $items
     * @param array $data
     * @param array $dictionary
     * @return array|RenderForm
     */
    protected function buildForm($items, $data = [], $dictionary = [])
    {
        $dictionary = $dictionary ?: $this->getWord();

        $render = new RenderForm($data, $dictionary);

        if (!is_array($items) || empty($items)) {
            return $render;
        }

        foreach ($items as $item) {
            $render->append($this->property($item));
        }

        return $render;
    }

    /**
     * 保存数据
     * @param array $data 保存的数据
     * @param null $validate 使用的验证类
     * @return bool|false|int
     */
    protected function save($data = [], $validate = null)
    {
        if (empty($data)) {
            $this->operationAfter(false, $data, '');
        }

        if ($validate === null) {
            $validate = $this->getClass(Constant::CLASS_TYPE_VALIDATE);
        }

        /**
         * @var Logic $class
         */
        $class = $this->getClass();

        if ($validate) {
            $class->setValidate($validate);
        }

        $result = $class->save($data);

        $this->operationAfter($result, $data, $class);

        return $result;
    }

    /**
     * 操作数据后的动作
     * @param mixed $result
     * @param array $data
     * @param Logic $class
     */
    protected function operationAfter($result, $data = [], $class = null)
    {

    }

    /**
     * @return array
     */
    protected function search()
    {
        return [];
    }

    /**
     * 获取赛选的条件信息
     * @param array $data
     * @return array
     */
    public function getCondition($data = [])
    {
        $condition = [];
        if (empty($data)) {
            $data = Request::instance()->get();
        }

        /**
         * 参数所用的表达式
         */
        $express = isset($data['express']) ? $data['express'] : [];
        unset($data['express']);

        if ($data) {
            foreach ($data as $name => $value) {

                if (isset($express[$name]) && $express[$name] && $value !== '') {

                    switch ($express[$name]) {
                        case 'eq':
                            $condition[$name] = $value;
                            break;
                        case 'like':
                            $condition[$name] = ['like', '%' . $value . '%'];
                            break;
                        case 'in':
                            $condition[$name] = ['in', $value];
                            break;
                    }
                }

            }
        }

        return $condition;
    }

    /**
     * 更新单个字段信息，如排序，状态等
     * @param $id
     * @param $field
     * @param null $value
     * @return bool|false|int
     */
    public function updateField($id, $field, $value = null)
    {
        if (empty($id) || empty($field) || $value === null) {
            return false;
        }

        /**
         * @var Logic $classLogic
         */
        $classLogic = $this->getClass(Constant::CLASS_TYPE_LOGIC);

        $result = $classLogic->updateField($field, $value, $id);

        $this->operationAfter($result, [], $classLogic);

        return $result;
    }

    /**
     * 删除数据
     * @param $id
     * @return bool|int
     */
    public function del($id)
    {
        if (empty($id)) {
            return false;
        }

        /**
         * @var Logic $classLogic
         */
        $classLogic = $this->getClass(Constant::CLASS_TYPE_LOGIC);

        $result = $classLogic->delete($id);

        $this->operationAfter($result, [], $classLogic);

        return $result;
    }
}
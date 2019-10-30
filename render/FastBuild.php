<?php

namespace magein\render\admin;

use magein\php_tools\common\Csv;
use magein\php_tools\common\UnixTime;
use magein\render\admin\component\Button;
use magein\render\admin\component\Property;
use magein\php_tools\common\Variable;
use magein\php_tools\object\QueryResult;
use magein\php_tools\think\Dictionary;
use magein\php_tools\think\Logic;
use think\Exception;
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
     * 数据对象，编辑的时候使用
     * @var array
     */
    protected $data = [];

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
     * 只展示数据
     * @var bool
     */
    protected $isShow = false;

    /**
     * 展示为图片的字段信息
     * @var array
     */
    protected $images = [];

    /**
     * 按钮类型
     * @var string
     */
    protected $buttonType = Button::TYPE_MODAL;

    /**
     * 设置为false则关闭
     * @var bool|string
     */
    protected $leftTopButton = true;

    /**
     * 设置为false则关闭
     * @var bool|string
     */
    protected $operationButton = true;

    /**
     * 页面提示信息
     * @var array
     */
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
     * @var string
     */
    protected $searchTime = '';

    /**
     * @var bool
     */
    protected $debug = false;

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
        if (false === $this->leftTopButton || $this->isShow) {
            return [];
        }


        $default = [
            'add' => $this->setButton('新增', 'add', [], $this->buttonType, 'layui-icon layui-icon-add-1'),
            'del' => $this->setButton('批量删除', 'del', [], 'del', 'layui-icon layui-icon-delete', 'layui-btn layui-btn-sm layui-btn-danger'),
        ];

        if (is_string($this->leftTopButton)) {
            return isset($default[$this->leftTopButton]) ? [$default[$this->leftTopButton]] : [];
        }

        return $default;
    }

    /**
     * @return array|mixed
     */
    protected function getOperationButton()
    {
        if (false === $this->operationButton || $this->isShow) {
            return [];
        }

        $default = [
            'edit' => $this->setButton('编辑', 'edit', ['id' => '__id__'], $this->buttonType),
            'del' => $this->setButton('删除', 'del', ['id' => '__id__'], 'del', '', 'layui-btn-danger'),
        ];

        if (is_string($this->operationButton)) {
            return isset($default[$this->operationButton]) ? [$default[$this->operationButton]] : [];
        }

        return $default;
    }

    /**
     * @param string $title
     * @param string $url 这里的按钮不使用url方法转化，也不在前段使用{:url}转化，如果需要请，自己在传递参数的时候调用url方法转化
     * @param string $type
     * @param array $param
     * @param string $icon
     * @param string $cla
     * @param array $attrs
     * @return array
     */
    protected function setButton($title = '按钮', $url = '', $param = ['id' => '__id__'], $type = Button::TYPE_OPEN, $icon = '', $cla = '', $attrs = [])
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
        return $this->setButton($title, $url, $param, Button::TYPE_MODAL, $icon, $cla, $attrs);
    }

    /**
     * @param string $title
     * @param string $url
     * @param string $confirm
     * @param array $param
     * @param string $icon
     * @param string $cla
     * @return array
     */
    protected function setButtonConfirm($title = '按钮', $url = '', $confirm = '', $param = ['id' => '__id__'], $icon = '', $cla = '')
    {
        return $this->setButton($title, $url, $param, Button::TYPE_MODAL, $icon, $cla, ['data-confirm' => $confirm ?: '请您再次确认？']);
    }

    /**
     * 图片模板
     * @param $field
     * @return string
     */
    protected function imageTemplate($field)
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
     * 开关模板
     * 包含字段信息：field字段名称  option 开关名称  value  默认选中的值
     * @param array $data
     * @param bool $toArray
     * @return string|array
     */
    protected function switchTemplate($data, $toArray = false)
    {
        $field = isset($data['field']) ? $data['field'] : '';

        if (empty($field)) {
            return '';
        }

        $option = isset($data['option']) ? $data['option'] : ['开', '关'];

        if (is_array($option)) {
            $option = implode('|', $option);
        }

        $check_value = isset($data['value']) ? (int)$data['value'] : 1;

        $template = <<<EOF
<div>

{{# if(d.$field==$check_value){ }}
 <input type="checkbox" name="$field" data-id="{{d.id}}" lay-skin="switch" lay-text="$option"checked>
{{# } else {  }}
 <input type="checkbox" name="$field" data-id="{{d.id}}" lay-skin="switch" lay-text="$option">
{{# }  }}
</div>
EOF;

        if ($toArray) {
            return [
                'field' => $field,
                'templet' => $template,
            ];
        }

        return $template;
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
    protected function getData($id = '')
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

        $this->data = $data;

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
     * @param $headers
     * @return array
     */
    protected function buildTable($headers = [], $callback = null)
    {
        if (empty($headers)) {
            $headers = $this->header();
        }

        if ($headers) {

            // 获取字典
            $word = $this->getWord();

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
                    $item['templet'] = $this->imageTemplate($field);
                }

                if (isset($item['type']) && $item['type'] == 'switch') {
                    $item['templet'] = $this->switchTemplate($item);
                }

                if ($callback) {
                    $item = call_user_func($callback, $item);
                }

                $item['title'] = $title;

            }
            unset($item);
        }

        if (false === $this->hiddenCheckbox && false === $this->isShow) {
            array_unshift($headers, [
                'type' => 'checkbox',
                'field' => $this->primaryKey
            ]);
        }

        if ($this->operationButton && false === $this->isShow) {
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
            } else {
                if (isset($data['option'])) {
                    $data['type'] = isset($data['type']) ? $data['type'] : 'select';
                }
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
     * @param array $data
     * @return array
     */
    protected function search($data = [])
    {
        return $data;
    }

    /**
     * @param array|string $params
     * @param array $other
     * @return array
     */
    protected function dateSearch($params, $other = [])
    {
        $trans = function ($field, $title = '', $type = 'datetime') {
            return [
                'field' => $field,
                'type' => $type,
                'title' => $title
            ];
        };

        $data = [];
        if ($params) {
            if (is_array($params)) {
                $data[] = [
                    'field' => 'time_field',
                    'type' => 'select',
                    'option' => $params,
                    'title' => '时间类型'
                ];
            }

            $data[] = $trans('start_time', '起始时间');
            $data[] = $trans('end_time', '终止时间');
        }

        return array_merge($data, $other);
    }

    /**
     * 获取筛选条件信息
     *
     * 筛选条件的值以及筛选所用的表达式有两种来源
     *
     * 1. 页面传递的搜索参数
     * 2. search方法中定义的参数
     *
     * 前端页面传递了值后，在从search中获取获取默认值
     *
     * @param array $data
     * @return array
     */
    protected function getCondition($data = [])
    {
        // 没有传递值的时候，则自动从连接中获取
        if (empty($data)) {
            $data = Request::instance()->get();
        }

        // 自动过滤掉分页参数
        unset($data['page_id'], $data['page_size']);

        // 前端没有传递值的时候，则验证是否声明了搜索参数，取里面的默认值以及表达式
        $search = $this->search();
        if ($search) {
            foreach ($search as $item) {
                $field = isset($item['field']) ? $item['field'] : '';
                $value = isset($item['value']) ? $item['value'] : null;
                // 如果search中指定了默认值，则验证data中是否设置，如果没有设置，则自动追加
                if ($field && $value !== null) {
                    if (!isset($data[$field])) {
                        $data[$field] = $item['value'];
                        $data['express'][$field] = isset($item['express']) ? $item['express'] : 'eq';
                    }
                }
            }
        }


        if (empty($data)) {
            return [];
        }
        /**
         * 参数所用的表达式
         */
        $express = isset($data['express']) ? $data['express'] : [];
        unset($data['express']);

        $condition = [];
        /**
         * 处理时间，
         * 处理多字段作为筛选时间，值只有一个，
         *
         * 则一个表示使用的字段信息，另一个表示时间值
         */
        if (isset($data['time_field']) && $data['time_field']) {

            $start = UnixTime::instance()->unix($data['start_time']) ?: 0;
            $end = UnixTime::instance()->unix($data['end_time']) ?: 0;

            if ($start && $end) {
                $condition[$data['time_field']] = ['between', [$start, $end]];
            }

            unset($data['time_field'], $data['end_time'], $data['start_time']);
        }

        if ($data) {
            foreach ($data as $name => $value) {

                $name = trim($name);

                $exp = isset($express[$name]) ? $express[$name] : 'eq';

                if ($value !== '') {
                    switch (trim($exp)) {
                        case 'eq':
                            $condition[$name] = $value;
                            break;
                        case 'gt':
                            $condition[$name] = ['gt', $value];
                            break;
                        case 'egt':
                            $condition[$name] = ['egt', $value];
                            break;
                        case 'lt':
                            $condition[$name] = ['lt', $value];
                            break;
                        case 'elt':
                            $condition[$name] = ['elt', $value];
                            break;
                        case 'like':
                            $condition[$name] = ['like', '%' . $value . '%'];
                            break;
                        case 'in':
                            $condition[$name] = ['in', $value];
                            break;
                        case 'between':
                            $condition[$name] = ['between', $value];
                            break;
                        case 'day':
                            $value = substr($value, 0, 10);

                            $start = UnixTime::instance()->startDay($value);
                            $end = UnixTime::instance()->endDay($value);
                            $condition[$name] = ['between', [$start, $end]];
                            break;
                        case 'range':
                            $value = explode('~', $value);
                            if (count($value) == 2) {
                                $start = UnixTime::instance()->startDay(trim($value[0]));
                                $end = UnixTime::instance()->endDay(trim($value[1]));
                            } else {
                                $start = UnixTime::instance()->startDay();
                                $end = UnixTime::instance()->endDay();
                            }
                            $condition[$name] = ['between', [$start, $end]];
                            break;
                        case 'exp':
                            $condition[$name] = ['exp', $value];
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

    /**
     * 下载数据
     */
    public function download()
    {
        $data = $this->buildTable($this->header());

        $result = $this->getList($this->getCondition(), Constant::QUERY_TYPE_SELECT);

        $list = $result->getList();

        $header = [];
        if ($data) {
            foreach ($data as $item) {
                if ($item['field']) {
                    $header[$item['field']] = $item['title'];
                }
            }
        }

        $csv = new Csv();
        $records = $csv->correcting($header, $list);
        $csv->export($header, $records);
    }
}
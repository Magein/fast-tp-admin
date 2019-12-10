let table = layui.table;

if (table === undefined) {
    console.log('没有获取到layui.table对象，请先加载layui.all.js');
}

layui.config({
    base: '/static/plugin/layui-extend/'
});

function Render() {

    let _this = this;

    // 表格唯一索引
    _this.id = 'data-list';
    // 单元格编辑时事件请求地址
    _this.updateFieldUrl = '';
    // 渲染所需要的参数
    _this.params = {};

    /**
     * 渲染普通表格
     */
    this.table = function () {

        let elem = _this.params.elem ? _this.params.elem : 'data-list';
        let height = _this.params.height ? _this.params.height : null;
        let limit = _this.params.page.per_page ? _this.params.per_page : 99999999;
        let header = _this.params.header ? _this.params.header : {};
        let list = _this.params.list ? _this.params.list : {};
        let even = _this.params.even ? _this.params.even : true;

        if (height == "0") {
            height = 'full-20';
        }

        //执行渲染
        table.render({
            id: _this.id,
            //指定原始表格元素选择器
            elem: '#' + elem,
            // 分页参数
            page: false,
            limit: limit,
            //容器高度
            height: height,
            text: {
                none: '暂无相关数据'
            },
            toolbar: '#leftTopButtons',
            defaultToolbar: ['filter', 'exports'],
            cols: [header],
            data: list,
            //开启隔行背景
            even: even,
            //小尺寸的表格
            size: 'lg',
        });
    };

    this.tableEvent = function () {

        /**
         * 监听工具栏事件
         */
        table.on('toolbar(' + this.id + ')', function (obj) {
            if (obj.event === 'LAYTABLE_COLS' || obj.event === 'LAYTABLE_EXPORT') {
                return false;
            }

            let _this = $(this);

            let callback = function () {
                let data = getData(_this, obj);
                transData(data);
                return false;
            };

            let confirm = $(this).data('confirm');

            if (confirm) {
                $.msg.confirm(confirm, function () {
                    return callback();
                });
                return false;
            }

            return callback();
        });

        /**
         * 监听操作栏事件
         */
        table.on('tool(' + this.id + ')', function (obj) {

            let _this = $(this);

            let callback = function () {
                let data = getData(_this, obj);
                transData(data);
                return false;
            };

            let confirm = $(this).data('confirm');

            if (confirm) {
                $.msg.confirm(confirm, function () {
                    return callback();
                });
                return false;
            }

            return callback();
        });

        /**
         * 监听单元格编辑
         */
        table.on('edit(' + this.id + ')', function (obj) {
            let data = {};
            data.type = 'update';

            if (_this.updateFieldUrl === '') {
                console.log('快速更新请求链接为空');
                return false;
            }
            data.url = _this.updateFieldUrl;
            data.param = {
                id: obj.data.id,
                field: obj.field,
                value: obj.value,
            };
            transData(data);
        });

        /**
         * 获取点击事件可以得到的相关数值
         * @param _this 点击的对象 用于获取额外属性
         * @param obj   layui-table 监听事件返回的参数
         */
        function getData(_this, obj) {
            let data = {};
            let param = _this.data('param');
            let postData = {};
            if (param) {
                for (let i in param) {
                    let value = param[i];
                    let reg = /__[\w]+__/;
                    if (reg.test(value)) {
                        value = value.slice(2, -2);
                        try {
                            postData[i] = $.trim(obj.data[value]);
                        } catch (e) {

                        }
                    } else {
                        postData[i] = param[i];
                    }
                }
            }

            data.title = _this.data('title');
            data.type = _this.data('type');
            data.url = _this.data('url');
            data.param = postData;

            return data;
        }

        function transData(data, callback) {
            let url = data.url;
            if (data.param) {
                url += '?';
                for (let i in data.param) {
                    url += i + '=' + data.param[i] + '&';
                }
            }
            let reg = /\?$/;
            if (reg.test(url)) {
                url = url.replace('?', '');
            }

            // 获取批量选中的值
            let ids = _this.getSelectedId();

            switch (data.type) {
                case 'modal':
                    // 模态对话框
                    data.param.modal = 1;
                    if (data.param.id && ids) {
                        data.param.id.concat(ids);
                    } else {
                        console.log(ids);
                        data.param.id = ids;
                    }
                    $.form.modal(data.url, data.param, data.title);
                    break;
                case 'update':
                    $.form.load(data.url, data.param, 'post', callback);
                    break;
                case 'del':
                    if (data.param.id && ids) {
                        data.param.id.concat(ids);
                    } else {
                        console.log(ids);
                        data.param.id = ids;
                    }
                    $.form.load(data.url, data.param, 'get', callback);
                    break;
                case 'open':
                    window.location.href = url;
                    break;
                case 'redirect':
                    window.open(url);
                    break;
                // 树结构展示
                case 'tree':
                    let first = $('tbody [data-pid]').eq(1).parents('tr');
                    let status = 'hide';
                    if (first.hasClass('layui-hide')) {
                        status = 'show';
                    }
                    if (status === 'show') {
                        $('tbody [data-pid]').parents('tr').removeClass('layui-hide');
                    } else {
                        $('tbody [data-pid]').parents('tr').addClass('layui-hide');
                        $('tbody [data-pid=0]').parents('tr').removeClass('layui-hide');
                    }
                    break;
                default:
                    $.form.load(data.url, data.param, 'post', callback);
            }
            return false;
        }

        //监听指定开关
        window.form.on('switch()', function (data) {
            let elem = data.elem;
            let name = $(elem).attr('name');
            let value = this.checked ? 1 : 0;
            let id = $(elem).data('id');

            $.form.load(_this.updateFieldUrl, {id: id, field: name, value: value}, 'post');
        });
    };
    /**
     * 树状表格
     */
    this.treeTable = function () {
        let header = _this.params.header;
        if (header) {
            let level = 1;
            for (let i in header) {
                let data = header[i];
                if (category_tree_list.indexOf(data.field) >= 0) {
                    data.templet = '<div>' +
                        '<div class="category-switch" data-id="{{ d.id }}" data-pid="{{ d.pid }}" data-node="{{ d.node }}" style="padding-left: {{ (d.level-1)*25 }}px;">' +
                        '{{# if (d.level == ' + level + '){ }}' +
                        '<i class="fa fa-arrows"></i>{{# }; }} ' +
                        '{{ d.' + data.field + '.replace(/\|--/g,"") }}' +
                        '</div>' +
                        '</div>';
                    $('body').on('click', '.category-switch', function () {
                        let node = $(this).data('node');
                        if (node) {
                            // 隐藏状态
                            let obj = $('[data-node^=' + node + '_]');
                            obj.parents('tr').each(function () {
                                if ($(this).hasClass('layui-hide')) {
                                    $(this).removeClass('layui-hide');
                                } else {
                                    $(this).addClass('layui-hide');
                                }
                            });
                        }
                    });
                }
                header[i] = data;
            }
        }
        _this.params.header = header;

        _this.table();
    };

    /**
     * 数据分页
     */
    this.page = function () {

        let data = _this.params.page;
        let limit = data.per_page ? data.per_page : 999;
        let total = data.total ? data.total : 0;
        let current_page = data.current_page ? data.current_page : 1;
        let var_page = data.var_page ? data.var_page : 1;

        layui.laypage.render({
            // 分页容器
            elem: 'page-container',
            layout: ['prev', 'page', 'next', 'count', 'limit'],
            //数据总数，从服务端得到
            count: total,
            // 每页数量
            limit: limit,
            limits: [15, 30, 50, 100, 500],
            // 当前页
            curr: current_page,
            prev: '<',
            next: '>',
            jump: function (obj, first) {

                //首次不执行
                if (first) {
                    return false;
                }

                let href = window.location.href;

                if (href.indexOf('?') === -1) {
                    href += '?';
                }

                /**
                 * 处理分页字段
                 */
                let var_page = '&' + var_page + '=';
                if (href.indexOf(var_page) === -1) {
                    href += var_page + obj.curr;
                } else {
                    href = href.replace(new RegExp(var_page + '[0-9]+'), var_page + obj.curr);
                }

                /**
                 * 处理每页显示数量
                 */
                let page_size = '&page_size=';
                if (href.indexOf(page_size) === -1) {
                    href += page_size + obj.limit;
                } else {
                    href = href.replace(new RegExp(page_size + '[0-9]+'), page_size + obj.limit);
                }

                window.location.href = href;
            }
        });
    };
    /**
     * 获取表格选中的数据
     * @param field 获取选中的字段值
     * @return {Array}
     */
    this.getSelectedId = function (field) {
        if (!field) {
            field = 'id';
        }
        let all = table.checkStatus(this.id);
        let data = all.data;
        let ids = [];
        if (data) {
            for (let i in data) {
                ids.push(data[i][field]);
            }
        }
        return ids;
    };
    /**
     * 下拉框筛选组件
     * @param callback
     */
    this.formSelect = function (callback) {
        layui.use(['jquery', 'formSelects'], function () {
            let formSelects = layui.formSelects;
            if (callback) {
                callback(formSelects);
            }
        });
    }
};

/**
 * 绑定时间插件
 */
(function () {
    let date_time_input_num = 1;
    $('input.date-time-field').each(function () {
        /**
         * 这里使用了动态设置id的属性,使用样式(.)选择器的话多个时间框的时候只有第一个能用
         * @type {string}
         */
        let id = 'datetime-field-' + date_time_input_num;
        $(this).attr('id', id);

        /**
         * 获取时间格式,通过input的date-format设置
         * @type {jQuery}
         */
        let dateFormat = $(this).data('format');
        if (dateFormat === '') {
            dateFormat = 'datetime';
        }

        /**
         * 是否使用时间区间
         * @type {jQuery}
         */
        let range = $(this).data('range');
        if (range === '') {
            range = false;
        }

        /**
         * 绑定时间插件
         */
        //日期时间范围
        laydate.render({
            elem: '#' + id,
            type: dateFormat,
            range: range
        });

        /**
         * 用于区别id值
         */
        date_time_input_num++;
    })
})();

/**
 * 城市数据联动，并且回填数据
 *
 * 1. 用于表单回填，表单回填的值从 data中去
 * 2. 回填搜索框的值，搜索框的值从 url中获取
 */
let province_id = parseInt($("[name=province_id]").val());
window.form.on('select(province_id)', function (data) {
    province_id = data.value;
    changeCity(province_id);
});
window.form.on('select(city_id)', function (data) {
    changeArea(province_id, data.value);
});

getProvince(province_id);

/**
 * 获取省份数据
 */
function getProvince(province_id) {

    let obj = $('[name=province_id]');

    if (obj.find('option').length > 1) {
        return false;
    }

    let option = '<option value="">请选择省份</option>';
    for (let i in REGION) {
        let data = REGION[i];
        option += '<option value="' + data.id + '">' + data.name + '</option>';
    }

    obj.html(option);

    // 回填数据
    if (province_id) {
        $('[name=province_id] option[value=' + province_id + ']').attr('selected', 'selected');
    }

    window.form.render();
}

/**
 * 切换城市
 */
function changeCity(province_id, value) {

    if (!province_id) {
        return false;
    }

    let data = REGION[province_id];
    if (data === undefined) {
        return false;
    }

    let children = data.children;

    if (children === undefined) {
        return false;
    }

    let option = '<option value="">请选择市</option>';
    for (let i in children) {
        let data = children[i];
        option += '<option value="' + data.id + '">' + data.name + '</option>';
    }

    $('[name=city_id]').html(option);

    // 回填数据
    if (value) {
        $('[name=city_id] option[value=' + value + ']').attr('selected', 'selected');
    }

    window.form.render();
}

/**
 * 切换区域信息
 * @param province_id
 * @param city_id
 * @param value
 * @returns {boolean}
 */
function changeArea(province_id, city_id, value) {

    if (!province_id || !city_id) {
        return false;
    }

    let children = REGION[province_id]['children'][city_id]['children'];
    if (children === undefined) {
        return false;
    }

    let option = '<option value="">请选择县/区</option>';
    for (let i in children) {
        let data = children[i];
        option += '<option value="' + data.id + '">' + data.name + '</option>';
    }

    $('[name=area_id]').html(option);

    // 回填数据
    if (value) {
        $('[name=area_id] option[value=' + value + ']').attr('selected', 'selected');
    }

    window.form.render();
}
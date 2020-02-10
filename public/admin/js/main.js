// IE兼容提示
(function (w) {
    if (!("WebSocket" in w && 2 === w.WebSocket.CLOSING)) {
        document.body.innerHTML = '<div class="version-debug">您使用的浏览器已经<strong>过时</strong>，建议使用最新版本的谷歌浏览器。<a target="_blank" href="https://pc.qq.com/detail/1/detail_2661.html" class="layui-btn layui-btn-primary">立即下载</a></div>';
    }
}(window));

// Layui及jQuery兼容处理
if (typeof jQuery === 'undefined') {
    window.$ = window.jQuery = layui.$;
}

window.form = layui.form;
window.layer = layui.layer;
window.laydate = layui.laydate;

$(function () {

    // require 配置参数
    require.config({
        waitSeconds: 60,
        baseUrl: '/static/',
        map: {
            '*':
                {
                    css: window.cdn.require.require_css_js
                }
        },
        paths: {
            'ueditor': ['plugin/ueditor/ueditor.all.min'],
            'ueditor.config': ['plugin/ueditor/ueditor.config'],
            'ZeroClipboard': ['plugin/ueditor/third-party/zeroclipboard/ZeroClipboard'],
            'jquery': window.cdn.require.jquery_js,
            'jquery.ztree': window.cdn.require.jquery_ztree_js,
            // 需要注意版本信息
            'jquery.migrate': window.cdn.require.jquery_migrate_js,
            'jquery.cookie': window.cdn.require.jquery_cookie_js,
        },
        shim: {
            'ZeroClipboard': {
                deps: ['ueditor.config', 'ueditor']
            },
            'jquery.ztree': {
                deps: ['jquery', 'jquery.migrate', 'css!' + window.cdn.require.jquery_ztree_css]
            }
        },
        //禁止缓存
        urlArgs: "bust=" + (new Date()).getTime()
    });

    // 注册jquery到require模块
    define('jquery', [], function () {
        return layui.$;
    });

    window.$body = $('body');
    /**
     * 菜单模式切换
     */
    (function ($menu, miniClass) {
        // Mini 菜单模式切换及显示
        if (layui.data('menu')['type-min']) {
            $menu.addClass(miniClass)
        }
        $body.on('click', '[data-target-menu-type]', function () {
            $menu.toggleClass(miniClass);
            layui.data('menu', {key: 'type-min', value: $menu.hasClass(miniClass)});
        }).on('resize', function () {
            var isMini = $('.layui-layout-left-mini').size() > 0;
            $body.width() > 1000 ? isMini && $menu.toggleClass(miniClass) : isMini || $menu.toggleClass(miniClass);
        }).trigger('resize');
        //  Mini 菜单模式时TIPS文字显示
        $('[data-target-tips]').mouseenter(function () {
            if ($menu.hasClass(miniClass)) $(this).attr('index', layer.tips($(this).attr('data-target-tips') || '', this));
        }).mouseleave(function () {
            layer.close($(this).attr('index'));
        })
    })($('.layui-layout-admin'), 'layui-layout-left-mini');

    /*! 消息组件实例 */
    $.msg = new function () {
        var that = this;
        this.indexs = [];
        this.shade = [0.02, '#000'];
        // 关闭消息框
        this.close = function (index) {
            return layer.close(index);
        };
        // 弹出警告框
        this.alert = function (msg, callback) {
            var index = layer.alert(msg, {end: callback, scrollbar: false});
            return this.indexs.push(index), index;
        };
        // 确认对话框
        this.confirm = function (msg, ok, no) {
            var index = layer.confirm(msg, {title: '操作确认', btn: ['确认', '取消']}, function () {
                typeof ok === 'function' && ok.call(this, index);
            }, function () {
                typeof no === 'function' && no.call(this, index);
                that.close(index);
            });
            return index;
        };
        /**
         * 显示成功类型的消息
         * 传递的callback，会执行两次
         */
        this.success = function (msg, time, callback) {
            let index = layer.msg(msg, {
                icon: 1,
                shade: this.shade,
                scrollbar: false,
                end: callback,
                time: (time || 3) * 1000,
                shadeClose: true
            });

            this.indexs.push(index);

            return index;
        };
        // 显示失败类型的消息
        this.error = function (msg, time, callback) {
            var index = layer.msg(msg, {
                icon: 2,
                shade: this.shade,
                scrollbar: false,
                time: (time || 3) * 1000,
                end: callback,
                shadeClose: true
            });
            return this.indexs.push(index), index;
        };
        // 状态消息提示
        this.tips = function (msg, time, callback) {
            var index = layer.msg(msg, {
                time: (time || 3) * 1000,
                shade: this.shade,
                end: callback,
                shadeClose: true
            });
            return this.indexs.push(index), index;
        };
        // 显示正在加载中的提示
        this.loading = function (msg, callback) {
            var index = msg ? layer.msg(msg, {
                icon: 16,
                scrollbar: false,
                shade: this.shade,
                time: 0,
                end: callback
            }) : layer.load(2, {time: 0, scrollbar: false, shade: this.shade, end: callback});
            return this.indexs.push(index), index;
        };
        /**
         * 处理返回的数据信息
         * @param ret
         * @param time
         * @returns {*}
         */
        this.auto = function (ret, time) {
            let url = ret.url ? ret.url : '';
            let msg = ret.msg || (typeof ret.info === 'string' ? ret.info : '');

            if (time === undefined) {
                time = ret.time || 3;
            }

            let code = parseInt(ret.code);
            if (code === 1) {
                return this.success(msg, time, function () {
                    switch (url) {
                        case '__GO_BACK__':
                            window.history.back();
                            break;
                        case '__RELOAD__':
                            window.location.reload();
                            break;
                        default:
                            window.location.href = url;
                            break;
                    }
                    for (let i in that.indexs) {
                        layer.close(that.indexs[i]);
                    }
                    that.indexs = [];
                    return false;
                });
            }
            return this.error(msg, 3, function () {
                switch (url) {
                    case '__GO_BACK__':
                    case '__RELOAD__':
                        break;
                    default:
                        if (url) {
                            window.location.href = url;
                        }
                        break;
                }
            });
        };
    };

    /*! 表单自动化组件 */
    $.form = new function () {
        var that = this;
        // 内容区选择器
        this.targetClass = '.layui-layout-admin>.layui-body';
        // 刷新当前页面
        this.reload = function () {
            window.onhashchange.call(this);
        };
        // 内容区域动态加载后初始化
        this.reInit = function ($dom) {

            upload(layui.upload);

            $.validate.listen($dom);
        };
        // 在内容区显示视图
        this.show = function (html) {
            $(this.targetClass).html(html);
            this.reInit();
            setTimeout(this.reInit, 500);
            setTimeout(this.reInit, 1000);
        };
        // 以hash打开网页
        this.href = function (url, obj) {
            if (url !== '#') window.location.href = '#' + $.menu.parseUri(url, obj);
            else if (obj && obj.getAttribute('data-menu-node')) {
                var node = obj.getAttribute('data-menu-node');
                $('[data-menu-node^="' + node + '-"][data-open!="#"]:first').trigger('click');
            }
        };
        /**
         * 请求数据
         * @param url       请求地址
         * @param data      请求的数组
         * @param method    请求的方法
         * @param callback  回调函数  model方法有可能会返回一个对象，多为此次请求的结果，如保存成功还是失败等等
         * @param loading   提示方法
         * @param tips      提示内容
         * @param time      延迟时间
         * @param headers   请求的header
         */
        this.load = function (url, data, method, callback, loading, tips, time, headers) {

            let index = loading !== false ? $.msg.loading(tips) : 0;

            if (typeof data === 'object' && typeof data['_csrf_'] === 'string') {
                headers = headers || {};
                headers['User-Token-Csrf'] = data['_csrf_'];
                delete data['_csrf_'];
            }

            $.ajax({
                type: method || 'GET',
                data: data || {},
                url: url,
                beforeSend: function (xhr) {
                    if (typeof Pace === 'object') {
                        Pace.restart();
                    }
                    if (typeof headers === 'object') {
                        for (let i in headers) xhr.setRequestHeader(i, headers[i]);
                    }
                },
                error: function (XMLHttpRequest) {
                    if (parseInt(XMLHttpRequest.status) === 200) {
                        this.success(XMLHttpRequest.responseText);
                    } else {
                        $.msg.tips('E' + XMLHttpRequest.status + ' - 服务器繁忙，请稍候再试！');
                    }
                },
                success: function (ret) {
                    /**
                     * 注意modal方法传递的值，如果返回的是一个对象，那么表示成功或者失败的结果，
                     */
                    if (typeof callback === 'function') {
                        return callback.call(that, ret);
                    }

                    if (typeof ret === 'object') {
                        return $.msg.auto(ret, time || ret.wait || undefined);
                    } else {
                        // 返回的如果是静态页面，则调用show方法
                        return that.show(ret)
                    }
                },
                complete: function () {
                    $.msg.close(index);
                }
            });
        };
        // 加载HTML到目标位置
        this.open = function (url, data, callback, loading, tips) {
            this.load(url, data, 'get', function (ret) {
                return typeof ret === 'object' ? $.msg.auto(ret) : that.show(ret);
            }, loading, tips);
        };
        // 打开一个iframe窗口
        this.iframe = function (url, title, area) {
            return layer.open({
                title: title || '窗口',
                type: 2,
                area: area || ['800px', '550px'],
                fix: true,
                maxmin: false,
                content: url
            });
        };
        // 加载HTML到弹出层
        this.modal = function (url, data, title, callback, loading, tips) {
            this.load(url, data, 'GET', function (res) {
                if (typeof (res) === 'object') {
                    return $.msg.auto(res);
                }
                let index = layer.open({
                    type: 1,
                    btn: false,
                    area: "800px",
                    content: res,
                    title: title || '',
                    success: function (dom, index) {
                        $(dom).find('[data-close]').off('click').on('click', function () {
                            if ($(this).attr('data-confirm')) {
                                return $.msg.confirm($(this).attr('data-confirm'), function (_index) {
                                    layer.close(_index);
                                    layer.close(index);
                                });
                            }
                            layer.close(index);
                        });
                        $.form.reInit($(dom));
                    }
                });
                $.msg.indexs.push(index);
                return (typeof callback === 'function') && callback.call(this);
            }, loading, tips);
        };
    };

    /*! 注册对象到Jq */
    $.validate = function (form, callback, options) {
        return (new function () {
            let that = this;
            // 表单元素
            this.tags = 'input,textarea,select';
            // 检测元素事件
            this.checkEvent = {change: true, blur: true, keyup: false};
            // 去除字符串两头的空格
            this.trim = function (str) {
                return str.replace(/(^\s*)|(\s*$)/g, '');
            };
            // 标签元素是否可见
            this.isVisible = function (ele) {
                return $(ele).is(':visible');
            };
            // 检测属性是否有定义
            this.hasProp = function (ele, prop) {
                if (typeof prop !== "string") return false;
                let attrProp = ele.getAttribute(prop);
                return (typeof attrProp !== 'undefined' && attrProp !== null && attrProp !== false);
            };
            // 判断表单元素是否为空
            this.isEmpty = function (ele, value) {
                let trimValue = this.trim(ele.value);
                value = value || ele.getAttribute('placeholder');
                return (trimValue === "" || trimValue === value);
            };
            // 正则验证表单元素
            this.isRegex = function (ele, regex, params) {
                let inputValue = $(ele).val();
                let realValue = this.trim(inputValue);
                regex = regex || ele.getAttribute('pattern');
                if (realValue === "" || !regex) return true;
                return new RegExp(regex, params || 'i').test(realValue);
            };
            // 检侧所的表单元素
            this.checkAllInput = function () {
                let isPass = true;
                $(form).find(this.tags).each(function () {
                    if (that.checkInput(this) === false) return $(this).focus(), isPass = false;
                });
                return isPass;
            };
            // 检测表单单元
            this.checkInput = function (input) {
                let tag = input.tagName.toLowerCase(), need = this.hasProp(input, "required");
                let type = (input.getAttribute("type") || '').replace(/\W+/, "").toLowerCase();
                if (this.hasProp(input, 'data-auto-none')) return true;
                let ingoreTags = ['select'],
                    ingoreType = ['radio', 'checkbox', 'submit', 'reset', 'image', 'file', 'hidden'];
                for (let i in ingoreTags) if (tag === ingoreTags[i]) return true;
                for (let i in ingoreType) if (type === ingoreType[i]) return true;
                if (need && this.isEmpty(input)) return this.remind(input);
                return this.isRegex(input) ? (this.hideError(input), true) : this.remind(input);
            };
            // 验证标志
            this.remind = function (input) {
                if (!this.isVisible(input)) return true;
                this.showError(input, input.getAttribute('title') || input.getAttribute('placeholder') || '输入错误');
                return false;
            };
            // 错误消息显示
            this.showError = function (ele, content) {
                $(ele).addClass('validate-error'), this.insertError(ele);
                $($(ele).data('input-info')).addClass('layui-anim layui-anim-fadein').css({width: 'auto'}).html(content);
            };
            // 错误消息消除
            this.hideError = function (ele) {
                $(ele).removeClass('validate-error'), this.insertError(ele);
                $($(ele).data('input-info')).removeClass('layui-anim-fadein').css({width: '30px'}).html('');
            };
            // 错误消息标签插入
            this.insertError = function (ele) {
                let $html = $('<span style="padding-right:12px;color:#a94442;position:absolute;right:0;font-size:12px;z-index:2;display:block;width:34px;text-align:center;pointer-events:none"></span>');
                $html.css({
                    top: $(ele).position().top + 'px',
                    paddingBottom: $(ele).css('paddingBottom'),
                    lineHeight: $(ele).css('height')
                });
                $(ele).data('input-info') || $(ele).data('input-info', $html.insertAfter(ele));
            };
            // 表单验证入口
            this.check = function (form, callback) {
                $(form).attr("novalidate", "novalidate");
                $(form).find(that.tags).map(function () {
                    this.bindEventMethod = function () {
                        that.checkInput(this);
                    };
                    for (var e in that.checkEvent) if (that.checkEvent[e] === true) {
                        $(this).off(e, this.bindEventMethod).on(e, this.bindEventMethod);
                    }
                });
                $(form).bind("submit", function (event) {
                    if (that.checkAllInput() && typeof callback === 'function') {
                        if (typeof CKEDITOR === 'object' && typeof CKEDITOR.instances === 'object') {
                            for (var i in CKEDITOR.instances) CKEDITOR.instances[i].updateElement();
                        }
                        callback.call(this, $(form).formToJson());
                    }
                    return event.preventDefault(), false;
                });
                $(form).find('[data-form-loaded]').map(function () {
                    $(this).html(this.getAttribute('data-form-loaded') || this.innerHTML);
                    $(this).removeAttr('data-form-loaded').removeClass('layui-disabled');
                });
                return $(form).data('validate', this);
            };
        }).check(form, callback, options);
    };

    /*! 自动监听规则内表单 */
    $.validate.listen = function () {
        $('form[data-auto]').map(function () {
            let _this = $(this);
            if (_this.data('listen') !== 'true') {
                _this.data('listen', 'true').validate(function (data) {
                    let call = _this.attr('data-callback') || '_default_callback';
                    let type = this.getAttribute('method') || 'POST';
                    let tips = this.getAttribute('data-tips') || undefined;
                    let time = this.getAttribute('data-time') || undefined;
                    let href = this.getAttribute('action') || window.location.href;
                    // 提交 tijiao
                    $.form.load(href, data, type, window[call] || undefined, true, tips, time);
                })
            }
            _this = _this || $(this.targetClass);
            _this.find('[required]').parent().prevAll('label').addClass('label-required');
            _this.find('[data-date-range]').map(function () {
                laydate.render({range: true, elem: this});
                this.setAttribute('autocomplete', 'off');
            });
        });
    };

    /*! 注册对象到JqFn */
    $.fn.validate = function (callback, options) {
        return $.validate(this, callback, options);
    };

    /*! 表单转JSON */
    $.fn.formToJson = function () {
        let self = this, data = {}, pushCounters = {};
        let patterns = {
            "key": /[a-zA-Z0-9_]+|(?=\[\])/g,
            "push": /^$/,
            "fixed": /^\d+$/,
            "named": /^[a-zA-Z0-9_]+$/
        };
        this.build = function (base, key, value) {
            base[key] = value;
            return base;
        };
        this.pushCounter = function (name) {
            if (pushCounters[name] === undefined) pushCounters[name] = 0;
            return pushCounters[name]++;
        };
        $.each($(this).serializeArray(), function () {
            let key, keys = this.name.match(patterns.key), merge = this.value, name = this.name;
            while ((key = keys.pop()) !== undefined) {
                name = name.replace(new RegExp("\\[" + key + "\\]$"), '');
                if (key.match(patterns.push)) { // push
                    merge = self.build([], self.pushCounter(name), merge);
                } else if (key.match(patterns.fixed)) { // fixed
                    merge = self.build([], key, merge);
                } else if (key.match(patterns.named)) { // named
                    merge = self.build({}, key, merge);
                }
            }
            data = $.extend(true, data, merge);
        });
        return data;
    };

    $.validate.listen();

    /*! 注册 data-icon 事件行为 */
    $body.on('click', '[data-icon]', function () {
        let field = $(this).attr('data-icon') || $(this).attr('data-field') || 'icon';
        let location = window.ROOT_URL + '/admin/plugin/icon?&field=' + field;
        $.form.iframe(location, '图标选择');
    });

    /*! 注册 data-serach 表单搜索行为 */
    $body.on('submit', 'form.form-search', function () {

        let _this = $(this);
        let method = _this.attr('method') || 'get';

        let param = {};

        let url = window.location.href;

        url = url.replace(/&?page=\d+/g, '');

        let question_mark = url.indexOf('?');

        if (question_mark !== -1) {
            let url_params = url.substr(question_mark + 1);
            url_params = url_params.split('&');
            if (url_params && url_params.length > 0) {
                for (let i in url_params) {
                    let value = url_params[i];
                    value = value.split('=');
                    let name = value[0];
                    param[name] = $.trim(value[1]);
                }
            }
        }

        let form_data = _this.serializeArray();

        if (form_data) {
            for (let i in form_data) {
                let data = form_data[i];
                let name = data.name;
                let value = $.trim(data.value);
                param[name] = value;
            }
        }

        if (param) {
            let action = url;
            if (question_mark !== -1) {
                action = url.substr(0, question_mark+1);
            } else {
                action = action + '?';
            }
            for (let i in param) {
                action += i + '=' + param[i] + '&';
            }
            url = action.substr(0, action.length - 1);
        }

        if (method.toLowerCase() === 'get') {
            return window.location.href = url;
        }

        $.form.load(url, this, 'post');
    });

    /*! 注册 数据下载 */
    $('.form-search [type=button]').on('click', function () {

        let _this = $(this);
        let url = _this.parents('form').data('download-url');

        if (url === undefined) {
            return false;
        }

        let split = url.indexOf('?') === -1 ? '?' : '&';
        let method = _this.parents('form').attr('method') || 'get';
        let form_data = _this.parents('form').serializeArray();
        console.log(form_data);
        let param = [];
        if (form_data) {
            for (let i in form_data) {
                let data = form_data[i];
                param.push(data.name + '=' + $.trim(data.value));
            }
        }

        if (param) {
            param = param.join('&');
        } else {
            param = '';
        }

        url = url + split + param;

        window.location.href = url + split + param;

        return false;
    });

    /*! 注册 data-serach 表单搜索的重置行为 */
    $body.on('click', '[type=reset]', function () {
        let form = $(this).parents('form');

        form.find('input[type=text]').each(function () {
            $(this).val('');
        });

        form.find('select option').each(function () {
            $($(this)).removeAttr('selected');
        });

        return false;
    });

    /*! 注册 data-load 事件行为 */
    $body.on('click', '[data-load]', function () {
        var url = $(this).attr('data-load'), tips = $(this).attr('data-tips'), time = $(this).attr('data-time');
        if ($(this).attr('data-confirm')) return $.msg.confirm($(this).attr('data-confirm'), function () {
            $.form.load(url, {}, 'get', null, true, tips, time);
        });
        $.form.load(url, {}, 'get', null, true, tips, time);
    });

    /*! 注册 data-modal 事件行为 */
    $body.on('click', '[data-modal]', function () {
        let _this = $(this);
        if (!_this.data('modal')) {
            return false;
        }
        let callback = function () {
            return $.form.modal(_this.attr('data-modal'), {}, _this.attr('data-title') || _this.text() || '编辑');
        };
        if (_this.attr('data-confirm')) {
            return $.msg.confirm($(this).attr('data-confirm'), function () {
                return callback();
            })
        } else {
            return callback();
        }
    });

    // 删除上传照片
    $body.on('click', 'div.image-list img', function () {
        let _this = $(this);
        layer.confirm('确认删除吗?', {title: '请确认', 'icon': 3}, function (index) {
            _this.parent('div').remove();
            layer.close(index);
        });
    });

    // 文件上传
    layui.use(['upload'], function () {
        upload(layui.upload);
    });

    function upload(upload) {
        // 绑定上传文件
        $('.upload-file').map(function () {

            let _this = $(this);
            let name = _this.data('name');
            let id = _this.attr('id');

            if (!id) {
                id = 'upload-file-' + parseInt(Math.random() * 10000);
                _this.attr('id', id);
            }

            let field = _this.attr('field') || 'file';
            let accept = _this.attr('accept') || 'images';
            let ext = _this.data('ext') || 'jpeg|jpg|png|gif';
            // 设置文件最大可允许上传的大小，单位 KB。不支持ie8/9
            let size = _this.data('size') || 1024;
            let auto = _this.data('auto') || true;
            let value = _this.data('value');
            let num = parseInt(_this.data('num')) || 1;

            // 上传完成后的回调函数
            let callback = function (_this, url) {

                let _name = name;

                if (num > 1) {
                    _name = _name + '[]';
                }

                let template = '<div>' +
                    '<input type="hidden" name="' + _name + '" value="' + url + '">' +
                    '<img class="w80" src="' + url + '"/>' +
                    '</div>';

                let obj = _this.siblings('div.image-list');
                obj.removeClass('layui-hide');
                // 一个的话直接替换文件
                if (num === 1) {
                    obj.html(template);
                } else {
                    let len = $('.image-list img').length;
                    if (len >= num) {
                        $.msg.error('已经超出允许上传的数量（允许最大数量:' + num + '）');
                        return false;
                    }
                    obj.append(template);
                }
            };

            // 追加图片列表模板
            let template = '<div class="image-list layui-hide"></div>';
            _this.after(template);

            if (value) {
                if (typeof value !== 'string') {
                    value = '' + value;
                }
                value = value.split(',');
                if (value.length > 0) {
                    for (let i in value) {
                        callback(_this, value[i]);
                    }
                }
            }

            upload.render({
                elem: '#' + id,
                url: window.UPLOAD_PATH,
                field: field,
                accept: accept,
                exts: ext,
                size: size,
                auto: auto,
                data: {
                    field: field,
                    ext: ext,
                    size: size
                },
                done: function (res, index, upload) {
                    if (res.code) {
                        console.log(res.data);
                        callback(_this, res.data.url)
                    } else {
                        $.msg.error(res.msg);
                    }
                }
            });
        });
    }

    /**
     * 二级联动切换
     */
    window.form.on('select()', function (data) {
        let elem = data.elem;
        let child_data = $(elem).data('child-data');
        if (child_data !== undefined) {
            let child_name = $(elem).data('child-name');
            linkage(data.value, child_data, child_name);
        }
    });

    /**
     * 二级联动回填
     */
    $('select[data-child-data]').each(function () {
        let child_data = $(this).data('child-data');
        if (child_data === '') {
            return false;
        }
        let child_name = $(this).data('child-name');
        let child_title = $(this).data('child-title') || '';
        let value = $(this).val();
        linkage(value, child_data, child_name, child_title);
    });

    /**
     * 二级联动的方法
     */
    function linkage(key, data, name, child_title) {
        try {
            if (typeof data === "string") {
                data = JSON.parse(data);
            }
        } catch (e) {
            console.log(data);
            console.log('请检查二级联动的数据，一般是传递值的时候没有用json_encode进行编码，name值：' + name);
            return false;
        }
        let category = data[key];

        if (!name) {
            return false;
        }

        if (!child_title || child_title.length === 0) {
            child_title = ' --全部--';
        }

        let child = $('select[name=' + name + ']');
        let option = '<option value=""> ' + child_title + ' </option>';

        if (category) {
            let value = parseInt(child.data('value'));
            for (let i in category) {
                i = parseInt(i);
                if (i == value) {
                    option += '<option value="' + i + '" selected>' + category[i] + '</option>'
                } else {
                    option += '<option value="' + i + '">' + category[i] + '</option>'
                }
            }
        }

        child.html(option);
        window.form.render();
    }

    /**
     * 富文本编辑器
     */
    if ($('textarea.editor').length > 0) {
        require(['ZeroClipboard'], function (ZeroClipboard) {
            window.ZeroClipboard = ZeroClipboard;
            $('textarea.editor').map(function () {
                let ue = UE.getEditor($(this).attr('id'), {
                    zIndex: 1,
                }).addListener('beforefullscreenchange', function (event, isFullScreen) {
                    if (isFullScreen) {
                        $('.layui-layout-admin>.layui-header').addClass('layui-hide');
                        $('.layui-layout-admin>.layui-side').addClass('layui-hide');
                    } else {
                        $('.layui-layout-admin>.layui-header').removeClass('layui-hide');
                        $('.layui-layout-admin>.layui-side').removeClass('layui-hide');
                    }
                });
            });
        })
    }


    window.form.on('select(province_id)', function (data) {
        province_id = data.value;
        changeCity(province_id);
    });
    window.form.on('select(city_id)', function (data) {
        changeArea(province_id, data.value);
    });

    regionFetch();

    bindDateTimePlugin();
});


function bindDateTimePlugin() {
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
}

/**
 * 城市数据联动，并且回填数据
 *
 * 1. 用于表单回填，表单回填的值从 data中去
 * 2. 回填搜索框的值，搜索框的值从 url中获取
 */
function regionFetch() {

    let province_id = parseInt($("[name=province_id]").val());
    if (isNaN(province_id)) {
        province_id = parseInt($("[name=province_id]").data('value'));
    }
    getProvince(province_id);

    let city_id = parseInt($("[name=city_id]").val());
    if (isNaN(city_id)) {
        city_id = parseInt($("[name=city_id]").data('value'));
    }
    if (city_id) {
        changeCity(province_id, city_id);
    }

    let area_id = parseInt($("[name=area_id]").val());
    if (isNaN(area_id)) {
        area_id = parseInt($("[name=area_id]").data('value'));
    }
    if (area_id) {
        changeArea(province_id, city_id, area_id);
    }


}

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
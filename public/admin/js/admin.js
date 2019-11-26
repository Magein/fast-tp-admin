$(function () {

    /**
     * 会员生日短信模板切换的时候，回填模板短信内容
     */

    changeBirthdaySmsContentByTemplateId();

    function changeBirthdaySmsContentByTemplateId() {

        // 填充模板信息
        window.form.on('select(template)', function (data) {
            let content = $(data.elem).data('template');
            callback(data.value, content);
        });

        /**
         * 填充内容
         * @param id
         * @param content
         */
        let callback = function (id, content) {

            if (typeof content === "string") {
                content = JSON.parse(content);
            }
            if (content[id]) {
                $('[name=content]').val(content[id]);
            }
        };
    }

    $('body').on('click', '.hidden-phone', function () {
        $(this).addClass('layui-hide');
        $(this).siblings('.show-phone').removeClass('layui-hide')
    });

    window.form.on('select(city_id)', function (data) {
        let city_id = data.value;
        changeArea(city_id);
    });

    let city_id = $("select[name=city_id]").val();
    changeArea(city_id);

    function changeArea(city_id) {

        if (!city_id) {
            return;
        }

        let children = REGION['340000']['children'][city_id]['children'] || [];
        if (children) {
            // 回填默认值
            let value = $('select[name=area_id]').attr('value');
            let option = '';
            for (let i in children) {
                let area_id = children[i]['id'];
                let area_name = children[i]['name'];

                if (value && value == area_id) {
                    option += '<option value="' + area_id + '" selected>' + area_name + '</option>';
                } else {
                    option += '<option value="' + area_id + '">' + area_name + '</option>';
                }
            }

            $('select[name=area_id]').html(option);
            window.form.render();
        }
    }

    $('body').on('click', '.show-order-no-template', function () {

        let order_no = $(this).data('order-no');

        $.form.modal('/admin/order/info', {order_no: order_no}, '订单详情');
    });
});
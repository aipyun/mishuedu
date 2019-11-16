define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var Uploader = require('upload');
    var WebUploader = require('edusoho.webuploader');
    require('colorpicker');
    
	exports.run = function() {

        var validator = new Validator({
            element: '#member-zone-form'
        });

        validator.addItem({
            element: '[name=default_buy_months]',
            required: true,
            rule: 'positive_integer'
        });

        validator.addItem({
            element: '[name=default_buy_years]',
            required: true,
            rule: 'positive_integer'
        });

        validator.addItem({
            element: '[name=upgrade_min_day]',
            required: true,
            rule: 'positive_integer'
        });

        validator.addItem({
            element: '[name=default_buy_months10]',
            required: true,
            rule: 'positive_integer'
        });

        validator.addItem({
            element: '[name=default_buy_years10]',
            required: true,
            rule: 'positive_integer'
        });

        validator.addItem({
            element: '[name=daysOfNotifyBeforeDeadline]',
            required: true,
            rule: 'positive_integer'
        });

        $("input[name=buyType]").change(function() {

            var type = $('input[name=buyType]:checked').val();

            $('.buy-help').hide();

            $('.' + type).show();

        });

        $('.colorpicker-input').colorpicker();

        $("input[name='deadlineNotify']").change(function(){
            var element = $(this);
            if(element.val()=='1') {
                $("#beforeNotificationDay").show();
            } else {
                $("#beforeNotificationDay").hide();
            }
        })

        var uploader = new WebUploader({
            element: '#site-poster-upload'
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#site-poster-upload").data("gotoUrl");

            $.post(url, response ,function(data){
                $("#site-poster-container").html('<img src="' + data.url + '">');
                $('#member-zone-form').find('[name=poster]').val(data.path);
                $("#site-poster-remove").show();
                Notify.success('上传海报成功！');
            });
        });
        $("#site-poster-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-poster-container").html('');
                $('#member-zone-form').find('[name=poster]').val('');
                $btn.hide();
                Notify.success('删除海报成功！');
            }).error(function(){
                Notify.danger('删除海报失败！');
            });
        });

	};

});
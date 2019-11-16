define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    
	exports.run = function() {

        var $modal = $('#member-modal-form').parents('.modal');
        var validator = new Validator({
            element: '#member-modal-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    console.log(html);
                    $modal.modal('hide');
                    Notify.success('用户信息保存成功');
                    var $tr = $(html);
                    $('#' + $tr.attr('id')).replaceWith($tr);
                }).error(function(){
                    Notify.danger('操作失败');
                });
            }
        });

        validator.addItem({
            element: '[id="nickname"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
        });

        validator.addItem({
            element: '[id="deadline"]',
            required: true,
            rule: 'date'
        });

        validator.addItem({
            element: '[name=levelId]',
            required: true,
            errormessageRequired: '请选择会员等级!'
        });

        $("#deadline").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
	};

});
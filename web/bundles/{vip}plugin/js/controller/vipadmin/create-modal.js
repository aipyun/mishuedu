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
					$modal.modal('hide');
					Notify.success('新会员添加成功');
                    window.location.reload();
				}).error(function(){
					Notify.danger('新会员添加失败');
				});

            }
        });

        validator.addItem({
            element: '#nickname',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
        });

        validator.addItem({
            element: '#deadline',
            required: true,
            rule: 'date',
            failSilently:true
        });

        validator.addItem({
            element: '[name=levelId]',
            required: true,
            errormessageRequired: '请选择会员等级!'
        });

        var boughtUnitVal = $('input[name="boughtUnit"]:checked').val();
        getBoughtUnitValidator(boughtUnitVal, validator);

        $('input[name="boughtUnit"]').change(function(){
            var value = $(this).attr('checked',true).val();
            getBoughtUnitValidator(value, validator)
        })

        function getBoughtUnitValidator(boughtUnitVal, validator)
        {
            if (boughtUnitVal == 'month') {
                validator.addItem({
                    element: '#boughtDuration',
                    required: true,
                    rule: 'positive_integer max{max:999} min{min:1}'
                });
            } else {
                validator.addItem({
                    element: '#boughtDuration',
                    required: true,
                    rule: 'positive_integer max{max:99} min{min:1}'
                });
            }
        }
        

        $("#deadline").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        }).on('hide', function(ev){
            validator.query('#deadline').execute();
        });
	};

});
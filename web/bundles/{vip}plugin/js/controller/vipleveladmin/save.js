define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    require('jquery.form');
    require('ckeditor');


	exports.run = function() {
        var $form = $('#memberlevel-form');
        var $table = $('#memberlevel-table');

        var validator = new Validator({
            element: $form
        });

        validator.addItem({
            element: '[name="name"]',
            required: true
        });

        validator.addItem({
            element: '[name="monthPrice"]',
            required: true,
            rule:'currency arithmetic_number maxlength{max:8}',
            display: '包月价格'           
        });

        validator.addItem({
            element: '[name="yearPrice"]',
            required: true,
            rule:'currency arithmetic_number maxlength{max:8}',
            display: '包年价格'            
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });
        
    };

});
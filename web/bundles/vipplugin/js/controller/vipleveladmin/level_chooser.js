define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $table = $("#level-table"),
            $modal = $table.parents('.modal');

        $table.on('click', '.choose-vip', function(e){
        	var levelId = $(this).data('target');
        	var levelName = $(this).data('name');
            var html = '<span><strong>'+levelName+'</strong></span>';
        	$('#choose-vip-input').val(levelId);
        	$('#course-display .well').html(html);
            $('.js-rechoose-vip').show();
            $('.js-rechoose-course').hide();        	
        	$('#course-display').show();
            $modal.modal('hide');
            Notify.success('指定会员成功');
        });

        $modal.on('hidden.bs.modal', function (e) {
            if (!$('#choose-vip-input').val()) {
                $('.js-vip-radios').button('reset');
            };
        })
    };
})
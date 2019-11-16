define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var Notify = require('common/bootstrap-notify');


    exports.run = function() {

        var $modal = $('#member-cancel-form').parents('.modal');
        var $table = $('#member-table');

        $('#member-table').on('click', '.delete-member', function(){

            if(!confirm('真的要取消该会员吗?')){
                return ;
            }

            var ids = [];
            ids.push($(this).data('id'));

            ajaxDelete($(this).data('url'), ids);
            
        });

        $('.js-batch-delete-btn').click(function(){
            var ids = [];
            $('#table-list-panel').find('[data-role=batch-item]:checked').each(function() {
                ids.push(this.value);
            });
            if(ids == ""){
                Notify.danger('请先选择你要取消的会员!');
                return;
            }

            if(!confirm('真的要取消这些会员吗?')){
                return ;
            }

            ajaxDelete($(this).data('url'), ids);

        })

        function ajaxDelete(url, userIds) {
            
            $.post(url, {userIds:userIds}, function(response){
                if (response) {
                    $.each(userIds, function(i,userId){
                        $('#member-table-tr-'+userId).remove();
                    })

                    var member_count = $('.member_count'),
                        member_count_text = parseInt($('.member_count').text());
                    member_count.text(member_count_text - userIds.length);
                    Notify.success('操作成功!');
                }
                
            });
        }
        
        if ($("#startDate").length > 0) {
            $("#startDate").datetimepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                minView: 'month',
            }).on('changeDate',function(){
                $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
            });

            $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
        }
        
        if ($("#endDate").length > 0) {
            $("#endDate").datetimepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                minView: 'month',
            }).on('changeDate',function(){
                $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
            });

            $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
        }

        $('.item-vip-edit').click(function(){
            var url = $(this).data('url');
            var userIds = $(this).data('id');

            $('#modal').html('');
            $('#modal').load(url,{userIds:userIds});
            $('#modal').modal('show');
        })

        var $panel = $('#table-list-panel');
        require('../../../../topxiaadmin/js/util/batch-select')($panel);

        $('.js-batch-edit-btn').click(function(){
            var ids = [];
            $('#table-list-panel').find('[data-role=batch-item]:checked').each(function() {
                ids.push(this.value);
            });
            if(ids == ""){
                Notify.danger('请先选择你要编辑的会员!');
                return;
            }

            $('#modal').html('');
            $('#modal').load($(this).data('url'),{userIds:ids});
            $('#modal').modal('show');
        })
    };

});
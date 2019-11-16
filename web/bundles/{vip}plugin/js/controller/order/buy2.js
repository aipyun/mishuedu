define(function(require, exports, module) {

    var moment = require('moment');
    var Validator = require('bootstrap.validator');
    
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $("#member-buy-form");
        var $modal = $("#member-buy-confirm-modal");
        $modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                var type = $form.find('[name=type]').val();
                if (type == 'renew') {
                    var levelName = $form.find('.level-name').text();
                } else {
                    var levelName = $form.find('[name=level]:checked').parent().text();
                }

                $modal.find('.confirm-level').html(levelName);
                $modal.find('.confirm-amount').html($form.find('.amount').text());

                $modal.modal('show');
            }
        });

        validator.addItem({
            element: '[name="duration"]',
            display: '开通时长',
            required: true,
            rule: 'integer min{min:1} max{max:999}'
        })

        $form.find('[name=level]').on('change', function() {
            refresh();
        });
        $form.find('[name=targetId]').on('change', function() {
            refresh_price();
        });

        $form.find('[name=unit]').on('change', function() {
            if ($form.find('[name=unit]:checked').val() == 'month') {
                $defaultBuyMonth = $('[name=defaultBuyMonth]').val();
                $form.find('[name=duration]').val($defaultBuyMonth);
            } else {
                $defaultBuyYear = $('[name=defaultBuyYear]').val();
                $form.find('[name=duration]').val($defaultBuyYear);
            }
            refresh();
        });

        $form.find('[name=duration]').on('change', function() {
            refresh();
        });

        $("#member-order-confirm-btn").on('click', function() {
            $form[0].submit();
        });

        refresh();
        refresh_price();
    };

    function refresh_price() {
        var $form = $("#member-buy-form");
        var target = $form.find('[name=targetId]:checked').val() || $form.find('[name=targetId]').val();
        var prices = $form.find('.js-vip-price').data('vipPrice');
        var coinDisplay = $form.find('.js-vip-price').data('coinDisplay');
        var cashRate = $form.find('.js-vip-price').data('cashRate');
        var coinName = $form.find('.js-vip-price').data('coinName');
        if(coinDisplay) {
            $form.find('[name=unit]').each(function(){
                var price = '<span class="text-primary">(' + prices[target][$(this).val()] * cashRate + ' ' + coinName + ')</span>';
                $(this).parent().find('span').remove();
                $(this).parent().append(price);
            });
        } else {
            $form.find('[name=unit]').each(function(){
                var price = '<span class="text-primary">(' + prices[target][$(this).val()] + ' 元)</span>';
                $(this).parent().find('span').remove();
                $(this).parent().append(price);
            });
        }
    }

    function refresh() {
        var $form = $("#member-buy-form");

        var unit = $form.find('[name=unit]:checked').val();
        var duration = $form.find('[name=duration]').val();

        $form.find('.unit-label').hide();
        $form.find('.unit-label-' + unit).show();

        var startDate = $form.find('[name=startDate]').val();
        if (startDate) {
            var deadline = moment(startDate);
        } else {
            var deadline = moment();
        }
        deadline = deadline.add(unit + 's', duration).format('YYYY-MM-DD');

        $form.find('.deadline').html(deadline).parent().removeClass('hide');
        refresh_price();
    }

});
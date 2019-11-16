define(function(require, exports, module) {

    exports.run = function() {

        var $form = $("#member-upgrade-form");

        $("#member-order-confirm-btn").on('click', function() {
            $form[0].submit();
        });

    }

});
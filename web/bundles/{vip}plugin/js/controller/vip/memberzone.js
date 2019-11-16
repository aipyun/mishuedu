define(function(require, exports, module) {

	require('jquery.cycle2');

    exports.run = function() {

        $('#deadlineAlert').on('click', function() {
            document.cookie = " deadlineAlert= " + escape("closed");
        });

        $('.homepage-feature').cycle({
	        fx:"scrollHorz",
	        slides: "> a, > img",
	        log: "false",
	        pauseOnHover: "true"
    	});

        $(".vip-property-tips")
        .popover({
            html: true,
            placement: 'right',
            trigger: 'hover',
            animation:true,
            container:'body'
        })
        .click(function(e) {
                e.preventDefault()
        });
        
    };

});
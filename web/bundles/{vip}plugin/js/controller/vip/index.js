define(function(require, exports, module) {
    
    var Lazyload = require('echo.js');
    var Swiper = require('swiper');

    exports.run = function() {
        Lazyload.init();

        if ($(".vip-swiper-container .swiper-slide").length > 3) {
            var swiper = new Swiper('.vip-swiper-container .swiper-container', {
                slidesPerView: 3,
                // autoplay: 5000,
                autoplayDisableOnInteraction: false,
                loop: true,
                calculateHeight: true,
                roundLengths: true,
                onInit: function(swiper) {
                   $(".swiper-slide").removeClass('swiper-hidden'); 
                   $(".arrow-left,.arrow-right").removeClass('hidden');
                }
            });
            $('.arrow-left').on('click', function(e){
                e.preventDefault()
                swiper.swipePrev();
            });
            $('.arrow-right').on('click', function(e){
                e.preventDefault()
                swiper.swipeNext();
            });
        }
        if ($('.vip-show').html() == 0 ) {
            $('.vip-show').html("<div class='empty'>暂无会员课程或班级</div>");
        }; 

        $('.vip-filer').on('click','.vip-cat li a',function(){
            $this = $(this);
            $.get($this.data('courseUrl'),function(courseHtml){
                $this.parent('li').addClass('active').siblings().removeClass('active');
                $('.vip-show').html(courseHtml);
                Lazyload.init();
                $.get($this.data('classroomUrl'),function(classroomHtml){
                    $('.vip-show').append(classroomHtml);
                    Lazyload.init();
                    if(courseHtml.length ==0 & classroomHtml.length == 0){
                        $('.vip-show').html("<div class='empty'>暂无会员课程或班级</div>");
                    }
                })
            })
        });
    }

});
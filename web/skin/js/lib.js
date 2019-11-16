
$(document).ready(function($) {

    // 手机导航
    if ($(".header").find($('.menuBtn')).length>0) {
        $("body").addClass("ok");
    }
    $('.menuBtn').append('<b></b><b></b><b></b>');
    $('.menuBtn').click(function(event) {
        $(this).toggleClass('open');
        var _winw = $(window).width();
        var _winh = $(window).height();
        if( $(this).hasClass('open') ){
            if( _winw<=1340 ){
                $('.soBox').stop().slideDown();
                $('.nav').stop().slideDown();
            }
        }else{
            $('body').removeClass('open');
            if( _winw<=1340 ){
                $('.soBox').stop().slideUp();
                $('.nav').stop().slideUp();
            }
        }
    });

    (function(){
        // 自定义多选
        $('[role=checkbox]').each(function(){
            var input = $(this).find('input[type="checkbox"]');

                input.each(function(){
                    if( $(this).attr('checked')){
                        $(this).parents('label').addClass('checked');
                        $(this).prop("checked", true)
                    }
                })

                input.change(function(){
                    $(this).parents('label').toggleClass('checked');
                });
        })

    })();


        (function(){
            // 自定义单选
        $('[role=radio]').each(function(){
            var input = $(this).find('input[type="radio"]'),
                label = $(this).find('label');

                input.each(function(){
                    if( $(this).attr('checked')){
                        $(this).parents('label').addClass('checked');
                        $(this).prop("checked", true)
                    }
                })

                input.change(function(){
                    label.removeClass('checked');
                    $(this).parents('label').addClass('checked');
                    input.removeAttr('checked');
                    $(this).prop("checked", true)
                })
        })
    })();

    //头部滑动加背景
    $(window).scroll(function(){
        var _top = $(window).scrollTop();
        if( _top>1 ){
            $('#hd').addClass('active');
        }else{
            $('#hd').removeClass('active');
        }
    });
    var _top = $(window).scrollTop();
    if( _top>1 ){
        $('#hd').addClass('active');
    }else{
        $('#hd').removeClass('active');
    }




    // 选项卡 鼠标点击切换
    $(".TAB_CLICK li").click(function(){
      var tab=$(this).parent(".TAB_CLICK");
      var con=tab.attr("id");
      var on=tab.find("li").index(this);
      $(this).addClass('on').siblings(tab.find("li")).removeClass('on');
      $(con).eq(on).show().siblings(con).hide();
    });

    // 选项卡 鼠标经过切换
    $(".TAB li").mousemove(function(){
      var tab=$(this).parent(".TAB");
      var con=tab.attr("id");
      var on=tab.find("li").index(this);
      $(this).addClass('hover').siblings(tab.find("li")).removeClass('hover');
      $(con).eq(on).show().siblings(con).hide();
    });

    //底部地图二维码
    $(".f-ma p span").click(function(event){
        $(this).parents(".f-ma").find("p").removeClass('on');
        $(this).parent().addClass("on");
        event.stopPropagation();
    });
    $("body").click(function(event){
        $(".f-ma p").removeClass("on");
        // event.stopPropagation();
    });

    //弹出框
    $('.js-city').click(function(){
        $('#m-city').stop(true,true).fadeIn(200);
    });
    $('#m-city .close').click(function(){
        $('#m-city').stop(true,true).fadeOut(200);
    })
    $('.myfancy').click(function(){
        var _id = $(this).attr('href');
        $(this).parents(".pop-lb").fadeOut();
        $(_id).addClass('open');
    });
    $('.pop-bg,.close,.close2').click(function(){
        $(this).parents(".m-pop").removeClass('open');
        if ($('.v1')) $(".v1")[0].pause();
    });
    $('.table-bar .del').click(function(){
        $('#m-del').addClass('open');
    });
    $('#m-del .btn .s2').click(function(){
        $(this).parents(".m-pop").removeClass('open');
    });

    // 表单全选
    $(".selectAll").click(function () {
       if(this.checked){
            $(".table-js table :checkbox").prop("checked", true);
            $('.table-bar a').css('color','#333');
       }else{
            $(".table-js table :checkbox").prop("checked", false);
            $('.table-bar a').css('color','#666');
       }
    });
    $(".table-js table :checkbox").click(function(){
        var len = $(".table-s1 table :checkbox:checked").length;
        if ( len != 0 ) {
            $('.table-bar a').css('color','#333');
        } else {
            $('.table-bar a').css('color','#666');
        }
    })
    $("#check-on").click(function () {
        $(".table-s1 table :checkbox:checked").parents('tr').removeClass('unread').find('td:last').text('已读');
    });

    //头部区域切换
    $("#hd .city").click(function(event){
        $(this).toggleClass('open');
        event.stopPropagation();
    });
    $("body").click(function(event){
        $("#hd .city").removeClass("open");
    });
    //左右高度
    var height1 = $(".col-main").innerHeight();
    var height2 = $(".col-slide").innerHeight();
    var height3 = $(".g-tit-lb").innerHeight();
    $(".col-box").css("min-height",height2-height3-20-77);


    $('.m-register-ly .items input').focus(function() {
        $(this).parent().css('border-color', '#e1ceae');
    }).blur(function() {
        $(this).parent().css('border-color', '#e5e5e5');
    });



});

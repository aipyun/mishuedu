webpackJsonp(["app/js/course/explore/index"],{bf96baae4c1bc6c67d72:function(e,c,o){"use strict";$(".js-search-type").on("click",function(e){var c=$(e.currentTarget);window.location.href=c.val()}),$(".open-course-list").on("click",".section-more-btn a",function(e){var c=$(void 0).attr("data-url");$.ajax({url:c,dataType:"html",success:function(e){var c=$(".open-course-list .course-block,.open-course-list .section-more-btn",$(e)).fadeIn("slow");$(".section-more-btn").remove(),$(".open-course-list").append(c),echo.init()}})})}},["bf96baae4c1bc6c67d72"]);
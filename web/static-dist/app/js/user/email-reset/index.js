webpackJsonp(["app/js/user/email-reset/index"],{"013c192aad0c33a0f8a5":function(e,t,a){"use strict";var i=a("d69bf8068ab8ba1f8cc0");new(function(e){return e&&e.__esModule?e:{default:e}}(i).default)},d69bf8068ab8ba1f8cc0:function(e,t,a){"use strict";function i(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var n=a("7ab4a89ebadbfdecc2bf"),r=i(n),d=a("4602c3f5fe7ad9e3e91d"),s=i(d),u=a("bbc0ef257199acca9fed"),c=i(u),l=function(){function e(){(0,r.default)(this,e),this.drag=$("#drag-btn").length?new c.default($("#drag-btn"),$(".js-jigsaw"),{limitType:"web_register"}):null,this.container=$("#reset-email-form"),this.btn=$("#next-btn"),this.initValidator(),this.init()}return(0,s.default)(e,[{key:"init",value:function(){var e=this;e.btn.click(function(){e.validator.form()&&($btn.button("loadding"),e.container.submit())})}},{key:"initValidator",value:function(){var e=this;this.validator=e.container.validate({rules:{email:{required:!0,es_email:!0,es_remote:{type:"get"}},password:{required:!0,minlength:5,maxlength:20},dragCaptchaToken:{required:!0}},messages:{dragCaptchaToken:{required:Translator.trans("auth.register.drag_captcha_tips")}}})}}]),e}();t.default=l}},["013c192aad0c33a0f8a5"]);
//后面好像改了 没用这两个变量
var Domain = "yiliu88.com";
var StaticDomain = "s." + Domain;


//全局禁用 mobile的页面ajax加载(必须在jquery.mobile.js引用之前)，有些页面使用ajax加载有问题
$(document).bind("mobileinit", function () {
    $.mobile.ajaxEnabled = false
});

//底部登录注册加载
$(function () {
    var url = "/Api/UserInfo?action=show&Callback=?&v=" + Math.random();
    $.getJSON(url, function (json) {
        if (json.retData != "") {
            if (json.retData.uid > 0) {//&& json.retData.registerType != 3
                //成功登录的内容 json.retData.UserName
                var Username = "";
                if (json.retData.UserName != "" || json.retData.UserName != null) {
                    Username = json.retData.UserName;
                }
                else if (json.retData.Mobile != "") {
                    Username = json.retData.Mobile;
                }
                if (Username == "") {
                    Username = json.retData.uid;
                }
                //if (json.retData.OpenId == "") {
                //  //  alert(json.retData.OpenId);
                //    $.getJSON("/Api/UserInfo?action=openid&Callback=?&v=" + Math.random(), function (json) {
                //        alert(json.retData);
                //    });
                //}
              //  alert(json.retData.OpenId);
                $("#divLog").html("<li><a href='/UserCenter/MemberInfo'>" + Username + "</a></li><li><a href=\"javascript:LoginOutUrl();\"  target=\"_self\">退出</a></li><li><a href=\"#\">反馈</a></li><li style=\"border-right: 0px none;\"><a href=\"javascript:void(0)\" onclick=\"javascript:$('html,body').animate({scrollTop:0},400)\">返回顶部</a></li>");
            }
        }
    });
});



/*
一些js方法是 把PC端copy过来修改的 如果PC端有调整 可能wap端也需要调整
不能直接调用PC端的js 原因是有些不一样的地方 如果修改原始方法，会造成什么影响未知
*/

//正则表达式取字符串
//str 查找字符串 regstr 正则表达式 isite查询结果分组位置
function GetMach(str, regstr, isite) {
    var rePattan = eval("/" + regstr + "/ig"); //创建正则表达式模式;
    var re = new RegExp(rePattan);
    var r = re.exec(str)

    if (r == null || r == "") {
        return "";
    }
    else {
        if (isite < r.length) {
            return r[isite];
        }
        else {
            return r[0];
        }
    }
}

//正则表达式判断查询是否存在(存在返回true ,不存在返回false)
//str 查找字符串 regstr 正则表达式
function IsMach(str, regstr) {
    var rePattan = eval("/" + regstr + "/ig"); //创建正则表达式模式;
    var re = new RegExp(rePattan);
    return re.test(str)
}

//返回按钮
//str 预留 根据不同的值做不通的操作，暂时无效
function goBack(str) {
    window.history.go(-1);
}


//tag切换
function showboxTag(containID, showContent, selfObj, currentClass) {
    var tag = document.getElementById(containID).getElementsByTagName("li");
    var taglength = tag.length;
    for (var i = 0; i < taglength; i++) {
        tag[i].className = "";
    }
    selfObj.className = currentClass;
    var pre = showContent.replace(/\d+/g, "");
    for (j = 0; j < taglength; j++) {
        document.getElementById(pre + j).style.display = "none";
    }
    document.getElementById(showContent).style.display = "block";
}

//用户退出登录
function LoginOutUrl() {
    layer.open({
        content: '退出登录？',
        btn: ['确认', '取消'],
        shadeClose: false,
        yes: function () {
            var url = "/api/UserLogout?Callback=?&v=" + Math.random();
            $.getJSON(url, function (json) {
                if (json.retCode == 1) { //退出成功
                    window.location = window.location.href;
                }
            });
        }, no: function () {
        }
    });
}

//显示或隐藏一个元素
//str 元素ID
function ShowNav(str) {
    if ($("#" + str).is(":hidden")) {
        $("#" + str).show();
    }
    else {
        $("#" + str).hide();
    }
}

//--自定义弹出框(jquery mobile的接口)（类似alert提示,暂时没用 样式没做）--
function MyAlert(str) {
    if ($("#popdiv_alert").length > 0) {
        $("#popdiv_alert").remove();
    }
    $('<div id="popdiv_alert">' + str + '<a href="#" data-inline="true" data-rel="back">取消</a></div>').appendTo("body");
    var popupDialogObj = $('#popdiv_alert');
    //popupDialogObj.trigger('create');
    popupDialogObj.popup();
    popupDialogObj.popup("open");
}


//搜索框
//defstr当关键字为空时候默认查询的关键词
function submitSearch(defstr) {
    var keywords = $("#keywords").val();
    if (keywords == "") {
        $("#keywords").val(defstr); //默认值 最后根据要求修改
    }
    if (!keywords) {
        layer.open({
            content: '请输入查询词!',
            style: 'background-color:#09C1FF; color:#fff; border:none;',
            time: 2
        });
        $("#keywords").focus();
        return;
    }
    document.getElementById("frmSearch").submit();
}

//生成表单
function CreateForm(actionadd, field, values, tagert) {
    if ($("#frmSearch").length > 0) {
        $("#frmSearch").remove();
    }
    form = $("<form></form>");
    form.attr('name', 'frmSearch');
    form.attr('id', 'frmSearch');
    form.attr('action', actionadd);
    form.attr('method', 'post');
    if (tagert != "") {
        form.attr('target', tagert);
    }

    var arrfield = field.split("/**/");
    var arrvalue = values.split("/**/");
    for (var i = 0; i < arrfield.length; i++) {
        form.append($("<input type='hidden' name='" + arrfield[i] + "' id='" + arrfield[i] + "' value='" + arrvalue[i] + "' />"));
    }
    form.appendTo("body");
    form.css('display', 'none');

    document.getElementById("frmSearch").submit();
}

//列表页的排序
function ListSort(str, type) {
    var theSort = 0;
    if (type == 1) { //热度排序
        if (str >= 3) { //当前是热度升序排（str==3）或者是价格排序（str>3）
            theSort = 2; //倒序排
        }
        else {
            theSort = 3; //升序排
        }
    }
    else { //价格排序
        if (str == 5 || str < 4) { //当前是价格升序排(str == 5) 或者 热度排序(str<4)
            theSort = 4; //倒序排
        }
        else {
            theSort = 5; //升序排
        }
    }
    document.getElementById("sortway").value = theSort; //这个是分页里面隐藏的文本域，列表页是肯定有这个字段的
    document.getElementById("frm_page").submit();
}

//--详情页-----
//数量减少
function delBaseBum(obj) {
    var buyBaseBum = $("#BuyBaseNum").val(); //最小购买基数
    var LimitBuyNum = $("#LimitBuyNum").val(); //最大购买数

    var baseNum = parseInt($("#" + obj).val());
    if (parseInt(baseNum) > parseInt(buyBaseBum) && parseInt(baseNum) - parseInt(buyBaseBum) > 0) {
        baseNum -= parseInt(buyBaseBum);
    }
    else {
        baseNum = buyBaseBum;
    }
    $("#" + obj).val(baseNum);
}

//数量增加
function addBaseBum(obj, num) {
    var buyBaseBum = $("#BuyBaseNum").val(); //最小购买基数
    var LimitBuyNum = $("#LimitBuyNum").val(); //最大购买数

    var baseNum = parseInt($("#" + obj).val());
    if (baseNum >= num && num > 0) {
        layer.open({
            content: '超过库存量!',
            style: 'background-color:#09C1FF; color:#fff; border:none;',
            time: 2
        });
        baseNum = num;
    }
    else {
        baseNum += parseInt(buyBaseBum);
    }
    if (parseInt(LimitBuyNum) > 0 && baseNum > parseInt(LimitBuyNum)) {
        baseNum = parseInt(LimitBuyNum);
    }
    $("#" + obj).val(baseNum);
}

//数量检查
function CheckBuyNum(obj, num) {
    var buyBaseBum = $("#BuyBaseNum").val(); //最小购买基数
    var LimitBuyNum = $("#LimitBuyNum").val(); //最大购买数
    var tmpBuyNum = parseInt($("#" + obj).val());
    if (tmpBuyNum > num && num > 0) {
        layer.open({
            content: '超过库存量!',
            style: 'background-color:#09C1FF; color:#fff; border:none;',
            time: 2
        });
        tmpBuyNum = num;
    }
    if (tmpBuyNum < buyBaseBum) {
        tmpBuyNum = buyBaseBum;
    }
    else {
        if (parseInt(LimitBuyNum) > 0 && tmpBuyNum > parseInt(LimitBuyNum)) {
            tmpBuyNum = LimitBuyNum;
        }
        if (tmpBuyNum % parseInt(buyBaseBum) != 0) {
            tmpBuyNum = buyBaseBum;
        }
    }
    $("#" + obj).val(tmpBuyNum);
    $("#" + obj).focus();
}

//ajax加载产品详细信息
//str产品标识ID
function GetProductDetail(str, loadDiv, containID) {
    var index = layer.open({
        content: '产品详情图片较多，可能会消耗较多流量，是否继续访问？',
        btn: ['确认', '取消'],
        shadeClose: false,
        yes: function () {
            $.ajax({
                type: "POST",
                url: "/Api/ProductDetail",
                data: "id=" + str,
                beforeSend: function () {
                    document.getElementById(loadDiv).innerHTML = "加载中...";
                    layer.close(index);
                },
                success: function (data) {
                    document.getElementById(containID).innerHTML = data;
                    layer.close(index);
                }
            });
        }, no: function () {
        }
    });
}

/*------------------------用户中心相关-------------------------------------------------*/
//去付款
function MemberGoPay(paywaryen, pno) {
    self.location = "/Cart/Pay/" + paywaryen + "/" + pno + "";
}

///取消订单
function ordercancel(idno) {
    var url = "/Api/OrderInfo?action=ordercancel&orderid=" + idno + "&v=" + Math.random();
    $.getJSON(url, function (json) {
        if (json.retData != "") {
            // document.getElementById("frm_page").submit(); //暂时只有列表页面有这个功能，如果其它页面也有再来修改
            //alert("取消订单成功");
            layer.open({
                content: '取消订单成功!',
                style: 'background-color:#09C1FF; color:#fff; border:none;',
                time: 2
            });
            window.location.reload();
        }
        else {
            //alert("取消订单失败");
            layer.open({
                content: '取消订单失败!',
                style: 'background-color:#09C1FF; color:#fff; border:none;',
                time: 2
            });
        }
    });
}

///恢复订单
function orderrecovery(idno) {
    var url = "/Api/OrderInfo?action=orderrecovery&orderid=" + idno + "&r=" + Math.random();
    $.getJSON(url, function (json) {
        if (json.retData != "") {
            //alert("恢复订单成功");
            layer.open({
                content: '恢复订单成功!',
                style: 'background-color:#09C1FF; color:#fff; border:none;',
                time: 2
            });
            //document.getElementById("frm_page").submit(); //暂时只有列表页面有这个功能，如果其它页面也有再来修改
            window.location.reload();
        }
        else {
            //alert("恢复订单失败");
            layer.open({
                content: '恢复订单失败!',
                style: 'background-color:#09C1FF; color:#fff; border:none;',
                time: 2
            });
        }
    });
}



/*----------------------------------购物车相关---------------------------------------------------------------*/
//立即购买
function Buynows() {
    var productype = $("#productType").val();
    var properteptyvalue = $("#propertleftyvalue").val() + "|" + $("#propertrightyvalue").val();
    if (productype != "1") {
        properteptyvalue = "";
    }
    var url = "/Api/CartInfo?action=cartproductadd&pid=" + $("#productid").val() + "&num=" + $("#productnum").val() + "&color=" + $("#productcolor").val() + "&size=" + $("#productsize").val() + "&propertyvalue=" + properteptyvalue + "&Callback=?&v=" + Math.random();
    if (properteptyvalue == "|" && productype == "1") {
        layer.open({
            content: '购买失败,请选择光度！',
            style: 'background-color:#09C1FF; color:#fff; border:none;',
            time: 2
        });
        properteptyvalue = "";
        url = "";
    }

    $.getJSON(url, function (json) {
        $("#CartCollectinfo").html("");
        $("#CartCollectinfo").html("<h4 id=\"addcartret\">" + json.retMsg + "</h4>");
        if (json.retCode == 1 || json.retCode == 2) {
            var tempRet = json.retData.toString().split("|");
            if (tempRet.length > 1) {
                window.location.href = "/Cart";
            }
            else {
                layer.open({
                    content: '系统错误！',
                    style: 'background-color:#09C1FF; color:#fff; border:none;',
                    time: 2
                });
            }
        }
    });
}


//添加商品到购物车去
function AddCart() {
    //    var productype = $("#productType").val();
    //    var properteptyvalue = $("#propertleftyvalue").val() + "|" + $("#propertrightyvalue").val();
    //    if (productype != "1") {
    //        properteptyvalue = "";
    //    }
    var properteptyvalue = "";
    var url = "/Api/CartInfo?action=cartproductadd&pid=" + $("#productid").val() + "&num=" + $("#productnum").val() + "&color=" + $("#productcolor").val() + "&size=" + $("#productsize").val() + "&propertyvalue=" + properteptyvalue + "&Callback=?&v=" + Math.random();
    //    if (properteptyvalue == "|" && productype == "1") {
    //        alert("购买失败,请选择光度！");
    //        properteptyvalue = "";
    //        url = "";
    //    }

    $.getJSON(url, function (json) {
        //json.retData==true为成功
        //$("#CartCollectinfo").html("");
        //$("#CartCollectinfo").html("<h4 id=\"addcartret\">" + json.retMsg + "</h4>");
        if (json.retCode == 1 || json.retCode == 2) {
            //alert(json.retData.toString());
            var tempRet = json.retData.toString().split("|");
            if (tempRet.length > 1) {
                //                $("#cartnum").html(tempRet[0]);
                //                $("#cartprice").html(tempRet[1]);
                //                $(".cartinfo").show();
            }


            layer.open({
                content: '购物车添加成功！',
                style: 'background-color:#09C1FF; color:#fff; border:none;',
                time: 2
            });

            //            var html = "";
            //            html += " 购物车共有<span id=\"cartnum\">" + tempRet[0] + "</span>件商品，合计：<span><b id=\"cartprice\">" + tempRet[1] + "</b></span>元<br />";
            //            html += "<input class=\"fl\"  type=\"image\"  onclick=\"GoToCart();\"  src=\"http://" + StaticDomain + "/Images/list/js_btn.jpg\" />";
            //            html += "<a href=\"javascript:AddCartClose();\">继续购物>></a>";
            //$("#addcartret").after(html);
        }
    });

    var url = "/api/CartInfo?action=cartproductnum&Callback=?&v=" + Math.random();
    $.getJSON(url, function (json) {
        if (json.retData != "") {
            $("#cartnum").html(json.retData);
        } else {
            $("#cartnum").html(json.retData);
        }
    });
}


//添加商品到购物车去——套餐
function GroupAddCart(groupid) {
    var productype = $("#productType").val();
    var properteptyvalue = $("#propertleftyvalue").val() + "|" + $("#propertrightyvalue").val();
    if (productype != "1") {
        properteptyvalue = "";
    }
    var url = "/Api/CartInfo?action=cartproductadd&pid=" + $("#productid").val() + "&num=" + 1 + "&color=" + $("#productcolor").val() + "&size=" + $("#productsize").val() + "&propertyvalue=" + properteptyvalue + "&pgid=" + groupid + "&Callback=?&v=" + Math.random();
    if (properteptyvalue == "|" && productype == "1") {
        alert("购买失败,请选择光度！");
        properteptyvalue = "";
        url = "";
    }

    $.getJSON(url, function (json) {
        if (json.retCode == 1 || json.retCode == 2) {
            var tempRet = json.retData.toString().split("|");
            if (tempRet.length > 1) {
                layer.open({
                    content: '购物车添加成功！',
                    style: 'background-color:#09C1FF; color:#fff; border:none;',
                    time: 2
                });
                window.location.href = "/product/" + $("#productid").val() + ".html";
            }
            else {
                layer.open({
                    content: '购物车添加失败！',
                    style: 'background-color:#09C1FF; color:#fff; border:none;',
                    time: 2
                });
            }
        }
    });
}


//地区选择
function ChangeAddressArea(action) {
    document.getElementById("ctadd" + action).innerHTML = $("#slarea" + action).find("option:selected").text();
    OnLoadAddressArea(action + 1, $("#slarea" + action).val());
    SelectAddressArea(action);
}
//获取地区选择值
function SelectAddressArea(action) {
    if (action == 3) {
        $("#lbaddress").html($("#slarea1").find("option:selected").text() + "，" + $("#slarea2").find("option:selected").text() + "，" + $("#slarea3").find("option:selected").text());
        $("#deliverareaid").val($("#slarea3").val());
        $("#areaid").val($("#slarea3").val());
        CartChangePayWayNew();
    }
    else if (action == 2) {
        $("#lbaddress").html($("#slarea1").find("option:selected").text() + "，" + $("#slarea2").find("option:selected").text());
    }
    else {
        $("#lbaddress").html($("#slarea1").find("option:selected").text());
    }
}

//加载地区信息
function OnLoadAddressArea(action, pid) {
    var url = "/Api/AreaInfo?action=" + action + "&pid=" + pid + "&Callback=?&v=" + Math.random();
    $.getJSON(url, function (json) {
        if (json.retData != "") {
            //$("#slarea" + action).empty();
            for (var num = action; num <= 3; num++) {
                $("#slarea" + num).empty();
            }
            if (action == 1) {
                $("#slarea" + (action)).append("<option value=\"0\">--请选择省份--</option>");
                $("#slarea" + (action + 1)).append("<option value=\"0\">--请选择城市--</option>");
                $("#slarea" + (action + 2)).append("<option value=\"0\">--请选择地区--</option>");
            }
            else if (action == 2) {
                document.getElementById("ctadd" + action).innerHTML = "--请选择城市--";
                document.getElementById("ctadd" + (action + 1)).innerHTML = "--请选择地区--";
                $("#slarea" + (action)).append("<option value=\"0\">--请选择城市--</option>");
                $("#slarea" + (action + 1)).append("<option value=\"0\">--请选择地区--</option>");
            }
            else if (action == 3) {
                document.getElementById("ctadd" + action).innerHTML = "--请选择地区--";
                $("#slarea" + (action)).append("<option value=\"0\">--请选择地区--</option>");
            }

            var data = json.retData;
            var tempHtml = "";
            var codeId = "";

            for (var i = 0; i < data.length; i++) {
                if (i == 0) {
                    codeId = data[i]["CodeId"];
                }
                $("#slarea" + action).append("<option id=" + data[i]["CodeId"] + " value=\"" + data[i]["CodeId"] + "\"   >" + data[i]["CodeName"] + "</option>")

            }
            if (action == 2) {
                OnloadAreaddres1();
            }
            if (action == 3) {
                OnloadAreaddres2();
            }
            if (action + 1 < 3) {
                // OnLoadAddressArea(action + 1, codeId);
            }
        }

    });
}

function OnloadAreaddres() {
    var seal1 = $("#provinces1").val();
    if ($("#" + seal1 + "").val() != "") {
        $("#" + seal1 + "").attr("selected", "selected");
        document.getElementById("ctadd1").innerHTML = $("#" + seal1 + "").text();
    }

}

function OnloadAreaddres1() {
    var seal2 = $("#city2").val();
    if ($("#" + seal2 + "").length > 0 && $("#" + seal2 + "").val() != "") {
        $("#" + seal2 + "").attr("selected", "selected");
        document.getElementById("ctadd2").innerHTML = $("#" + seal2 + "").text();
        OnLoadAddressArea(3, $("#slarea2").val());
    }
}
function OnloadAreaddres2() {
    var seal3 = $("#area3").val();
    if ($("#" + seal3 + "").length > 0 && $("#" + seal3 + "").val() != "") {
        $("#" + seal3 + "").attr("selected", "selected");
        document.getElementById("ctadd3").innerHTML = $("#" + seal3 + "").text();
        OnLoadAddressArea(4, $("#slarea3").val());
    }
}

//------------------------------------------------订单提交页面----------------------------------------------------------
//显示与获取当前地址信息
function OrderAddressCurrentEdit(id) {
    //$("input[name=rddeliver][value=" + id + "]").attr("checked", "checked"); //jquery不熟 没找到原因 为什么这句会有问题
    var rddeliverList = $("input[name=rddeliver]");
    for (var rdi = 0; rdi < rddeliverList.length; rdi++) {
        if (rddeliverList[rdi].value == id) {
            rddeliverList[rdi].checked = true;
        }
        else {
            rddeliverList[rdi].checked = false;
        }
    }

    $(".o_middle").show();
    var url = "/api/AddressInfo?action=get&id=" + id + "&v=" + Math.random();
    $.getJSON(url, function (json) {
        if (json.retData != "") {
            var data = json.retData;

            if (data.length > 0) {
                $("#modification").val(1);
                $("#receivname").val(data[0]["DeliverName"]);
                //$("#lbaddress").html(data[0]["deliverarea"]);
                $('input[type="radio"][name="p_choose"][value=' + data[0]["SamePeople"] + ']').attr("checked", true);
                //$("#tel").val(data[0]["DeliverTel"]);
                //$("#zip").val(data[0]["PostCode"]);
                $("#mobile").val(data[0]["DeliverMobile"]);
                $("#deliverareaid").val(data[0]["DeliverAreaId"]);
                $("#address2").val(data[0]["DeliverAddress"]);
                //$("#DebitName").val(data[0]["DebitName"]);
                //$("#DebitTel").val(data[0]["DebitTel"]);
                //                if (data[0]["SamePeople"].toString() == "0") {
                //                    $("#dgr").show();
                //                    $("#lxfs").show();
                //                    $("#DebitName").attr("datatype", "z2-4");
                //                    $("#DebitTel").attr("datatype", "phone");
                //                }
                //                else {
                //                    $("#dgr").hide();
                //                    $("#lxfs").hide();
                //                    $("#DebitName").removeAttr("datatype");
                //                    $("#DebitTel").removeAttr("datatype");
                //                }
                $(".o_middle").show();
                OrderAddressArea(data[0]["DeliverAreaId"]);
            }
        }
    });
}

function OrderAddressArea(CodeId) {
    var url = "/api/AreaInfo?action=orderareaadress&CodeId=" + CodeId + "&v=" + Math.random();
    $.getJSON(url, function (json) {
        var data = json.retData;
        if (data.length > 0) {
            $("#provinces1").val(data[0]["CodeId"]);
            $("#city2").val(data[1]["CodeId"]);
            $("#area3").val(data[2]["CodeId"]);
            OnloadAreaddres();
            OnLoadAddressArea(2, $("#slarea1").val());
        }
    });
}

//选择支付方式
//function CartChangePayWayNew() {
//    var productWeight = $("#productWeight").val();
//    var productPrice = $("#productPrice").val();
//    $("#trdevhead").html("&nbsp;");
//    var url = "/Api/CartInfo?action=cartfreight&payway=" + $('input[type="radio"][name="payway"]:checked').val() + "&weight=" + productWeight + "&productprice=" + productPrice + "&citycodeid=" + $("#citycodeid").val() + "&v=" + Math.random();
//    $.getJSON(url, function (json) {
//        if (json.retData) {
//            for (var i = 0; i < json.retData.length; i++) {
//                var tmpDeliver = "<div>"
//                tmpDeliver += "<input class=\"check fl\" data-role=\"none\" type=\"radio\" id=\"rad" + json.retData[i]["DeliverTypeId"] + "\" name=\"DeliverTypeName\" pname=\"" + json.retData[i]["DeliverTypeName"] + "\" value=\"" + json.retData[i]["DeliverTypeId"] + "\"   /><span>" + json.retData[i]["DeliverTypeName"] + "</span>&nbsp;&nbsp;&nbsp;&nbsp;";
//                var pweight = json.retData[i]["freight"]; //运费
//                var pType = $('input[type="radio"][name="payway"]:checked').val(); //支付方式
//                if (pType == 3) { //货到付款加5元运费
//                    pweight = pweight + 5;
//                    $("#divcoupons").hide();//优惠券
//                    $("#couponsekeyid").val("0");//优惠券id
//                    $("#couponsmoney").val("0");//优惠券金额
//                    $('input[type="radio"][name="rdcoupons"]:eq(0)').attr("checked", "checked");//优惠券选择空
//                    // $("#yhj").html("0");
//                    // $("#spanIncreasePrice1").html("0.00");
//                }
//                else {
//                    $("#divcoupons").show();//优惠券
//                }
//                var pDeliverFee = "";
//                if (pweight == 0) {
//                    pDeliverFee = "免运费";
//                }
//                else {
//                    pDeliverFee = parseFloat(pweight).toFixed(2) + "元";
//                }
//                tmpDeliver += "<span id='pfreight" + json.retData[i]["DeliverTypeId"] + "'>" + pDeliverFee + "</span>";
//                $("#transportprice").val(parseFloat(pweight).toFixed(2));//运费
//                // $("#pyunfeitotal").html(parseFloat(pweight).toFixed(2));
//                tmpDeliver += "</div>";
//                $("#trdevhead").html(tmpDeliver);

//            }
//            $(':radio[name=DeliverTypeName]').each(function () {
//                $(this).attr('checked', 'checked');
//                $(this).trigger("click");
//                return false;
//            });
//            //计算代码//
//            OrderPriceCalculation();
//        }
//    });
//}

function CartChangePayWayNew() {
    var productWeight = $("#productWeight").val();
    var productPrice = $("#productPrice").val();
    var areaid = $("#areaid").val();
    var proprice = $("#prosums").val();
    var ProductTypeId = $("#protypes").val();
    var ProductNum = $("#pronums").val();
    $("#trdevhead").html("&nbsp;");
    var url = "/Api/CartInfo?action=cartfreight&payway=" + $('input[type="radio"][name="payway"]:checked').val() + "&weight=" + productWeight + "&productprice=" + productPrice + "&citycodeid=" + $("#citycodeid").val() + "&areaid=" + areaid + "&proprice=" + proprice + "&ProductTypeId=" + ProductTypeId + "&ProductNum=" + ProductNum + "&v=" + Math.random();
    $.getJSON(url, function(json) {
        if (json.retData) {
            for (var i = 0; i < json.retData.length; i++) {
                var tmpDeliver = "<div>"
                tmpDeliver += "<input class=\"check fl\" data-role=\"none\" type=\"radio\" id=\"rad" + json.retData[i]["DeliverTypeId"] + "\" name=\"DeliverTypeName\" pname=\"" + json.retData[i]["DeliverTypeName"] + "\" value=\"" + json.retData[i]["DeliverTypeId"] + "\"   /><span>" + json.retData[i]["DeliverTypeName"] + "</span>&nbsp;&nbsp;&nbsp;&nbsp;";
                var pweight = json.retData[i]["freightprice"]; //运费
                var pType = $('input[type="radio"][name="payway"]:checked').val(); //支付方式
                if (pType == 3) { //货到付款加5元运费
                    pweight = pweight + 5;
                    $("#divcoupons").hide(); //优惠券
                    $("#couponsekeyid").val("0"); //优惠券id
                    $("#couponsmoney").val("0"); //优惠券金额
                    $('input[type="radio"][name="rdcoupons"]:eq(0)').attr("checked", "checked"); //优惠券选择空
                    // $("#yhj").html("0");
                    // $("#spanIncreasePrice1").html("0.00");
                }
                else {
                    $("#divcoupons").show(); //优惠券
                }
                var pDeliverFee = "";
                if (pweight == 0) {
                    pDeliverFee = "免运费";
                }
                else {
                    pDeliverFee = parseFloat(pweight).toFixed(2) + "元";
                }
                tmpDeliver += "<span id='pfreight" + json.retData[i]["DeliverTypeId"] + "'>" + pDeliverFee + "</span>";
                $("#transportprice").val(parseFloat(pweight).toFixed(2)); //运费
                // $("#pyunfeitotal").html(parseFloat(pweight).toFixed(2));
                tmpDeliver += "</div>";
                $("#trdevhead").html(tmpDeliver);

            }
            $(':radio[name=DeliverTypeName]').each(function() {
                $(this).attr('checked', 'checked');
                $(this).trigger("click");
                return false;
            });
            //计算代码//
            OrderPriceCalculation();
        }
    });
}
//使用积分
function UpdateUserInputIntegralNew(cat) {
    var nubmer = parseInt($("#useuserintegral").val());
    var paymoney = parseFloat($("#ordertotal").val());//总金额
    if (isNaN(nubmer) || nubmer <= 0 || !(/^\d+$/.test(nubmer))) {
        $("#useuserintegral").val(0);
    }
    else {
        //计算代码//
        OrderPriceCalculation();
    }
}

//激活优惠券
function CouponsActive() {
    var paytype = $('input[type="radio"][name="payway"]:checked').val();
    if (paytype != "3") {
        $("#couponsmoney").val(0);
        $("#couponsekeyid").val(0);
        //if ($("#chkcoupons").attr("checked")) {

        var url = "/Api/Coupons?action=get&couponspwd=" + $("#couponspwd").val() + "&v=" + Math.random() + "&couponscats=" + $("#couponscats").val();
        $.getJSON(url, function (json) {

            var html = "";
            switch (json.retCode) {
                case 1:
                case 100:
                    html += "<li><input rate=\"0\" onclick=\"CouponsCheck();\"  money=\"0\" type=\"radio\" name=\"rdcoupons\" keyid=\"\" CouponsType=\"\"  value=\"\"  />不使用优惠券</li>";
                    var data = json.retData;
                    if (data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            html += "<li><input rate=\"" + parseFloat(data[i]["CouponsConsumptionRate"]).toFixed(2) + "\" onclick=\"CouponsCheck();\"  money=\"" + parseFloat(data[i]["CouponsDenomination"]).toFixed(2) + "\" type=\"radio\" name=\"rdcoupons\" keyid=\"" + data[i]["CouponsId"] + "\" CouponsType=\"" + data[i]["CouponsType"] + " value=\"\" checked />" + (data[i]["CouponsType"] == 1 ? "实体优惠券" : "电子优惠卷") + data[i]["CouponsDenomination"] + "元，购物满" + data[i]["CouponsConsumptionRate"] + "元可使用。" + "(有效期" + data[i]["ExpiredTime1"] + ")</li>";
                        }
                        $("#ulcoupons").html(html);
                        $(':radio[name=rdcoupons]').each(function () {
                            jQuery(this).attr('checked', 'checked');
                            $(this).trigger("click");
                            return false;
                        });
                    }
                    else if ($("#couponspwd").val() != "") {

                        html = "<li>当前优惠券已失效</li>";
                        $("#ulcoupons").html(html);
                    }
                    break;
                case 2:
                case 3:
                case 4: //当前优惠券已失效
                default:
                    html = "<li>当前优惠券已失效</li>";
                    $("#ulcoupons").html(html);
                    break;
            }

        });

    }
}

//选择优惠券
function CouponsCheck() {
    var paytype = $('input[type="radio"][name="payway"]:checked').val();
    if (paytype != "3") {
        var url = "/api/Coupons?action=check&CouponsId=" + $('input[type="radio"][name="rdcoupons"]:checked').attr("keyid") + "&couponscats=" + $("#couponscats").val() + "&v=" + Math.random();
        $.getJSON(url, function (json) {
            if (json.retCode == 100) {
                var money = $('input[type="radio"][name="rdcoupons"]:checked').attr("money") //可优惠金额
                if (parseFloat(money) > 0) {
                    $("#useuserintegral").val("0");
                }

                var limitmoney = $('input[type="radio"][name="rdcoupons"]:checked').attr("rate"); //限制金额
                var kid = $('input[type="radio"][name="rdcoupons"]:checked').attr("keyid"); //kid
                var ordertotal = parseFloat($("#ordertotal").val());
                if (ordertotal > 0 && ordertotal >= limitmoney && (ordertotal - money) > 0) {


                    $("#couponsmoney").val(money);
                    $("#couponsekeyid").val(kid);
                    //计算代码//
                    OrderPriceCalculation();
                }
                else {
                    alert("不符合使用优惠券条件");
                    //$("#couponprice1").hide();
                }
            }
            else {
                var money = $('input[type="radio"][name="rdcoupons"]:checked').attr("money") //可优惠金额

                var limitmoney = $('input[type="radio"][name="rdcoupons"]:checked').attr("rate"); //限制金额
                var kid = $('input[type="radio"][name="rdcoupons"]:checked').attr("keyid"); //kid
                var ordertotal = parseFloat($("#ordertotal").val());
                if (ordertotal > 0 && ordertotal >= limitmoney && (ordertotal - money) > 0) {

                    $("#couponsmoney").val(money);
                    $("#couponsekeyid").val(kid);
                    //计算代码//
                    OrderPriceCalculation();
                }
                else {
                    //$("#couponprice1").hide();
                }
            }
        });
    } else {
        $('input[type="radio"][name="rdcoupons"]:eq(0)').attr("checked", "checked");
        //alert("货到付款不能使用优惠券");
    }
}

//计算金额
function OrderPriceCalculation() {
    /*-------运费----------*/
    var transportprice = parseFloat($("#transportprice").val());
    $("#pyunfeitotal").html(transportprice.toFixed(2));
    /*-------运费end----------*/

    /*-------积分----------*/
    var userintegraltotal = parseFloat($("#userintegraltotal").html()); //可用积分
    var useuserintegral = parseInt($("#useuserintegral").val()); //当前将使用积分
    if (useuserintegral > userintegraltotal) {
        useuserintegral = userintegraltotal;
        $("#useuserintegral").val(useuserintegral);
    }
    var userintegraldkmoney = parseInt(useuserintegral * 0.01);
    $("#useintegraldkm").html(useuserintegral);
    $("#userintegraldkmoney").html(parseFloat(userintegraldkmoney).toFixed(2));
    /*-------积分end----------*/

    /*-------优惠券----------*/
    var couponsmoney = parseFloat($("#couponsmoney").val());
    var couponsekeyid = $("#couponsekeyid").val();
    if (couponsmoney != "0") {
        useuserintegral = 0;
        $("#useuserintegral").val(0);
        $("#useintegraldkm").html("0");
        $("#userintegraldkmoney").html("0.00");
    }
    $("#yhj").html(couponsmoney.toFixed(2));
    $("#spanIncreasePrice1").html(couponsmoney.toFixed(2));
    /*-------优惠券end----------*/

    /*-------产品价格----------*/
    var productPrice = parseFloat($("#productPrice").val());
    /*-------产品价格end----------*/

    /*-------订单总金额----------*/
    var ordertotal = parseFloat(productPrice) + parseFloat(transportprice) - parseFloat(userintegraldkmoney) - parseFloat(couponsmoney);
    $("#ordertotal").val(ordertotal.toFixed(2));
    $("#paymoney").html("￥" + ordertotal.toFixed(2));
    /*-------订单总金额end----------*/
}
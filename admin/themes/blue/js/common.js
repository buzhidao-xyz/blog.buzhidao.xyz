$(document).ready(function() {
    /**
     * 更新顶部菜单样式(背景、选中)
     * 更新顶部链接地址说明(navlink)内容
     */
    $("#header .nav ul.hmenu li").click(function() {
        $("#header .nav ul.hmenu li").removeClass('navon');
        $(this).addClass("navon");
    });
    
    /**
     * 更改左侧菜单选中背景样式
     * 更新顶部链接地址说明(navlink)内容
     */
    $(".menu ul li a").live('click', function() {
        var thisa = $(this);
        $(this).parent().parent().find(".menusub").slideToggle(100,function(){
            if ($(this).css("display") == "block")
                thisa.removeClass("plus").addClass("mius");
            else
                thisa.removeClass("mius").addClass("plus");
        });
    });
    $(".menu .menusub li a").live('click', function() {
        $(".menu .menusub li a").removeClass('tabon');
        $(this).addClass('tabon');
    });

    //colorbox edit触发方法
    $("a[name=colorboxEdit]").click(function (){
        window.parent.colorboxAjaxHtml($(this).attr("href"));
        return false;
    });

    /*添加的FORM ajax提交方法*/
    $('#ajaxform').live("submit", function(){
        // $("#ajaxform input:[type=submit]").attr('disabled','disabled');
        if($('#ajaxform').length>0){
            var data = $("#ajaxform").serialize();
            var url = $('#ajaxform').attr('action');
            $.post(url,data,function(data){
                alert(data.info);
                if (!data.status) {
                    location.href = location.href;
                    // $("#ajaxform input:[type=submit]").removeAttr('disabled');
                }
            }, 'json');
            return false;
        } else {
            alert('表单为空');
        }
    });
    /*添加的FORM ajax提交方法*/

    /*colorbox的FORM ajax提交方法*/
    $('#colorBoxAjaxForm').live("submit", function(){
        // $("#ajaxform input:[type=submit]").attr('disabled','disabled');
        if($('#colorBoxAjaxForm').length>0){
            var data = $("#colorBoxAjaxForm").serialize();
            var url = $('#colorBoxAjaxForm').attr('action');
            $.post(url,data,function(data){
                alert(data.info);
                if (!data.status) {
                    $.colorbox.close();
                    window.frames["main"].location.reload();
                    // $("#colorBoxAjaxForm input:[type=submit]").removeAttr('disabled');
                }
            }, 'json');
            return false;
        } else {
            alert('表单为空');
        }
    });
    /*colorbox的FORM ajax提交方法*/

    ullilist={};
    //数据表格操作
    /*删除按钮绑定*/
    ullilist.delajax = function(that){
        if (that.attr('isdel')){
            var msg = "确定恢复吗？";
        } else if (that.attr('ischeck')){
            var msg = "确定通过吗？";
        } else {
            var msg = "确定删除吗？";
        }
        if (that.attr('msg')) var msg = that.attr('msg');
        if(confirm(msg)){
            var d = {delid:that.attr('delid'), action:'del',delname:that.attr('delname')};
            if(that.attr('a')) d.a = that.attr('a');
            if(that.attr('m')) d.m = that.attr('m');
            $.post(that.attr('delurl'), d, function(data){
                if(ullilist.alertres(data)){
                    that.parent().parent().hide();
                };          
            }, 'json');
        };         
    }
    ullilist.alertres = function(data){
        alert(data.info);
        if(!data.status){
            location.href = location.href;
        }
    }
    /*删除按钮绑定*/
    
    $('a[name=del]').live("click", function(){
        ullilist.delajax($(this));
    });

    //监控ajax请求链接
    $("a[name=ajax]").click(function (){
        var d = {
            data: $(this).attr("data")
        }

        var msg = $(this).attr("msg");
        if (msg && confirm(msg)) {
            $.post($(this).attr("href"), d, function(data){
                alert(data.info);
                if (!data.status) location.href = location.href;
            }, 'json');
        } else {
            $.post($(this).attr("href"), d, function(data){
                alert(data.info);
                if (!data.status) location.href = location.href;
            }, 'json');
        }
        return false;
    });
});
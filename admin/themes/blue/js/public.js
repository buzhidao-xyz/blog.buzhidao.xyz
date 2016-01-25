$(document).ready(function() {
    /** table list样式 */
    $("div.contentTableList ul.table_list").each(function (){
        if (!($(this).index()%2 == 1)) $(this).addClass("bgfff");
    });
    /** table list样式 */

    /** input样式定义 为Hover兼容IE样式 */
    $("input:[type='text'],textarea").hover(function() {
        $(this).addClass("input_textarea_hover");
    },function() {
        $(this).removeClass("input_textarea_hover");
    });
    $("input:[type='text'],textarea").focus(function(){
        $(this).addClass("input_textarea_hover");
    });
    $("input:[type='text'],textarea").blur(function(){
        $(this).removeClass("input_textarea_hover");
    });
    /** end */
    
    //对只有节点浏览权限的用户隐藏掉所有增删改查操作功能链接
    $("*[accessStatus=0]").remove();

    //角色配置页面 是否配置角色的功能节点
    $("input[isSetNode=1]").click(function (){
        $("#nodeTree").slideDown(300);
    });

    //选项卡切换
    $(".shtabtitlec .shtabtitle").click(function (){
        var index = $(this).index();
        $(".shtabtitlec .shtabtitle").removeClass("shtabcurrent");
        $(this).addClass("shtabcurrent");
        $(".shtabcontentc .shtabcontent").removeClass("shtabcontentcurrent");
        $(".shtabcontentc .shtabcontent:eq("+index+")").addClass("shtabcontentcurrent");
    });

    //返回上一页
    $("#goback").click(function (){
        window.history.go(-1);
    });

    //左菜单显示隐藏
    $("#menuSlide").live("click",function (){
        var display = $("#menuTree .menu").css("display");
        if (display == "none") {
            $("#menuTree .menu").css("display","block");
            $("#menuTree .menu").animate({
                width: "185px"
            },300,"swing",function (){
                $("#menuSlide .menuSlidebg").removeClass("menuSlidebg2").addClass("menuSlidebg1");
            });
            $("#menuTree .menu").animate({
                width: "185px"
            },300);
            $("#menuSlide .menu").animate({
                left: "185px"
            },300);
            $("#main").animate({
                left: "191px"
            },300);
        } else {
            $("#menuTree .menu").animate({
                width: "0px"
            },300,"swing",function (){
                $("#menuTree .menu").css("display","none");
                $("#menuSlide .menuSlidebg").removeClass("menuSlidebg1").addClass("menuSlidebg2");
            });
            $("#menuTree .menu").animate({
                width: "0px"
            },300);
            $("#menuSlide .menu").animate({
                left: "0px"
            },300);
            $("#main").animate({
                left: "5px"
            },300);
        }
    });

    //数据列表序号
    var tablenolength = $(".contentTableList ul.table_list li.table_list_no").length;
    if (tablenolength) {
        for (i=0; i<tablenolength; i++) {
            $(".contentTableList ul.table_list li.table_list_no:eq("+i+")").text(parseInt($(".contentTableList ul.table_list li.table_list_no:eq(0)").text())+i);
        }
    }
});
$(document).ready(function() {
    /**
     * input样式定义 为Hover兼容IE样式
     */
    $("input,textarea").hover(function() {
        $(this).addClass("input_textarea_hover");
    },function() {
        $(this).removeClass("input_textarea_hover");
    });
    $("input,textarea").focus(function(){
        $(this).css("border-color","#0099CC");
        $(this).css("background","#F5F9FD");
    });
    $("input,textarea").blur(function(){
        $(this).css("border-color","");
        $(this).css("background","");
    });
    /** end */
});
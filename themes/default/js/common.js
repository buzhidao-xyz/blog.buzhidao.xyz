/**
 * 通用JS库
 * baoqing wang
 * 2013-11-28
 */

//系统类库
var SystemClass = function() {
	var System = new Object;

	//系统提示弹出层 改写原生alert
	System.alert = function(msg) {
		alert(msg);
	}

	//实现随机颜色
	System.getRandomColor = function() {
	  return '#'+(Math.random()*0xffffff<<0).toString(16);
	}

	//生成min-max之间的随机数
	System.random = function(min,max) {
		return Math.floor(min+Math.random()*(max-min));
	}

	return System;
}
//初始化系统类库
var System = new SystemClass();



$(document).ready(function() {
/**
 * 评论功能
 */
var CommentClass = function() {
	var commentObject = $(".ArchiveComment");
	var formObject = $("form[name=msgform]");

	//点击回复按钮链接
	commentObject.find("ul.list li a[name=Reply]").click(function() {
		var sourcecid = $(this).attr("sourcecid");
		var username = $(this).attr("username");
		formObject.find("input[name=sourcecid]").val(sourcecid);
		formObject.find("textarea[name=content]").val("回复"+username+"：");
	});

	//发表评论
	formObject.submit(function() {
		var action = $(this).attr("action");
		var data = $(this).serialize();
		$.post(action,data,function(data) {
			System.alert(data.info);
			if (!data.status) {
				//评论发表成功之后清空form表单
				$(this) 
				 .not(':button, :submit, :reset, :hidden')  
				 .val('')  
				 .removeAttr('checked')  
				 .removeAttr('selected');

				//刷新本页
				location.reload();
			}
		},"json");
		
		return false;
	});
}();
});
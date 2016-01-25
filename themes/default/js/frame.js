/**
 * 框架JS代码
 * baoqing wang
 * 2013-11-30
 */

//GlobalBar样式控制
$(window).scroll(function() {
	var scrollTop = $(window).scrollTop();
	if (scrollTop != 0)
		$('#GlobalBar').stop().animate({'opacity':'0.88'},400);
	else
		$('#GlobalBar').stop().animate({'opacity':'1'},400);
});
$('#GlobalBar').hover(function (e) {
	var scrollTop = $(window).scrollTop();
	if (scrollTop != 0) {
		$('#GlobalBar').stop().animate({'opacity':'1'},400);
	}
},function (e) {
	var scrollTop = $(window).scrollTop();
	if (scrollTop != 0) {
	$('#GlobalBar').stop().animate({'opacity':'0.88'},400);
}
});


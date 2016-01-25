/**
 * 图片上传函数
 */
$("input[name=imageUploadButton]").click(function (){
	if (!$("input[name=images]").val()) {
		alert("请选择图片！");
		return false;
	}
	var imageTitle = $("input[name=imageTitle]").val();
	var imageUploadAction = $("input[name=imageUploadAction]").val();
	var imageUploadUrl = $("#imageUploadBox").attr("ajaxUrl")+"&imageUploadAction="+imageUploadAction+"&imageTitle="+imageTitle;
	
	$.ajaxFileUpload ({
		url : imageUploadUrl,
		secureuri : false,
		fileElementId : 'images',
		dataType : 'json',
		success : function (data, status){
			alert(data.info);
			if (!data.status) {
				var newImage = '<div class="imageBlock"><input type="hidden" name="imageids[]" value="'+data.data.imageid+'" /><span class="imageBlockimage"><img src="'+data.data.src+'" width="150" height="auto" /></span><span class="imageBlocktitle">'+data.data.imageTitle+'</span></div>';
				$("#imageBox").html($("#imageBox").html()+newImage);
				$("input[name=images]").val(null);
				$("input[name=imageTitle]").val(null);
			}
		},
		error: function (s, data, status, e){
			alert(e);
		}
	})
});
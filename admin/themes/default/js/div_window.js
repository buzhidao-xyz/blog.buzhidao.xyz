
function Div_window(id){
	this.id = id;
	this.obj = $('#'+this.id);
	this.submitval = {};
};

Div_window.prototype.open = function(e){
	var po ={
		y:parseInt(e.pageY),
		x:e.pageX
	}
	if (po.y > 50) {
		var top = po.y-50;
	}
	if (po.y > 180) {
		var top = po.y-180;
	}
	if (po.y > 300) {
		var top = po.y-300;
	}
	var top = $(window).scrollTop()+30;
	this.obj.hide();
	this.obj.fadeIn().css('top',top+'px')
	return this;
};
/*
targetobj 为目标的name
val 为插入的value值，或是于之匹配的value值，当type为checkbox时，value为一串逗号分割的字符串
type 为插入数据的样式，可能值为：text,radio,checkbox,selected,textarea
callback 回调函数,第一个参数为更具targetname成功选择的 jquery对象
参数示例：[
{targetname:'username',val:'codyy',type:'text',callback:function(that){alert(that)}},
{targetname:'userintrest',val:'football,game,girl',type:'checkbox',callback:function(){}}
]
*/
Div_window.prototype.insertvalue = function(param_array){ 
	this.close();
	var that = this;
	var doinsert = function(targetname, val, type, callback){
		var thisobj;
		switch (type){
			case 'text':{
				thisobj = that.obj.find('input[name='+targetname+']');
				thisobj.val(val);
			}
			break;
			
			case 'radio':{
				thisobj = that.obj.find('input[name='+targetname+']');
				thisobj.each(function(){
					if($(this).val() === val){
						$(this).trigger('click');
						return true;
					};				
				});
			}
			break;
			
			case 'checkbox':{
				thisobj = that.obj.find('input[name='+targetname+']');
				thisobj.each(function(){
					var r = val.indexOf($(this).val());
					if(r!== -1){
						$(this).trigger('click');
					};
				});
			}
			break;
			
			case 'select':{
				thisobj = that.obj.find('select[name='+targetname+']');
				thisobj.find('option').each(function(){
					if($(this).val() === val){						
						$(this).attr('selected','selected');
						return true;
					};			
				})
			}
			break;
			
			case 'textarea':{
				thisobj = that.obj.find('textarea[name='+targetname+']')
				thisobj.val(val);
			}
			break;		
		}
		if(typeof callback !== 'undefined'){callback(thisobj);};		
	};
	
	if(typeof param_array === 'object' && typeof param_array.sort === 'function' && typeof param_array.length === 'number'){
			var num = param_array.length,
				p_obj;
			for(var i=0; i<num; i++){
				p_obj = param_array[i];
				doinsert(p_obj.targetname, p_obj.val, p_obj.type, p_obj.callback);
			};	
	};
	return that;
};

Div_window.prototype.formsubmit = function(url, callback){
	var data = this.obj.find('form').serializeArray();
	$.post(url, data, function(d){callback(d);}, 'json');
	return this;
};
Div_window.prototype.clear = function(){
	this.obj.find('input[type="text"]').val('');
	this.obj.find('input[type="file"]').val('');
	this.obj.find('input[type="radio"]').removeAttr('checked','');
	this.obj.find('input[type="checkbox"]').removeAttr('checked','');
	this.obj.find('textarea').html('');
	this.obj.find('img').attr('src', '');
	this.obj.find('select').each(function(){
		$(this).find('option').eq(0).attr('selected','selected');
	})
}

Div_window.prototype.close = function(){
	this.obj.fadeOut();
	this.clear();
	return this;
};
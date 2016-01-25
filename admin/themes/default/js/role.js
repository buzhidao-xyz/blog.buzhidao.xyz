// JavaScript Document
void function(){
	/*获取url参数加强版*/
var Get_QueryString_Plus = function(url){
	var no_q = 1,
	now_url = url && (url.split('?')[1] || no_q) || document.location.search.slice(1) || no_q;
	if(now_url === no_q) return false;
        var q_array = now_url.split('&'),
       q_o = {},
       v_array; 
        for(var i=0;i<q_array.length;i++){
              v_array = q_array[i].split('=');
      		  q_o[v_array[0]] = decodeURIComponent(v_array[1]);
        };
        return q_o;
    }
 /*获取url参数加强版*/

var __APP__ = $("#app").attr("appurl");
var Rolemanage = function(){
	var that = this;
	this.step = 1;
	this.stepid = 'step';
	this.stepclass = 'stable_box';
	//this.treeurl = '/operation-admin/adminmenu.txt';
	this.treeurl = __APP__+'/role/rolemodify/';
	this.ajaxurl = $('#roleform').attr('action');
	this.modifyurl = __APP__+'/role/rolemodify';
	this.dom = $("#treeDemo");
	this.treedata = {};
	this.setting = {
			keepParent: false,
			keepLeaf: false,
			checkable : true,
			isSimpleData : true,
  			treeNodeKey : "mid",
 		    treeNodeParentKey : "pid"
		};
	this.treeextend = false;
	this.rolelistid = 'rolelist';
}
Rolemanage.prototype.stepshow = function(num){//第几步显示
	$('.'+this.stepclass).hide();
	$('#'+this.stepid+num).fadeIn('fast');
	this.step = num;
	return this.step;
}

Rolemanage.prototype.submit = function(){
	var that = this;
	var data={}
	data.rolename = $('input[name=rolename]').val();
	data.desc = $('input[name=desc]').val();
	data.status = $("input:checked[name=status]").val();
	data.roleid = $('#roleid').val();
	data.role = [];
	var num = $('.rolelist').length,
		roleobj;
	for(var i=0;i<num;i++){
		roleobj = {
			id:$('.rolelist').eq(i).find('.rolelistb').attr('roleid'),
			status:$('.rolelist').eq(i).find('input:checked').val(),
			name:$('.rolelist').eq(i).find('.rolelistb').html()
		}
		data.role.push(roleobj);
	}
	$.post(this.ajaxurl, data, function(d){
		if(!d.errorcode){
			alert(d.errormsg);
			if(data.roleid){
				location.href = __APP__+'/role/rolemodify'
				return false;
			}		
			location.href=location.href;
			return true;
		}
		else alert(d.errormsg);
		return false;
		},'json');
	return false;
}
Rolemanage.prototype.getcheckbox = function(){
	return this.zTree.getCheckedNodes(); 
}


Rolemanage.prototype.createrolelist = function(obj){
	var ischecked1 = 'checked="checked"',
		ischecked2 = '';
	if(obj.status==2){
		ischecked2 = ischecked1;
		ischecked1 = '';
	}
	var str = '<tr class="rolelist"><td colspan="4" style="line-height:25px;"><b class="rolelistb" roleid="'+obj.mid+'">'+obj.name+'</b>'+
  	 	'<label><input checkedall="op1" type="radio" name="type'+obj.mid+'" value="1" '+ischecked1+'/>可见</label>&nbsp;&nbsp;'+
		'<label><input checkedall="op2" type="radio" name="type'+obj.mid+'" value="2" '+ischecked2+' style="margin-top:-2px;" />可操作</label></td>'+
  		'</tr>'
	return str;
}
Rolemanage.prototype.putcheckrole = function(obj_array){
	var num = obj_array.length,
		str = "";
	$('.rolelist').remove();
	for (var i=0; i < num; i++) {
		str += this.createrolelist(obj_array[i]);
	};
	str += '<tr class="rolelist1"><td colspan="4" style="line-height:25px;"><b class="rolelistb">全选</b>'+
  	 	'<label><input  type="radio"  name="type000"  value="1" />可见</label>&nbsp;&nbsp;'+
		'<label><input  type="radio"  name="type000"  value="2" style="margin-top:-2px;" />可操作</label></td>'+
  		'</tr>'
	$('#'+this.rolelistid).after(str);
	$("input[name=type000]").each(function (){
		$(this).click(function (){
			if ($(this).val() == 1) $("input[checkedall=op1]").attr("checked","checked");
			if ($(this).val() == 2) $("input[checkedall=op2]").attr("checked","checked");
		});
	});
}

Rolemanage.prototype.renew = function(callback){
	var that = this;
	$.post(that.treeurl, {"action":"gettree","roleid":$('#roleid').val()}, function(data){
		that.treedata = data;
		that.zTree = that.dom.zTree(that.setting, that.treedata);
		callback();		
		},'json');
	}

Rolemanage.prototype.extend = function(){
	this.treeextend = !this.treeextend;
	this.zTree.expandAll(this.treeextend);
}

Rolemanage.prototype.putmodifydata = function(){

	var obj = Get_QueryString_Plus();
	
		if(typeof obj.roleid !== 'undefined'){
			this.ajaxurl = this.modifyurl;

				for(var i in obj){
						 if(i !== 'status')	{$('#'+i).val(obj[i]);}
						 else {
							 	$('input[name="status"]').each(function(){
							 		if($(this).val() == obj[i]){
							 			$(this).attr('checked','checked')
							 		}
							 	})
						 }
				}	
		}
}

Rolemanage.prototype.intial = function(){
	var that = this;
	that.putmodifydata();//这里如果是修改的话
	that.stepshow(1);
	that.renew(function(){});
	$('input[name=subutstep]').click(function(){
		var text = $(this).val();
		if(text =='下一步'){		
			if(that.step === 1){
				var	rolename = $('input[name=rolename]').val(),
					desc = $('input[name=desc]').val(); 
				if(rolename=='' || desc==''){
					alert('角色名称和描述不能为空');
					return false;
				}
			}
			if(that.step === 2){
				var checkobj = that.getcheckbox();
				if(checkobj.length === 0){
					alert('请选择权限');
					return false;
				}
				that.putcheckrole(checkobj);
			}
			var num = that.stepshow(that.step+1);
			return false;
		}
		else if(text =='上一步'){
			that.stepshow(that.step-1);
			return false;
		}
		else if(text == '完成提交'){ that.submit();return false;}
		else if(text == '展开/收缩'){
			that.extend();
		}
	});
}	
if($('#roleform').length>0){
	var role = new Rolemanage();
	role.intial();
}


		
//如果是角色管理列表 	
if($('.role_table').length>0){
	$('a[name="modify"]').click(function(event){
		var e = event;
		var that = $(this);
			thattd = that.parent().parent().find('li'),
			pstr='&',
			param ={
				roleid:that.attr('mcid'),
				rolename:encodeURIComponent(thattd.eq(1).html()),
				desc:encodeURIComponent(thattd.eq(2).html()),
				status:that.attr('typeid')
		  }
		  for(var j in param){
			pstr += j +'='+param[j]+'&'				  	
		  }		
			 pstr += 'action=modify' 
		 that.attr('href',__APP__+'/role/roleadd'+pstr);
		 return true;
	});
//	$('a[name="del"]').click(function(){
//		ullilist.delajax($(this));
//	});
}
//如果是角色管理列表 
	



//如果是角色分配页面 	
if($('.role_send_table').length>0){
		var roleshow={
			sel:$('#role_sel'),
			addbt:$('#addrole'),
			u_role_class:'userrole',
			insert_after:$('#rolehr'),
			del_class:'role_del',
			roleidinput:'roleid',
			rolesubmit:$('#rolesubmit')
		}
		roleshow.getname = function(rolearray){
			var obj = {
				name :'',
				desc :'',
				roleid:''
			};
			if(rolearray.length === 0) return obj;
			for(var i=0;i<rolearray.length;i++){
					roleshow.sel.find('option').each(function(){
						if($(this).val() == rolearray[i]){
							obj.name+=$(this).html()+',';
							obj.desc+=$(this).attr('desc')+',';
							obj.roleid+=$(this).val()+',';
						}
					})
			}
			obj.name = obj.name.slice(0,obj.name.length-1)
			return obj;
		}
		
		
		roleshow.cleardivtable = function(){
			roleshow.binddelclick(false); //解除绑定
			$('.'+roleshow.u_role_class).remove();
		}
		roleshow.removeinputid = function(id){
			var val = $('#'+roleshow.roleidinput).val(),
			    str = val.replace(id+',','');
			$('#'+roleshow.roleidinput).val(str);
		}
	
		roleshow.binddelclick = function(bool){
				var delobj = $('.'+roleshow.del_class);
				if(bool){
					delobj.click(function(){
						if(confirm('确定删除吗？')){
							var rid = $(this).attr('roleid');
							$(this).parent().parent().remove();
							roleshow.removeinputid(rid);
						}
						//return false;
					})
				}
				else{
					delobj.unbind();
				}
		}
		roleshow.createstr = function(namearray){
			var str = '';
			for(var i=0; i<namearray.length; i++){			
				if(!namearray[i].name) continue;
				str += '<tr>'+
				   	   '<td  class="stgap userrole" colspan="2" title="'+namearray[i].desc+'"><b>'+namearray[i].name+'</b>&nbsp;&nbsp;&nbsp;<a class="role_del" roleid="'+namearray[i].roleid+'" href="javascript:;">删除</a></td>'+ 
				 	   '</tr>' 
			}
			return str;
		}
		roleshow.putdivtable = function(that){
			if(that.val()==','){
				that.val('');
				return false;}
			if(that.val()==''){return false;}
			var rolearray = that.val().split(','),
				rolearray = $.grep(rolearray, function(n, i){
					if(parseInt(n)>0 && (parseInt(n) == n)){
						return true;
					}
					return false;				
				});
				namearray =	roleshow.getname(rolearray).name.split(',');
				descarray = roleshow.getname(rolearray).desc.split(',');
				strobj=[];	
			for(var j=0;j<namearray.length;j++){
				strobj[j]={};
				strobj[j].roleid = rolearray[j];
				strobj[j].name = namearray[j];
				strobj[j].desc = descarray[j];
			}
			var str = roleshow.createstr(strobj);
			roleshow.insert_after.after(str);
			roleshow.binddelclick(true); //添加绑定
		}
	    roleshow.hascheck = function(id){//判断是否存在
				var val = $('#'+roleshow.roleidinput).val(),
			    	str = val.indexOf(id+',');
			    if(str === -1) return false;
			    return true;
		}
		roleshow.bindaddclick = function(){//绑定添加
			roleshow.addbt.click(function(){
				var rid = roleshow.sel.find('option:selected').val(),
					name = roleshow.sel.find('option:selected').html(),
					desc = roleshow.sel.find('option:selected').attr('desc');
				if (!roleshow.hascheck(rid)){
					var strarray=[{roleid:rid,name:name,desc:desc}],
						val = $('#'+roleshow.roleidinput).val(),
						str = roleshow.createstr(strarray);
						roleshow.rolesubmit.before(str);
						$('#'+roleshow.roleidinput).val(val+rid+',');
						roleshow.binddelclick(true); //添加绑定
					return true;
				};
				alert('角色已存在');
				return false;
			})
		}();
		/*
		roleshow.puttable = function(){
				$('.role_send_table').find('li[name="role"]').each(function(){
					var rolearray = $(this).attr('roleid').split(','),
					    str = roleshow.getname(rolearray).name;
					$(this).html(str);
				})		
		}();
		*/
//修改资料
		if($('#modify_div').length>0){ 
			var m_d = new Div_window('modify_div');				
			$('a[name="modify"]').click(function(event){
				var e = event;
				var that = $(this);
				var thattd = that.parent().parent().find('li');
				var param_array = [
				{targetname:'roleid', val:thattd.eq(2).attr('roleid')+',', type:'text',callback:function(that){
					roleshow.cleardivtable();
					roleshow.putdivtable(that);
				}},
				{targetname:'userid', val:that.attr('mcid'), type:'text'}
				];
				m_d.insertvalue(param_array).open(e);
			});
			
			$('#modify_div_close').click(function(){
				m_d.close();
			});
			$('#user_role_modify_div_form').submit(function(){
				m_d.formsubmit(__APP__+'/Role/roleadmin', function(data){
					alert(data.errormsg);
					if (!data.errorcode) {
						location.href = location.href;
					};
				}).close();
				return false;
			})
			
		}
//修改资料	
}//如果是角色分配页面 

}()
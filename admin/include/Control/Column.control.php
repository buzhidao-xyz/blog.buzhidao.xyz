<?php
/**
 * 网站栏目管理器
 * by buzhidao 2012-11-19
 */
class ColumnControl extends CommonControl
{
	//栏目属性ID与访问Action对应
	protected $_Action = array(
		1 => 'index',
		2 => 'lists',
		3 => 'link'
	);

	public function __construct()
	{
		parent::__construct();
	}

	//获取栏目ID
	private function _getColumnID()
	{
		$columnid = q('columnid');

		return $columnid;
	}

	private function _getparentid()
	{
		$parentid = q("parentid");
		return $parentid ? $parentid : 0;
	}

	private function _getColumnModel()
	{
		$columnmodel = q("columnmodel");
		return $columnmodel;
	}

	private function _getColumnName()
	{
		$columnname = q("columnname");
		if (!$columnname) return false;
		return $columnname;
	}

	private function _getsortrank()
	{
		$sortrank = q("sortrank");
		if (!$sortrank) return false;
		return $sortrank;
	}

	private function _getcolumntype()
	{
		$columntype = q("columntype");
		return $columntype;
	}

	private function _getAction($columntype=null)
	{
		return $columntype ? $this->_Action[$columntype] : $this->_Action[1];
	}

	private function _getisshow()
	{
		$isshow = q("isshow");
		return $isshow;
	}

	private function _gettitle()
	{
		$title = q("title");
		return $title;
	}

	private function _getkeyword()
	{
		$keyword = q("keyword");
		return $keyword;
	}

	private function _getdescription()
	{
		$description = q("description");
		return $description;
	}

	private function _getcontent()
	{
		$content = q("content");
		return $content;
	}

	/**
	 * 获取最顶级父栏目的栏目ID
	 */
	private function getTopID($parentid)
	{
		$topid = 0;
		if ($parentid) {
			$columnInfo = M("Column")->getColumn($parentid);
			if ($columnInfo[0]['parentid'] == 0) {
				return $parentid;
			} else {
				return $this->getTopID($columnInfo[0]['parentid']);
			}
		}

		return $topid;
	}

	//主入口
	public function index()
	{
		$columnList = M("Column")->getTopColumn();
		$this->assign("dataList",$columnList);
		
		$this->display("Column/column.html");
	}

	//添加栏目
	public function newColumn()
	{
		$columnid = $this->_getColumnID();
		$this->assign("columnid",$columnid);
		$this->assign("columnTree", $this->getColumnTree());

		$columnModelTree  = null;
        $columnModelTree .= '<option value="">|-请选择内容模型...</option>';
		$columnModelList = M("CTModel")->ColumnModelList();
		foreach ($columnModelList['data'] as $v) {
			$columnModelTree .= '<option value="'.$v['id'].'">&nbsp;&nbsp;|-'.$v['name'].'</option>';
		}
		$this->assign("columnModelTree", $columnModelTree);

		$this->display("Column/newcolumn.html");
	}

	//保存栏目信息
	public function saveColumn()
	{
		$columnname  = $this->_getColumnName();
		$parentid    = $this->_getparentid();
		$columnmodel = $this->_getColumnModel();
		$sortrank    = $this->_getsortrank();
		$columntype  = $this->_getcolumntype();
		$action      = $this->_getAction($columntype);
		$isshow      = $this->_getisshow();
		$title       = $this->_gettitle();
		$keyword     = $this->_getkeyword();
		$description = $this->_getdescription();
		$content     = $this->_getcontent();

		$columnpy = Pinyin($columnname,'UTF-8');
		//栏目模板
		$template_index = q('template_index'); //栏目页
		$template_list = q('template_list');  //列表页
		$template_body = q('template_body');  //详细页

		$topid = $this->getTopID($parentid);

		$data = array(
			'columnname'  => $columnname,
			'columnpy'    => $columnpy,
			'parentid'    => $parentid,
			'columnmodel' => $columnmodel,
			'action'      => $action,
			'topid'       => $topid,
			'sortrank'    => $sortrank,
			'columntype'  => $columntype,
			'isshow'      => $isshow,
			'title'       => $title,
			'keyword'     => $keyword,
			'description' => $description,
			'content'     => $content,
			'template_index' => $template_index,
			'template_list'  => $template_list,
			'template_body'  => $template_body,
			'createtime'  => TIMESTAMP,
			'updatetime'  => TIMESTAMP
		);

		$columnid = M("Column")->addColumn($data);
		if ($columnid) {
			if (!$topid) M("Column")->updateColumn($columnid,array("topid"=>$columnid));
			$NextOperation = array(
				array('name'=>'查看栏目', 'link'=>__APP__.'/index.php?s=Column/updateColumn&columnid='.$columnid)
			);
			$this->assign("NextOperation", $NextOperation);
			$this->display("Common/success.html");
		} else {
			$this->display("Common/error.html");
		}
	}

	/**
	 * 获取栏目树
	 * @param $control string 内容模型控制器
	 * @param $columnid string 当前内容模型id
	 */
	public function getColumnTree($control=null,$parentid=null)
	{
		$data = M("Column")->getTopColumn($control);

		$dataTree  = null;
        $dataTree .= '<option value="">|-请选择栏目...</option>';
        if (is_array($data) && !empty($data)) {
	        foreach ($data as $v) {
	        	if ($v['id'] == $parentid) {
		            $dataTree .= '<option value="'.$v['id'].'" selected>&nbsp;&nbsp;|-'.$v['columnname'].'</option>';
	        	} else {
		            $dataTree .= '<option value="'.$v['id'].'">&nbsp;&nbsp;|-'.$v['columnname'].'</option>';
		        }
	            $dataTree .= $this->getSubColumnTree($v['id'],$control,2,$parentid);
	        }
	    }

		return $dataTree;
	}

	//获取子栏目树
	public function getSubColumnTree($columnid=null,$control=null,$depth=0,$parentid=null)
	{
		if (!$columnid) return null;
		$nbsp = array_fill(0, $depth, "&nbsp;&nbsp;");
		$data = M("Column")->getSubColumn($columnid,$control);

		$dataTree  = null;
        if (is_array($data) && !empty($data)) {
	        foreach ($data as $v) {
	        	if ($v['id'] == $parentid) {
		            $dataTree .= '<option value="'.$v['id'].'" selected>'.implode($nbsp,"").'|-'.$v['columnname'].'</option>';
	        	} else {
		            $dataTree .= '<option value="'.$v['id'].'">'.implode($nbsp,"").'|-'.$v['columnname'].'</option>';
		        }
	            
	            $dataTree .= $this->getSubColumnTree($v['id'],$control,$depth+1,$parentid);
	        }
	    }

		return $dataTree;
	}

	//AJAX获取子栏目
	public function getSubColumn()
	{
		$columnid = $this->_getColumnID();
		$data = M("Column")->getSubColumn($columnid);

		$dataTree  = null;
        if (is_array($data) && !empty($data)) {
	        foreach ($data as $v) {
	        	$isshow = $v['isshow']==1 ? '<font color="green"></font>' : '<font color="red">[隐]</font>';
	        	$dataTree .= '<div class="ul columnlistd">
								<div class="li columnplusmius columnplus" columnid="'.$v['id'].'"></div>
								<div class="li columnListd700" flag="columnTableList">
									<a href="'.__APP__.'/index.php?s=Article/index&columnid='.$v['id'].'">'.$v['columnname'].'</a>
									'.$isshow.'
								</div>
								<div class="li columnListd300" flag="columnTableList">
									<a href="javascript:;">预览</a> |
									<a href="'.__APP__.'/index.php?s=Article/index&columnid='.$v['id'].'">栏目文档</a> |
									<a href="'.__APP__.'/index.php?s=Column/newColumn&columnid='.$v['id'].'">增加子栏目</a> |
									<a href="'.__APP__.'/index.php?s=Column/updateColumn&columnid='.$v['id'].'">编辑</a> |
									<a delurl="'.__APP__.'/index.php?s=Column/deleteColumn&columnid='.$v['id'].'" href="javascript:;" name="del" msg="确定删除该栏目吗？">删除</a>
								</div>
							</div>
							<div class="ul columnSubList"></div>';
	        }
	    }
	    $this->ajaxReturn(0,1,$dataTree);
	}

	//编辑栏目信息
	public function updateColumn()
	{
		$this->assign("accessStatus", 1);
		$columnid = $this->_getColumnID();

		$columnInfo = M("Column")->getColumn($columnid);
		$columnInfo = $columnInfo[0];
		$this->assign("ColumnInfo", $columnInfo);
		$this->assign("columnTree", $this->getColumnTree(null,$columnInfo['parentid']));

		$columnModelTree  = null;
        $columnModelTree .= '<option value="">|-请选择内容模型...</option>';
		$columnModelList = M("CTModel")->ColumnModelList();
		foreach ($columnModelList['data'] as $v) {
			if ($v['id'] == $columnInfo['columnmodel']) {
				$columnModelTree .= '<option value="'.$v['id'].'" selected>&nbsp;&nbsp;|-'.$v['name'].'</option>';
			} else {
				$columnModelTree .= '<option value="'.$v['id'].'">&nbsp;&nbsp;|-'.$v['name'].'</option>';
			}
		}
		$this->assign("columnModelTree", $columnModelTree);

		$this->display("Column/upColumn.html");
	}

	//保存编辑栏目信息
	public function saveUpColumn()
	{
		$columnid = $this->_getColumnID();
		if (!FilterHelper::C_int($columnid)) $this->display("Common/error.html");

		$columnname  = $this->_getColumnName();
		$parentid    = $this->_getparentid();
		$columnmodel = $this->_getColumnModel();
		$sortrank    = $this->_getsortrank();
		$columntype  = $this->_getcolumntype();
		$action      = $this->_getAction($columntype);
		$isshow      = $this->_getisshow();
		$title       = $this->_gettitle();
		$keyword     = $this->_getkeyword();
		$description = $this->_getdescription();
		$content     = $this->_getcontent();

		$columnpy = q('columnpy');
		//栏目模板
		$template_index = q('template_index'); //栏目页
		$template_list = q('template_list');  //列表页
		$template_body = q('template_body');  //详细页

		$topid = $this->getTopID($parentid);

		$data = array(
			'columnname'  => $columnname,
			'columnpy'    => $columnpy,
			'parentid'    => $parentid,
			'columnmodel' => $columnmodel,
			'action'      => $action,
			'topid'       => $topid,
			'sortrank'    => $sortrank,
			'columntype'  => $columntype,
			'isshow'      => $isshow,
			'title'       => $title,
			'keyword'     => $keyword,
			'description' => $description,
			'content'     => $content,
			'template_index' => $template_index,
			'template_list'  => $template_list,
			'template_body'  => $template_body,
			'updatetime'  => TIMESTAMP
		);

		$return = M("Column")->updateColumn($columnid,$data);
		if ($return) {
			if (!$topid) M("Column")->updateColumn($columnid,array("topid"=>$columnid));
			$NextOperation = array(
				array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Column/updateColumn&columnid='.$columnid)
			);
			$this->assign("NextOperation", $NextOperation);
			$this->display("Common/success.html");
		} else {
			$this->display("Common/error.html");
		}
	}

	//删除栏目
	public function deleteColumn()
	{
		$columnid = $this->_getColumnID();
		if (!$columnid) $this->ajaxReturn(1,"栏目ID错误！");

		if (T("Column")->where(array("parentid"=>$columnid))->count())
			$this->ajaxReturn(1,"删除失败！该栏目非空！");

		$return = M("Column")->deleteColumn($columnid);
		if ($return) {
			$this->ajaxReturn(0,"删除成功！");
		} else {
			$this->ajaxReturn(1,"删除失败！");
		}
	}
}
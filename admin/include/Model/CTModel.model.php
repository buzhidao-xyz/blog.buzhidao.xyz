<?php
/**
 * 内容模型
 * by laucen 2012-9-6
 */
class CTModel extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//获取内容模型列表
	public function ColumnModelList($id=0,$where=array())
	{
		if ($id) $where["id"] = is_array($id) ? array("in",$id) : $id;

		$total = T("column_model")->where($where)->count();
		$data = T("column_model")->where($where)->select();

		return array("total"=>$total, "data"=>$data);
	}

	//保存新增内容模型
	public function saveColumnModel($data=array())
	{
		if (empty($data)) return false;

		return T("column_model")->add($data);
	}

	//通过栏目模型控制器获取模型信息
	public function getColumnModelByControl($control=null)
	{
		if (!$control) return array();

		return T("column_model")->where(array("control"=>$control))->find();
	}

	/**
	 * 获取单页页面列表
	 * @param int/array $id 单页页面id
	 * @param int $start 分页开始记录数
	 * @param int $length 每页数据数
	 * @param array $where 条件数组
	 */
	public function getSinglePage($id=null,$start=0,$length=0,$where=array())
	{
		if ($id) $where['id'] = is_array($id) ? array('in', $id) : $id;

		$total = T("singlepage")->where($where)->count();
		$obj = T("singlepage")->where($where);
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->select();

		return array('total'=>$total,'data'=>$data);
	}

	/**
	 * 保存单页页面信息
	 * @param array $data 单页数据
	 */
	public function saveSinglePage($data=array())
	{
		if (!is_array($data) || empty($data)) return false;
		return T("singlepage")->add($data);
	}

	/**
	 * 保存单页页面信息
	 * @param int $singlepageid 单页页面id
	 * @param array $data 单页数据
	 */
	public function saveEditSinglePage($singlepageid=null,$data=array())
	{
		if (!$singlepageid || empty($data)) return false;
		return T("singlepage")->where(array("id"=>$singlepageid))->update($data);
	}

	/**
	 * 保存单页页面信息
	 * @param int/array $singlepageid 单页页面id
	 */
	public function deleteSinglePage($singlepageid=null)
	{
		if (empty($singlepageid)) return false;
		$where['id'] = is_array($singlepageid) ? array("in",$singlepageid) : $singlepageid;
		return T("singlepage")->where($where)->update(array("state"=>0));
	}
}
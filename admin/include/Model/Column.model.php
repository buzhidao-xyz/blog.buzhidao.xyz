<?php
/**
 * 栏目模型
 * by laucen 2012-9-6
 */
class Column extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//添加栏目
	public function addColumn($data=array())
	{
		if (!is_array($data) || empty($data)) return false;
		return T("column")->add($data);
	}

	/**
	 * 获取栏目
	 * @param $columnid int 栏目id
	 */
	public function getColumn($columnid=null)
	{
		$where = array();
		if ($columnid) $where['id'] = is_array($columnid) ? array("in", $columnid) : $columnid;

		$data = T("column")->where($where)->select();

		return $data;
	}

	//获取顶级栏目
	public function getTopColumn($control=null)
	{
		$where = array(
			'a.parentid' => 0
		);
		if ($control) $where['b.control'] = $control;
		$data = T("column")->join(" ".TBF."column_model as b on a.columnmodel=b.id")->field("a.*,b.control")->where($where)->select();

		return $data;
	}

	//获取子栏目
	public function getSubColumn($columnid=null,$control=null,$where=array())
	{
		if ($columnid) $where['a.parentid'] = is_array($columnid) ? array("in", $columnid) : $columnid;
		if ($control) $where['b.control'] = $control;

		$data = T("column")->join(" ".TBF."column_model as b on a.columnmodel=b.id")->field("a.*,b.control")->where($where)->select();

		return $data;
	}

	//根据栏目ID获取其子栏目ID 无限级
	public function getSubColumnID($columnid=null, $return=array())
	{
		if (!$columnid) return array();

		$data = $this->getSubColumn($columnid);
		foreach ($data as $k=>$v) {
			$return[] = $v['id'];
			$return = $this->getSubColumnID($v['id'], $return);
		}

		return $return;
	}

	//更新栏目信息
	public function updateColumn($columnid=null,$data=array())
	{
		if (!$columnid) return false;
		return T("column")->where(array("id"=>$columnid))->update($data);
	}

	//删除栏目
	public function deleteColumn($columnid = null)
	{
		if (!$columnid) return false;
		return T("column")->where(array("id"=>$columnid))->delete();
	}
}
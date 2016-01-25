<?php
/**
 * 标签模型
 * baoqing wang
 * 2013-12-07
 */
class Tag extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//保存标签
	public function saveTag($data=array())
	{
		if (!is_array($data) || empty($data)) return false;

		return T("tag")->add($data);
	}

	//获取标签信息
	public function getTag($name=null)
	{
		if (!$name || empty($name)) return false;

		$where = array("tagname"=>is_array($name) ? array("in",$name) : $name);
		return T("tag")->where($where)->select();
	}
}
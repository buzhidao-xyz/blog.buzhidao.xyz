<?php
/**
 * 日志模型 数据层
 * by baoqing wang 2013-10-05
 */
class Log extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//获取管理员登录日志列表
	public function getAdminLoginLog($logid=null,$start=0,$length=0,$begintime=null,$endtime=null,$where=array())
	{
		if ($logid) $where['logid'] = is_array($logid) ? array("in", $logid) : $logid;
		if ($begintime) $where['logintime'] = array("egt", $begintime);
		if ($endtime) $where['logintime'] = array("elt", $endtime);
		if ($begintime && $endtime) $where['logintime'] = array("between", $begintime, $endtime);

		$total = T("adminloginlog")->where($where)->count();
		$object = T("adminloginlog")->where($where);
		if ($length) $object = $object->limit($start,$length);
		$data = $object->order(array("logintime"=>"desc"))->select();

		return array("total"=>$total, "data"=>$data);
	}
}
<?php
/**
 * 日志控制器
 * by buzhidao 2012-11-14
 */
class LogControl extends CommonControl
{
	public function __construct()
	{
		parent::__construct();
	}

	//日志管理入口
	public function index()
	{

	}

	//获取管理员名称
	public function getAdminName()
	{
		$adminname = q("adminname");
		$this->assign("adminname", $adminname);
		if ($adminname && !FilterHelper::C_PCharacter($adminname)) {
			$this->showMessage("管理员名称格式错误!",0);
		}
		return $adminname;
	}

	//获取开始日期
	public function getBeginTime()
	{
		$begintime = q("begintime");
		$this->assign("begintime", $begintime);
		if ($begintime) {
			$begintime = explode(" ", $begintime);
			$begintime1 = explode("-", $begintime[0]);
			$begintime2 = explode(":", $begintime[1]);
			$begintime = mktime($begintime2[0],$begintime2[1],$begintime2[2],$begintime1[1],$begintime1[2],$begintime1[0]);
		}
		return $begintime;
	}

	//获取截止日期
	public function getEndTime()
	{
		$endtime = q("endtime");
		$this->assign("endtime", $endtime);
		if ($endtime) {
			$endtime = explode(" ", $endtime);
			$endtime1 = explode("-", $endtime[0]);
			$endtime2 = explode(":", $endtime[1]);
			$endtime = mktime($endtime2[0],$endtime2[1],$endtime2[2],$endtime1[1],$endtime1[2],$endtime1[0]);
		}
		return $endtime;
	}

	//管理员登录日志
	public function AdminLoginLog()
	{
		$where = array();
		$adminname = $this->getAdminName();
		if ($adminname) $where['adminname'] = array("like","%".$adminname."%");
		$begintime = $this->getBeginTime();
		$endtime = $this->getEndTime();

		list($start,$length) = $this->getpages();
		$dataList = M("Log")->getAdminLoginLog(null,$start,$length,$begintime,$endtime,$where);

		$this->assign("total", $dataList['total']);
		$this->assign("dataList", $dataList['data']);

		$parameters = array(
			'adminname' => $adminname,
			'begintime' => mkdate($begintime),
			'endtime'   => mkdate($endtime)
		);
        $this->assign("page", getPage($dataList['total'],$this->_pagesize,0,$parameters));
		$this->display("Log/AdminLoginLog.html");
	}
}
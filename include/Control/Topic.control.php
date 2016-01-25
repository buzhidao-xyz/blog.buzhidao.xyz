<?php
/**
 * 专题控制器
 * by wbq 2013-09-13
 * 处理逻辑数据 执行具体的功能操作
 */
class TopicControl extends ArchiveControl
{
	//控制器名
    protected $_control = 'Topic';
    //分页每页记录数
    protected $_pagesize = 15;

	public function __construct()
	{
		parent::__construct();
	}

	//专题首页
	public function index()
	{
		$this->display("Topic/index.html");
	}

	//专题详细页
	public function View()
	{
		$archiveid = $this->_getArchiveID();
		$archiveInfo = M("Topic")->getTopicInfo($this->_columnid,$archiveid);

		$cardList = M("Topic")->getTopicCard(null,$archiveid);
		$archiveInfo['cardList'] = $cardList;
		
		// dump($archiveInfo);exit;
		$this->assign("archiveInfo",$archiveInfo);
		$this->display($this->_Column['template_body']);
	}
}
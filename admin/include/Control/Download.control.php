<?php
/**
 * 文章控制器
 * by buzhidao 2012-12-26
 */
class DownloadControl extends ArchiveControl
{
	//控制器名
	protected $_Control = "Download";

	public function __construct()
	{
		parent::__construct();
	}

	//主入口文档列表
	public function index()
	{
		$columnid = $this->_getColumnID();

		$columnids = array();
		if ($columnid) $columnids = array_merge(M("Column")->getSubColumnID($columnid),array($columnid));

		list($start,$length) = $this->getPages();
        $articleList = M("Download")->getDownload(null,$start,$length,1,$columnids,$this->_Control);
        $this->assign("total", $articleList['total']);
        $this->assign("dataList", $articleList['data']);

        $this->assign("page", getPage($articleList['total'],$this->_pagesize));
        $this->display("Download/index.html");
	}

	//新下载文件
	public function add()
	{
		$this->assign("accessStatus",1);

		$this->assign("columnTree", D("Column")->getColumnTree($this->_Control));

		$this->display("Download/add.html");
	}

	//保存下载文件
	public function save()
	{
		$data = $this->dealArchiveSubmit();
		$archiveid = M("Archive")->saveArchive($data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		
		if ($archiveid) {
			$attachmentid = D('Attachment')->saveAttachment($archiveid);
			if ($attachmentid) {
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Attachment/edit&archiveid='.$archiveid)
				);
				$this->assign("NextOperation", $NextOperation);
				$this->display("Common/success.html");
			} else {
				$this->display("Common/error.html");
			}
		} else {
			$this->display("Common/error.html");
		}
	}

	//更新文档信息
	public function edit()
	{
		$this->assign("accessStatus", 1);

		$ArchiveID = $this->_getArchiveID();
		$ArchiveInfo = M("Download")->getDownload($ArchiveID,0,0,null);
		$ArchiveInfo = !empty($ArchiveInfo['data']) ? $ArchiveInfo['data'][0] : array();

		if (empty($ArchiveInfo)) $this->display("Common/error.html");

		$this->assign("ArchiveInfo", $ArchiveInfo);

		$this->assign("columnTree", D("Column")->getColumnTree());
		$this->display("Download/edit.html");
	}

	//保存更新文档信息
	public function saveEdit()
	{
		$ArchiveID = $this->_getArchiveID();
		$data = $this->dealArchiveSubmit();
		$return = M("Archive")->upArchive($ArchiveID,$data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		if ($return) {
			M("Attachment")->deleteArchiveAttachment($ArchiveID);
			$return = D('Attachment')->saveAttachment($ArchiveID);
			if ($return === false) {
				$this->display("Common/error.html");
			} else {
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Download/edit&archiveid='.$ArchiveID)
				);
				$this->assign("NextOperation", $NextOperation);
				$this->display("Common/success.html");
			}
		} else {
			$this->display("Common/error.html");
		}
	}

	//回收站
	public function recover()
	{
		$this->assign("accessStatus", 1);

		list($start,$length) = $this->getPages();
        $dataList = M("Download")->getDownload(null,$start,$length,0,null,$this->_Control);
        $this->assign("total", $dataList['total']);
        $this->assign("dataList", $dataList['data']);

        $this->assign("page", getPage($dataList['total'],$this->_pagesize));
		$this->display("Download/recover.html");
	}
}
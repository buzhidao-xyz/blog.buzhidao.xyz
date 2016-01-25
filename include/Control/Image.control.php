<?php
/**
 * 图集模型控制器
 * by buzhidao 2013-02-03
 * 处理逻辑数据 执行具体的功能操作
 */
class ImageControl extends ArchiveControl
{
	//图集分页展示 每页图片数量
	protected $_ImageNum = 8;

	//控制器名
    protected $_control = 'Image';

	public function __construct()
	{
		parent::__construct();
	}

	//栏目封面页
	public function index()
	{
		$this->display($this->_Column['template_index']);
	}

	//栏目列表页
	public function lists()
	{
		$ArchiveList = $this->getAllArchive();

		$this->assign("ArchiveList", $ArchiveList['data']);
		$this->assign("page", getPage($ArchiveList['total'],$this->_pagesize));
		$this->display($this->_Column['template_list']);
	}

	//获取文档内容
	public function View()
	{
		$archiveid = $this->_getArchiveID();
		list($start,$length) = $this->getPages($this->_ImageNum);
		$archiveInfo = M("Image")->getImageInfo($start,$length,$this->_columnid,$archiveid);
		$archiveInfo['archiveImage'] = $this->MsonaryImageCol($archiveInfo['archiveImage']);

		$this->assign("archiveInfo",$archiveInfo);
		$this->display("Image/body.html");
	}

	//瀑布流图集图片col格式化
	public function MsonaryImageCol($archiveImage=array())
	{
		if (empty($archiveImage)) return array();

		foreach ($archiveImage as $k=>$image) {
			if ($image['width'] >= 1600) $archiveImage[$k]['coln'] = 6;
			if ($image['width'] < 1600) $archiveImage[$k]['coln'] = 5;
			if ($image['width'] < 1400) $archiveImage[$k]['coln'] = 4;
			if ($image['width'] < 1200) $archiveImage[$k]['coln'] = 3;
			if ($image['width'] < 1000) $archiveImage[$k]['coln'] = 2;
			if ($image['width'] < 500) $archiveImage[$k]['coln'] = 1;
		}

		return $archiveImage;
	}

	//瀑布流展示图集详细内容
	public function Msonary()
	{
		$archiveid = $this->_getArchiveID();
		list($start,$length) = $this->getPages($this->_ImageNum);
		$archiveInfo = M("Image")->getImageInfo($start,$length,$this->_columnid,$archiveid);
		$archiveInfo['archiveImage'] = $this->MsonaryImageCol($archiveInfo['archiveImage']);

		$this->assign("archiveInfo",$archiveInfo);
		$this->display("Image/Msonary.html");
	}
}
<?php
/**
 * 图片模型
 * by buzhidao 2013-03-26
 * 图片操作 轮播图片 增删改查
 */
class Image extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取首页轮播图片
	 * @param $id int 图片id
	 * @param $where array 条件数组
	 */
	public function getHomeScrollImage($id=null,$where=array())
	{
		if ($id) $where['id'] = $id;

		$where['isshow'] = 1;
		$where['isdelete'] = 0;
		return T("scrollimage")->where($where)->select();
	}

	/**
	 * 获取图集内容
	 * @param $archiveid int 文章id
	 */
	public function getImageDetail($archiveid=null)
	{
		if (empty($archiveid)) return false;

		$where = array();
		$where['archiveid'] = is_array($archiveid) ? array("in", $archiveid) : $archiveid;
		
		return T("images")->where($where)->select();
	}

	/**
	 * 获取某个图集详情
	 * @param $start int 分页记录开始数
	 * @param $length int 每页记录数
	 * @param $columnid int 栏目id
	 * @param $archiveid int 文档id
	 */
	public function getImageInfo($start=0,$length=0,$columnid=null,$archiveid=null,$where=array())
	{
		$archiveInfo = $this->getArchive($archiveid,0,0,$where);
		if (!$archiveInfo['total']) return false;

		$archiveInfo = $archiveInfo['data'][0];
		$imageDetail = $this->getImageDetail($archiveid);
		$archiveInfo = array_merge($imageDetail[0],$archiveInfo);
		$archiveInfo['archiveImage'] = $this->getArchiveImages($archiveid,$start,$length);

		$archiveInfo['prev'] = $this->getPrevArchive($columnid,$archiveid);
		$archiveInfo['next'] = $this->getNextArchive($columnid,$archiveid);
		return $archiveInfo;
	}
}
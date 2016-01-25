<?php
/**
 * 文档模型
 * by wbq 2011-12-13
 * edit by buzhidao 2012-12-13
 * 文档的基本增删改查操作
 */
class Article extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取某个文档详情
	 * @param $columnid int 栏目id
	 * @param $archiveid int 文档id
	 */
	public function getArticleInfo($archiveid=null,$where=array())
	{
		$archiveInfo = $this->getArchive($archiveid,0,0,$where);
		if (!$archiveInfo['total']) return false;

		$archiveInfo = $archiveInfo['data'][0];
		$articleIndex = $this->getArticleContent($archiveid);
		$archiveInfo['content'] = $articleIndex[0]['content'];

		$archiveInfo['prev'] = $this->getPrevArchive($archiveInfo['columnid'],$archiveid);
		$archiveInfo['next'] = $this->getNextArchive($archiveInfo['columnid'],$archiveid);
		return $archiveInfo;
	}

	/**
	 * 获取文章内容
	 * @param $archiveid int 文章id
	 */
	public function getArticleContent($archiveid=null)
	{
		if (empty($archiveid)) return false;

		$where = array();
		$where['archiveid'] = is_array($archiveid) ? array("in", $archiveid) : $archiveid;
		
		return T("article")->where($where)->select();
	}
}
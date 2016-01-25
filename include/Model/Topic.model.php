<?php
/**
 * 专题模型
 * by wbq 2013-09-22
 * edit by buzhidao 2013-09-22
 * 专题的基本增删改查操作
 */
class Topic extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取专题内容
	 * @param $archiveid int 文章id
	 */
	public function getTopicContent($archiveid=null)
	{
		if (empty($archiveid)) return false;

		$where = array();
		$where['archiveid'] = is_array($archiveid) ? array("in", $archiveid) : $archiveid;
		
		return T("topic")->where($where)->select();
	}

	/**
	 * 获取某个专题详情
	 * @param $columnid int 栏目id
	 * @param $archiveid int 文档id
	 */
	public function getTopicInfo($columnid=null,$archiveid=null,$where=array())
	{
		$archiveInfo = $this->getArchive($archiveid,0,0,$where);
		if (!$archiveInfo['total']) return false;

		$archiveInfo = $archiveInfo['data'][0];
		$topicIndex = $this->getTopicContent($archiveid);
		$archiveInfo['content'] = $topicIndex[0]['content'];

		return $archiveInfo;
	}

	//获取专题项
	public function getTopicCard($topiccardid=null,$topicid=null)
	{
		$where = array();
		if ($topiccardid) $where['a.id'] = is_array($topiccardid) ? array("in",$topiccardid) : $topiccardid;
		if ($topicid) $where['a.archiveid'] = is_array($topicid) ? array("in",$topicid) : $topicid;

		$data = T("topic_card")->where($where)->order("id","asc")->select();
		$data = DataListAutoIndex($data);
		if (is_array($data) && !empty($data)) {
			foreach ($data as $k=>$d) {
				$archiveList = T("topic_article")->join(' '.TBF.'archive as b on a.archiveid=b.id ')->field("a.archiveid,a.topicid,a.topiccardid,a.content,b.*")->where(array("a.topiccardid"=>$d['id']))->order("a.id","desc")->select();
				$archiveList = DataListAutoIndex($archiveList);
				$data[$k]['archiveList'] = empty($archiveList) ? array() : $archiveList;
			}
		}
		
		return $data;
	}
}
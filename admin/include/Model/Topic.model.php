<?php
/**
 * 专题模型
 * by laucen 2013-03-22
 */
class Topic extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	//保存专题内容
	public function saveTopic($archiveid=null,$content=null)
	{
		if (!$archiveid) return false;

		$data = array(
			'archiveid'  => $archiveid,
			'content'    => $content,
			"updatetime" => TIMESTAMP
		);
		return T("topic")->add($data);
	}

	/**
	 * 获取专题列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getTopic($id=null,$start=0,$length=0,$state=1,$columnids=array(),$control=null)
	{
		$where = array();
		if ($state !== null) $where['state'] = $state;
		if (is_array($columnids) && !empty($columnids)) $where['columnid'] = array("in", $columnids);
		if ($control) $where['control'] = $control;

		return $this->getArchive($id,$start,$length,$where);
	}

	/**
	 * 获取文档内容
	 * @param int $archiveid 文档id
	 */
	public function getTopicContent($archiveid=null)
	{
		if (!$archiveid) return null;

		$data = T("topic")->where(array("archiveid"=>$archiveid))->find();
		return isset($data['content']) ? $data['content'] : null;
	}

	//保存编辑的专题信息
	public function upTopicContent($archiveid=null,$content=null)
	{
		if (empty($archiveid)) return null;

		$data = array(
			"content"    => $content,
			"updatetime" => TIMESTAMP
		);
		return T("topic")->where(array("archiveid"=>$archiveid))->update($data);
	}

	//添加专题项
	public function saveTopicCard($data=array())
	{
		if (!is_array($data)||empty($data)) return false;

		return T("topic_card")->add($data);
	}

	//专题项列表
	public function getTopicCard($id=null,$archiveid=null)
	{
		$where = array();
		if ($id) $where["a.id"] = is_array($id) ? array("in", $id) : $id;
		if ($archiveid) $where["a.archiveid"] = is_array($archiveid) ? array("in", $archiveid) : $archiveid;

		$data = T("topic_card")->join(' '.TBF.'archive as b on a.archiveid=b.id ')->field("a.*,b.title as topictitle")->where($where)->select();

		return $data;
	}

	//保存编辑后的专题项信息
	public function TopicCardEditSave($topiccardid=null,$data=array())
	{
		if (!$topiccardid || empty($data)) return false;

		return T("topic_card")->where(array("id"=>$topiccardid))->update($data);
	}

	//删除专题项
	public function deleteTopicCard($topiccardid=null)
	{
		if (!$topiccardid) return false;

		//删除专题项
		T("topic_card")->where(array("id"=>$topiccardid))->delete();
		//删除专题项文档关联
		T("topic_article")->where(array("topiccardid"=>$topiccardid))->update(array("topiccardid"=>""));

		return true;
	}

	//专题文档列表
	public function getTopicArticle($archiveid=null,$topicid=null,$topiccardid=null,$start=0,$length=0,$state=1)
	{
		$where = array(
			'b.state' => $state
		);
		if ($archiveid) $where["a.archiveid"] = is_array($archiveid) ? array("in",$archiveid) : $archiveid;
		if ($topicid) $where["a.topicid"] = is_array($topicid) ? array("in",$topicid) : $topicid;
		if ($topiccardid) $where["a.topiccardid"] = is_array($topiccardid) ? array("in",$topiccardid) : $topiccardid;

		$total = T("topic_article")->join(' '.TBF.'archive as b on a.archiveid=b.id ')->field("a.id")->where($where)->count();
		$obj = T("topic_article")->join(' '.TBF.'archive as b on a.archiveid=b.id ')->field("a.id as topicarticleid,a.archiveid,a.topicid,a.topiccardid,a.content,a.updatetime as updatetime1,b.*")->where($where);
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->select();

		if (is_array($data) && !empty($data)) {
			foreach ($data as $k=>$d) {
				$TopicInfo = $this->getTopic($d['topicid']);
				$data[$k]['topictitle'] = $TopicInfo['data'][0]['title'];
				$data[$k]['columnname'] = $TopicInfo['data'][0]['columnname'];

				//查询所属专题项
				if ($d['topiccardid']) {
					$TopicCard = $this->getTopicCard($d['topiccardid']);
					$data[$k]['topiccardname'] = $TopicCard[0]['title'];
				} else {
					$data[$k]['topiccardname'] = "";
				}
			}
		}

		return array("total"=>$total, "data"=>$data);
	}

	//保存文档信息
	public function saveTopicArticle($archiveid=null,$topicid=null,$topiccardid=null,$content=null,$updatetime=null)
	{
		if (!$archiveid || !$topicid || !$updatetime) return false;

		$data = array(
			'archiveid' => $archiveid,
			'topicid'   => $topicid,
			'topiccardid' => $topiccardid,
			'content'     => $content,
			'updatetime'  => $updatetime
		);
		return T("topic_article")->add($data);
	}

	//保存编辑文档信息
	public function TopicArticleEditSave($archiveid=null,$topicid=null,$topiccardid=null,$content=null,$updatetime=null)
	{
		if (!$archiveid || !$topicid || !$updatetime) return false;

		$data = array(
			'topicid'   => $topicid,
			'topiccardid' => $topiccardid,
			'content'     => $content,
			'updatetime'  => $updatetime
		);
		return T("topic_article")->where(array('archiveid'=>$archiveid))->update($data);
	}
}
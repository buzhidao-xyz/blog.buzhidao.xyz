<?php
/**
 * 专题模型栏目控制器
 * by buzhidao 2013-04-27
 */
class TopicControl extends ArchiveControl
{
	//控制器名
	protected $_Control = "Topic";

	public function __construct()
	{
		parent::__construct();
	}

	//专题列表
	public function index()
	{
		$columnid = $this->_getColumnID();
		$this->assign("columnid", $columnid);

		$columnids = array();
		if ($columnid) $columnids = array_merge(M("Column")->getSubColumnID($columnid),array($columnid));

		list($start,$length) = $this->getPages();
        $dataList = M("Topic")->getTopic(null,$start,$length,1,$columnids,$this->_Control);
        $this->assign("total", $dataList['total']);
        $this->assign("dataList", $dataList['data']);

        $this->assign("page", getPage($dataList['total'],$this->_pagesize));
		$this->display("Topic/index.html");
	}

	//添加新专题
	public function add()
	{
		$this->assign("accessStatus",1);
		$columnid = $this->_getColumnID();
		$this->assign("columnid", $columnid);
		
		$this->assign("columnTree", D("Column")->getColumnTree($this->_Control));

		$this->display("Topic/add.html");
	}

	//保存专题内容
	public function save()
	{
		$data = $this->dealArchiveSubmit();
		$archiveid = M("Archive")->saveArchive($data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		
		if ($archiveid) {
			$content = $this->_getContent();
			$topicid = M('Topic')->saveTopic($archiveid,$content);
			if ($topicid) {
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Topic/edit&archiveid='.$archiveid)
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

	//编辑专题
	public function edit()
	{
		$this->assign("accessStatus",1);
		$columnid = $this->_getColumnID();
		$this->assign("columnid", $columnid);
		
		$archiveid = q("archiveid");
		$this->assign("archiveid", $archiveid);
		$ArchiveInfo = M("Topic")->getTopic($archiveid,0,0,null);
		$ArchiveInfo = !empty($ArchiveInfo['data']) ? $ArchiveInfo['data'][0] : array();

		if (empty($ArchiveInfo)) $this->display("Common/error.html");

		$ArchiveInfo['content'] = M("Topic")->getTopicContent($ArchiveInfo["id"]);
		$this->assign("ArchiveInfo", $ArchiveInfo);

		$this->assign("columnTree", D("Column")->getColumnTree($this->_Control));
		$this->display("Topic/edit.html");
	}

	//编辑保存专题
	public function saveEdit()
	{
		$archiveid = q("archiveid");
		$this->assign("archiveid", $archiveid);

		$data = $this->dealArchiveSubmit();
		$return = M("Archive")->upArchive($archiveid,$data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		if ($return) {
			$content = $this->_getContent();
			$return = M("Topic")->upTopicContent($archiveid,$content);
			if ($return) {
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Topic/edit&archiveid='.$archiveid)
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

	//生成专题项Tree
	public function makeTopicCardTree()
	{
		$columnid = $this->_getColumnID();

		$columnids = array();
		if ($columnid) $columnids = array_merge(M("Column")->getSubColumnID($columnid),array($columnid));

		$TopicList = M("Topic")->getTopic(null,0,0,1,$columnids,$this->_Control);

		$this->assign("TopicList", $TopicList['data']);
	}

	//获取createtime
	protected function _getcreatetime()
	{
		$createtime = q("createtime");
		$createtime = explode(" ", $createtime);
		$createtime1 = explode("-", $createtime[0]);
		$createtime2 = explode(":", $createtime[1]);
		$createtime = mktime($createtime2[0],$createtime2[1],$createtime2[2],$createtime1[1],$createtime1[2],$createtime1[0]);

		return $createtime;
	}

	//专题项列表
	public function TopicCardList()
	{
		$archiveid = q("archiveid");
		$dataList = M("Topic")->getTopicCard(null,$archiveid);

		$this->assign("dataList", $dataList);
		$this->assign("archiveid", $archiveid);
		$this->display("Topic/TopicCardList.html");
	}

	//新增专题项
	public function newTopicCard()
	{
		$archiveid = q("archiveid");
		$this->assign("archiveid", $archiveid);

		$this->makeTopicCardTree();
		$this->display("Topic/newTopicCard.html");
	}

	//保存专题项内容
	public function saveTopicCard()
	{
		$title = q("title");
		$archiveid = q("archiveid");
		if (!$archiveid) $this->showMessage("请选择所属专题!",0);

		$createtime = $this->_getcreatetime();
		$image = $this->_getImage();
		$description = q("description");

		$data = array(
			'archiveid' => $archiveid,
			'title'   => $title,
			'description' => $description,
			'createtime'  => $createtime,
			'updatetime'  => TIMESTAMP
		);
		if ($image) $data['thumbimage'] = $image;

		$return = M("Topic")->saveTopicCard($data);
		if ($return) {
			$this->showMessage("专题项添加成功!");
		} else {
			$this->showMessage("专题项添加失败!");
		}
	}

	//修改专题项
	public function TopicCardEdit()
	{
		$archiveid = q("archiveid");
		$this->assign("archiveid", $archiveid);
		
		$id = q("id");
		if (!$id) $this->showMessage("请选择专题项!",0);

		$TopicCard = M("Topic")->getTopicCard($id);
		if (empty($TopicCard)) $this->showMessage("请选择专题项!",0);
		$TopicCard = $TopicCard[0];
		$this->assign("TopicCard",$TopicCard);

		$this->makeTopicCardTree();

		$this->assign("topiccardid",$id);
		$this->display("Topic/TopicCardEdit.html");
	}

	//保存专题项修改信息
	public function TopicCardEditSave()
	{
		$topiccardid = q("topiccardid");
		if (!$topiccardid) $this->showMessage("请选择所属专题!",0);

		$title = q("title");
		$archiveid = q("archiveid");
		if (!$archiveid) $this->showMessage("请选择所属专题!",0);

		$createtime = $this->_getcreatetime();
		$image = $this->_getImage();
		$description = q("description");

		$data = array(
			'archiveid' => $archiveid,
			'title'   => $title,
			'description' => $description,
			'createtime'  => $createtime,
			'updatetime'  => TIMESTAMP
		);
		if ($image) $data['thumbimage'] = $image;

		$return = M("Topic")->TopicCardEditSave($topiccardid,$data);
		if ($return) {
			$this->showMessage("专题项添加成功!");
		} else {
			$this->showMessage("专题项添加失败!");
		}
	}

	//删除专题项
	public function deleteTopicCard()
	{
		$topiccardid = q("id");
		if (!$topiccardid) $this->ajaxReturn(1,"请选择所属专题!");

		$return = M("Topic")->deleteTopicCard($topiccardid);
		if ($return) {
			$this->ajaxReturn(0,"删除成功!");
		} else {
			$this->ajaxReturn(1,"删除失败!");
		}
	}

	//专题文档列表
	public function TopicArticleList()
	{
		$archiveid = q("archiveid");
		$this->assign("archiveid", $archiveid);

		list($start,$length) = $this->getPages();
		$dataList = M("Topic")->getTopicArticle(null,$archiveid,null,$start,$length);
		$this->assign("total", $dataList['total']);
		$this->assign("dataList", $dataList['data']);

        $this->assign("page", getPage($dataList['total'],$this->_pagesize));
		$this->display("Topic/TopicArticleList.html");
	}

	//新增专题文档
	public function newTopicArticle()
	{
		$archiveid = q("archiveid");
		$this->assign("archiveid", $archiveid);

		$TopicInfo = M("Topic")->getTopic($archiveid,0,0,null);
		$TopicInfo = !empty($TopicInfo['data']) ? $TopicInfo['data'][0] : array();
		$this->assign("TopicInfo", $TopicInfo);

		//专题项列表
		$TopicCardList = M("Topic")->getTopicCard(null,$archiveid);
		$this->assign("TopicCardList", $TopicCardList);

		$this->display("Topic/newTopicArticle.html");
	}

	//保存专题文档
	public function saveTopicArticle()
	{
		$data = $this->dealArchiveSubmit();
		$archiveid = M("Archive")->saveArchive($data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		
		if ($archiveid) {
			$topicid = q("topicid");
			$topiccardid = q("topiccardid");
			$content = $this->_getContent();
			$topicarticleid = M('Topic')->saveTopicArticle($archiveid,$topicid,$topiccardid,$content,TIMESTAMP);
			if ($topicarticleid) {
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Topic/TopicArticleEdit&archiveid='.$topicid.'&topicarticleid='.$archiveid)
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

	//编辑专题文档
	public function TopicArticleEdit()
	{
		$archiveid = q("archiveid");
		$this->assign("archiveid", $archiveid);

		$topicarticleid = q("topicarticleid");
		$this->assign("topicarticleid", $topicarticleid);

		$TopicArticleInfo = M("Topic")->getTopicArticle($topicarticleid,$archiveid);
		$TopicArticleInfo = $TopicArticleInfo['data'][0];
		$this->assign("TopicArticleInfo", $TopicArticleInfo);

		$TopicInfo = M("Topic")->getTopic($archiveid,0,0,null);
		$TopicInfo = !empty($TopicInfo['data']) ? $TopicInfo['data'][0] : array();
		$this->assign("TopicInfo", $TopicInfo);

		//专题项列表
		$TopicCardList = M("Topic")->getTopicCard(null,$archiveid);
		$this->assign("TopicCardList", $TopicCardList);

		$this->display("Topic/TopicArticleEdit.html");
	}

	//保存编辑
	public function TopicArticleEditSave()
	{
		$archiveid = q("archiveid");
		$data = $this->dealArchiveSubmit();
		$return = M("Archive")->upArchive($archiveid,$data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		if ($return) {
			$topicid = q("topicid");
			$topiccardid = q("topiccardid");
			$content = $this->_getContent();
			$return = M('Topic')->TopicArticleEditSave($archiveid,$topicid,$topiccardid,$content,TIMESTAMP);
			if ($return) {
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Topic/TopicArticleEdit&archiveid='.$topicid.'&topicarticleid='.$archiveid)
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
}
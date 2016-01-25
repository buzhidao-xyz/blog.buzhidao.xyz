<?php
/**
 * 文章控制器
 * by buzhidao 2012-12-26
 */
class ArticleControl extends ArchiveControl
{
	//控制器名
	protected $_Control = "Article";

	public function __construct()
	{
		parent::__construct();
	}

	//获取文章ID
	protected function _getArticleID()
	{
		$articleid = q("articleid");
		return $articleid;
	}

	//主入口文档列表
	public function index()
	{
		$columnid = $this->_getColumnID();

		$columnids = array();
		if ($columnid) $columnids = array_merge(M("Column")->getSubColumnID($columnid),array($columnid));

		list($start,$length) = $this->getPages();
        $articleList = M("Article")->getArticle(null,$start,$length,1,$columnids,$this->_Control);
        $this->assign("total", $articleList['total']);
        $this->assign("dataList", $articleList['data']);

        $this->assign("page", getPage($articleList['total'],$this->_pagesize));
		$this->display("Article/index.html");
	}

	//新文章
	public function add()
	{
		$this->assign("accessStatus",1);

		$this->assign("adminInfo",$this->adminInfo);
		$this->assign("columnTree", D("Column")->getColumnTree($this->_Control));

		$this->display("Article/add.html");
	}

	/**
	 * 保存文章入库
	 * @param $title string 文章标题 必须
	 */
	public function save()
	{
		$data = $this->dealArchiveSubmit();
		$archiveid = M("Archive")->saveArchive($data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime'],$data['updatetime']);
		
		if ($archiveid) {
			$content = $this->_getContent();
			$articleid = M('Article')->saveArticle($archiveid,$content);
			if ($articleid) {
				//标签解析入库
				$this->parseAndSaveTag($data["tag"]);
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Article/edit&archiveid='.$archiveid)
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
		$ArchiveInfo = M("Article")->getArticle($ArchiveID,0,0,null);
		$ArchiveInfo = !empty($ArchiveInfo['data']) ? $ArchiveInfo['data'][0] : array();

		if (empty($ArchiveInfo)) $this->display("Common/error.html");

		$ArchiveInfo['content'] = M("Article")->getArticleContent($ArchiveInfo["id"]);
		$this->assign("ArchiveInfo", $ArchiveInfo);
        
		$this->assign("columnTree", D("Column")->getColumnTree());
		$this->display("Article/edit.html");
	}

	//保存更新文档信息
	public function saveEdit()
	{
		$ArchiveID = $this->_getArchiveID();
		$data = $this->dealArchiveSubmit();
		$return = M("Archive")->upArchive($ArchiveID,$data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime'],$data['updatetime']);
		if ($return) {
			$content = $this->_getContent();
			$return = M("Article")->upArticleContent($ArchiveID,$content);
			if ($return) {
				//标签解析入库
				$this->parseAndSaveTag($data["tag"]);
				$NextOperation = array(
					array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Article/edit&archiveid='.$ArchiveID)
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

	//文档回收站
	public function recover()
	{
		$this->assign("accessStatus", 1);

		list($start,$length) = $this->getPages();
        $articleList = M("Article")->getArticle(null,$start,$length,0,null,$this->_Control);
        $this->assign("total", $articleList['total']);
        $this->assign("dataList", $articleList['data']);

        $this->assign("page", getPage($articleList['total'],$this->_pagesize));
		$this->display("Article/recover.html");
	}
}
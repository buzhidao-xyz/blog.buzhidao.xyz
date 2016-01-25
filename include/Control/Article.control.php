<?php
/**
 * 文档控制器
 * by wbq 2013-03-28
 * 处理逻辑数据 执行具体的功能操作
 */
class ArticleControl extends ArchiveControl
{
	//控制器名
    protected $_control = 'Article';

    //模板
    protected $_tpl = array(
    	'index' => 'Article/index.html',
    	'list' => 'Article/list.html',
    	'body' => 'Article/body.html'
    );

	public function __construct($query=null)
	{
		parent::__construct();
		$this->_query = $query;
	}

	//栏目封面页
	public function index()
	{
        //获取栏目详情
        $this->getColumn();

		$this->display($this->_Column['template_index']);
	}

	//栏目列表页
	public function lists()
	{
        //获取栏目详情
        $this->getColumn();

		$ArchiveList = $this->getAllArchive();

        if (is_array($ArchiveList["data"]) && !empty($ArchiveList["data"])) {
            foreach ($ArchiveList["data"] as $k=>$v) {
                $ArticleInfo = M("Article")->getArticleContent($v["id"]);
                $ArchiveList["data"][$k]["content"] = isset($ArticleInfo[0]) ? $ArticleInfo[0]["content"] : null;
            }
        }

		$this->assign("ArchiveList", $ArchiveList['data']);
		$this->assign("page", getPage($ArchiveList['total'],$this->_pagesize,1));
		$this->display($this->_Column['template_list']);
	}
	//短名称
	public function l()
	{
		$this->lists();
	}

	//获取文档内容
	public function View()
	{
		$this->_getArchiveID();

		//文章点击数加一
		M("Archive")->upArchiveNumInfo($this->_archiveid,"clicknum");

		//获取文章详情
		$archiveInfo = M("Article")->getArticleInfo($this->_archiveid);
		$this->assign("archiveInfo",$archiveInfo);

		//获取文档评论
		$commentList = M("Comment")->getCommentByArchiveID($this->_archiveid);
		$this->assign("commentList",$commentList);

        //获取栏目详情
        $this->getColumn($archiveInfo["columnid"]);

		//输出页面标题和描述等信息
		$this->GCSEOInfo($archiveInfo["title"],$archiveInfo["tag"],$archiveInfo["description"]);

		//页面链接
		$this->assign("archiveURL", __HOST__."/".__SELF__."?s=a.v.".$this->_archiveid);

		$tpl = isset($this->_Column['template_body']) ? $this->_Column['template_body'] : $this->_tpl['body'];
		$this->display($tpl);
	}
	//短名称
	public function v()
	{
		$this->View();
	}
}
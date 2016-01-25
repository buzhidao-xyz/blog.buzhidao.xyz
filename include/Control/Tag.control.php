<?php
/**
 * 全文检索控制器 sphinx
 * baoqing wang
 * 2013-12-08 
 */
class TagControl extends CommonControl
{
    //标签
    protected $_tag = null;

	public function __construct($query=null)
	{
        parent::__construct();
		$this->_query = $query;

        $this->_getTag();
    }

    public function index(){}
    public function i(){}

    //获取搜索标签
    private function _getTag()
    {
        $tag = q("tag");
        $tag = $tag ? $tag : $this->_query["params"][0];
        $tag ? $this->_tag = $tag : null;

        $this->assign("tag", $this->_tag);
        return $this->_tag;
    }

    //标签搜索
    public function search()
    {
		list($start,$length) = $this->getPages();
		$ArchiveList = M("Tag")->getArchiveByTagName($this->_tag,$start,$length);
        if (is_array($ArchiveList["data"]) && !empty($ArchiveList["data"])) {
            foreach ($ArchiveList["data"] as $k=>$v) {
                $ArticleInfo = M("Article")->getArticleContent($v["id"]);
                $ArchiveList["data"][$k]["content"] = isset($ArticleInfo[0]) ? $ArticleInfo[0]["content"] : null;
            }
        }

        $this->assign("ArchiveList", $ArchiveList['data']);
        $this->assign("page", getPage($ArchiveList['total'],$this->_pagesize,1));
        $this->display("Tag/search.html");
    }
    //短名称
    public function s()
    {
        $this->search();
    }
}
<?php
/**
 * 全文检索控制器 sphinx
 * baoqing wang
 * 2013-12-08 
 */
class SearchControl extends CommonControl
{
    //关键字
    protected $_keyword = null;

	public function __construct($query=null)
	{
        parent::__construct();
        $this->_query = $query;

        $this->_getKeyword();
    }

    public function index(){}
    public function i(){}

    //获取搜索关键字
    private function _getKeyword()
    {
        $keyword = q("keyword");
        $keyword = $keyword ? $keyword : $this->_query["params"][0];
        $keyword ? $this->_keyword = $keyword : null;

        $this->assign("keyword", $this->_keyword);
        return $this->_keyword;
    }

    //关键字搜索
    public function search()
    {
        list($start,$length) = $this->getPages();
        $ArchiveList = M("Search")->getArchiveByKeyword($this->_keyword,$start,$length);
        if (is_array($ArchiveList["data"]) && !empty($ArchiveList["data"])) {
            foreach ($ArchiveList["data"] as $k=>$v) {
                $ArticleInfo = M("Article")->getArticleContent($v["id"]);
                $ArchiveList["data"][$k]["content"] = isset($ArticleInfo[0]) ? $ArticleInfo[0]["content"] : null;
            }
        }
        
        $this->assign("ArchiveList", $ArchiveList['data']);
        $this->assign("page", getPage($ArchiveList['total'],$this->_pagesize,1));
        $this->display("Search/search.html");
    }
    //短名称
    public function s()
    {
        $this->search();
    }
}
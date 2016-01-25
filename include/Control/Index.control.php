<?php
/**
 * 主控制类
 * by wbq 2011-12-01
 * 处理逻辑数据 执行具体的功能操作
 */
class IndexControl extends ArchiveControl
{
    //控制器名
    protected $_control = 'Index';
    
    //定义缓存有效时间(秒)
    static public $_life_time = 10;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    //主页
    public function index()
    {
        $ArchiveList = $this->getAllArchive($this->_columnid);

        if (is_array($ArchiveList["data"]) && !empty($ArchiveList["data"])) {
            foreach ($ArchiveList["data"] as $k=>$v) {
                $ArticleInfo = M("Article")->getArticleContent($v["id"]);
                $ArchiveList["data"][$k]["content"] = isset($ArticleInfo[0]) ? $ArticleInfo[0]["content"] : null;
            }
        }
        
        $this->assign("ArchiveList", $ArchiveList["data"]);
        $this->assign("page", getPage($ArchiveList["total"],$this->_pagesize,1));

        //获取博客评论 时间倒序前10条
        $commentList = M("Comment")->getComment(null,0,10,null,"desc");
        $this->assign("commentList",$commentList);

        $this->display('index.html');
    }
}

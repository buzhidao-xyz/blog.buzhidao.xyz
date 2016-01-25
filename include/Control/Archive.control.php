<?php
/**
 * 文档控制器
 * by wbq 2013-03-28
 * 处理逻辑数据 执行具体的功能操作
 */
class ArchiveControl extends CommonControl
{
	//控制器名
    protected $_control = 'Archive';
    //分页每页记录数
    protected $_pagesize = 10;

    //文档ID
    protected $_archiveid;

    //模板
    protected $_tpl = array(
    	'index' => 'Archive/index.html',
    	'list' => 'Archive/list.html',
    	'body' => 'Archive/body.html'
    );

	public function __construct($query=null)
	{
		parent::__construct();

		$this->_query = $query;
	}

	//获取文档ID
	protected function _getArchiveID()
	{
		$archiveid = q("archiveid");
		$archiveid = $archiveid ? $archiveid : $this->_query["params"][0];

		$archiveid ? $this->_archiveid = $archiveid : null;

		$this->assign("archiveid", $this->_archiveid);
		return $this->_archiveid;
	}

    /**
     * 获取某个栏目下面的所有文档
     * @param $columnid int 栏目id
     * @param $num int 要获取的条数
     */
    public function getAllArchive($columnid=null,$num=0,$orderby="publishtime",$orderway="desc")
    {
        $columnid = $columnid !== null ? $columnid : $this->_columnid;
        $columnids = M("Column")->getChildrenColumnID($columnid);

        list($start,$length) = $this->getPages();
        $where = empty($columnids) ? array() : array("columnid"=>array("in",$columnids));
        $length = $num ? $num : $length;
        return M("Archive")->getArchive(null,$start,$length,$where,1,$orderby,$orderway);
    }

	//栏目封面页
	public function index()
	{
		$this->display($this->_Column['template_index']);
	}

	//栏目列表页
	public function lists()
	{
		list($start,$length) = $this->getPages();
		$ArchiveList = $this->getAllArchive();

		$this->assign("ArchiveList", $ArchiveList['data']);
		$this->assign("page", getPage($ArchiveList['total'],$this->_pagesize,1));
		$this->display($this->_Column['template_list']);
	}

	//获取文档内容
	public function View()
	{
		$archiveid = $this->_getArchiveID();
		$archiveInfo = M("Archive")->getArticleInfo($archiveid);
		$this->assign("archiveInfo",$archiveInfo);
		
		$tpl = isset($this->_Column['template_body']) ? $this->_Column['template_body'] : $this->_tpl['body'];
		$this->display($tpl);
	}
}
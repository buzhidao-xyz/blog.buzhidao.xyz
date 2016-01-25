<?php
/**
 * 通用控制器基类
 * by wbq 2012-3-27
 */
class CommonControl extends BaseControl
{
	//控制器名
    protected $_control = null;
    //方法名
    protected $_action = null;

    //分页每页记录数
    protected $_pagesize = 15;

    //栏目id
    protected $_columnid = 0;
    //栏目内容
    protected $_Column;

    //query请求数据对象
    protected $_query;

    //初始化构造函数
    public function __construct()
    {
        parent::__construct();

        //导航栏目列表
        $this->ColumnList();

        $this->assign("control", CONTROL);
        $this->assign("action", ACTION);
        //获取友情链接
        $this->getFlink();
        //获取导航列表
        // $this->getFootNavigation();

        //缓存配置信息
        $this->getConfig();
    }

    //获取分页页码
    protected function getPage()
    {
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        return $page;
    }

    /**
     * 获取分页开始和条数
     * @param int $pagesize 每页显示记录数
     */
    protected function getPages($pagesize=null)
    {
        $pagesize = $pagesize ? $pagesize : $this->_pagesize;

        $page = $this->getPage();
        $start = ($page-1)*$pagesize;

        $this->assign('start',$start);
        $this->assign('length',$pagesize);

        return array($start, $pagesize);
    }

    //跳转到主页
    protected function _host()
    {
        header("location:".__APP__);
        exit;
    }

    //获取栏目ID
    protected function _getColumnID()
    {
        $columnid = q("columnid");
        $columnid = $columnid ? $columnid : $this->_query["params"][0];
        $columnid ? $this->_columnid = $columnid : null;

        $this->assign("columnid",$this->_columnid);
        return $this->_columnid;
    }

    //获取栏目详细信息
    protected function getColumn($columnid=null)
    {
        $columnid = $columnid ? $columnid : $this->_getColumnID();
        $this->_Column = M("Column")->getColumn($columnid);
        $this->assign("Column", $this->_Column);
    }

    //获取顶部导航栏目列表
    protected function ColumnList($columnid=null)
    {
        $ColumnList = M("Column")->getColumnList($columnid);
        if ($columnid) {
            return $ColumnList;
        } else {
            $this->assign("ColumnList", $ColumnList);
        }
    }

    /**
     * 格式化文档列表
     * @param $data array 文档数组列表
     */
    public function dealArchive($data=array())
    {
        if (!is_array($data) || empty($data)) return array();

        //加入文档号
        $i = 1;
        foreach ($data as $k=>$d) {
            $data[$k]['AutoIndex'] = $i;
            $i++;
        }

        return $data;
    }

    /**
     * 获取友情链接
     */
    public function getFlink()
    {
        $FlinkList = array();
        $dataList = M("Plugin")->getFlink();
        if (is_array($dataList)&&!empty($dataList)) {
            $i = 1;
            foreach ($dataList as $k=>$v) {
                $linkInfo = array(
                    'linkname' => $v['linkname'],
                    'linkurl'  => $v['linkurl'],
                    'catalogid'=> $v['catalogid'],
                    'createtime'=>$v['createtime']
                );
                if (isset($FlinkList[$v['catalogid']])) {
                    $FlinkList[$v['catalogid']]['flinklist'][] = $linkInfo;
                } else {
                    $FlinkList[$v['catalogid']] = array(
                        'autoIndex'   => $i,
                        'catalogname' => $v['catalogname'],
                        'sort'        => $v['sort'],
                        'flinklist'   => array($linkInfo)
                    );
                }
                $i++;
            }
        }
        $this->assign("FlinkList",$FlinkList);
    }

    /**
     * 获取底部导航信息
     */
    public function getFootNavigation()
    {
        $FootNavigationList = array();
        $FootNavigationList = T("navigation")->where(array("flag"=>1))->select();
        $FootNavigationList = DataListAutoIndex($FootNavigationList);
        $this->assign('FootNavigationList',$FootNavigationList);
    }

    //获取配置信息并打印输出 - SEO优化
    public function getConfig()
    {
        $Config = array(
            'host' => C('CACHE.host'),
            'sitename' => C('CACHE.sitename'),
            'keywords' => C('CACHE.keywords'),
            'description' => C('CACHE.description'),
            'HomeSiteTitle' => C('CACHE.HomeSiteTitle'),
            'AboutUs' => C('CACHE.AboutUs'),
        );
        // dump($Config);exit;
        $this->assign('Config', $Config);
    }

    //生成页面SEO信息
    protected function GCSEOInfo($title=null,$keywords=null,$description=null)
    {
        $SEOInfo = array(
            'title' => $title,
            'keywords' => $keywords,
            'description' => $description,
        );
        $this->assign("SEOInfo",$SEOInfo);
    }
}

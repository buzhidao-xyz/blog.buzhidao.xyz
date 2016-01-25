<?php
/**
 * 通用控制器
 * by wbq 2012-3-21
 */
class CommonControl extends BaseControl
{
    protected $_Control = 'Common';

    //用户权限
    protected $AdminAccess;

    //分页 每页显示数据数
    protected $_pagesize = 30;
    
    //缩略图标准宽高
    static protected $_Width = 320;
    static protected $_Height = 260;
    //缩略图最大字节
    static protected $_ImageSize = 2097152; //2M

    /**
     * 初始化并读取用户权限信息
     */
	public function __construct()
	{
        parent::__construct();

        new AccessControl();

        $this->getAdminAccess();
        $this->setAdminAccess();

        $this->assign("timestamp",TIMESTAMP);
        $this->assign("Control", $this->_Control);
    }

    //获取用户权限信息
    public function getAdminAccess()
    {
         $this->AdminAccess = session('AdminAccess');
    }

    //设置用户权限信息
    public function setAdminAccess()
    {
        $this->assign('AdminAccess', $this->AdminAccess);
    }

    /**
     * 检查一个节点是否是有效的数据库节点
     * @param $control string 要访问的类/控制器
     * @param $action string 要访问的节点/方法
     */
    protected function _isDBNode($control,$action)
    {
        $return = null;

        $where = array(
            'control' => $control,
            'action'  => $action
        );

        return T('node')->where($where)->count() ? true : false;
    }

    /**
     * 验证用户权限
     * @param $control string 要访问的类/控制器
     * @param $action string 要访问的节点/方法
     */
    public function checkAdminAccess($control,$action,$flag=0)
    {
        $accessStatus = 0;
        $return = false;
        $control = str_replace("Control", "", $control);

        $status = $this->_isDBNode($control,$action);

        if ($status) {
            foreach ($this->AdminAccess as $v) {
                if (isset($v['cnode']) && is_array($v['cnode']) && !empty($v['cnode'])) {
                    foreach ($v['cnode'] as $v1) {
                        if (isset($v1['cnode']) && is_array($v1['cnode']) && !empty($v1['cnode'])) {
                            foreach ($v1['cnode'] as $v2) {
                                if (ucfirst($v2['control']) == ucfirst($control) && $v2['action'] == $action) {
                                    $accessStatus = $v2['access'];
                                    $return = $flag ? $v2['access'] : true;
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        } else $return = true;

        //赋值节点操作权限以便用于前台判断是否显示某些操作链接
        $this->assign('accessStatus',$accessStatus);
        return $return;
    }

    /**
     * 检测节点内增删改查操作接口     
     * @param $control string 要访问的类/控制器
     * @param $action string 访问的节点/方法名(增删改查等)
     */
    protected function _checkNodeAccess($control,$action)
    {
        if (!$this->checkAdminAccess($control,$action,1)) {
            return false;exit;
        }
    }

    //获取分页页码
    protected function getPage()
    {
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        return $page;
    }

    //获取分页开始和条数
    protected function getPages($pagesize=null)
    {
        $pagesize = $pagesize ? $pagesize : $this->_pagesize;
        $page = $this->getPage();
        $start = ($page-1)*$pagesize;
        $length = $pagesize;

        $this->assign('start',$start);
        $this->assign('length',$length);

        return array($start, $length);
    }

    //跳转到主页
    protected function _host()
    {
        header("location:".__APP__);
        exit;
    }
}

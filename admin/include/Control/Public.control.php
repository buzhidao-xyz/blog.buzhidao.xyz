<?php
/**
 * 公共模块
 * by laucen 2012-8-7
 */
class PublicControl extends CommonControl
{
    protected $_Control = 'Public';

	public function __construct()
    {
        parent::__construct();
    }

    /**
     * 主入口
     */
    public function index()
    {

    }

    private function _getGroupid()
    {
        $groupid = isset($_REQUEST['groupid']) ? $_REQUEST['groupid'] : 0;

        return $groupid;
    }

    /**
     * head页
     */
    public function head()
    {
        $this->display('Public/head.html');
    }

    /**
     * 菜单
     */
    public function menu()
    {
        $groupid = $this->_getGroupid();
        $this->assign("groupid", $groupid);

        $menu = $this->getGroup($groupid);
        if (is_array($menu) && !empty($menu)) {
            $this->assign('menu',$menu['cnode']);
            $html = $this->fetch('Public/menu.html');
        } else {
            $html = $this->fetch('Public/menu_index.html');
        }

        $this->ajaxReturn(0,'OK',$html);
    }

    /**
     * 欢迎页
     */
    public function welcome()
    {
        $phpinfo = M('System')->getPHPInfo();
        $this->assign("phpinfo", $phpinfo);
    	$this->display('Public/welcome.html');
    }

    //获取组信息
    private function getGroup($groupid=null)
    {
        foreach ($this->AdminAccess as $v) {
            if ($v['id'] == $groupid) return $v;
        }
    }

    //获取快捷操作
    private function _getFastOperation()
    {
        
    }

    //新增快捷操作
    public function newFastOperation()
    {

    }
}
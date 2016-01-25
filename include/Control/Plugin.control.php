<?php
/**
 * 插件控制器
 * by buzhidao 2013-02-04
 * 处理逻辑数据 执行具体的功能操作
 */
class PluginControl extends CommonControl
{
	//控制器名
    protected $_control = 'plugin';

    //分页每页记录数
    protected $_pagesize = 10;

	public function __construct()
	{
		parent::__construct();
	}

	public function index(){}

	

	/**
     * 获取某个栏目下的所有招聘信息
     * @param $columnid int 栏目id
     * @param $num int 要获取的条数
     */
    public function getAllCooperate($columnid=null,$num=0)
    {
    	list($start,$length) = $this->getPages();
        $columnid = $columnid ? $columnid : $this->_columnid;
        $columnids = M("Column")->getChildrenColumnID($columnid);

        $where = empty($columnids) ? array() : array("columnid"=>array("in",$columnids));
        return M("Plugin")->getCooperate(null,$start,$length,$where);
    }

	//人才招聘插件
	public function Cooperate()
	{
		list($start,$length) = $this->getPages();
		$CooperateList = $this->getAllCooperate();

		$this->assign("CooperateList", $CooperateList['data']);
		$this->assign("page", getPage($CooperateList['total'],$this->_pagesize));
		$this->display("Plugin/cooperate.html");
	}

	//帮助(问答)插件
	public function QA()
	{
		$this->assign("page", getPage(170,15));
		$this->display("QA/index.html");
	}

	/*****************************留言板插件*****************************/

	/**
     * 获取验证码
     */
    static private function vcodeCheck()
    {
        $vcode = q('vcode');
        if (session('vcode') == md5(strtoupper($vcode))) {
            return true;
        } else {
            session('ecode', 1004);
            self::LoginIndex();
        }
    }

	public function saveMessage()
	{
		$username = q("username");
		if ($username&&strlen($username)>20) $this->ajaxReturn(1,'姓名在1-20个字符之间！');
		$email = q("email");
		if (!FilterHelper::C_email($email)) $this->ajaxReturn(1,'邮箱格式不正确！');
		$content = q("content");
		if (empty($content)) $this->ajaxReturn(1,'请填写留言内容！');

		$vcode = q("vcode");
		if (md5(strtoupper($vcode)) != session('vcode_MessageBoard')) $this->ajaxReturn(1,'验证码错误！');

		$return = M("Plugin")->saveMessage($username,$email,$content,TIMESTAMP);
		if ($return) {
			$this->ajaxReturn(0,'留言成功！');
		} else {
			$this->ajaxReturn(0,'留言失败！');
		}
	}
}
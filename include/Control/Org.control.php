<?php
/**
 * ORG第三方类库控制器
 * 用户可以无需登录就可以访问该控制器 
 * 不会经过系统的URL路由格式化
 * by laucen 2013-01-17
 */
class OrgControl extends CommonControl
{
    protected $_control = 'org';

	public function __construct()
    {
        parent::__construct();
    }

    //主入口
    public function index()
    {
        
    }

    //输出验证码
    public function MessageBoardVcode()
    {
        import('Lib.ORG.VCode');
        $vcode = new VCode("MessageBoard");
        $vcode->index();
    }

    //检测验证码
    public function MessageBoardCheck()
    {
        $vcode = q("vcode");
    }
}
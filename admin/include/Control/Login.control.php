<?php
/**
 * 后台登录控制器 包括登录 状态验证 登录用户信息缓存
 * by wbq 2011-12-19
 */
class LoginControl extends BaseControl
{
    //控制器类名
    protected $_Control = 'Login';
    
    /**
     * 管理中心登录入口启用/停用控制
     */
    static private $_enable = true;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 载入登录界面
     */
    public function index()
    {
        if (!self::$_enable) return false;

        $this->assign('ecode', eCode(session('ecode')));

        if (session_id()) session_destroy();
        $this->display('login.html');
    }
    
    /**
     * 登录失败则跳转到登录页
     */
    static public function LoginIndex()
    {
        if (self::isAjax()) {
            self::ajaxReturn(1,eCode(session('ecode')));
        } else {
            header("location:".__APP__."/index.php?s=login"); exit;
        }
    }
    
    /**
     * 获取用户名
     * @return adminname 登录用户名
     */
    static private function getadminname()
    {
        $adminname = q('adminname');
        if (Check::__Check('adminName',$adminname)) {
            return $adminname;
        } else {
            session('ecode', 1003);
            self::LoginIndex();
        }
    }
    
    /**
     * 获取密码
     * @return password 登录密码
     */
    static private function getPassword()
    {
        $password = q('password');
        if (Check::__Check('adminPwd',$password)) {
            return $password;
        } else {
            session('ecode', 1003);
            self::LoginIndex();
        }
    }
    
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
    
    /**
     * 验证form的登录信息 用户名 密码 验证码
     * 如果用户信息正确 登录成功并跳转到系统首页
     * 登录失败则跳转到登录页
     */
    static public function loginCheck()
    {
        self::vcodeCheck();
        $adminname = self::getadminname();
        $password = self::getPassword();
        
        $res = T('admin')->field('id,adminname,password,ukey,ustate,lastlogintime,lastloginip,logincount')->where(array('adminname'=>$adminname))->find();
        
        if (empty($res) || $adminname != $res['adminname'] || M('Admin')->passwdEncrypt($password,$res['ukey']) != $res['password']) {
            session('ecode', 1003);
            self::LoginIndex();
        }

        $time = TIMESTAMP;
        $ip = ip2longs(getIp());
        $count = $res['logincount'] + 1;
        //更新登录时间和次数
        M('Admin')->AdminEditSave($res['id'],array(
            'lastlogintime' => $time,
            'lastloginip'   => $ip,
            'logincount'    => $count
        ));

        //记录管理员登录日志
        $logdata = array(
            'adminname' => $res['adminname'],
            'loginip'   => $ip,
            'logintime' => $time
        );
        M("Admin")->saveAdminLoginLog($logdata);
        
        //session缓存管理员信息
        $adminInfo = array(
            'id'        => $res['id'],
            'adminname' => $res['adminname'],
            'lastlogintime' => $time,
            'lastloginip' => $ip,
            'logincount' => $count,
            'ukey'      => $res['ukey']
        );
		session('adminInfo', $adminInfo);
		session('sstate', md5(md5($adminname).$res['ukey']));
		session('ustate', $res['ustate']);

        if (self::isAjax()) {
            self::ajaxReturn(0,'登录成功！');
        } else {
            // header("Cache-Control:no-cache, must-revalidate");
            header("location:".__APP__."/index.php?s=index");exit;
        }
    }
 
    /**
     * 退出登录
     */
    public function logout()
    {
        if (session_id()) {
            session('ecode',null);
            session('vcode',null);
            session('adminInfo',null);
            session('sstate',null);
            session('ustate',null);
            session('AdminAccess',null);
            session('superAdmin',null);
            // session_destroy();
        }

        header("location:".__APP__);
    }
}

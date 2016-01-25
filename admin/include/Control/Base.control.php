<?php
/**
 * 控制器基类
 * by laucen 2012-5-16
 */
class BaseControl
{
    protected $_Control = 'Base';
    static protected $_control_suffix = 'Control';

    protected $_template;   //模板解析类对象
    protected $_adminInfo;   //用户信息

    protected $_system_model = null;

    static public $_sys = null;
    
    public function __construct()
    {
        if (!$this->_template) $this->_template = new Template();
        if (!$this->_system_model) $this->_system_model = new System();

        $this->adminInfo = session('adminInfo');

        $this->getSys();
        $this->isLoged();

        $this->assign('adminInfo', $this->adminInfo);

        $this->assign('uniquecode',md5(TIMESTAMP));
        
        //静态文件JS/CSS/IMAGE服务器HOST地址
        $this->assign("STATIC_FILE_HOST", C("STATIC_FILE_HOST"));
    }
    
    /**
     * 获取系统信息
     */
    private function getSys()
    {
        self::$_sys = self::$_sys ? self::$_sys : $this->_system_model->getSys();
        $this->assign("sys", self::$_sys);
    }
    
    /**
     * 登录验证程序
     */
    private function isLoged()
    {
		$res = null;
        $sstate = session('sstate'); $ustate = session('ustate');
        
        $url = parse_url(request_uri());
		if (isset($url['query'])) $res = preg_match("/^s=(Login|Org|Weixin|Mobile)(\/index)?/i",$url['query']);

        if (!$res && (empty($this->adminInfo) || $sstate != $ustate)) {
            header("location:".__APP__.'/index.php?s=Login');
            exit;
        }
    }
    
    /**
     * 检测控制器类、类方法是否存在
     * @param $control 类名
     * @param $function 类方法名
     * @return $return array('control','function')
     */
    static protected function checkControl($control='', $function='')
    {
        $return = array(0=>'', 1=>'');
        
        $control = ucfirst($control).self::$_control_suffix;
        $control = class_exists($control) ? $control : '';
        
        $return[1] = $control && method_exists($control, $function) ? $function : '';
        $return[0] = $return[1] ? $control : '';
        
        return $return;
    }

    //判断一个请求是否为AJAX请求
    static protected function isAjax()
    {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
            if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
            // 判断Ajax方式提交
            return true;
        return false;
    }

    /**
     * ajax返回
     * @param $status int 状态码 0:正常 1:有错误
     * @param $info string ajax信息
     * @param $data mixed 返回数据
     * @param $type string 数据编码类型 默认为json
     */
    static protected function ajaxReturn($status=0,$info='',$data=null,$type='json')
    {
        $return = array(
            'status' => $status,
            'info'   => $info,
            'data'   => $data
        );

        switch ($type) {
            case 'json':
                $return = json_encode($return);
                break;
            default:
                break;
        }

        echo $return; exit;
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param Boolean $waitSecond 跳转等待时间
     * @return void
     */
    protected function success($message) {
        if($this->isAjax()) $this->ajaxReturn($status, $message);
        $this->display("Common/success.html");
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param Boolean $waitSecond 跳转等待时间
     * @return void
     */
    protected function error($message) {
        if($this->isAjax()) $this->ajaxReturn($status, $message);
        $this->display("Common/error.html");
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param Boolean $waitSecond 跳转等待时间
     * @access private
     * @return void
     */
    protected function showMessage($message,$status=1,$jumpUrl='',$waitSecond=3) {
        if($this->isAjax()) $this->ajaxReturn($status, $message, $jumpUrl);

        // 提示标题
        $this->assign('msgTitle', "系统提示信息");
        $this->assign('status',$status);
        $this->assign('message',$status ? '<font color="green">'.$message.'</font>' : '<font color="red">'.$message.'</font>');// 提示信息
        // 默认停留3秒
        $this->assign('waitSecond',$waitSecond);
        // 默认操作成功自动返回操作前页面
        $this->assign("jumpUrl",!empty($jumpUrl) ? $jumpUrl : $_SERVER["HTTP_REFERER"]);
        $this->display("Common/showMessage.html");
    }

    /**
     * 赋值
     */
    protected function assign($key,$value)
    {
        $this->_template->assign($key,$value);
    }

    //fetch
    protected function fetch($tpl)
    {
        return $this->_template->fetchd($tpl);
    }

    /**
     * 解析模版
     * @param $tpl string 模版字符串 
     */
    protected function display($tpl)
    {
        $this->_template->display($tpl);
        exit;
    }
}

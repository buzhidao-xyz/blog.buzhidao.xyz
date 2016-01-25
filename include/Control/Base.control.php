<?php
/**
 * 控制器基类
 * by laucen 2012-5-16
 */
class BaseControl
{
    protected $_template;   //模板解析类对象
    protected $_userInfo;   //用户信息

	public function __construct()
	{
        if (!$this->_template) $this->_template = new Template();

        //静态文件JS/CSS/IMAGE服务器HOST地址
        $this->assign("STATIC_FILE_HOST", C("STATIC_FILE_HOST"));
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
        $control = class_exists($control)?$control:'';
        
        $return[1] = $control&&method_exists($control, $function)?$function:'';
        $return[0] = $return[1]?$control:'';
        
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
    static protected function ajaxReturn($status=0,$info='',$data=array(),$type='json')
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
    }
}
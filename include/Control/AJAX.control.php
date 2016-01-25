<?php
/**
 * ajax控制器
 * by wbq 2011-12-08
 */
class AJAXControl extends CommonControl
{
    //定义类名
    static private $_class_name = 'Ajax Control';
    
    /**
     * 如果请求的控制器不存在 则用此定义的类名
     */
    static protected $_control = 'AJAX';
    
    /**
     * 如果请求的方法不存在 则用此定义的方法
     */
    static protected $_function = 'ajaxData';
    
    /**
     * $_data返回值数据格式
     * status: 0 处理出错 1 处理成功 999 AJAX默认状态值
     * data: 返回数据(数据/出错信息) mixed
     */
    static protected $_data = array(
        'status' => 999,
        'data'   => '请求出错!'
    );
    
    public function __construct()
    {
        
    }
    
    static public function index()
    {
        list($_control, $_function) = self::get();
        list($_control, $_function) = self::checkControl($_control, $_function);
        list($_control, $_function) = self::set($_control, $_function);

        $data = $_control::$_function();
        $return = self::go($data);
        
        echo $return;
    }
    
    static protected function go($data)
    {
        $return = array();
        $return['status'] = isset($data['status'])?$data['status']:self::$_data['status'];
        $return['data'] = isset($data['data'])?$data['data']:self::$_data['data'];

        return json_encode($return);
    }
    
    static protected function get()
    {
        $control = q('c');
        $function = q('f');
        
        return array($control, $function);
    }
    
    static protected function set($control, $function)
    {
        $return = array();
        
        $return[0] = $control?$control:self::$_control.self::$_control_suffix;
        $return[1] = $function?$function:self::$_function;

        return $return;
    }
    
    static protected function ajaxData()
    {
        return array();
    }
}

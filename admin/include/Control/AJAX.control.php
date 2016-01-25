<?php
/**
 * ajax控制器
 * by wbq 2011-12-08
 */
class AJAXControl extends BaseControl
{
    //定义类名
    static protected $_class = 'Ajax';
    
    //如果请求的控制器不存在 则用此定义的类名
    protected $_Control = 'AJAX';
    
    //如果请求的方法不存在 则用此定义的方法
    static protected $_function = 'ajaxData';
    
    /**
     * $_data返回值数据格式
     * status: 0 处理出错 1 处理成功 999 AJAX默认状态值
     * data: 返回数据(数据/出错信息) mixed
     */
    static protected $_data = array(
        'status' => 999,
        'info'   => '',
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

        $_obj = new $_control();
        self::go($_obj->$_function());
    }
    
    static protected function go($data)
    {
        $_data = array_merge(self::$_data, $data);
        self::ajaxReturn($_data['status'],$_data['info'],$_data['data']);
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
        
        $return[0] = $control ? $control : self::$_control.self::$_control_suffix;
        $return[1] = $function ? $function : self::$_function;

        return $return;
    }
    
    static protected function ajaxData()
    {
        return array();
    }
}

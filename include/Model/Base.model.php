<?php
/**
 * base 所有类的父类
 * by wbq 2011-12-05
 */
class Base
{
    static protected $_control = 'Base';
    
    static protected $_control_suffix = '_Control';
    
    public function __construct()
    {
        
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
    
    /**
     * 获取文件内容
     * @param $f 文件位置
     * @param $m 0:以字符串方式读取 1:以数组方式读取 默认为0
     */
    static protected function _getFile($f,$m=0)
    {
        $content = null;
        
        if (!$m) {
            $content = file_exists($f)?file_get_contents($f):'';
        } else {
            $h = @fopen($f,'r');
            if ($h) {
                while ($l = fgets($h)) {
                    $content[] = $l;
                }
            }
            fclose($h);
        }
        
        return $content;
    }
}
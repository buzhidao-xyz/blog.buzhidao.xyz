<?php
/**
 * base 所有类的父类
 * by wbq 2011-12-05
 */
class Base
{
    static protected $_model = 'Base';
    
    static protected $_control_suffix = 'Control';
    
    public function __construct()
    {
        
    }

    /**
     * 解析orm映射字段
     * @param $fields array 要解析的字段数组
     * @param $table array 表名
     * @param $type bool 解析类型 true 正解析 false 反解析
     */
    protected function parseMap($fields,$table,$type=ture)
    {
        global $orm;
        $key = null;
        $return = array();

        if ($type == true) {

        } else {
            foreach ($fields as $k=>$v) {
                foreach ($table as $t) {
                    if (in_array($k, $orm[$t])) {
                        $keys = array_keys($orm[$t],$k);
                        $key = $keys[0];
                        break;
                    }
                }
                $key = $key ? $key : $k;
                $return[$key] = $v;
            }
        }

        return $return;
    }
}

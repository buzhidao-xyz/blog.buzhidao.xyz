<?php
/**
 * 路由器 重新定位url路径
 * by wbq 2011-11-09
 */

class Route
{
    static private $_current_url;
    
    /**
     * 定义查询数组
     */
    static private $_query = array(
        'control' => 'Index',
        'action'  => 'index',
        'string'  => ''
    );

    //控制器后缀
    static private $_control = 'Control';
    
    public function __construct()
    {
        self::_getUrl();
        self::_checkUrl();
        self::_analyze();
        self::_call();
    }
    
    /**
     * 获取当前访问url
     */
    static private function _getUrl()
    {
        self::$_current_url = urldecode(__HOST__.request_uri());
    }
    
    /**
     * 验证当前url有效性
     */
    static private function _checkUrl()
    {
        $url = "#^http://[0-9a-z.:-]+/(".__SELF__."/)?(index\.php)?(\?(s=.+)?)?$#i";
        if (!preg_match($url, self::$_current_url)) {
            self::_host();
        }
    }
    
    /**
     * 分析url
     */
    private function _analyze()
    {
        $url_array = parse_url(self::$_current_url);
        if (isset($url_array['query'])) {
            preg_match_all("/^s=([0-9a-z]+)(\/)?([0-9a-z]+)?(&.+)*/i", $url_array['query'], $url_array);

            self::$_query['control'] = ucfirst($url_array[1][0]);
            if (!class_exists(self::$_query['control'].self::$_control)) $this->_host();
            
            self::$_query['action'] = $url_array[3][0] ? $url_array[3][0] : 'index';
            if (!method_exists(self::$_query['control'].self::$_control, self::$_query['action'])) self::$_query['action'] = 'index';
            
            if (!empty($url_array[4])) {
                $query_string = explode('&', $url_array[4][0]);
                
                foreach ($query_string as $value) {
                    if ($value && strstr($value, '=')) {
                        $value = explode('=', $value);
                        self::$_query['string'][$value[0]] = $value[1];
                    }
                }
            }
        }
    }
    
    /**
     * 跳转到主页(host)
     */
    static private function _host()
    {
        header("location:".__APP__);
        exit;
    }
    
    /**
     * 访问控制层类 方法
     */
    static private function _call()
    {
        $_control  = self::$_query['control'].self::$_control;
        $_action = self::$_query['action'];

        //定义页面page
        define('PAGEINDEX', 'index.php');
        //定义CONTROL和ACTION
        define('CONTROL', self::$_query['control']);
        define('ACTION', self::$_query['action']);
        
        $obj = new $_control(self::$_query);
        
        if (method_exists($_control, 'checkAdminAccess') && !$obj->checkAdminAccess($_control, $_action)) self::_host();

        $obj->$_action();
    }
}

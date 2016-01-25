<?php
/**
 * 路由器 重新定位url路径
 * by wbq 2011-11-09
 */

class Route
{
    static private $_current_url;
    
    //定义查询数组
    static private $_query = array(
        'control' => 'Index',
        'action'  => 'index',
        'params'  => null,
        'string'  => null
    );

    //控制器后缀
    static private $_control = 'Control';

    //URL配置文件对象
    private $_url_route;
    
    public function __construct()
    {
        //URL配置文件
        require('include/Config/url.config.php');
        $this->_url_route = $url_route;

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
        $url = '#^http://[0-9a-z.:-]+/('.__SELF__.'/)?(index\.php)?(\?(s=.+)?)?$#i';
        if (!preg_match($url, self::$_current_url)) {
            self::_host();
        }
    }
    
    /**
     * 分析url
     * 2013-12-15 添加UTF-8汉字正则匹配
     */
    private function _analyze()
    {
        $url_array = parse_url(self::$_current_url);
        if (isset($url_array['query'])) {
            // preg_match_all('/^s=([0-9a-z]+)(\/)?([0-9a-z]+)?(&.+)*/i', $url_array['query'], $url_array);
            preg_match_all('/^s=([0-9a-z]+)([\/\.][0-9a-z\x{4e00}-\x{9fa5}]+)?(([\/\.][0-9a-z\x{4e00}-\x{9fa5}]*)*)?(&.+)*/ui', $url_array['query'], $url_array);

            $control = null;
            $action = null;
            $controlString = $url_array[1][0];
            $actionString = $url_array[2][0];
            $paramString = substr($url_array[3][0],1);
            //判断是shortURL模式还是fullURL模式
            //如果是fullURL模式
            if (preg_match('/^\//i', $actionString)) {
                $control = $controlString;
                $actionString ? $action = str_replace("/","",$actionString) : null;
            }
            //如果是shortURL模式
            if (preg_match('/^\./i',$actionString)) {
                //查询并获取URL Control配置
                if (!isset($this->_url_route[$controlString])) {
                    $this->_host();
                }
                $routeConfig = $this->_url_route[$controlString];

                $control = $routeConfig['control'];
                $action = $actionString ? str_replace(".","",$actionString) : $routeConfig['action'];

                //整理params
                $params = explode(".", $paramString);
                self::$_query['params'] = $params;
            } else {
                $control = isset($this->_url_route[$controlString]) ? $this->_url_route[$controlString]['control'] : $controlString;
            }

            $control ? self::$_query['control'] = ucfirst($control) : null;
            $classControl = self::$_query['control'].self::$_control;
            if (!class_exists($classControl)) $this->_host();
            
            $action ? self::$_query['action'] = $action : null;
            if (!method_exists($classControl, self::$_query['action'])) self::$_query['action'] = 'index';
            
            if (!empty($url_array[5])) {
                $query_string = explode('&', $url_array[5][0]);
                
                foreach ($query_string as $value) {
                    if ($value && strstr($value, '=')) {
                        $value = explode('=', $value);
                        self::$_query['string'][$value[0]] = $value[1];
                    }
                }
            }
            // dump(self::$_query);exit;
        }
    }
    
    /**
     * 跳转到主页(host)
     */
    static private function _host()
    {
        header("location:".__APP__."/");
        exit;
    }
    
    /**
     * 访问控制层类 方法
     */
    static private function _call()
    {
        $_control = self::$_query['control'].self::$_control;
        $_action = self::$_query['action'];

        //定义页面page
        define('PAGEINDEX', 'index.php');
        //定义CONTROL和ACTION
        define('CONTROL', self::$_query['control']);
        define('ACTION', self::$_query['action']);
        
        $obj = new $_control(self::$_query);
        $obj->$_action();
    }
}
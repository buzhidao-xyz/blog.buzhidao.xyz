<?php
/**
 * 系统首页控制器
 * by wbq 2011-12-21
 */
class IndexControl extends CommonControl
{
    //定义类名
    protected $_Control = 'Index';
    
    //定义缓存有效时间(秒)
    static public $_life_time = 10;
    
    //定义调用数组
    static private $_query = array(
        'control'   => 'Index_Control',
        'action'    => 'index',
        'string'    => ''
    );
    
    public function __construct($query)
    {
		parent::__construct();
        self::$_query = $query;
    }
    
    /**
     * 主页控制
     */
    public function index()
    {
        $this->display('index.html');
    }
}

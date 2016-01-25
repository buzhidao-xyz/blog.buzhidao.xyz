<?php
/**
 * memcache类 缓存数据
 * by wbq 2011-12-15
 */
class Memcacheg
{
    static private $_class_name = "Memcacheg Model";
    
    /**
     * memcache开关
     */
    static private $_enable = false;
    
    /**
     * memcacheg静态对象
     */
    static public $memcacheg;
    
    /**
     * 默认缓存数据有效期(S)
     * 0 表示永不过期
     */
    static private $_time = 3600;
    
    /**
     * 默认缓存服务器配置 host=localhost port=11211
     */
    static private $_default_server = array('localhost',11211);
    
    /**
     * 构造函数 初始化memcache连接
     * 连接对象指向静态变量$mem
     */
    public function __construct()
    {
        if (!extension_loaded('memcache') || !self::$_enable) {
            self::$_enable = false;
            return false;
        }
        
        $this->connect();
    }
    
    /**
     * 新建memcache类实例 并缓存服务器
     * 默认是连接到本地localhost服务器
     */
    private function connect()
    {
        if (!self::$_enable) return false;
        
        list($host, $port) = self::$_default_server;
        
        self::$memcacheg = new Memcache();
        self::$memcacheg->connect($host, $port);
    }
    
    /**
     * 增加一个缓存服务器
     * @param $host 服务器地址
     * @param $port 服务器端口号
     */
    static public function server($host, $port)
    {
        if (!self::$_enable) return false;
        
        if (!$host) return false;
        if (!$port) return false;
        
        self::$memcacheg->addServer($host, $port);
    }
    
    /**
     * 在缓存里面增加一条记录
     * @param $k 键
     * @param $v 键值
     * @param $t 缓存有效期(秒)
     * @param $p 具体操作 0 不复写已有值 1 复写已有值
     */
    static public function set($k, $v, $t=null, $p=0)
    {
        if (!self::$_enable) return false;
        
        if (!$k) return false;
        $t = preg_match("/^0|[1-9][0-9]{0,6}$/", $t) ? $t : self::$_time;

        if (self::$memcacheg->get($k) === false) {
            $return = self::$memcacheg->add($k, $v, false, $t);
        } else if ($p == 1) {
            $return = self::$memcacheg->set($k, $v, false, $t);
        } else {
            $return = false;
        }
        
        return $return;
    }
    
    /**
     * 删除一条缓存记录
     * @param $k 键
     * @param $t 定时器 多长时间之后执行删除操作
     * @return bool
     */
    static public function delete($k, $t=0)
    {
        if (!self::$_enable) return false;
        
        if (!$k) return false;
        $t = preg_match("/^0|[1-9][0-9]{0,6}$/", $t)?$t:0;
        
        return self::$memcacheg->delete($k, $t);
    }
    
    /**
     * 从缓存服务器取一条记录
     * @param $k 键
     * @return 取得的键值
     */
    static public function get($k)
    {
        if (!self::$_enable) return false;
        
        if (!$k) return false;
        
        return self::$memcacheg->get($k);
    }
    
    /**
     * 关闭缓存服务器的连接
     */
    static public function close()
    {
        if (!self::$_enable) return false;
        
        return self::$memcacheg->close();
    }
    
    /**
     * 清空所有缓存数据
     */
    static public function flush()
    {
        if (!self::$_enable) return false;
        
        return self::$memcacheg->flush();
    }
}

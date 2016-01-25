<?php
/**
 * 日志(log)处理
 * by wbq 2012-1-31
 */
class LogsControl extends CommonControl
{
    /**
     * 控制器名
     */
    static private $_class_name = 'Logs Control';
    
    /**
     * 日志文件列表
     */
    static private $_file_list = array();
    
    /**
     * log内容
     */
    static private $_logContent = array();
    
    /**
     * log文件
     */
    static private $_logFile = null;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Log控制器主入口 
     */
    public function index()
    {   
        list($u,$p) = self::_get();
        Logs::checkLog($u,$p);
        
        $f = g('f');
        $logFile = $f ? $f : self::$_logFile;
        
        ob_flush();
        $logContent = Logs::_getLog($f);
        $logContent = $logContent ? $logContent : self::$_logContent;
        
        $this->assign("logfile", $logFile);
        $this->assign('logcontent', $logContent);
        
        $this->display('Log/index.html');
    }
    
    /**
     * 备份日志文件
     */
    static public function backup()
    {
        Logs::_backup();
        header("location:?s=logs");
    }
    
    /**
     * 清空当前的日志记录
     */
    static public function clear()
    {
        Logs::_clear();
        header("location:?s=logs");
    }
    
    /**
     * 查看备份的日志文件列表
     */
    public function logflist()
    {
        $_file_list = Logs::_list();
        
        $this->assign("logfile", 1);
        $this->assign("filelist", $_file_list);
        $this->display('Log/list.html');
    }
    
    /**
     * 获取客户端输入的用户名 密码
     */
    static private function _get()
    {
        $username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
        
        return array($username, $password);
    }
}

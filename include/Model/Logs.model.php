<?php
/**
 * 日志(log)处理
 * by wbq 2012-1-31
 */
class Logs extends Base
{
    /**
     * 定义类名
     */
    static private $_class_name = 'Logs Model';
    
    /**
     * 登录用户名
     */
    static private $_username = 'admin';
    
    /**
     * 登录用户密码
     */
    static private $_password = '123456';
    
    static private $_log_file = 'access.log';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 验证登录
     */
    static public function checkLog($u,$p)
    {
        if ($u != self::$_username || $p != self::$_password) {
            Header("WWW-Authenticate: Basic realm=\"guess it!\"");
			Header("HTTP/1.0 401 Unauthorized");

			echo <<<EOB
				<html><body>
				<h1>Rejected!</h1>
				<big>Wrong Username or Password!</big>
				</body></html>
EOB;
			exit;
        }
    }
    
    /**
     * 读取日志内容
     */
    static public function _getLog($f=null)
    {
        $log_file = self::_log_file($f);
        
        return self::_getFile($log_file,1);
    }
    
    /**
     * 备份日志文件
     */
    static public function _backup()
    {
        $log_file = self::_log_file();
        
        $backup_file = str_replace(".log", "_".date("YmdHis", TIMESTAMP).".log", $log_file);
        @copy($log_file, $backup_file);
        file_put_contents($log_file, '');
    }
    
    /**
     * 清除当前的日志记录
     */
    static public function _clear()
    {
        $log_file = self::_log_file();
        
        @file_put_contents($log_file, '');
    }
    
    /**
     * 备份的日志列表
     * @return 备份的日志文件名列表
     */
    static public function _list()
    {
        $dir = C('LOG_DIR');
        $files = array();
        
        if (is_dir($dir)) {
            $h = opendir($dir);
            while (($f = readdir($h)) !== false) {
                if ($f != "." && $f != "..") {
                    $files[] = $f;
                }
            }
            closedir($h);
            
            return $files;
        }
    }
    
    /**
     * 获取日志文件
     * @param $f 传来的日志文件
     */
    static public function _log_file($f=null)
    {
        $dir = C('LOG_DIR');

        return empty($f) ? $dir.'/'.self::$_log_file : $dir.'/'.$f;
    }
}

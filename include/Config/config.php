<?php
//开启session
session_start();

error_reporting(E_ALL | E_STRICT);

//数据库配置缓存
require('cache.config.php');

//常量配置文件
require('system.config.php');

//数据库和主配置文件
require('main.config.php');

//orm数据映射文件
require('orm.config.php');

//载入错误码映射文件
require('ecode.config.php');

//数据库配置参数缓存
$config['CACHE'] = isset($cache) ? $cache : array();

//读取c.config.php文件内容
function C($key)
{
    global $config;
    $key = explode(".", $key);
    if (isset($key[1])) {
        return array_key_exists($key[0], $config) && array_key_exists($key[1], $config[$key[0]]) ? $config[$key[0]][$key[1]] : '';
    } else {
        return array_key_exists($key[0], $config) ? $config[$key[0]] : '';
    }
}

//自动加载工具类，无需调用
function __autoload($class)
{
    $include_dir = C('INCLUDE_DIR');
    $classpath = $include_dir.'/Model/'.$class.".model.php";

	if (file_exists($classpath)) {
	   include_once($classpath);
	} else {
        $type = strstr($class, 'Control') ? 'Control' : 0;
        $type = !$type && strstr($class, 'Helper') ? 'Helper' : 0;
        switch ($type) {
            case 'Control':
                $classname = str_replace('Control', '', $class);
                $classpath = $include_dir.'/Control/'.$classname.".control.php";
                break;
            case 'Helper':
                $classname = str_replace('Helper', '', $class);
                $classpath = $include_dir.'/Helper/'.$classname.".helper.php";
                break;
            default:
                return true;
        }
        
        if (file_exists($classpath)) include_once($classpath);
        else {
            $include_path = ROOT_DIR.'/include';
            $classpath = $include_path.'/Model/'.$class.".model.php";

            if (file_exists($classpath)) {
               include_once($classpath);
            } else {
                $type = strstr($class, 'Helper') ? 'Helper' : 0;
                $type = !$type && strstr($class, 'Smarty') ? 'Smarty' : $type;
                switch ($type) {
                    case 'Helper':
                        $classname = str_replace('Helper', '', $class);
                        $classpath = $include_path.'/Helper/'.$classname.".helper.php";
                        break;
                    case 'Smarty':
                        $classpath = $include_path.'/Vendor/Smarty/sysplugins/'.strtolower($classname).".php";
                        break;
                    default:
                        return true;
                }
                
                if (file_exists($classpath)) include($classpath);
            }
        }
	}
}

//引入全局方法
require(C('INCLUDE_DIR').'/function.php');
require(C('INCLUDE_DIR').'/common.php');

//加载cmstag解析函数库
require(C('INCLUDE_DIR').'/cmstag.php');

function Error_Handler($errno,$errstr,$errorfile,$errline,$errcontext)
{
	if ($errno) {
		$error = getIp().' 系统发生错误: '.$errstr.', in '.$errorfile.' on line '.$errline;

        dump("<font color=red>".$error."</font>");
		// Errorlog($error);
	}
}

//设置自定义的错误记录程序
set_error_handler('Error_Handler');

//初始化数据库操作类对象
$DBArray = C("DB");
static $DBOBJECTIVE = array();
foreach ($DBArray as $k=>$v) {
    $dbclass = "DB".$v['dbtype'];
    import('Lib.Driver.DB.'.$dbclass);
    $DBOBJECTIVE[] = new $dbclass();
}

//初始化memcache缓存类
// new Memcacheg();
//SAE Memcache初始化
IS_SAE ? $mmc = memcache_init() : NULL;

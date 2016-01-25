<?php
//配置文件(config.php)目录
define('CONFIG_DIR', str_replace('\\', '/', dirname(__FILE__)));

//根目录
define('ROOT_DIR', str_replace('/include/Config', '', str_replace('\\', '/', dirname(__FILE__))));

//管理中心目录
define('ADMIN_DIR', ROOT_DIR.'/'.$cache['admin_path']);

//服务器环境
define('IS_CGI',substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );

//环境检测 根据SAE_APPNAME常量判断是否SAE环境 http://appid.sinaapp.com/
defined('SAE_APPNAME') ? define('IS_SAE', true) : define('IS_SAE', false);

//主域名HOST
define('__HOST__', 'http://'.$_SERVER['HTTP_HOST']);

//URL常量
define('__SELF__', substr(str_replace('/index.php', '', $_SERVER['PHP_SELF']),1));

//工程目录
define('APP_PATH', str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])));

//工程名
define('APP_NAME', substr(APP_PATH, strripos(APP_PATH, '/')+1));

//当前项目目录相对网站服务器路径地址
define('__APP__', isset($_SERVER['SCRIPT_NAME']) ? str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) : __SELF__);

//网站主项目目录相对网站服务器路径地址
define('__APPM__', str_replace("/".$cache['admin_path'], "", __HOST__.__APP__));

//网站include主目录
define('__INC__', ROOT_DIR.'/include');

//缓存（Memcache）目录
define('CACHE_DIR', IS_SAE ? 'saemc://' : ROOT_DIR.'/');
define('ADMIN_CACHE_DIR', IS_SAE ? 'saemc://'.$cache['admin_path'].'/' : ROOT_DIR.'/'.$cache['admin_path'].'/');

//时区
date_default_timezone_set('Asia/Shanghai');

//时间戳
define('TIMESTAMP', time());

//数据库表前缀
define('TBF', '#@__');


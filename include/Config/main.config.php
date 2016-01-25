<?php
include("db.config.php");
$config = array(
    'STYLE_DEFAULT'        => ROOT_DIR.'/themes/default',
    'STYLE_PUBLIC'         => ROOT_DIR.'/themes/public',

    'INCLUDE_DIR'          => ROOT_DIR.'/include',
    'CACHE_DIR'            => CACHE_DIR.'cache/compile',
    'LOG_DIR'              => ROOT_DIR.'/data/log',
    'DATABASE_BACKUP_DIR'  => ROOT_DIR.'/data/database',
    'VENDOR'               => ROOT_DIR.'/include/Vendor',

    'UPLOAD_PATH'          => ROOT_DIR.'/uploads',

    //session变量名混淆字符串
    'SESSION_ENCRYPT'      => 'imbzd',

    'COMPILE_LIFE_TIME'    => 10, //编译文件有效时间 N秒

    'TEMPLATE_TYPE'        => 'Smarty',   //模版引擎类型 Laugh/Smarty
    //模版选项 Smarty模版起作用
    'TEMPLATE_OPTIONS' => array(
        'debug'            => false,
        'caching'          => false,
        'cache_lifetime'   => 120,
        'template'         => ROOT_DIR.'/themes/default',
        'template_compile' => CACHE_DIR.'cache/compile',
        'template_cache'   => CACHE_DIR.'cache/cache',
        'plugin_dir'       => ROOT_DIR.'/include/Vendor/Smarty/plugins',
    ),

    //Laugh数据模型数据库配置信息
    'DB' => IS_SAE ? array('db0'=>$db_config['sae']) : array('db0'=>$db_config['db0']),

    //SAE静态JS/CSS/IMAGE文件服务器HOST地址
    'STATIC_FILE_HOST' => IS_SAE ? 'http://imbzd.sinaapp.com/' : __APP__."/",
);
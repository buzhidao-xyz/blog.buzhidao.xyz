<?php
$db_config = array(
	//默认
	'db0' => array(
		'dbtype'   => 'pdomysql',
		'host'     => '127.0.0.1',
		'port'     => 3306,
        'username' => 'root',
        'password' => 123456,
        'database' => 'blog_buzhidao_xyz',
        'prefix'   => 'la_'
	),
	//sae
	'sae' => array(
		'dbtype'   => 'pdomysql',
		'host'     => defined('SAE_MYSQL_HOST_M') ? SAE_MYSQL_HOST_M : null,
		'port'     => defined('SAE_MYSQL_PORT') ? SAE_MYSQL_PORT : null,
        'username' => defined('SAE_MYSQL_USER') ? SAE_MYSQL_USER : null,
        'password' => defined('SAE_MYSQL_PASS') ? SAE_MYSQL_PASS : null,
        'database' => defined('SAE_MYSQL_DB') ? SAE_MYSQL_DB : null,
        'prefix'   => 'la_'
	),
);
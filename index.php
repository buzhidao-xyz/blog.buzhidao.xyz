<?php
//开启缓冲区 用gzip压缩内容输出
ob_start('ob_gzhandler');

//引入配置文件
require("include/Config/config.php");

//引入路由器
require("route.php");
new Route();

//输出缓冲区内容并关闭缓冲
ob_end_flush();
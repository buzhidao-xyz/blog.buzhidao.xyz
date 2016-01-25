<?php
/**
 * URL路由配置文件
 * zhidao bu
 * 2013-12-14
 */
$url_route = array(
	//文档shortURL配置
	"a" => array("control"=>"Article","action"=>"i"),
	//标签
	"t" => array("control"=>"Tag","action"=>"i"),
	//搜索
	"s" => array("control"=>"Search","action"=>"i"),
	//评论
	"c" => array("control"=>"Comment","action"=>"i"),
	//公共类
	"p" => array("control"=>"Public","action"=>"i"),
);
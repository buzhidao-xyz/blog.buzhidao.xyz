<?php
/**
 * 标签数据模型
 * zhidao bu
 * 2013-12-24
 */
class Tag extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    //根据标签名获取文档列表
    public function getArchiveByTagName($tag=null,$start=0,$length=0)
    {
        if (!$tag) return array("total"=>null, "data"=>null);

    	$where = array(
    		"tag" => array("like","%".$tag."%")
    	);
    	$archiveList = M("Archive")->getArchive(null,$start,$length,$where);

    	return $archiveList;
    }
}
<?php
/**
 * 搜索数据模型
 * zhidao bu
 * 2013-12-24
 */
class Search extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    //根据标签名获取文档列表
    public function getArchiveByKeyword($keyword=null,$start=0,$length=0)
    {
        if (!$keyword) return array("total"=>null, "data"=>null);

    	$where = array(
    		"a.title" => array("like","%".$keyword."%")
    	);
    	$archiveList = M("Archive")->getArchive(null,$start,$length,$where);

    	return $archiveList;
    }
}
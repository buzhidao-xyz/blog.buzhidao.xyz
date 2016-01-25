<?php
/**
 * 评论模型
 * zhidao bu
 * 2013-12-16
 */
class Comment extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//保存评论
	public function saveComment($data=array())
	{
		if (!is_array($data) || empty($data)) return false;

		return T("comment")->add($data);
	}

	//获取全部评论列表
	public function getComment($id=null,$start=null,$length=null,$where=array(),$orderway="asc")
	{
		!is_array($where) ? $where = array() : null;

		$order = array(
			"a.createtime" => $orderway
		);

		$total = T("comment")->where($where)->count();

		$obj = T("comment")->join(" ".TBF."archive b on a.archiveid=b.id ")->field("a.*,b.title")->where($where)->order($order);
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->select();

		return array("total"=>$total,"data"=>$data);
	}

	//获取某个文档的评论列表
	public function getCommentByArchiveID($archiveid=null)
	{
		if (!$archiveid) return false;

		$commentList = $this->getComment(null,0,0,array("archiveid"=>$archiveid));

		return $commentList;
	}
}
<?php
/**
 * 文档模型
 * by laucen 2013-03-27
 */
class Archive extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 保存文档信息
	 * @param array $filter 被过滤的字段 不需要更新的
	 */
	public function saveArchive($title=null,$tag=null,$source=null,$author=null,$columnid=null,$status=null,$seotitle=null,$keyword=null,$description=null,$thumbimage=null,$publishtime=null,$updatetime=null,$filter=array())
	{
		$data = array(
			'title' => $title,
			'tag'   => $tag,
			'source'   => $source,
			'author'   => $author,
			'columnid' => $columnid,
			'status'   => $status,
			'seotitle' => $seotitle,
			'keyword'  => $keyword,
			'description' => $description,
			'updatetime'  => $updatetime
		);

		if ($thumbimage) $data['thumbimage'] = $thumbimage;

		if (!in_array("publishtime", $filter)) $data['publishtime'] = $publishtime;

		return T("archive")->add($data);
	}

	/**
	 * 获取文档列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getArchive($id=null,$start=0,$length=0,$where=array())
	{
		if ($id) $where['a.id'] = is_array($id) ? array('in', $id) : $id;

		if (isset($where['state'])) {
			$where['a.state'] = $where['state'];
			unset($where['state']);
		}
		if (isset($where['control'])) {
			$ColumnModelInfo = M("CTModel")->getColumnModelByControl($where['control']);
			$where['b.columnmodel'] = $ColumnModelInfo['id'];
			unset($where['control']);
		}

		$total = T("archive")->join(' '.TBF.'column as b on a.columnid=b.id ')->field('*')->where($where)->count();
		$obj = T("archive")->join(' '.TBF.'column as b on a.columnid=b.id ')->field('a.*,b.columnname,b.columntype')->where($where)->order("a.id","desc");
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->select();

		return array(
			'total' => $total,
			'data'  => $data
		);
	}

	/**
	 * 更新文档信息
	 * @param array $filter 被过滤的字段 不需要更新的
	 */
	public function upArchive($archiveid=null,$title=null,$tag=null,$source=null,$author=null,$columnid=null,$status=null,$seotitle=null,$keyword=null,$description=null,$thumbimage=null,$publishtime=null,$updatetime=null,$filter=array())
	{
		$data = array(
			'title' => $title,
			'tag'   => $tag,
			'source'   => $source,
			'author'   => $author,
			'columnid' => $columnid,
			'status'   => $status,
			'seotitle' => $seotitle,
			'keyword'  => $keyword,
			'description' => $description,
			'updatetime'  => $updatetime
		);

		if ($thumbimage) $data['thumbimage'] = $thumbimage;

		if (!in_array("publishtime", $filter)) $data['publishtime'] = $publishtime;

		return T("archive")->where(array("id"=>$archiveid))->update($data);
	}

	/**
	 * 回收文档
	 * @param $ArchiveID array 文档数组
	 */
	public function recoverArchive($ArchiveID=array())
	{
		if (!is_array($ArchiveID)||empty($ArchiveID)) return false;

		$where = array("id"=>array("in",$ArchiveID));
		return T("archive")->where($where)->update(array("state"=>0));
	}

	/**
	 * 还原文档
	 * @param $ArchiveID array 文档数组
	 */
	public function backArchive($ArchiveID=array())
	{
		if (!is_array($ArchiveID)||empty($ArchiveID)) return false;

		$where = array("id"=>array("in",$ArchiveID));
		return T("archive")->where($where)->update(array("state"=>1));
	}

	//彻底删除文档
	public function deleteArchive($ArchiveID=array())
	{
		if (!is_array($ArchiveID)||empty($ArchiveID)) return false;

		$where = array("id"=>array("in",$ArchiveID));
		return T("archive")->where($where)->delete();
	}

	/**
	 * 删除文档图片
	 * @param $archiveid int/array 文档ID
	 * @param $imageids int/array 图片ID
	 */
	public function deleteArchiveImages($archiveid=null)
	{
		if (!$archiveid) return false;

		$where['archiveid'] = is_array($archiveid) ? array("in",$archiveid) : $archiveid;

		return T("images")->where($where)->update(array("archiveid"=>0));
	}

	/**
	 * 保存文档图片
	 * @param $archiveid int 文档ID
	 * @param $imageids int/array 图片ID
	 */
	public function addArchiveImages($archiveid=null,$imageids=null)
	{
		if (!$archiveid || empty($imageids)) return false;

		$where = array();
		$where['id'] = is_array($imageids) ? array("in",$imageids) : $imageids;

		$data = array("archiveid"=>$archiveid);
		return T("images")->where($where)->update($data);
	}

	/**
	 * 获取文档图片
	 * @param $archiveid int 文档ID
	 */
	public function getArchiveImages($archiveid=null)
	{
		if (!$archiveid) return false;
		return T("images")->where(array("archiveid"=>$archiveid))->select();
	}

	/**
	 * 获取文档图片
	 * @param $archiveid int 文档ID
	 * @param $columnid int 栏目ID
	 */
	public function upArchiveColumn($archiveid=null,$columnid=null)
	{
		if (empty($archiveid)||!$columnid) return false;
		$where = array(
			'id' => is_array($archiveid) ? array("in",$archiveid) : $archiveid
		);
		return T("archive")->where($where)->update(array("columnid"=>$columnid));
	}
}
<?php
/**
 * 广告模型
 * by wbq 2013-05-10
 */
class Advertise extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取首页中部广告
	 * @param int $id 广告id
	 * @param int $start 分页开始记录数
	 * @param int $length 分页结束记录数
	 * @param int $flag 广告分类标记 默认为1
	 * @param array $where 条件数组
	 */
	public function getAdvertise($id=null,$start=0,$length=0,$flag=null,$where=array())
	{
		if (!empty($id)) $where['id'] = is_array($id) ? array("in",$id) : $id;
		if ($flag) $where['flag'] = $flag;

		$total = T("advertise")->where($where)->count();
		$obj = T("advertise")->where($where);
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->order("createtime","asc")->select();

		return array('total'=>$total,'data'=>$data);
	}

	/**
	 * 保存广告
	 */
	public function saveAdvertise($title=null,$link=null,$path=null,$flag=1,$status=1,$createtime=null)
	{
		$data = array(
			'title' => $title,
			'link'  => $link,
			'path'  => $path,
			'flag'  => $flag,
			'status'=> $status,
			'createtime' => $createtime
		);
		return T("advertise")->add($data);
	}

	//修改广告信息
	public function AdvertiseEditSave($id=null,$title=null,$link=null,$status=null)
	{
		if (empty($id)) return false;
		$data = array(
			'title' => $title,
			'link'  => $link,
			'status'=> $status
		);
		return T("advertise")->where(array("id"=>$id))->update($data);
	}

	//更新状态
	public function UpdateHomeCenterAdStatus($id=null,$status=1)
	{
		if (empty($id)) return false;
		return T("Advertise")->where(array("id"=>$id))->update(array("status"=>$status));
	}

	//删除广告
	public function DeleteAdvertise($id=null)
	{
		if (empty($id)) return false;
		return T("Advertise")->where(array("id"=>$id))->delete();
	}
}
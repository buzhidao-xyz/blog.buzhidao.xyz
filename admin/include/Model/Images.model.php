<?php
/**
 * 图片管理模型
 * by buzhidao 2013-03-26
 */
class Images extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 保存轮播图片
	 */
	public function saveHomeScrollImage($data=array())
	{
		if (!is_array($data)||empty($data)) return false;
		return T("scrollimage")->add($data);
	}

	/**
	 * 获取首页轮播图片
	 */
	public function getHomeScrollImage($id=null,$where=array())
	{
		if ($id) $where['id'] = $id;
		$where['isdelete'] = 0;

		$total = T("scrollimage")->where($where)->count();
		$data = T("scrollimage")->where($where)->select();

		return array("total"=>$total,"data"=>$data);
	}

	//保存修改首页轮播图片
	public function UpdateHomeScrollImage($id=null,$data=array())
	{
		if (!$id) return false;
		return T("scrollimage")->where(array("id"=>$id))->update($data);
	}

	//删除首页轮播图片
	public function deleteHomeScrollImage($id=null)
	{
		if (!$id) return false;
		return T("scrollimage")->where(array("id"=>$id))->update(array("isdelete"=>1));
	}

	/**
	 * 保存上传的图片 产品图片/图片集等
	 * @param $imagepath string 图片地址
	 * @param $thumbpath string 缩略图地址
	 * @param $imageTitle string 图片标题描述
	 * @param $imageLink string 图片链接
	 * @param $archiveid int 文档ID
	 * @param $imagename string 图片原始名称
	 * @param $savename string 图片保存名称
	 * @param $imagesize int 图片大小
	 */
	public function saveUploadImage($imagepath=null,$thumbpath=null,$imagetitle=null,$imagelink=null,$archiveid=0,$imagename=null,$savename=null,$imagesize=0,$width=0,$height=0,$createtime=0)
	{
		if (!$imagepath) return false;

		$data = array(
			'imagepath'  => $imagepath,
			'thumbpath'  => $thumbpath,
			'imagetitle' => $imagetitle,
			'imagelink'  => $imagelink,
			'archiveid'  => $archiveid,
			'imagename'  => $imagename,
			'savename'  => $savename,
			'width'     => $width,
			'height'    => $height,
			'createtime'=> $createtime
		);
		if ($imagesize) $data['imagesize'] = $imagesize;

		return T("images")->add($data);
	}

	/**
	 * 获取图集列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getImages($id=null,$start=0,$length=0,$state=1,$columnids=array(),$control=null)
	{
		$where = array();
		if ($state !== null) $where['state'] = $state;
		if (is_array($columnids) && !empty($columnids)) $where['columnid'] = array("in", $columnids);
		if ($control) $where['control'] = $control;

		return $this->getArchive($id,$start,$length,$where);
	}
}
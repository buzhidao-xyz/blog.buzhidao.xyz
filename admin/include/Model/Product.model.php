<?php
/**
 * 产品模型
 * by buzhidao 2013-03-27
 */
class Product extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取产品列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getProduct($id=null,$start=0,$length=0,$state=1,$columnids=array(),$control=null)
	{
		$where = array();
		if ($state !== null) $where['state'] = $state;
		if (is_array($columnids) && !empty($columnids)) $where['columnid'] = array("in", $columnids);
		if ($control) $where['control'] = $control;

		return $this->getArchive($id,$start,$length,$where);
	}

	/**
	 * 获取产品信息
	 * @param $archiveid int 文档ID
	 */
	public function getProductDetail($archiveid=null)
	{
		if (!$archiveid) return null;

		$data = T("product")->where(array("archiveid"=>$archiveid))->find();
		return $data;
	}

	/**
	 * 保存产品信息
	 * @param $archiveid int 文档ID
	 */
	public function saveProduct($archiveid=null,$model=null,$brand=null,$color=null,$material=null,$size=null,$price=null,$total=null,$instruction=null)
	{
		if (!$archiveid) return false;

		$data = array(
			'archiveid' => $archiveid,
			'model' => $model,
			'brand' => $brand,
			'color' => $color,
			'material' => $material,
			'size' => $size,
			'price' => $price,
			'total' => $total,
			'instruction' => $instruction,
			'updatetime' => TIMESTAMP
		);
		return T("product")->add($data);
	}

	/**
	 * 保存产品信息
	 * @param $archiveid int 文档ID
	 */
	public function upProduct($archiveid,$model,$brand,$color,$material,$size,$price,$total,$instruction)
	{
		if (!$archiveid) return false;

		$data = array(
			'model' => $model,
			'brand' => $brand,
			'color' => $color,
			'material' => $material,
			'size' => $size,
			'price' => $price,
			'total' => $total,
			'instruction' => $instruction,
			'updatetime' => TIMESTAMP
		);
		return T("product")->where(array("archiveid"=>$archiveid))->update($data);
	}
}
<?php
/**
 * 插件模型
 * by buzhidao 2013-04-11
 */
class Plugin extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 保存招聘信息
	 * @param $data array 信息数组
	 */
	public function CooperateSave($data=array())
	{
		if (!is_array($data) || empty($data)) return false;
		return T("cooperate")->add($data);
	}

	/**
	 * 保存招聘信息
	 * @param $data array 信息数组
	 */
	public function CooperateUpdate($id=null,$data=array())
	{
		if (!$id || !is_array($data) || empty($data)) return false;
		return T("cooperate")->where(array("id"=>$id))->update($data);
	}

	/**
	 * 删除招聘信息
	 * @param int $id 信息id
	 */
	public function CooperateDelete($id=null)
	{
		if (!$id) return false;
		return T("cooperate")->where(array("id"=>$id))->update(array("state"=>0));
	}

	/**
	 * 获取文章列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getCooperateList($id=null,$start=0,$length=0,$columnids=array(),$control=null)
	{
		$where = array();
		if (is_array($columnids) && !empty($columnids)) $where['columnid'] = array("in", $columnids);
		if ($control) $where['control'] = $control;

		if ($id) $where['a.id'] = is_array($id) ? array('in', $id) : $id;

		$where['a.state'] = 1;
		if (isset($where['control'])) {
			$ColumnModelInfo = M("CTModel")->getColumnModelByControl($where['control']);
			$where['b.columnmodel'] = $ColumnModelInfo['id'];
			unset($where['control']);
		}

		$total = T("cooperate")->join(' '.TBF.'column as b on a.columnid=b.id ')->field('*')->where($where)->count();
		$obj = T("cooperate")->join(' '.TBF.'column as b on a.columnid=b.id ')->field('a.*,b.columnname,b.columntype')->where($where)->order("a.id","desc");
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->select();

		return array('total'=>$total,'data'=>$data);
	}

	/**
	 * 获取友情链接列表
	 * @param string/array $id 友情链接ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getFlink($id=null,$start=0,$length=0,$where=array())
	{
		if ($id) $where['id'] = is_array($id) ? array('in', $id) : $id;

		$total = T("flink")->join(' '.TBF.'flink_catalog as b on a.catalogid=b.id ')->field('*')->where($where)->count();
		$obj = T("flink")->join(' '.TBF.'flink_catalog as b on a.catalogid=b.id ')->field('a.*,b.catalogname,b.sort')->where($where)->order("a.id","desc");
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->order("id","asc")->select();

		return array('total'=>$total,'data'=>$data);
	}

	/**
	 * 保存友情链接信息
	 * @param int $catalogid 链接分类ID
	 * @param string $linkname 链接名称
	 * @param string $orderway 链接地址
	 */
	public function FlinkSave($catalogid=null,$linkname=null,$linkurl=null,$createtime=null)
	{
		if (!$catalogid || !$linkname || !$linkurl) return false;
		$data = array(
			'catalogid' => $catalogid,
			'linkname'  => $linkname,
			'linkurl'   => $linkurl,
			'createtime'=> $createtime
		);
		return T("flink")->add($data);
	}

	/**
	 * 修改友情链接信息
	 * @param int $linkid 链接ID
	 * @param int $catalogid 链接分类ID
	 * @param string $linkname 链接名称
	 * @param string $orderway 链接地址
	 */
	public function FlinkUpdate($linkid=null,$catalogid=null,$linkname=null,$linkurl=null)
	{
		if (!$catalogid || !$linkname || !$linkurl) return false;
		$data = array(
			'catalogid' => $catalogid,
			'linkname'  => $linkname,
			'linkurl'   => $linkurl
		);
		return T("flink")->where(array("id"=>$linkid))->update($data);
	}

	//删除链接
	public function FlinkDelete($id=null)
	{
		if (!$id) return false;
		return T("flink")->where(array("id"=>$id))->delete();
	}

	/**
	 * 获取友情链接分类列表
	 * @param string/array $id 链接分类ID
	 * @param array $where 条件数组
	 * @param string $orderway 排序方式
	 */
	public function getFlinkCatalog($id=null,$where=array(),$orderway="asc")
	{
		if ($id) $where['id'] = is_array($id) ? array("in",$id) : $id;
		$data = T("flink_catalog")->where($where)->order("sort",$orderway)->select();

		return $data;
	}

	/**
	 * 保存友情链接分类
	 * @param string $catalogname 链接分类名称
	 * @param int $state 是否显示
	 * @param int $createtime 创建时间
	 */
	public function FlinkCatalogSave($catalogname=null,$state=1,$createtime=null)
	{
		if (empty($catalogname)) return false;

        $sort = 1;
        $catalogInfo = $this->getFlinkCatalog(null,array(),"desc");
        if (is_array($catalogInfo)&&!empty($catalogInfo)) $sort = intval($catalogInfo[0]['sort'])+1;

        $data = array(
            'catalogname' => $catalogname,
            'sort'        => $sort,
            'state'       => $state,
            'createtime'  => $createtime
        );

        return T("flink_catalog")->add($data);
	}

	/**
	 * 保存友情链接分类
	 * @param int $id 链接分类ID
	 * @param string $catalogname 链接分类名称
	 * @param int $state 是否显示
	 * @param int $sort 排序位置
	 */
	public function FlinkCatalogEditSave($id=null,$catalogname=null,$state=1,$sort=null)
	{
		if (empty($id)||empty($catalogname)) return false;

        $data = array(
            'catalogname' => $catalogname,
            'sort'        => $sort,
            'state'       => $state
        );
        return T("flink_catalog")->where(array("id"=>$id))->update($data);
	}

	/**
	 * 删除链接分类
	 * @param int $id 链接分类ID
	 */
	public function FlinkCatalogDelete($id=null)
	{
		if (empty($id)) return false;

		$FlinkList = $this->getFlink(null,0,0,array("catalogid"=>$id));
		if (!empty($FlinkList['data'])) return false;

		return T("flink_catalog")->where(array("id"=>$id))->delete();
	}

	/**
	 * 留言板列表
	 * @param int $id 留言id
	 * @param int $start 分页开始记录数
	 * @param int $length 分页结束记录数
	 * @param array $where 条件数组
	 */
	public function getMessageList($id=null,$start=0,$length=0,$where=array())
	{
		if (!empty($id)) $where['id'] = is_array($id) ? array("in",$id) : $id;

		$total = T("message_board")->where($where)->count();
		$obj = T("message_board")->where($where);
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->order("createtime","desc")->select();

		return array("total"=>$total,"data"=>$data);
	}

	/**
	 * 删除留言信息
	 * @param int/array $id 留言id
	 */
	public function messageDelete($id=null)
	{
		if (empty($id)) return false;
		$where['id'] = is_array($id) ? array("in",$id) : $id;

		return T("message_board")->where($where)->delete();
	}

	/**
	 * 获取导航列表
	 * @param int $id 导航id
	 * @param int $flag 导航位置 1:底部导航 2:快捷导航
	 */
	public function getNavigation($id=null,$flag=null)
	{
		$where = $flag ? array('flag'=>$flag) : array();
		if (!empty($id)) $where['id'] = is_array($id) ? array("in", $id) : $id;

		return T('navigation')->where($where)->select();
	}

	//保存导航
	public function NavigationSave($title=null,$link=null,$sort=0,$flag=1)
	{
		$data = array(
			'title' => $title,
			'link'  => $link,
			'sort'  => $sort,
			'flag'  => $flag,
			'createtime' => TIMESTAMP
		);

		return T("navigation")->add($data);
	}

	//保存修改后的导航信息
	public function NavigationEditSave($id=null,$title=null,$link=null,$sort=0)
	{
		if (empty($id)) return false;
		$data = array(
			'title' => $title,
			'link'  => $link,
			'sort'  => $sort
		);
		return T("navigation")->where(array('id'=>$id))->update($data);
	}

	//删除导航
	public function NavigationDelete($id=null)
	{
		if (empty($id)) return false;

		return T("navigation")->where(array('id'=>$id))->delete();
	}
}
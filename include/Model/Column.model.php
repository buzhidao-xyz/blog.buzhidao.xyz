<?php
/**
 * 栏目数据模型
 * by buzhidao 2013-2-1
 * 栏目的基本查询操作
 */
class Column extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取栏目列表
	 * @param $parentid mixed 父栏目ID
	 * @param $isshow int 栏目属性 默认为1:只获取显示的栏目 0:获取隐藏栏目
	 */
	public function getColumnList($parentid=null,$isshow=1,$where=array())
	{
		$where['isshow'] = $isshow;
		if ($parentid) $where['a.parentid'] = is_array($parentid) ? array("in", $parentid) : $parentid;

		$data = T("column")->join(' '.TBF.'column_model as b on a.columnmodel=b.id ')->field("a.*,b.table,b.usefields,b.control")->where($where)->order("a.id")->select();
		if (is_array($data) && !empty($data)) {
			foreach ($data as $k=>$v) {
				$data[$k]["archivenum"] = T("archive")->where(array("columnid"=>$v["id"],"state"=>1))->count();
			}
		}
		$data = $this->makeColumnList($data);

		return $data;
	}

	/**
	 * 格式化栏目列表 最多支持四级栏目格式化
	 * @param $ColumnList array() 栏目列表
	 */
	public function makeColumnList($ColumnList=array())
	{
		$data = array();

		if (empty($ColumnList)) return false;
		$i = 1;
		foreach ($ColumnList as $column) {
			$column['control'] = $column['control'] ? $column['control'] : 'Index';
			$column['action'] = $column['action'] ? $column['action'] : 'index';

			if (!$column['parentid']) {
				// if ($column['control'] == CONTROL && $column['action'] == ACTION) $column['navon'] = true;
				$column["AutoIndex"] = $i;
				$data[] = $column;
				$i++;
			} else {
				$j = 1;
				foreach ($data as $k=>$d) {
					if ($column['parentid'] == $d['id']) {
						// if ($column['control'] == CONTROL && $column['action'] == ACTION) $data[$k]['navon'] = true;
						$column["AutoIndex"] = $j;
						$data[$k]['SubColumnList'][] = $column;
						$j++;
					} else {
						if (isset($d['SubColumnList'])&&!empty($d['SubColumnList'])) {
							$m = 1;
							foreach ($d['SubColumnList'] as $k1=>$d1) {
								if ($column['parentid'] == $d1['id']) {
									// if ($column['control'] == CONTROL && $column['action'] == ACTION) $data[$k]['navon'] = true;
									$column["AutoIndex"] = $m;
									$data[$k]['SubColumnList'][$k1]['SubColumnList'][] = $column;
									$m++;
								} else {
									if (isset($d1['SubColumnList'])&&!empty($d1['SubColumnList'])) {
										$n = 1;
										foreach ($d1['SubColumnList'] as $k2=>$d2) {
											if ($column['parentid'] == $d2['id']) {
												// if ($column['control'] == CONTROL && $column['action'] == ACTION) $data[$k]['navon'] = true;
												$column["AutoIndex"] = $n;
												$data[$k]['SubColumnList'][$k1]['SubColumnList'][$k2]['SubColumnList'][] = $column;
												$n++;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $data;
	}

	/**
	 * 根据栏目id获取其下级子栏目信息
	 * @param $columnid int 栏目id
	 * @param $num int 获取子栏目数
	 */
	public function getSubColumn($columnid=null,$num=5,$isshow=1)
	{
		if (!$columnid) return array();
		$where = array(
			'parentid' => $columnid,
			'isshow' => $isshow
		);
		$data = T("column")->join(' '.TBF.'column_model as b on a.columnmodel=b.id ')->field("a.*,b.table,b.usefields,b.control")->where($where)->limit(0,$num)->order("a.id")->select();
		return $data;
	}

	/**
	 * 获取某个栏目内容
	 * @param $columnid int 栏目ID
	 */
	public function getColumn($columnid=null,$where=array())
	{
		if (!$columnid) return false;
		$where['a.id'] = $columnid;
		$column = T("column")->join(' '.TBF.'column_model as b on a.columnmodel=b.id ')->field("a.*,b.table,b.usefields,b.control")->where($where)->find();

		return $column;
	}

	/**
	 * 根据栏目id获取其所有子栏目id 递归方法
	 * @param $columnid mixed 栏目id
	 */
	public function getChildrenColumnID($columnid=null,$data=array())
	{
		if ($columnid === null) return array();

		$data = is_array($columnid) ? $columnid : array($columnid);
		$where = is_array($columnid) ? array("parentid"=>array("in",$columnid)) : array("parentid"=>$columnid);

		$columnids = array(); $return = array();
		$columnList = T("column")->where($where)->select();
		if (is_array($columnList) && !empty($columnList)) {
			foreach ($columnList as $d) {
				$columnids[] = $d['id'];
			}
			$return = $this->getChildrenColumnID($columnids,$data);
		}

		return array_merge($data,$return);
	}
}
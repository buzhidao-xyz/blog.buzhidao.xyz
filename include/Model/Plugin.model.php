<?php
/**
 * 插件模型
 * by wbq 2013-03-28
 * edit by buzhidao 2013-03-28
 * 人才招聘 友情链接等插件的增删改查操作
 */
class Plugin extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取友情链接分类信息
	 * @param int $id 分类ID
	 */
	public function getFlinkCatalog($id=null)
	{
		$where = array('state'=>1);
		if ($id) $where['id'] = is_array($id) ? array("in", $id) : $id;

		return T("flink_catalog")->where($where)->order("sort","asc")->select();
	}

	/**
	 * 获取友情链接
	 * @param int $catalogid 分类ID
	 */
	public function getFlink($catalogid=null)
	{
		$where = array('b.state'=>1);
		if ($catalogid) $where['b.catalogid'] = is_array($catalogid) ? array("in", $catalogid) : $catalogid;

		$data = T("flink")->join(' '.TBF.'flink_catalog as b on a.catalogid=b.id ')->field("a.*,b.catalogname,b.sort,b.state")->where($where)->order("b.sort","asc")->select();
		return $data;
	}

	/**
	 * 获取人才招聘信息
	 * @param int $id 信息ID
	 * @param $start int 分页开始记录数
	 * @param $length int 每页记录数
	 * @param $where array() 条件数组
	 */
	public function getCooperate($id=null,$start=0,$length=0,$where=array())
	{
		$where['state'] = 1;
		if ($id) $where['a.id'] = is_array($id) ? array("in", $id) : $id;

		$total = T("cooperate")->where($where)->count();
		$obj = T("cooperate")->where($where);
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->order("publishtime","desc")->select();
		$data = $this->dealData($data);

		return array('total'=>$total,'data'=>$data);
	}

	/**
	 * 格式化文档列表
	 * @param $data array 文档数组列表
	 */
	public function dealData($data=array())
	{
		if (!is_array($data) || empty($data)) return array();

		//加入文档号
		$i = 1;
		foreach ($data as $k=>$d) {
			$data[$k]['AutoIndex'] = $i;
			$i++;
		}

		return $data;
	}

	/**
	 * 保存留言
	 * @param $username string 留言者姓名
	 * @param $email string 留言者邮箱
	 * @param $content string 留言内容
	 * @param $createtime int 留言时间
	 */
	public function saveMessage($username=null,$email=null,$content=null,$createtime=null)
	{
		if (!$content) return false;

		$data = array(
			'username' => $username,
			'email'    => $email,
			'content'  => $content,
			'createtime' => $createtime
		);
		return T("message_board")->add($data);
	}
}
<?php
/**
 * 组模型 数据处理
 * by laucen 2012-9-28
 */
class Group extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//新增导航组
	public function addGroup($data=array())
	{
		if (!is_array($data) || empty($data)) return false;

		return T('group')->add($data);
	}

	/**
	 * 获取组树
	 * @param $start int 开始位置
	 * @param $length int 获取数据长度
	 */
	public function getGroupTree($id=null)
	{
		$where = array();
		if ($id) $where['id'] = is_array($id) ? array("in",$id) : $id;

		return T('group')->where($where)->select();
	}

    /**
     * 根据组id获取组菜单信息
     * @param $groupid int/array 组id或者id数组
     */
    public function getGroup($groupid=null)
    {
        if (!$groupid) return false;

        $where = array(
        	'id' => is_array($groupid) ? array('in', $groupid) : $groupid,
        	'isshow' => 1
        );
        return T('group')->where($where)->order('id')->select();
    }

	/**
	 * 根据组名称获取组信息
	 * @param $id int 组ID
	 * @param $title string 组名称
	 */
	public function getGroupByIDTitle($id=null,$title=null)
	{
		if (!$id || !$title) return false;

		$count = T('group')->where(array("id"=>array("neq",$id),"title"=>$title))->count();

		return $count;
	}

	/**
	 * 更新组信息
	 * @param $id int 组id
	 * @param $data array() 组信息
	 */
	public function GroupEditSave($id=null,$data=array())
	{
		if (!$id || empty($data)) return false;

		return T('group')->where(array('id'=>$id))->update($data);
	}

	//删除组信息
	public function GroupDelete($id=null)
	{
		if (!$id) return false;

        return T('group')->where(array('id'=>$id))->delete();
	}
}
<?php
/**
 * 用户角色模型 数据处理
 * by laucen 2012-9-6
 */
class Role extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//新角色保存
	public function saveRole($data=array())
	{
		if (!is_array($data) || empty($data)) return false;
		return T("role")->add($data);
	}

	//角色列表
	public function getRole($id=null,$start=0,$length=0)
	{
		$where = array();
		if ($id) $where['id'] = is_array($id) && !empty($id) ? array('in',$id) : $id;

		$total = T('role')->where($where)->count();
		$obj = T('role')->where($where);
		if ($length) $obj = $obj->limit($start,$length);
		$data = $obj->select();

		return array('total'=>$total, 'data'=>$data);
	}

	/**
     * 获取用户的角色信息
     * @param $adminid int 用户id
	 */
	public function getAdminRole($adminid=null)
	{
        $return = array();
		$result = T('role_admin')->field('roleid')->where(array('adminid'=>$adminid))->select();
        if (!empty($result) && is_array($result)) {
            foreach ($result as $v) {
                $return[] = $v['roleid'];
            }
        }
		return $return;
	}

	//为管理员分配角色
	public function AdminRoleEditSave($adminid=null,$roleid=array())
	{
		if (!$adminid) return false;

		$return = T('role_admin')->where(array('adminid'=>$adminid))->delete();

		$data = array();
		foreach ($roleid as $v) {
			if ($v) $data[] = array('roleid'=>$v,'adminid'=>$adminid);
		}
		$return = T('role_admin')->add($data,true);

		return $return===false ? false : true;
	}

	//修改角色信息
	public function upRole($id=null,$data=array())
	{
		if (!$id || !is_array($data) || empty($data)) return false;

		return T('role')->where(array('id'=>$id))->update($data);
	}
}
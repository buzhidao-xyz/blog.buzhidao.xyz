<?php
/**
 * 节点模型 数据处理
 * by laucen 2012-9-6
 */
class Node extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * 获取某个节点的子节点
     * @param $nodeid 节点id 默认为0 取全部
     * @param $pid 父节点id 默认为0
     * @param $isshow 获取节点类型(显/隐) 默认true 取全部
     */
    public function getNode($nodeid=0,$pid=0,$isshow=true)
    {
        $where = array();
        if ($nodeid) $where["id"] = is_array($nodeid) ? array("in", $nodeid) : $nodeid;
        if ($pid) $where['pid'] = $pid;
        if ($isshow !== true) $where['isshow'] = $isshow;

        $data = T('node')->where($where)->order('id')->select();
        return array("total"=>count($data),"data"=>$data);
    }

    /**
     * 获取某个节点的信息
     * @param $nodeid 节点id
     */
    public function getNodeInfo($nodeid=null)
    {
        if (!$nodeid) return array();

        $data = T('node')->where(array('id'=>$nodeid))->find();
        if (!$data['groupid']) {
            $data1 = T('node')->where(array('id'=>$data['pid']))->find();
            $data['groupid'] = $data1['groupid'];
        }

        return $data;
    }

    /**
     * 保存节点信息
     */
    public function saveNode($data)
    {
        if (!is_array($data) || empty($data)) return false;
        
        return T('node')->add($data);
    }

	/**
	 * 获取某个组的子节点/节点树
	 * @param $groupid int 组id 默认为Null 返回所有节点
	 */
	public function getNodeTree($groupid=null)
	{
		if ($groupid === 0) return array();

		$where = array(
			'groupid' => $groupid,
			'isshow'  => 1
		);
		$return = T('node')->where($where)->select();

		return $return;
	}

    /**
     * 获取用户的node权限
     * @param $adminid int 管理员id
     */
    public function getUserNode($adminid=null)
    {
        if (!$adminid) return false;

        $where = array(
        	'a.adminid' => $adminid,
			'b.isshow'  => 1
        );
        $res = T('admin_access')->join(' '.TBF.'node as b on a.nodeid=b.id ')->field('a.nodeid,b.id,b.title,b.control,b.action,b.pid,b.groupid')->where($where)->select();

        return $res;
    }

	/**
     * 获取角色信息的node权限
     * @param $roleids array 角色信息
     * @param $f int 是否只返回节点id数组 默认1返回全部
	 */
	public function getRoleNode($roleids,$f=1)
    {
        if (!is_array($roleids) || empty($roleids)) return array();

        $where = array(
        	'a.roleid' => array('in', $roleids)
        );
        if ($f === 1) {
            $where['b.isshow'] = 1;
            $return = T('role_node')->join(' '.TBF.'node as b on a.nodeid=b.id ')->field('a.nodeid,a.access,b.id,b.title,b.control,b.action,b.pid,b.groupid')->where($where)->order("a.nodeid","asc")->select();
        } else if ($f === 0) {
            $return = array(
                'node' => array(),
                'access' => array()
            );
            $res = T('role_node')->field('nodeid,access')->where($where)->select();
            foreach ($res as $k=>$v) {
                $return['node'][] = $v['nodeid'];
                $return['access'][$v['nodeid']] = $v['access'];
            }
        }
        return $return;
    }

    /**
     * 更新节点信息
     * @param $id int 节点id
     * @param $data array() 数据数组
     */
    public function NodeEditSave($id=null,$data=array())
    {
        if (!$id) return false;

        return T('node')->where(array('id'=>$id))->update($data);
    }

    //删除节点
    public function NodeDelete($id=null)
    {
        if (!$id) return false;

        return T('node')->where(array('id'=>$id))->delete();
    }

    //生成节点分配列表树
    public function makeNodeTree()
    {
        $node = $this->getNode();
        $node = $this->dealNode($node['data']);

        $groupids = array();
        foreach ($node as $v) {
            if ($v['pid'] == 0) $groupids[$v['groupid']] = $v['groupid'];
        }

        $group = M('Group')->getGroup($groupids);
        if (is_array($group) && !empty($group))
            return M('Node')->dealGroupNode($group, $node);
        else
            return array();
    }

    /**
     * 整理节点信息
     * @param $roleNode array 角色节点数组
     * @param $userNode array 用户单独节点数组
     */
    private function dealNode($roleNode=array(),$userNode=array())
    {
        $return = array();

        $roleNode = array_merge($roleNode,$userNode);
        foreach ($roleNode as $v) {
            if ($v['pid'] == 0) {
                $m = 0;
                foreach ($return as $k0=>$v0) {
                    if ($v0['id'] == $v['id']) {
                        if ($v0['access'] == 1) {
                            $m = 1;
                            break;
                        } else {
                            unset($return[$k0]);
                        }
                    }
                }
                if (!$m) $return[] = $v;
            }
        }
        foreach ($roleNode as $k=>$v) {
            if ($v['pid'] != 0) {
                foreach ($return as $k1=>$v1) {
                    if ($v['pid'] == $v1['id']) {
                        $m = 0;
                        if (array_key_exists('cnode', $return[$k1])) {
                            foreach ($return[$k1]['cnode'] as $k2=>$v2) {
                                if ($v2['id'] == $v['id']) {
                                    if ($v2['access'] == 1) {
                                        $m = 1;
                                        break;
                                    } else {
                                        unset($return[$k1]['cnode'][$k2]);
                                    }
                                }
                            }
                        }
                        if (!$m) $return[$k1]['cnode'][] = $v;
                        break;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * 整理节点与组信息
     * @param $group array 组信息
     * @param $node array 节点信息
     */
    private function dealGroupNode($group,$node)
    {
        $AdminAccess = array();

        foreach ($node as $k=>$v) {
            foreach ($group as $k1=>$v1) {
                if ($v['groupid'] == $v1['id']) {
                    if (!array_key_exists($v['groupid'], $AdminAccess)) $AdminAccess[$v['groupid']] = $v1;
                    $AdminAccess[$v['groupid']]['cnode'][] = $v;
                    break;
                }
            }
        }

        return $AdminAccess;
    }

    //更新角色节点
    public function upRoleNode($roleid=null,$data=array())
    {
        if (!$roleid || !is_array($data)) return false;

        T('role_node')->where(array('roleid'=>$roleid))->delete();
        if (!empty($data)) T('role_node')->add($data,true);
        return true;
    }
}
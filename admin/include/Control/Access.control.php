<?php
/**
 * 用户访问权限控制
 * by wbq 2012-3-22
 */
class AccessControl extends BaseControl
{
	//控制器名
	protected $_control = 'Access';

    //超级管理员账户id数组
    private $_superAdmin;

	public function __construct()
	{
		parent::__construct();

        $this->_NODE = M('Node');
        $this->_GROUP = M('Group');
        $this->_ROLE = M('Role');

        $this->_superAdmin = $this->_getSuperAdmin();

		$this->_getAdminAccess();
	}

    //获取超级管理员账户
    private function _getSuperAdmin()
    {
        $return = array();

        $superAdmin = M('Admin')->getAdmin(null,0,0,array('super'=>1));
        if (is_array($superAdmin) && !empty($superAdmin) && $superAdmin['total']) {
            foreach ($superAdmin['data'] as $k=>$v) {
                $return[] = $v['id'];
            }
        }

        return $return;
    }

	/**
	 * 获取用户的控制访问权限
	 */
	private function _getAdminAccess()
	{
        if (session('AdminAccess')) return true;
        session('superAdmin', $this->_superAdmin);

        $admin = $this->adminInfo;
        if (in_array($admin['id'], $this->_superAdmin)) {
            $node = $this->_NODE->getNode();
            $node = $this->dealNode($node['data']);
        } else {
            $roleids = $this->_ROLE->getAdminRole($admin['id']);
            if (empty($roleids)) return true;

            $roleNode = $this->_NODE->getRoleNode($roleids);
            if (empty($roleNode)) return true;

            // $userNode = $this->_NODE->getUserNode($admin['id']);
            $node = $this->dealNode($roleNode);
        }

        foreach ($node as $v) {
            if ($v['pid'] == 0) $groupids[$v['groupid']] = $v['groupid'];
        }

        $group = $this->_GROUP->getGroup($groupids);
        if (empty($group)) return true;

        $AdminAccess = $this->dealGroupNode($group, $node);
        if (is_array($AdminAccess)) session('AdminAccess',$AdminAccess);

        return true;
	}

    /**
     * 整理节点信息
     * @param $roleNode array 角色节点数组
     * @param $userNode array 用户单独节点数组
     */
    protected function dealNode($roleNode=array(),$userNode=array())
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
                $v['access'] = isset($v['access']) ? $v['access'] : 1;
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
                        $v['access'] = isset($v['access']) ? $v['access'] : 1;
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
    protected function dealGroupNode($group,$node)
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
}

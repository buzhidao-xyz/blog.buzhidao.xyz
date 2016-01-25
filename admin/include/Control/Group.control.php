<?php
/**
 * 组菜单管理模块
 * by wbq 2012-9-6
 */
class GroupControl extends CommonControl
{
    //控制器
    protected $_Control = 'Group';

    private $_GROUP;

    public function __construct()
    {
        parent::__construct();

        if (!$this->_GROUP) $this->_GROUP = N('Group');
    }

    //获取组节点id
    public function _getID()
    {
        $id = q('id');
        if (!FilterHelper::C_int($id)) $this->ajaxReturn(1,'id错误');

        return $id;
    }

    //获取组节点id
    public function _getGroupID()
    {
        $groupid = q('groupid');
        if (!FilterHelper::C_int($groupid)) $this->ajaxReturn(1,'ID错误');

        return $groupid;
    }

    /**
     * 获取节点名称
     */
    private function _getTitle()
    {
        $title = q('title');
        if (!$title) $this->ajaxReturn(1,'请填写名称');

        return $title ? FilterHelper::F_htmlentities($title) : '';
    }

    //获取是否显示
    private function _getIsShow()
    {
        $isshow = q('isshow');
        if ((int)$isshow !== 0 && (int)$isshow !== 1) $this->ajaxReturn(1,'是否显示组菜单错误');

        return $isshow;
    }

    public function manageGroup()
    {
        $groupList = $this->_GROUP->getGroupTree();

        $this->assign("total", count($groupList));
        $this->assign("groupList", $groupList);
        $this->display("Group/manage.html");
    }

    //保存新组信息
    public function saveGroup()
    {
        if (!$this->isAjax()) return false;

        $title = $this->_getTitle();
        if ($this->_GROUP->getGroupByTitle($title)) $this->ajaxReturn(0, '组名称已存在');

        $data = array(
            'title' => $title,
            'sort'  => 0,
            'isshow'=> 1,
            'createtime' => TIMESTAMP,
            'updatetime' => TIMESTAMP
        );
        $return = $this->_GROUP->addGroup($data);

        $this->ajaxReturn(0, '添加成功', $return);
    }

    //编辑组菜单信息
    public function GroupEdit()
    {
        if (!$this->isAjax()) return false;
        $id = $this->_getID();
        $this->assign("groupid",$id);

        $GroupInfo = $this->_GROUP->getGroupTree($id);
        $this->assign("GroupInfo", $GroupInfo[0]);

        $this->display("Group/GroupEdit.html");
    }

    //更新组信息
    public function GroupEditSave()
    {
        if (!$this->isAjax()) return false;

        $id = $this->_getGroupID();
        $title = $this->_getTitle();
        $isshow = $this->_getIsShow();

        if ($this->_GROUP->getGroupByIDTitle($id,$title)) {
            $this->ajaxReturn(1,"组菜单名称已存在!");
        }

        $data = array(
            'title' => $title,
            'isshow'=> $isshow,
            'updatetime' => TIMESTAMP
        );
        $return = $this->_GROUP->GroupEditSave($id,$data);
        if ($return) {
            $this->ajaxReturn(0, '更新成功!');
        } else {
            $this->ajaxReturn(1, '更新失败!');
        }
    }

    //删除组信息
    public function GroupDelete()
    {
        $id = $this->_getID();
        $return = $this->_NODE->GroupDelete($id);
        if ($return) {
            $this->ajaxReturn(0,'组删除成功！',$return);
        } else {
            $this->ajaxReturn(1,'组删除失败！',$return);
        }
    }
}
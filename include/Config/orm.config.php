<?php
/**
 * orm字段映射表 相同的数据表字段最好用相同的映射key值
 */
$orm = array (
    'admin'             => array(
        'id'            => 'id',
        'username'      => 'username',
        'password'      => 'password',
        'ukey'          => 'ukey',
        'createtime'    => 'createtime',
        'status'        => 'status',
        'ustate'        => 'ustate',
        'lastlogintime' => 'lastlogintime',
        'lastloginip'   => 'lastloginip',
        'logincount'    => 'logincount'
    ),
    'admin_access'       => array(
        'userid'        => 'userid',
        'nodeid'        => 'nodeid',
        'groupid'       => 'groupid',
    ),
    'group'             => array(
	    'id'            => 'id',
        'title'         => 'title',
        'createtime'    => 'createtime',
        'updatetime'    => 'updatetime',
	    'sort'          => 'sort',
	    'isshow'        => 'isshow'
    ),
    'node'              => array(
        'id'            => 'id',
        'title'         => 'title',
        'remark'        => 'remark',
        'control'       => 'control',
        'action'        => 'action',
        'sort'          => 'sort',
        'pid'           => 'pid',
        'level'         => 'level',
        'groupid'       => 'groupid',
        'createtime'    => 'createtime',
        'updatetime'    => 'updatetime',
        'isshow'        => 'isshow'
    ),
    'role'              => array(
	    'id'            => 'id',
        'name'          => 'name',
	    'pid'           => 'pid',
        'status'        => 'status',
        'remark'        => 'remark',
        'createtime'    => 'createtime',
        'updatetime'    => 'updatetime',
        'pname'         => 'pname'
    ),
    'role_node'       => array(
    	'roleid'        => 'roleid',
    	'nodeid'        => 'nodeid'
    ),
    'role_admin'         => array(
    	'roleid'        => 'roleid',
    	'adminid'       => 'adminid'
    ),
    'system'            => array(
        'id'            => 'id',
        'host'          => 'host',
        'name'          => 'name',
        'keywords'      => 'keywords',
        'style'         => 'style',
        'admin_style'   => 'admin_style',
        'admin_dir'     => 'admin_dir'
    ),
    'user'              => array(
        'id'            => 'id',
        'username'      => 'username',
        'password'      => 'password',
	    'nickname'      => 'nickname',
        'email'         => 'email',
        'remark'        => 'remark',
        'ukey'          => 'ukey',
        'createtime'    => 'createtime',
        'status'        => 'status',
        'ustate'        => 'ustate',
        'urank'         => 'urank',
        'lastlogintime' => 'lastlogintime',
        'lastloginip'   => 'lastloginip',
        'logincount'    => 'logincount'
    ),
);

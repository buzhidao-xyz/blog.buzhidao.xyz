<?php
/**
 * 调试信息控制器
 * by wbq 2012-3-21
 */
class DebugControl extends CommonControl
{
	//控制器名
	protected $_Control = 'Debug';

	//定义查询字符串
    static private $_query;

	public function __construct($query)
	{
		parent::__construct();
		self::$_query = $query;
	}

	/**
	 * 主入口文件控制器/默认
	 */
	static public function index()
	{
		echo 'debug index page';
	}

	/**
	 * 将Head导航栏各分类写入数据库(group)
	 */
	static private function ag()
	{
		$groups = array(
			array('title'=>'系统管理', 'createtime'=>TIMESTAMP, 'updatetime'=>TIMESTAMP, 'sort'=>1, 'isshow'=>1),
			array('title'=>'用户管理', 'createtime'=>TIMESTAMP, 'updatetime'=>TIMESTAMP, 'sort'=>2, 'isshow'=>1),
			array('title'=>'文章管理', 'createtime'=>TIMESTAMP, 'updatetime'=>TIMESTAMP, 'sort'=>3, 'isshow'=>1),
			array('title'=>'网站设置', 'createtime'=>TIMESTAMP, 'updatetime'=>TIMESTAMP, 'sort'=>4, 'isshow'=>1),
			array('title'=>'系统工具', 'createtime'=>TIMESTAMP, 'updatetime'=>TIMESTAMP, 'sort'=>5, 'isshow'=>1),
			array('title'=>'全局管理', 'createtime'=>TIMESTAMP, 'updatetime'=>TIMESTAMP, 'sort'=>6, 'isshow'=>1),
		);

		T('group')->add($groups,1);
	}
}

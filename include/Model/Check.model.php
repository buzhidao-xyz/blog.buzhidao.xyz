<?php
/**
 * 用户角色模型 数据处理
 * by laucen 2012-9-6
 */
class Check extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	//变量内容检查与控制
	static public function __check($var=null,$value)
	{
		if (!$var) return false;

		$var .= "Check";
		return self::$var($value);
	}

	//管理员账户名
	static private function adminNameCheck($value=null)
	{
		$reg = "/^[0-9a-zA-Z][0-9a-zA-Z_.-@]{2,19}$/";
		if (!preg_match($reg, $value)) return false;
		return true;
	}

	//管理员密码
	static private function adminPwdCheck($value=null)
	{
		$reg = "/^[0-9a-zA-Z][0-9a-zA-Z_.-@#!]{2,19}$/";
		if (!preg_match($reg, $value)) return false;
		return true;
	}
}
<?php
/**
 * 会员信息控制器
 * by wbq 2011-11-09
 */
class UserControl extends CommonControl
{
    //定义类名
    protected $_Control = 'User';
    
    //定义缓存有效时间(秒)
    static public $_life_time = 10;
    
    public function __construct()
    {
		parent::__construct();
    }
    
    //主入口
    public function index(){}

    //会员列表
    public function userList()
    {

    }

    //会员级别
    public function userRank()
    {
        
    }
}

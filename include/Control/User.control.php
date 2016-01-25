<?php
/**
 * 用户信息控制器
 * by wbq 2011-11-09
 */
class UserControl extends CommonControl
{
    //定义缓存有效时间(秒)
    public $_life_time = 10;
    
    public function __construct($query)
    {
        parent::__construct();
    }
    
    public function index()
    {

    }
}

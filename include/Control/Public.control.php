<?php
/**
 * 公共模块控制器 关于我们 联系我们等
 * by wbq 2011-12-01
 * 处理逻辑数据 执行具体的功能操作
 */
class PublicControl extends CommonControl
{
	//控制器名
    protected $_control = 'public';

	public function __construct($query=null)
	{
		parent::__construct();
		$this->_query = $query;
	}

	public function index()
	{
		$this->display($this->_Column['template_index']);
	}

	//关于步知道
	public function aboutme()
	{
		$this->display("Public/aboutme.html");
	}
	//短名称
	public function am()
	{
		$this->aboutme();
	}

	//时间轴
	public function timeline()
	{
		$this->display("Public/timeline.html");
	}
    
    //iconfont字体
    public function iconfont()
    {
        header("Content-type:text/css");
        
        import('Lib.ORG.Font');
        $font = new Font();
        $iconfont = $font->iconfont();
        
        echo $iconfont;
    }
}
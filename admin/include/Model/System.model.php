<?php
/**
 * 系统控制数据模型
 * by laucen 2012-5-17
 */
class System extends Base
{
    private $_phpinfo = array(
        'php_version'      => PHP_VERSION,
        'safe_mode'        => '',
        'register_globals' => '',
        'magic_quotes_gpc' => '',
        'upload_max_filesize' => '',
        'allow_url_fopen'  => '',
    );

	public function __construct()
	{
		parent::__construct();
	}

	/**
     * 获取系统信息
     */
    public function getSys()
    {
        $sys = Memcacheg::get('sys');
        
        if (!$sys) {
            $sys = $this->_dealSys(T('system')->select());
            if (is_array($sys) && !empty($sys)) $sys['admin_host'] = $sys['host'].'/'.$sys['admin_path'].'/';
            Memcacheg::set('sys',$sys);
        }
        
        return $sys;
    }

    /**
     * 处理sys
     */
    private function _dealSys($sys=null)
    {
        if (!is_array($sys) || empty($sys)) return false;

        $return = array();
        foreach ($sys as $k => $v) {
            $return[$v['cfgname']] = $v['cfgvalue'];
        }

        return $return;
    }

    //获取服务器系统信息
    public function getPHPInfo($config=null)
    {
        if ($config) return ini_get($config);

        $return = array(
            'php_version' => PHP_VERSION
        );
        foreach ($this->_phpinfo as $k=>$v) {
            $return[$k] = $v ? $v : ini_get($k);
        }

        return $return;
    }

    /********************************系统参数********************************/

    /**
     * 获取系统参数
     * @param string $cfgname 参数名称
     */
    public function getSystemInfo($cfgname=null)
    {
        $where = array();
        if (!empty($cfgname)) $where['cfgname'] = $cfgname;
        return T("system")->where($where)->select();
    }

    //查询系统参数名称是否已存在
    public function cfgExists($cfgname=null)
    {
        if (empty($cfgname)) return false;
        return T("system")->where(array("cfgname"=>$cfgname))->count();
    }

    //添加新系统变量参数
    public function saveSystemcfg($cfgname=null,$cfgvalue=null,$cfginfo=null,$cfgtype=null,$cfggroupid=null)
    {
        if (empty($cfgname)||empty($cfginfo)||empty($cfgtype)||empty($cfggroupid)) return false;
        $data = array(
            'cfgname' => $cfgname,
            'cfgvalue'=> $cfgvalue,
            'cfginfo' => $cfginfo,
            'cfgtype' => $cfgtype,
            'cfggroupid' => $cfggroupid,
            'cfgtime' => TIMESTAMP
        );
        return T("system")->add($data);
    }

    //保存系统参数信息
    public function saveSystemInfo($data=array())
    {
        if (!is_array($data)||empty($data)) return false;
        foreach ($data as $k=>$v) {
            T('system')->where(array("cfgname"=>$k))->update(array("cfgvalue"=>$v));
        }
        return true;
    }
}
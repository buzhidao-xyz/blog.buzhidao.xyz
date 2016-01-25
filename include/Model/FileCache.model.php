<?php
/**
 * 文件缓存类，为数据建立缓存文件
 * 2011-11-4 wbq
 */
class FileCache
{
    /**
     * 定义默认开启缓存
     */
    static protected $_enable = true;
    static protected $_enable_data = true;
    static protected $_enable_compile = true;
    
    /**
     * 定义CACHE根目录
     */
    static private $_cache_dir = null;
    
    /**
     * 默认规则
     * life_time 缓存文件有效时间，以秒(S)作为单位，0表示永久不过期
     * serialize 采用序列化缓存
     * encoding_filename 编码文件名，便于搜索
     * cache_dir 缓存路径
     * cache_dir_umask 缓存文件读写权限
     */
    static protected $_default_policy = array (
        'life_time'         => 0,
        'serialize'         => true,
        'encoding_filename' => true,
        'cache_dir_depth'   => 2,
        'cache_dir'         => 'data',
        'cache_dir_umask'   => 0777,
        'type'              => 'crc32'
    );
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        
    }

    /*
     * 初始化
     */
    static private function _init()
    {
        self::$_cache_dir = C('CACHE_DIR');
    }
    
    /**
     * 写入缓存
     * @param string $id 缓存文件标记id
     * @param mixed $data 被缓存的内容（数据）
     * @param mixed $policy 缓存规则
     */
    static public function set($id, $data, array $policy)
    {
        if (!self::$_enable) return false;

        self::_init();
        
        $policy = self::_policy($policy);
        if (empty($data)) return false;
        
        $path = self::_path($id,$policy);
        
        $expireTime = $policy["life_time"] ? TIMESTAMP + $policy["life_time"]:0;
        is_array($data) ? $data['life_time'] = $expireTime : $data=array("purfull_string"=>$data,"life_time"=>$expireTime);
        //生成crc32校验码
        //$data['hash'] = self::_hash(json_encode($data), $policy['type']);
        
        $data = '<?php return '.var_export($data, true).';';
        $content = $data;
        unset($data);
        
        file_put_contents($path, $content);
    }
    
    /**
     * 读取缓存 失败或者缓存失效时返回false
     * @param string $id 缓存文件标记id
     * @param mixed $policy 缓存规则
     * @return mixed 
     */
    static public function get($id, array $policy = array())
    {
        if (!self::$_enable) return false;

        self::_init();
        
        $policy = self::_policy($policy);
        $path = self::_path($id, $policy);
        clearstatcache();
        
        if (!file_exists($path)) return false;
        $data = include($path);
        if ($data['life_time'] === 0 || $data['life_time'] > TIMESTAMP) {
            unset($data['life_time']);
            if (isset($data['purfull_string'])) {
                $data = $data['purfull_string'];
            }
            return $data;
        } else {
            return false;
        }
    }
    
    /**
     * 获取编译文件 生成编译文件 验证文件的有效时间
     * @param string $id 缓存文件标记id
     * @param mixed $view 写入文件的内容(视图view)
     * @param mixed $policy 缓存规则
     * @return string $path
     */
    static public function compile($id, $view = '', array $policy = array())
    {
        if (!self::$_enable_compile) return false;

        self::_init();
   
        $policy = self::_policy($policy);
        $path = self::_path($id,$policy);
        clearstatcache();
        
        $fmtime = file_exists($path)&&filemtime($path) ? filemtime($path)+$policy['life_time'] : $policy['life_time'];
        if ($fmtime < TIMESTAMP) {
            // if (empty($view)) {
            //     $path = false;
            // } else {
                file_put_contents($path, $view);
            // }
        }
        
        return $path;
    }
    
    /**
     * 删除缓存
     * @param string $id 缓存文件标记id
     * @param mixed $policy 缓存规则
     * @return boolean 
     */
    static public function remove($id, array $policy)
    {
        if (!self::$_enable) return false;

        self::_init();
   
        $path = self::_path($id, self::_policy($policy));
        if (is_file($path)) unlink($path);
    }
    
    /**
     * 生成缓存文件名，并创建缓存目录
     * @param string $id 缓存文件标记id
     * @param mixed $policy 缓存规则
     * @return string $filepath
     */
    static protected function _path($id, array $policy)
    {
        if ($policy['encoding_filename']) {
            $filename = substr(md5($id),0,32) . '.php';
        } else {
            $filename = $id . '.php';
        }
        
        if (empty($policy['cache_dir'])) {
            die('请先设置缓存目录!');
        }
        
        if (!is_dir(self::$_cache_dir)) {
            mkdir(self::$_cache_dir, $policy['cache_dir_umask']);
        }
        $root_dir = self::$_cache_dir.'/'.rtrim($policy['cache_dir'], '\\/');
        if (!is_dir($root_dir)) {
            mkdir($root_dir, $policy['cache_dir_umask']);
        }
        
        if ($policy['cache_dir_depth'] > 0) {
            $hash = md5($filename);
            for ($i= 1; $i<= $policy['cache_dir_depth']; $i++) {
                $root_dir .= '/'.substr($hash, 0, $i);
                if (is_dir($root_dir)) { continue; }
                mkdir($root_dir, $policy['cache_dir_umask']);
            }
        }
        
        return $root_dir.'/'.$filename;
    }
    
    /**
     * 生成有效的应用规则
     * @param mixed $policy 缓存规则
     */
    static protected function _policy(array $policy)
    {
        return empty($policy)?self::$_default_policy:array_merge(self::$_default_policy, $policy);
    }
    
    /**
     * 获得数据的效验码 crc32 速度较快，而且安全。md5 速度最慢，但最可靠。strlen 速度最快，可靠性略差
     * @param string $data
	 * @param string $type
	 * @return string
     */
    static protected function _hash($data, $type)
    {
        switch ($type) {
            case 'md5':
                return md5($data);
            case 'crc32':
                return sprintf('% 32u', crc32($data));
            default:
                return sprintf('% 32u', strlen($data));
        }
    }
}

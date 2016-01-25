<?php
/**
 * filename: template.class.php
 * author: wbq
 * create date: April 7th, 2009
 * last modified: April 9th, 2009
 * descript: a class to deal with template - my php template engineer
 */
//引入Smarty类库
include_once(C('VENDOR').'/Smarty/SmartyBC.class.php');

class Template extends SmartyBC
{
    /**
     * 编译文件有效时间 默认10S
     */
    static private $_life_time = 10;
    
    /**
     * 后台样式路径
     */
    static public $_style_default = null;

    //前台样式路径
    static public $_style_public = null;
    
    /**
     * 模板文件路径
     */
	static private $_template;
    
    /**
     * 编译文件路径
     */
    static private $_path;

    static private $_model_array = array (
        "/(\{)require\s*(.+\..+)(\})/i", 
        "/(\{)if\s*(!*)([a-zA-Z0-9_]+)(\})/i", 
        "/(\{)if\s*([a-zA-Z0-9_]+)\s*(<|>|==)\s*([0-9]+)(\})/i", 
        "/(\{)if\s*([a-zA-Z0-9_]+)\s*(<|>|==)\s*([a-zA-Z0-9_]+)(\})/i", 
        "/(\{)if\s*([a-zA-Z0-9_]+)\s*(<|>|==)\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(\})/i", 
        "/(\{)if\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(<|>|==)\s*([0-9]+)(\})/i", 
		"/(\{)if\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(<|>|==)\s*([a-zA-Z0-9_]+)(\})/i", 
        "/(\{)if\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(<|>|==)\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(\})/i", 
		"/(\{)else(\})/i", 
		"/(\{)end(\})/i", 
		"/(\{)foreach\s*(var=|name=)([a-zA-Z0-9_]+)\s*(key=value)\s*(\})/i", 
        "/(\{)foreach\s*(var=|name=)(value)\.([a-zA-Z0-9_]+)\s*(key1=value1)\s*(\})/i", 
		"/(\{)foreach\s*(var=|name=)([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(key=value)\s*(\})/i", 
		"/(\{)foreach\s*(var=|name=)([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(key=value)\s*(\})/i", 
		"/(\{)(value|value1|item)(\})/i", 
		"/(\{)(value|value1|item)\.([a-zA-Z0-9_]+)(\})/i", 
	    "/(\{)(global)\.([a-zA-Z0-9_]+)(\})/i", 
        "/(\{)([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(\})/i", 
		"/(\{)(checked|selected)(\})/i", 
		"/(\{)(plugin)\.([a-zA-Z0-9_]+)(\})/i", 
	);
    
	static private $_replace_array = array (
        '<?php require_once("\\2"); ?>', 
        '<?php if (\\2$data[\'\\3\']) { ?>', 
        '<?php if ($data[\'\\2\'] \\3 \\4) { ?>', 
        '<?php if ($data[\'\\2\'] \\3 $data[\'\\4\']) { ?>', 
        '<?php if ($data[\'\\2\'] \\3 $data[\'\\4\'][\'\\5\']) { ?>', 
        '<?php if ($data[\'\\2\'][\'\\3\'] \\4 \\5) { ?>', 
        '<?php if ($data[\'\\2\'][\'\\3\'] \\4 $data[\'\\5\']) { ?>', 
        '<?php if ($data[\'\\2\'][\'\\3\'] \\4 $data[\'\\5\'][\'\\6\']) { ?>', 
        '<?php } else { ?>', 
        '<?php } ?>', 
        '<?php foreach ($data[\'\\3\'] as $key=>$value) { ?>', 
        '<?php foreach ($value[\'\\4\'] as $key1=>$value1) { ?>', 
        '<?php foreach ($data[\'\\3\'][\'\\4\'] as $key=>$value) { ?>', 
        '<?php foreach ($data[\'\\3\'][\'\\4\'][\'\\5\'] as $key=>$value) { ?>', 
        '<?php echo $\\2; ?>', 
        '<?php echo $\\2[\'\\3\'] ?>', 
        '<?php echo $data[\'\\3\']; ?>',
        '<?php echo $data[\'\\2\'][\'\\3\']; ?>', 
        '<?php echo "\\2"; ?>', 
        '<?php echo $data[plugin][\'\\3\'] ?>', 
    );

    //常量名数组
    private $_constant_key = array(
        '__APP__','__APPM__',
    );
    //常量值数组
    private $_constant_value = array(
        __APP__,__APPM__,
    );

    private $_template_type = null;
    private $_smarty = null;

    public function __construct()
    {
        parent::__construct();

        $this->_template_type = C('TEMPLATE_TYPE');
        if ($this->_template_type == 'Smarty') {
            self::$_life_time = 0;
            $this->setForce_compile(true);
            $this->setTemplateDir(C('TEMPLATE_OPTIONS.template'));
            $this->setCompileDir(C('TEMPLATE_OPTIONS.template_compile'));
            $this->addPluginsDir(C('TEMPLATE_OPTIONS.plugin_dir'));

            //防止调用touch,saemc会自动更新时间，不需要touch
            $this->compile_locking = false;

            $this->CMSTagParse();
        }
    }

    static private function _init()
    {
        self::$_style_default = C('STYLE_DEFAULT');
        self::$_style_public = C('STYLE_PUBLIC');
    }

	static protected function setTemplate(&$template)
	{
        self::$_template = self::$_style_default.'/'.$template;
        self::$_template = file_exists(self::$_template) ? self::$_template : self::$_style_public.'/'.$template;
		self::loadTemplate();
	}

    static protected function loadTemplate()
	{
        try {
            if (!file_exists(self::$_template)) {
                throw new MyException("The file ".self::$_template." isn't exists.", 1);
				echo self::$_template." 模板文件不存在";
                exit;
            }
        } catch (MyException $e) {
            echo $e;
        }
    }

    static protected function getTemplate()
	{
        try {
			$restr = '';
            if (!(@$fp = fopen(self::$_template, "r"))) {
                throw new MyException("Can't open ".self::$_template.".",1);
                exit;
            }

            while (!feof($fp)) {
                $restr .= fgets($fp);
            }

            return $restr;
        } catch (MyException $e) {
            echo $e;
        }
    }

	static public function getFile($file)
	{
        try {
			$restr = '';
			if (!file_exists($file)) {
				echo $file." 文件不存在";
				exit;
			}

            if (!(@$fp = fopen($file, "r"))) {
                throw new MyException("Can't open ".$file.".",1);
                exit;
            }

            while (!feof($fp)) {
                $restr .= fgets($fp);
            }

            return $restr;
        } catch (MyException $e) {
            echo $e;
        }
    }

	static protected function displayRFile(&$objStr)
	{
		global $cbf;
		if (!$cbf) {
			function cbfunc($matches) {
				$file = Template::$_style_default."/".$matches[2];
                $file = file_exists($file) ? $file : Template::$_style_public.'/'.$matches[2];
				return Template::getFile($file);
			}
			$cbf = 1;
		}

		return preg_replace_callback("/(\{)require\s*(.+\..+)\s*(\})/i", "cbfunc", $objStr);
	}

	//模板语法解析
	static protected function TPLParse(&$objStr)
	{
		$objStr = self::displayRFile($objStr);
		return preg_replace(self::$_model_array, self::$_replace_array, $objStr);
	}

	static protected function getFrontIndexFrame()
    {
        return self::getFile(self::$_style_public."/index.frame.html");
    }
    
    static protected function _compile($view)
	{
	     self::$_path = FileCache::compile(self::$_template, $view, array('life_time'=>self::$_life_time, 'cache_dir'=>'compile'));
	}

    //fetch
    public function fetchd($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        global $data;

        $view = isset($view) ? $view : '';
        self::_init();
        self::setTemplate($template);

        if ($this->_template_type == 'Laugh') {
            self::_compile($view);
            if (!self::$_path) {
                $_template = self::getTemplate();
                
                $view = self::TPLParse($_template);
                $index_frame = self::getFrontIndexFrame();
                $view = str_replace('{index}', $view, $index_frame);
            }
        } else if ($this->_template_type == 'Smarty') {
            $view = $this->fetch($template);
        }

        $view = str_replace($this->_constant_key, $this->_constant_value, $view);

        return $view;
    }

	public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
	{
        $view = $this->fetchd($template,$cache_id,$compile_id,$parent);
        self::_compile($view);

        include(self::$_path);
	}

    //cmstag触发解析标签
    public function CMSTagParse()
    {
        //注册CMSTag标签
        $cmstag = array(
            array('type'=>'block','tag'=>'cmstag_archive','function'=>'CMSTagArchive'),
            array('type'=>'block','tag'=>'cmstag_topic','function'=>'CMSTagTopic'),
            array('type'=>'block','tag'=>'cmstag_navigation','function'=>'CMSTagNavigation'),
            array('type'=>'block','tag'=>'cmstag_tag','function'=>'CMSTagTag'),
            array('type'=>'function','tag'=>'cmstag_advertise','function'=>'CMSTagAdvertise'),
        );
        foreach ($cmstag as $d) {
            if ($d['type'] == 'function')
                $this->register_function($d['tag'],$d['function']);
            else if ($d['type'] == 'block')
                $this->register_block($d['tag'],$d['function']);
        }
    }
}

<?php
/**
 * 上传控制类
 * by wbq 2011-12-01
 * 处理逻辑数据 执行具体的功能操作
 */
class UEditorControl extends CommonControl
{
	//ueditor配置
	private $CONFIG = array();
	
	public function __construct()
	{
		parent::__construct();

		$this->CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(CONFIG_DIR."/ueditor.upload.json")), true);
	}

	public function index(){}

	/**
	 * 获取配置文件json
	 */
	public function config()
	{
		echo json_encode($this->CONFIG);exit;
	}

	//返回上传文件存放路径
    private function makeSavePath($folderpath=null)
    {
        return C("UPLOAD_PATH")."/".$folderpath."/".date("Ym/d/");
    }

    //上传返回
    protected function uploadReturn($state="SUCCESS",$url=null,$title='',$original=null)
    {
        $return = array(
            'state'    => $state,
            'url'      => $url,
            'title'    => $title,
            'original' => $original,
            'type'     => '',
            'size'     => ''
        );

        echo json_encode($return);
        exit;
    }

    //初始化上传类
    protected function initUploadHelperClass()
    {
    	return IS_SAE ? new SAEUploadHelper() : new UploadHelper();
    }

	//上传图片处理
	public function upImage()
	{
		$upload = $this->initUploadHelperClass();
		$upload->inputName = "upfile";
		$upload->maxSize  = 2097152; //2M
		$upload->savePath =  $this->makeSavePath("Image");
		if(!$upload->upload()) {
			$this->uploadReturn('ERROR','',$upload->getErrorMsg());
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			$this->uploadReturn('SUCCESS',__APP__.$url,$info[0]['savename'],$info[0]['name']);
		}
	}

	//涂鸦图片处理
	public function scrawlImage()
	{
		$upload = $this->initUploadHelperClass();
		$upload->savePath =  $this->makeSavePath("Scrawl");

        if(!$upload->uploadContent("upfile",null,"base64")) {
			$this->uploadReturn('ERROR','',$upload->getErrorMsg());
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info['savepath'].$info['savename']);
			$this->uploadReturn('SUCCESS',__APP__.$url,$info['savename']);
		}
	}

	/**
	 * 抓取远程图片
	 */
	public function catchImage()
	{
		
	}

	//上传附件处理
	public function upFile()
	{
		$upload = $this->initUploadHelperClass();
		$upload->inputName = "upfile";
		$upload->maxSize  = 2097152; //2M
		$upload->savePath =  $this->makeSavePath("Attachment");
		if(!$upload->upload()) {
			$this->uploadReturn('ERROR','',$upload->getErrorMsg());
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			$this->uploadReturn('SUCCESS',__APP__.$url,".".$info[0]['extension'],$info[0]['savename']);
		}
	}

	//获取视频
	public function getMovie()
	{
		error_reporting(E_ERROR|E_WARNING);
	    $key = htmlspecialchars($_POST["searchKey"]);
	    $type = htmlspecialchars($_POST["videoType"]);
	    $html = file_get_contents('http://api.tudou.com/v3/gw?method=item.search&appKey=myKey&format=json&kw='.$key.'&pageNo=1&pageSize=20&channelId='.$type.'&inDays=7&media=v&sort=s');
	    echo $html;
	}

	//图片管理器
	public function imageManager()
	{
	    //最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
	    $path = C("UPLOAD_PATH");
	    //每页显示多少条数据
	    $listSize = $this->CONFIG['imageManagerListSize'];
	    //允许列出的图片类型
	    $allowFiles = $this->CONFIG['imageManagerAllowFiles'];
	    $allowFiles = str_replace('.','|',implode('',$allowFiles));

		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;

		/* 获取文件列表 */
		// $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
		$files = $this->getfiles($path, $allowFiles);
		if (!count($files)) {
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}

		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}

		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));

		echo $result;
		exit;
	}

	/**
	 * 文件管理器
	 */
	public function fileManager()
	{
	    //最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
	    $path = C("UPLOAD_PATH").'/Attachment';
	    //每页显示多少条数据
	    $listSize = $this->CONFIG['fileManagerListSize'];
	    //允许列出的图片类型
	    $allowFiles = $this->CONFIG['fileManagerAllowFiles'];
	    $allowFiles = str_replace('.','|',implode('',$allowFiles));

		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;

		/* 获取文件列表 */
		// $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
		$files = $this->getfiles($path, $allowFiles);
		if (!count($files)) {
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}

		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}

		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));

		echo $result;
		exit;
	}

	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function getfiles($path, $allowFiles, &$files = array())
	{
	    if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                $this->getfiles($path2, $allowFiles, $files);
	            } else {
	                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
	                    $files[] = array(
	                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
	                        'mtime'=> filemtime($path2)
	                    );
	                }
	            }
	        }
	    }
	    return $files;
	}
}
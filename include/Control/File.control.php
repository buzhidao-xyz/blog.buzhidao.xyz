<?php
/**
 * 上传控制类
 * by wbq 2011-12-01
 * 处理逻辑数据 执行具体的功能操作
 */
class FileControl extends CommonControl
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index(){}

    //图片上传返回
    protected function imageUploadReturn($url=null,$title='',$state="SUCCESS")
    {
        $return = array(
            'url'      => $url,
            'title'    => $title,
            'state'    => $state
        );

        echo json_encode($return); exit;
    }

    //文件上传返回
    protected function fileUploadReturn($url=null,$fileType='',$original='',$state="SUCCESS")
    {
        $return = array(
            'url'      => $url,
            'fileType' => $fileType,
            'original' => $original,
            'state'    => $state
        );

        echo json_encode($return); exit;
    }

	//返回上传文件存放路径
    private function makeSavePath($folderpath=null)
    {
        return C("UPLOAD_PATH")."/".$folderpath."/".date("Ym/d/");
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
			$this->imageUploadReturn("","",$upload->getErrorMsg());
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			$this->imageUploadReturn(__APP__.$url,$info[0]['name']);
		}
	}

	//涂鸦图片处理
	public function scrawlImage()
	{
		$upload = $this->initUploadHelperClass();
		$upload->savePath =  $this->makeSavePath("Scrawl");

		$action = isset($_GET["action"]) ? htmlspecialchars($_GET["action"]) : "";
		if ($action == "tmpImg") {
			// 背景上传
	        if(!$upload->upload()) {
				$url = "";
				$state = $upload->getErrorMsg();
			} else {
				$info = $upload->getUploadFileInfo();
				$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
				$state = "SUCCESS";
			}
	        //返回数据，调用父页面的ue_callback回调
	        echo "<script>parent.ue_callback('".__APP__.$url."','".$state."')</script>";
	    } else {
	        if(!$upload->uploadContent("content","base64")) {
				$this->imageUploadReturn("","",$upload->getErrorMsg());
			} else {
				$info = $upload->getUploadFileInfo();
				$url = str_replace(ROOT_DIR, "", $info['savepath'].$info['savename']);
				$this->imageUploadReturn(__APP__.$url,$info['name']);
			}
	    }
	}

	//上传附件处理
	public function upFile()
	{
		$upload = $this->initUploadHelperClass();
		$upload->savePath =  $this->makeSavePath("Attachment");

		if(!$upload->upload()) {
			$this->fileUploadReturn("","","",$upload->getErrorMsg());
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			$this->fileUploadReturn(__APP__.$url,".".$info[0]['extension'],$info[0]['name']);
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
	    $action = htmlspecialchars($_POST["action"]);
	    if ($action == "get") {
	        $files = $this->getfiles($path);
	        if (!$files) return;
	        rsort($files,SORT_STRING);
	        $str = "";
	        foreach ( $files as $file ) {
	            $str .= __APP__.$file."ue_separate_ue";
	        }
	        echo $str;
	    }
	}

	/**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    private function getfiles( $path , &$files = array() )
    {
        if ( !is_dir( $path ) ) return null;
        $handle = opendir( $path );
        while ( false !== ( $file = readdir( $handle ) ) ) {
            if ( $file != '.' && $file != '..' ) {
                $path2 = $path . '/' . $file;
                if ( is_dir( $path2 ) ) {
                    $this->getfiles( $path2 , $files );
                } else {
                    if ( preg_match( "/\.(gif|jpeg|jpg|png|bmp)$/i" , $file ) ) {
                        $files[] = str_replace(ROOT_DIR, "", $path2);
                    }
                }
            }
        }
        return $files;
    }

    //获取下载文件的ID
    protected function getID()
    {
    	$id = q('id');
    	return FilterHelper::C_int($id) ? $id : false;
    }

    /**
     * 新窗口下载文件
     */
    public function Download()
    {
    	$msg = "文件不存在！";

    	$attachmentid = $this->getID();
    	if (!$attachmentid) {echo $msg;exit;}

    	$attachmentInfo = M("File")->getAttachment($attachmentid);
    	if (empty($attachmentInfo)) {echo $msg;exit;}

    	$name = $attachmentInfo['filename'];
    	$file = ROOT_DIR.$attachmentInfo['filepath'];
    	$size = $attachmentInfo['filesize'];
    	if (!file_exists($file)) {
    		echo $msg;exit;
    	} else {
		    $fp = fopen($file, "r");

		    //输入文件标签
		    Header("Content-type: application/octet-stream");
		    Header("Accept-Ranges: bytes");
		    Header("Accept-Length: " . $size);
		    Header("Content-Disposition: attachment; filename=" . $name);
		    //输出文件内容
		    //读取文件内容并直接输出到浏览器
		    echo fread($fp, $size);
		    fclose($fp);
		    exit();
		}
    }
}
<?php
/**
 * SAE 文件上传类
 * baoqing wang
 * 2013-12-10
 */
class SAEUploadHelper
{
	//access key
	private $_access_key = "oym2zl1441";
	//secret key
	private $_secret_key = "wl5zjx2mj5w24k4hwjm1wykihwhx1i0jmw3ykkhi";

    // input名称
    public $inputName;

     // 上传文件的最大值
    public $maxSize = 2097152; //2M

    // 上传文件保存路径
    public $savePath = null;

    // 使用对上传图片进行缩略图处理
    public $thumb   =  false;
    // 图库类包路径
    public $imageClassPath = 'Lib.ORG.Image';
    // 缩略图最大宽度
    public $thumbMaxWidth;
    // 缩略图最大高度
    public $thumbMaxHeight;
    // 缩略图前缀
    public $thumbPrefix   =  'thumb_';
    public $thumbSuffix  =  '';
    // 缩略图保存路径
    public $thumbPath = '';
    // 缩略图文件名
    public $thumbFile       =   '';
    // 是否移除原图
    public $thumbRemoveOrigin = false;

    // 错误信息
    private $error = '';

    // 上传成功的文件信息
    private $uploadFileInfo ;

    // 缩略图信息
    private $thumbImage;

    //SAE Storage存储路径域名
    public $_domain = "imbzd";

    //SAE Storage对象
    private $_saes = null;

	public function __construct()
	{
		//初始化SAE Storage
		$this->_saes = new SaeStorage($this->_access_key,$this->_secret_key);
	}

    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     * edit by buzhidao FILES数组匹配inputname才进入数组
     */
    private function dealFiles($files) {
        $fileArray = array();
        $n = 0;
        foreach ($files as $key=>$file){
            if ($key == $this->inputName) {
                if(is_array($file['name'])) {
                    $keys = array_keys($file);
                    $count    =   count($file['name']);
                    for ($i=0; $i<$count; $i++) {
                        foreach ($keys as $key)
                           $fileArray[$n][$key] = $file[$key][$i];
                        $n++;
                    }
                }else{
                    $fileArray[$n] = $file;
                    $n++;
                }
            }
        }
        return $fileArray;
    }

    /**
     * 根据上传文件命名规则取得保存文件名
     * @access private
     * @param string $file 数据
     * @return string
     */
    private function getSaveName($file) {
        return TIMESTAMP."_".getRandStrs(6,2)."_".rand(1,10000).'_'.$file['name'];
    }

	//上传
	public function upload()
	{
        $fileInfo = array();
        $isUpload   = false;
        
        // 获取上传的文件信息
        // 对$_FILES数组信息处理
        $files = $this->dealFiles($_FILES);
        foreach($files as $key => $file) {
            //过滤无效的上传
            if(!empty($file['name'])) {
                //登记上传文件的扩展信息
                $file['extension']  = $this->getExt($file['name']);
                $file['savename'] = $this->getSaveName($file);

                //上传文件
                $this->_saes->upload($this->_domain,$file['savename'],$file['tmp_name']);
                //获取上传文件路径
                $fileUrl = $this->_saes->getUrl($this->_domain,$file['savename']);
                //文件路径
                $file['savepath'] = str_replace($file['savename'], '', $fileUrl);

                if($this->thumb && in_array(strtolower($file['extension']),array('gif','jpg','jpeg','bmp','png'))) {
                    $image =  getimagesize($filename);
                    if(false !== $image) {
                        //是图像文件生成缩略图
                        $thumbWidth  = explode(',',$this->thumbMaxWidth);
                        $thumbHeight = explode(',',$this->thumbMaxHeight);
                        $thumbPrefix = explode(',',$this->thumbPrefix);
                        $thumbSuffix = explode(',',$this->thumbSuffix);
                        $thumbFile   = explode(',',$this->thumbFile);
                        $thumbPath   = $this->_domain;
                        // 生成图像缩略图
                        import($this->imageClassPath);
                        $realFilename  =  $file['savename'];

                        $thumbImageUrl = array();
                        for($i=0,$len=count($thumbWidth); $i<$len; $i++) {
                            $thumbname  =  $thumbPrefix[$i].substr($realFilename,0,strrpos($realFilename, '.')).$thumbSuffix[$i].'.'.$file['extension'];
                            $thumbImageUrl[] = $this->thumb($filename,$thumbname,'',$thumbWidth[$i],$thumbHeight[$i],true);
                        }
                        $this->thumbImage = $thumbImageUrl;
                    }
                }
                //添加缩略图信息
                if (!empty($this->thumbImage)) $file['thumb'] = $this->thumbImage;

                $fileInfo[] = $file;
                $isUpload   = true;
            }
        }
        if ($isUpload) {
            $this->uploadFileInfo = $fileInfo;
            return true;
        } else {
            $this->error = 'No Upload File!';
            return false;
        }
	}

    public function uploadContent($field="content",$code="base64")
    {
        $content = $_POST[$field];
        if ($code == "base64") {
            return $this->base64ToImage($content);
        }
    }

    private function base64ToImage($base64Data)
    {
        $img = base64_decode($base64Data);
        $this->fileName = TIMESTAMP . "_" . getRandStrs(6,2) . "_" . rand(1,10000) . ".png";

        $fileUrl = $this->_saes->write($this->_domain,$this->fileName,$img);
        if (!$fileUrl) {
            $this->error(7);
            return false;
        }
        $this->uploadFileInfo['savepath'] = str_replace($this->fileName, '', $fileUrl);
        $this->uploadFileInfo['savename'] = $this->fileName;
        $this->uploadFileInfo['name'] = $this->fileName;
        $this->uploadFileInfo['size'] = strlen($img);
        $this->uploadFileInfo['extension'] = ".png";
        return true;
    }

    /**
     * 获取错误代码信息
     * @access public
     * @param string $errorNo  错误号码
     * @return void
     * @throws ThinkExecption
     */
    protected function error($errorNo) {
         switch($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->error = '文件只有部分被上传';
                break;
            case 4:
                $this->error = '没有文件被上传';
                break;
            case 6:
                $this->error = '找不到临时文件夹';
                break;
            case 7:
                $this->error = '文件写入失败';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return ;
    }

    /**
     * 取得上传文件的后缀
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    private function getExt($filename) {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }

    /**
     * 取得上传文件的信息
     * @access public
     * @return array
     */
    public function getUploadFileInfo() {
        return $this->uploadFileInfo;
    }

    /**
     * 取得最后一次错误信息
     * @access public
     * @return string
     */
    public function getErrorMsg() {
        return $this->error;
    }

    /**
     * 取得图像信息
     *
     * @static
     * @access public
     * @param string $image 图像文件名
     * @return mixed
     */
    function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 生成缩略图
     * @static
     * @access public
     * @param string $image  原图
     * @param string $type 图像格式
     * @param string $thumbname 缩略图文件名
     * @param string $maxWidth  宽度
     * @param string $maxHeight  高度
     * @param string $position 缩略图保存目录
     * @param boolean $interlace 启用隔行扫描
     * @return void
     */
    static function thumb($image, $thumbname, $type='', $maxWidth=200, $maxHeight=50, $interlace=true) {
        // 获取原图信息
        $info = $this->getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if ($scale >= 1) {
                // 超过原图大小不再缩略
                $width = $srcWidth;
                $height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width = (int) ($srcWidth * $scale);
                $height = (int) ($srcHeight * $scale);
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $srcImg = $createFun($image);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);

            // 复制图片
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            if ('gif' == $type || 'png' == $type) {
                imagealphablending($thumbImg, false);//取消默认的混色模式
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                $background_color = imagecolorallocate($thumbImg, 0, 0, 0);  //  指定黑色
                imagecolortransparent($thumbImg, $background_color);  //  设置黑色为透明色，若注释掉该行则输出黑色的图
            }

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);

            ob_start();
            $imageFun($thumbImg);
            $imgdata = ob_get_contents();
            $fileUrl = $this->_saes->write($this->_domain,$thumbname,$imgdata);
            ob_end_clean();

            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $fileUrl;
        }
        return false;
    }
}
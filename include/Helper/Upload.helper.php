<?php
/**
 * 文件上传类 控制文件上传流程 包括文件安全性检测 BUG检测等
 * by wbq 2012-02-22
 * ﻿文件上传
 * 上传类使用ORG.Net.UpdateFile类，最新版本的上传类包含的功能如下（有些功能需要结合ThinkPHP系统其他类库）：
 *
 * 基本上传功能
 *
 * 支持批量上传
 *
 * 支持生成图片缩略图
 *
 * 自定义参数上传
 *
 * 上传检测（包括大小、后缀和类型）
 *
 * 支持覆盖方式上传
 *
 * 支持上传类型、附件大小、上传路径定义
 *
 * 支持哈希或者日期子目录保存上传文件
 *
 * 上传图片的安全性检测
 *
 * 支持上传文件命名规则
 *
 * 支持对上传文件的Hash验证
 *
 * 在ThinkPHP中使用上传功能无需进行特别处理。例如，下面是一个带有附件上传的表单提交：
 *
 * <form METHOD=POST action="__URL__/upload" enctype="multipart/form-data" >
 *
 * <input type="text" NAME="name" >
 *
 * <input type="text" NAME="email" >
 *
 * <input type="file" name="photo" >
 *
 * <input type="submit" value="保 存" >
 *
 * </form>
 *
 * 注意表单的Form标签中一定要添加 enctype="multipart/form-data"文件才能上传。因为表单提交到当前模块的upload操作方法，所以我们在模块类里面添加下面的upload方法即可：
 *
 * Public function upload(){
 *
 * import("ORG.Net.UploadFile");
 *
 * $upload = new UploadFile();// 实例化上传类
 *
 * $upload->maxSize = 3145728 ;// 设置附件上传大小
 *
 * $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
 *
 * $upload->savePath = './Public/Uploads/';// 设置附件上传目录
 *
 * if(!$upload->upload()) {// 上传错误提示错误信息
 *
 * $this->error($upload->getErrorMsg());
 *
 * }else{// 上传成功 获取上传文件信息
 *
 * $info = $upload->getUploadFileInfo();
 *
 * }
 *
 * // 保存表单数据 包括附件数据
 *
 * $User = M("User"); // 实例化User对象
 *
 * $User->create(); // 创建数据对象
 *
 * $User->photo = $info[0]["savename"]; // 保存上传的照片 根据需要自行组装
 *
 * $User->add(); // 写入用户数据到数据库
 *
 * $this->success("数据保存成功！");
 *
 * }
 *
 * 首先是实例化上传类
 *
 * import("ORG.Net.UploadFile");
 *
 * $upload = new UploadFile();// 实例化上传类
 *
 * 实例化上传类之后，就可以设置一些上传的属性（参数），支持的属性有：
 *
 * maxSize
 * 文件上传的最大文件大小（以字节为单位）默认为-1 不限大小
 *
 * savePath
 * 文件保存路径，如果留空会取UPLOAD_PATH常量定义的路径
 *
 * saveRule
 * 上传文件的保存规则，必须是一个无需任何参数的函数名，例如可以是 time、 uniqid com_create_guid 等，但必须能保证生成的文件名是唯一的，默认是uniqid
 *
 * hashType
 * 上传文件的哈希验证方法，默认是md5_file
 *
 * autoCheck
 * 是否自动检测附件，默认为自动检测
 *
 * uploadReplace
 * 存在同名文件是否是覆盖
 *
 * allowExts
 * 允许上传的文件后缀（留空为不限制），使用数组设置，默认为空数组
 *
 * allowTypes
 * 允许上传的文件类型（留空为不限制），使用数组设置，默认为空数组
 *
 * thumb
 * 是否需要对图片文件进行缩略图处理，默认为false
 *
 * thumbMaxWidth
 * 缩略图的最大宽度，多个使用逗号分隔
 *
 * thumbMaxHeight
 * 缩略图的最大高度，多个使用逗号分隔
 *
 * thumbPrefix
 * 缩略图的文件前缀，默认为thumb_
 *
 * thumbSuffix
 * 缩略图的文件后缀，默认为空
 *
 * thumbPath
 * 缩略图的保存路径，留空的话取文件上传目录本身
 *
 * thumbFile
 * 指定缩略图的文件名
 *
 * thumbRemoveOrigin
 * 生成缩略图后是否删除原图
 *
 * autoSub
 * 是否使用子目录保存上传文件
 *
 * subType
 * 子目录创建方式，默认为hash，可以设置为hash或者date
 *
 * dateFormat
 * 子目录方式为date的时候指定日期格式
 *
 * hashLevel
 * 子目录保存的层次，默认为一层
 *
 *
 * 以上属性都可以直接设置，例如：
 *
 * $upload->thumb = true
 *
 * $upload->thumbMaxWidth = "50,200"
 *
 * $upload->thumbMaxHeight = "50,200"
 *
 * 其中生成缩略图功能需要Image类的支持。
 *
 * 设置好上传的参数后，就可以调用UploadFile类的upload方法进行附件上传，如果失败，返回false，并且用getErrorMsg方法获取错误提示信息；如果上传成功，可以通过调用getUploadFileInfo方法获取成功上传的附件信息列表。因此getUploadFileInfo方法的返回值是一个数组，其中的每个元素就是上传的附件信息。每个附件信息又是一个记录了下面信息的数组，包括：
 *
 * key
 * 附件上传的表单名称
 *
 * savepath
 * 上传文件的保存路径
 *
 * name
 * 上传文件的原始名称
 *
 * savename
 * 上传文件的保存名称
 *
 * size
 * 上传文件的大小
 *
 * type
 * 上传文件的MIME类型
 *
 * extension
 * 上传文件的后缀类型
 *
 * hash
 * 上传文件的哈希验证字符串
 *
 *
 * 文件上传成功后，就可以通过这些附件信息来进行其他的数据存取操作，例如保存到当前数据表或者单独的附件数据表都可以。
 *
 * 如果需要使用多个文件上传，只需要修改表单，把
 *
 * <input type="file" name="photo" >
 *
 * 改为
 *
 * <input type="file" name="photo1" >
 *
 * <input type="file" name="photo2" >
 *
 * <input type="file" name="photo3" >
 *
 * 或者
 *
 * <input type="file" name="photo[]" >
 *
 * <input type="file" name="photo[]" >
 *
 * <input type="file" name="photo[]" >
 *
 * 两种方式的多附件上传系统的文件上传类都可以自动识别。
 *
 */
class UploadHelper
{
     // 上传文件的最大值
    public $maxSize = -1;

    // 是否支持多文件上传
    public $supportMulti = true;

    // 允许上传的文件后缀
    //  留空不作后缀检查
    public $allowExts = array();

    // 允许上传的文件类型
    // 留空不做检查
    public $allowTypes = array();

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
    // 压缩图片文件上传
    public $zipImages = false;
    // 启用子目录保存文件
    public $autoSub   =  false;
    // 子目录创建方式 可以使用hash date
    public $subType   = 'hash';
    public $dateFormat = 'Ymd';
    public $hashLevel =  1; // hash的目录层次
    // 上传文件保存路径
    public $savePath = null;
    public $autoCheck = true; // 是否自动检查附件
    // 存在同名是否覆盖
    public $uploadReplace = false;

    // 上传文件命名规则
    // 例如可以是 time uniqid com_create_guid 等
    // 必须是一个无需任何参数的函数名 可以使用自定义函数
    public $saveRule = '';

    // 上传文件Hash规则函数名
    // 例如可以是 md5_file sha1_file 等
    public $hashType = 'md5_file';

    // input名称
    public $inputName;

    // 错误信息
    private $error = '';

    // 上传成功的文件信息
    private $uploadFileInfo ;

    // 缩略图信息
    private $thumbImage;

    /**
     * 架构函数
     * @access public
     */
    public function __construct($maxSize='',$allowExts='',$allowTypes='',$savePath='',$saveRule='') {
        if(!empty($maxSize) && is_numeric($maxSize)) {
            $this->maxSize = $maxSize;
        }
        if(!empty($allowExts)) {
            if(is_array($allowExts)) {
                $this->allowExts = array_map('strtolower',$allowExts);
            }else {
                $this->allowExts = explode(',',strtolower($allowExts));
            }
        }
        if(!empty($allowTypes)) {
            if(is_array($allowTypes)) {
                $this->allowTypes = array_map('strtolower',$allowTypes);
            }else {
                $this->allowTypes = explode(',',strtolower($allowTypes));
            }
        }
        if(!empty($saveRule)) {
            $this->saveRule = $saveRule;
        }else{
            $this->saveRule = 'time';
        }
        $this->savePath = $savePath ? $savePath : C("UPLOAD_PATH")."/";
    }

    /**
     * 上传一个文件
     * @access public
     * @param mixed $name 数据
     * @param string $value  数据表名
     * @return string
     * @throws ThinkExecption
     */
    private function save($file) {
        $filename = $file['savepath'].$file['savename'];
        if(!$this->uploadReplace && is_file($filename)) {
            // 不覆盖同名文件
            $this->error    =   '文件已经存在！'.$filename;
            return false;
        }
        // 如果是图像文件 检测文件格式
        if( in_array(strtolower($file['extension']),array('gif','jpg','jpeg','bmp','png','swf')) && false === getimagesize($file['tmp_name'])) {
            $this->error = '非法图像文件';
            return false;
        }
        if(!move_uploaded_file($file['tmp_name'], $this->autoCharset($filename,'utf-8','gbk'))) {
            $this->error = '文件上传保存错误！';
            return false;
        }
        if($this->thumb && in_array(strtolower($file['extension']),array('gif','jpg','jpeg','bmp','png'))) {
            $image =  getimagesize($filename);
            if(false !== $image) {
                //是图像文件生成缩略图
                $thumbWidth  = explode(',',$this->thumbMaxWidth);
                $thumbHeight = explode(',',$this->thumbMaxHeight);
                $thumbPrefix = explode(',',$this->thumbPrefix);
                $thumbSuffix = explode(',',$this->thumbSuffix);
                $thumbFile   = explode(',',$this->thumbFile);
                $thumbPath   = $this->thumbPath?$this->thumbPath:$file['savepath'];
                // 生成图像缩略图
                import($this->imageClassPath);
                $realFilename  =  $this->autoSub?basename($file['savename']):$file['savename'];
                for($i=0,$len=count($thumbWidth); $i<$len; $i++) {
                    $thumbname  =   $thumbPath.$thumbPrefix[$i].substr($realFilename,0,strrpos($realFilename, '.')).$thumbSuffix[$i].'.'.$file['extension'];
                    Image::thumb($filename,$thumbname,'',$thumbWidth[$i],$thumbHeight[$i],true);
                }
                $this->thumbImage = $thumbname;
                if($this->thumbRemoveOrigin) {
                    // 生成缩略图之后删除原图
                    unlink($filename);
                }
            }
        }
        // if($this->zipImags) {
        //     // TODO 对图片压缩包在线解压

        // }
        return true;
    }

    /**
     * 上传所有文件
     * @access public
     * @param string $savePath  上传文件保存路径
     * @return string
     * @throws ThinkExecption
     */
    public function upload($savePath ='') {
        //如果不指定保存文件名，则由系统默认
        if(empty($savePath))
            $savePath = $this->savePath;
        // 检查上传目录
        if(!is_dir($savePath)) {
            // 检查目录是否编码后的
            if(is_dir(base64_decode($savePath))) {
                $savePath   =   base64_decode($savePath);
            }else{
                // 尝试创建目录
                if(!mk_dir($savePath)){
                    $this->error  =  '上传目录'.$savePath.'不存在';
                    return false;
                }
            }
        }else {
            if(!is_writeable($savePath)) {
                $this->error  =  '上传目录'.$savePath.'不可写';
                return false;
            }
        }
        $fileInfo = array();
        $isUpload   = false;

        // 获取上传的文件信息
        // 对$_FILES数组信息处理
        $files   =   $this->dealFiles($_FILES);
        foreach($files as $key => $file) {
            //过滤无效的上传
            if(!empty($file['name'])) {
                $file['savepath']   = $savePath;

                $savename = $this->getSaveName($file);
                $subpath = substr($savename,0,strrpos($savename,'/')).'/';

                //登记上传文件的扩展信息
                $file['key']        = $key;
                $file['extension']  = $this->getExt($file['name']);
                $file['savepath']   = $savePath.$subpath;
                $file['savename']   = str_replace($subpath,'',$savename);

                // 自动检查附件
                if($this->autoCheck) {
                    if(!$this->check($file))
                        return false;
                }

                //保存上传文件
                if(!$this->save($file)) return false;
                if(function_exists($this->hashType)) {
                    $fun =  $this->hashType;
                    $file['hash']   =  $fun($this->autoCharset($file['savepath'].$file['savename'],'utf-8','gbk'));
                }
                //上传成功后保存文件信息，供其他地方调用
                unset($file['tmp_name'],$file['error']);
                //添加缩略图信息
                if ($this->thumbImage) $file['thumb'] = $this->thumbImage;
                $fileInfo[] = $file;
                $isUpload   = true;
            }
        }
        if($isUpload) {
            $this->uploadFileInfo = $fileInfo;
            return true;
        }else {
            $this->error  =  '没有选择上传文件';
            return false;
        }
    }

    /**
     * 上传单个上传字段中的文件 支持多附件
     * @access public
     * @param array $file  上传文件信息
     * @param string $savePath  上传文件保存路径
     * @return string
     * @throws ThinkExecption
     */
    public function uploadOne($file,$savePath=''){
        //如果不指定保存文件名，则由系统默认
        if(empty($savePath))
            $savePath = $this->savePath;
        // 检查上传目录
        if(!is_dir($savePath)) {
            // 尝试创建目录
            if(!mk_dir($savePath)){
                $this->error  =  '上传目录'.$savePath.'不存在';
                return false;
            }
        }else {
            if(!is_writeable($savePath)) {
                $this->error  =  '上传目录'.$savePath.'不可写';
                return false;
            }
        }
        //过滤无效的上传
        if(!empty($file['name'])) {
            $fileArray = array();
            if(is_array($file['name'])) {
               $keys = array_keys($file);
               $count    =   count($file['name']);
               for ($i=0; $i<$count; $i++) {
                   foreach ($keys as $key)
                       $fileArray[$i][$key] = $file[$key][$i];
               }
            }else{
                $fileArray[] =  $file;
            }
            $info =  array();
            foreach ($fileArray as $key=>$file){
                $file['savepath']   = $savePath;

                $savename = $this->getSaveName($file);
                $subpath = substr($savename,0,strrpos($savename,'/')).'/';

                //登记上传文件的扩展信息
                $file['extension']  = $this->getExt($file['name']);
                $file['savepath']   = $savePath.$subpath;
                $file['savename']   = str_replace($subpath,'',$savename);
                // 自动检查附件
                if($this->autoCheck) {
                    if(!$this->check($file))
                        return false;
                }
                //保存上传文件
                if(!$this->save($file)) return false;
                if(function_exists($this->hashType)) {
                    $fun =  $this->hashType;
                    $file['hash']   =  $fun($this->autoCharset($file['savepath'].$file['savename'],'utf-8','gbk'));
                }
                unset($file['tmp_name'],$file['error']);
                $info[] = $file;
            }
            // 返回上传的文件信息
            return $info;
        }else {
            $this->error  =  '没有选择上传文件';
            return false;
        }
    }

    public function uploadContent($field="content",$content=null,$code="base64")
    {
        !$content ? $content = $_POST[$field] : null;
        if ($code == "base64") {
            return $this->base64ToImage($content);
        }
    }

    private function base64ToImage($base64Data)
    {
        if(!is_dir($this->savePath)) {
            mk_dir($this->savePath);
        }
        
        $img = base64_decode($base64Data);

        $file = array();
        $name = getRandStrs(6,2).".jpg";
        $file['name'] = $name;
        $file['savepath'] = $this->savePath;
        $savename = $this->getSaveName($file);
        $subpath = substr($savename,0,strrpos($savename,'/')).'/';

        $imageFile = $this->savePath . '/' . $savename;
        if (!file_put_contents($imageFile, $img)) {
            $this->error(7);
            return false;
        }
        $this->uploadFileInfo['savepath'] = $this->savePath.$subpath;
        $this->uploadFileInfo['savename'] = str_replace($subpath,'',$savename);
        $this->uploadFileInfo['size'] = strlen($img);
        $this->uploadFileInfo['extension'] = "jpg";
        return true;
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
    
    public function readFile($filename)
    {
        $row = 1;//第一行开始
        $data = array();
        if(($handle = fopen($filename, "r")) !== false) 
        {
            while(($dataSrc = fgetcsv($handle)) !== false) 
            {
                $num = count($dataSrc);
                for ($c=0; $c < $num; $c++)//列 column 
                {
                    $data = $dataSrc[$c];
                    foreach ($data as $k=>$v)
                    {
                        if($k == $c)//对应的字段
                        {
                            $data[$v] = $dataSrc[$c];
                        }
                    }
                }
                if(!empty($data))
                {
                     $dataRtn[] = $data;
                     unset($data);
                }
                $row++;
            }
            fclose($handle);
            return $dataRtn ? $dataRtn : false;
        }
    }
    
    public function export($file, $filename)
    {
        //判断要下载的文件是否存在
        if(!file_exists($file))
        {
            echo '对不起,你要下载的文件不存在。'.$file;
            return false;
        }
     
        $filesize = filesize($file);
     
        header("Content-type: application/octet-tream");
        header("Content-Type:csv/plain");
        header("Accept-Range : byte ");
        header("Accept-Length: $filesize");
        header("Content-Disposition: attachment; filename=".$filename);

        $fp= fopen($file,"r");
        $buffer_size = 1024;
        $cur_pos = 0;
     
        while(!feof($fp)&&$filesize-$cur_pos>$buffer_size)
        {
            $buffer = fread($fp,$buffer_size);
            echo $buffer;
            $cur_pos += $buffer_size;
        }
     
        $buffer = fread($fp,$filesize-$cur_pos);
        echo $buffer;
        fclose($fp);
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
     * 根据上传文件命名规则取得保存文件名
     * @access private
     * @param string $filename 数据
     * @return string
     */
    private function getSaveName($filename) {
        $rule = $this->saveRule;
        if (empty($rule)) {//没有定义命名规则，则保持文件名不变
            $saveName = $filename['name'];
        } else {
            if(function_exists($rule)) {
                //使用函数生成一个唯一文件标识号
                $saveName = $rule()."_".getRandStrs(6,2)."_".rand(1,10000).'_'.$filename['name'];
            } else {
                //使用给定的文件名作为标识号
                //$saveName = $rule."_".$filename['name'];
                $saveName = $rule."_".getRandStrs(6,2)."_".rand(1,10000).'_'.$filename['name'];
            }
        }
        if ($this->autoSub) {
            // 使用子目录保存文件
            $filename['savename'] = $saveName;
            $saveName = $this->getSubName($filename).'/'.$saveName;
        }
        return $saveName;
    }

    /**
     * 获取子目录的名称
     * @access private
     * @param array $file  上传的文件信息
     * @return string
     */
    private function getSubName($file) {
        switch($this->subType) {
            case 'date':
                $dir   =  date($this->dateFormat,time());
                break;
            case 'hash':
            default:
                $name = md5($file['savename']);
                $dir   =  '';
                for($i=0;$i<$this->hashLevel;$i++) {
                    $dir = $dir ? $dir.'/'.$name{$i} : $name{$i};
                }
                break;
        }
        if(!is_dir($file['savepath'].$dir)) {
            mk_dir($file['savepath'].$dir);
        }
        return $dir;
    }

    /**
     * 检查上传的文件
     * @access private
     * @param array $file 文件信息
     * @return boolean
     */
    private function check($file) {
        if($file['error']!== 0) {
            //文件上传失败
            //捕获错误代码
            $this->error($file['error']);
            return false;
        }
        //文件上传成功，进行自定义规则检查
        //检查文件大小
        if(!$this->checkSize($file['size'])) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        //检查文件Mime类型
        if(!$this->checkType($file['type'])) {
            $this->error = '上传文件MIME类型不允许！';
            return false;
        }
        //检查文件类型
        if(!$this->checkExt($file['extension'])) {
            $this->error ='上传文件类型不允许';
            return false;
        }

        //检查是否合法上传
        if(!$this->checkUpload($file['tmp_name'])) {
            $this->error = '非法上传文件！';
            return false;
        }
        return true;
    }

    // 自动转换字符集 支持数组转换
    private function autoCharset($fContents, $from='gbk', $to='utf-8') {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    }

    /**
     * 检查上传的文件类型是否合法
     * @access private
     * @param string $type 数据
     * @return boolean
     */
    private function checkType($type) {
        if(!empty($this->allowTypes))
            return in_array(strtolower($type),$this->allowTypes);
        return true;
    }


    /**
     * 检查上传的文件后缀是否合法
     * @access private
     * @param string $ext 后缀名
     * @return boolean
     */
    private function checkExt($ext) {
        if(!empty($this->allowExts))
            return in_array(strtolower($ext),$this->allowExts,true);
        return true;
    }

    /**
     * 检查文件大小是否合法
     * @access private
     * @param integer $size 数据
     * @return boolean
     */
    private function checkSize($size) {
        return !($size > $this->maxSize) || (-1 == $this->maxSize);
    }

    /**
     * 检查文件是否非法提交
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    private function checkUpload($filename) {
        return is_uploaded_file($filename);
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
}
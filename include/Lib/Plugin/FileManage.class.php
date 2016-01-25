<?php
/**
 * PHP文件系统管理器 插件
 * by buzhidao 2012-01-17
 */
class FileManage
{
	private $_c = '.';
	//要管理的文件夹
	private $_root = null;
	private $_dir = null;
	private $_diskSpace = 0; //目录大小 默认0B

	private $_encoding = array('UTF-8','UTF-32','GB2312','GBK','ASCII');

	//管理文件数组返回
	public $_fileArray = array(
		'filelist' => array(),  //下级目录和文件列表
		'pdir'     => null,  //父目录
		'cdir'     => null,  //当前目录
		'perms'    => null
	);

	//文件名过滤
	private $_nameFilter = array(
		'#([/\:*;"?<>|]+)#',
		'#^\.*$#'
	);

	//可编辑的文件扩展名
	private $_fileEditExt = array(
		'php','asp','aspx','jsp','txt','inc','html','htm','js','css','tpl','htaccess'
	);

	//初始化构造函数
	public function __construct($_root=null,$_dir=null)
	{
		if (!$_root) return flase;
		$this->_root = $_root;
		$this->_dir = $_dir;
	}

	//遍历返回
	public function getFilelist($dir=null)
	{
		$mdir = $this->_getMDir($dir);
		$this->_fileArray['filelist'] = $this->_traversalDir($mdir);
	}

	//遍历获取某个目录的下级文件夹和文件
	private function _traversalDir($dir=null)
	{
		$return1 = array();
		$return2 = array();
		if (is_dir($dir)) {
			$dh = opendir($dir);
			while (($filename = readdir($dh)) !== false) {
				if (!in_array($filename, array('.','..'))) {
		            $filedir = $dir . '/' . $filename;
					$filename = $this->_convertCoding($filename,$this->_checkCoding($filename),'UTF-8');
		            if (is_dir($filedir)) {
			            $return1[] = array(
			            	'filename' => $filename,
			            	'filetype' => 'dir',
			            	'filesize' => '',
			            	'filemtime'=> '',
			            	'editable' => false
			            );
			        } else {
		            	$fileInfo = pathinfo($filedir);
			        	$fileInfo["extension"] = isset($fileInfo["extension"]) ? $fileInfo["extension"] : 'unknow';
			        	$return2[] = array(
			            	'filename' => $filename,
			            	'filetype' => $fileInfo['extension'],
			            	'filesize' => formatBytes(filesize($filedir)),
			            	'filemtime'=> mkdate(filemtime($filedir)),
			            	'editable' => in_array(strtolower($fileInfo['extension']), $this->_fileEditExt) ? true : false
			            );
			        }
			    }
	        }
	        closedir($dh);
		}

		return array_merge($return1,$return2);
	}

	/**
	 * 获取管理目录
	 * @param $dir string 目录 
	 * @param $flag int 是否自动转码 0否 1是 默认1
	 */
	private function _getMDir($dir=null,$flag=1)
	{
		$dir = $dir ? $dir : $this->_dir;
		if (!$dir || $dir == $this->_c) $dir = $this->_root;
		if ($dir && substr($dir,0,2)==$this->_c."/") $dir = $this->_root."/".substr($dir,2);
		
		return $flag ? $this->_makeCoding($dir) : $dir;
	}

	//获取父目录
	public function getPDir()
	{
		$this->_fileArray['pdir'] = $this->_dir && $this->_dir != $this->_c ? substr($this->_dir,0,strrpos($this->_dir, '/')) : $this->_c;
	}

	//获取当前目录
	public function getCDir()
	{
		$this->_fileArray['cdir'] = $this->_dir ? $this->_dir : $this->_c;
	}

	//获取目录权限
	public function getPerms()
	{
		$dir = $this->_getMDir();
		$perms = fileperms($dir);
		if (($perms & 0xC000) == 0xC000) {
		    // Socket
		    $info = 's';
		} elseif (($perms & 0xA000) == 0xA000) {
		    // Symbolic Link
		    $info = 'l';
		} elseif (($perms & 0x8000) == 0x8000) {
		    // Regular
		    $info = '-';
		} elseif (($perms & 0x6000) == 0x6000) {
		    // Block special
		    $info = 'b';
		} elseif (($perms & 0x4000) == 0x4000) {
		    // Directory
		    $info = 'd';
		} elseif (($perms & 0x2000) == 0x2000) {
		    // Character special
		    $info = 'c';
		} elseif (($perms & 0x1000) == 0x1000) {
		    // FIFO pipe
		    $info = 'p';
		} else {
		    // Unknown
		    $info = 'u';
		}

		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
		            (($perms & 0x0800) ? 's' : 'x' ) :
		            (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
		            (($perms & 0x0400) ? 's' : 'x' ) :
		            (($perms & 0x0400) ? 'S' : '-'));

		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
		            (($perms & 0x0200) ? 't' : 'x' ) :
		            (($perms & 0x0200) ? 'T' : '-'));

		$this->_fileArray['perms'] = $info;
	}

	//返回fileArray数组
	public function getFileArray()
	{
		$this->getFilelist();
		$this->getPDir();
		$this->getCDir();
		$this->getPerms();

		return $this->_fileArray;
	}

	//检测文件名
	private function _checkFileName($filename=null)
	{
		if (!$filename) return false;
		foreach ($this->_nameFilter as $v) {
			if (preg_match($v, $filename)) return false;
		}
		return true;
	}

	//检测文件名编码
	private function _checkCoding($file=null)
	{
		if (!$file) return false;
		// foreach ($this->_encoding as $v) {
		// 	if (mb_check_encoding($file, $v)) return $v;
		// }
		return mb_detect_encoding($file, $this->_encoding);
	}

	//编码转换
	private function _convertCoding($file,$incoding=null,$outcoding=null)
	{
		if (!$file) return false;
		$incoding = $incoding ? $incoding : $this->_checkCoding($file);
		$outcoding = $outcoding ? $outcoding : $this->_checkCoding($file);
		//用//IGNORE忽略掉未知的字符编码
		return iconv($incoding, $outcoding."//IGNORE", $file);
	}

	//得到正确的编码的目录和文件名
	private function _makeCoding($dir=null,$file=null)
	{
		if (!is_dir($dir)) {
			foreach ($this->_encoding as $v) {
				$ndir = $this->_convertCoding($dir,'UTF-8',$v);
				if (is_dir($ndir)) {
					$dir = $ndir; break;
				}
			}
		}
		if ($file && (!is_dir($dir."/".$file) && !is_file($dir."/".$file))) {
			foreach ($this->_encoding as $v) {
				$nfile = $this->_convertCoding($file,'UTF-8',$v);
				if (is_dir($dir."/".$nfile) || is_file($dir."/".$nfile)) {
					$file = $nfile; break;
				}
			}
		}
		return $file ? $dir."/".$file : $dir;
	}

	//获取文件内容
	public function getFileContent($dir,$filename)
	{
		if (!$dir || !$filename) return false;
		$mdir = $this->_getMDir($dir);
		$file = $this->_makeCoding($mdir,$filename);
		$filecontent = file_get_contents($file);
		$filecontent = $this->_convertCoding($filecontent,$this->_checkCoding($filecontent),'UTF-8');
		return htmlspecialchars($filecontent);
	}

	/**
	 * 保存新文件
	 * @param $dir string 文件存放目录
	 * @param $filename string 文件名
	 * @param $filecontent string 文件内容
	 */
	public function fileSave($dir,$filename,$filecontent='')
	{
		if (!$dir || !$filename) return false;
		if (!$this->_checkFileName($filename))
			return array('state'=>0, 'msg'=>'新文件名错误！');

		$mdir = $this->_getMDir($dir);
		$mdir = $this->_mkdir($mdir);
		$file = $mdir."/".$this->_convertCoding($filename,'UTF-8',$this->_checkCoding($mdir));
		if (file_exists($file)) return array('state'=>0, 'msg'=>'文件已存在！');
		$return = file_put_contents($file, $filecontent);
		if ($return)
			return array('state'=>1, 'msg'=>'文件保存成功！');
		else
			return array('state'=>0, 'msg'=>'文件保存失败！');
	}

	/**
	 * 修改文件名
	 * @param $dir string 文件路径
	 * @param $oldfilename string 原文件名
	 * @param $newfilename string 新文件名
	 */
	public function fileRename($dir,$oldfilename,$newfilename)
	{
		if (!$this->_checkFileName($newfilename))
			return array('state'=>0, 'msg'=>'新文件名错误！');

		$mdir = $this->_getMDir($dir);
		$oldfile = $this->_makeCoding($mdir,$oldfilename);
		$newfile = $mdir."/".$this->_convertCoding($newfilename,'UTF-8',$this->_checkCoding($oldfile));
		if (file_exists($newfile)) return array('state'=>0, 'msg'=>'文件已存在！');
		if (rename($oldfile, $newfile))
			return array('state'=>1, 'msg'=>'文件名修改成功！');
		else
			return array('state'=>0, 'msg'=>'文件名修改失败！');
	}

	/**
	 * 删除文件
	 * @param $dir string 文件路径
	 * @param $filename string 文件名
	 */
	public function fileDelete($dir,$filename=null)
	{
		$mdir = $this->_getMDir($dir);
		$file = $this->_makeCoding($mdir,$filename);

		$return = false;
		if (is_file($file)) {
			$return = unlink($file);
		} else if (is_dir($file)) {
			if(file_exists($file.'/Thumbs.db')) unlink($file.'/Thumbs.db');
			$dh = opendir($file);
			$n = 0;
			while ($f = readdir($dh) !== false) $n++;
			closedir($dh);
			if ($n > 2) $msg = '删除失败！该目录非空！';
			else {
				$return = rmdir($file);
			}
		}
		if ($return)
			return array('state'=>1, 'msg'=>'文件删除成功！');
		else
			return array('state'=>0, 'msg'=>isset($msg) ? $msg : '文件删除失败！');
	}

	/**
	 * 文件移动
	 * @param $dir string 文件原目录
	 * @param $newdir string 文件新目录
	 * @param $filename string 文件名
	 */
	public function fileMove($dir=null,$newdir=null,$filename=null)
	{
		if (!$dir || !$newdir || !$filename) return false;

		$dir = $this->_getMDir($dir);
		$newdir = $this->_getMDir($this->_mkdir($newdir));

		$oldfile = $this->_makeCoding($dir,$filename);
		$newfile = $this->_makeCoding($newdir,$filename);

		if (rename($oldfile, $newfile))
			return array('state'=>1, 'msg'=>'文件移动成功！');
		else
			return array('state'=>0, 'msg'=>'文件移动失败！');
	}

	//新建保存目录
	public function saveDir($dir,$newdir)
	{
		if (!$dir) return false;
		if (!$this->_checkFileName($newdir))
			return array('state'=>0, 'msg'=>'目录名错误！');
		$dir = $this->_getMDir($dir);
		$this->_mkdir($dir."/".$newdir);
		return array('state'=>1, 'msg'=>'目录新建成功！');
	}

	//循环创建目录文件夹
	private function _mkdir($dir=null)
	{
		if (!$dir) return false;
		$dirArray = explode("/", $dir);
		$ndir = null;
		foreach ($dirArray as $v) {
			$ndir = $this->_makeCoding($ndir);
			// $v = $this->_convertCoding($v,'UTF-8','GB2312');
			$ndir .= $ndir ? "/".$v : $v;
			if (!is_dir($ndir)) {
				mkdir($ndir,0777);
			}
		}
		return $dir;
	}

	//获取目录的大小(使用空间)
	public function getDiskSpace($dir=null)
	{
		if (!$dir) return false;
		$dir = $this->_getMDir($dir);
		$dh = opendir($dir);
		while (($filename = readdir($dh)) !== false) {
			if ($filename != "." && $filename != "..") {
				$file = $dir."/".$filename;
				if(is_dir($file)) {
					$this->getDiskSpace($file);
				} else {
					$this->_diskSpace += filesize($file);
				}
			}
		}
		closedir($dh);
		return $this->_diskSpace;
	}

	//上传文件到目录
	public function fileUpload($dir=null)
	{
		if (!$dir) return false;
		$upload = new UploadHelper();
		$upload->inputName = "newfile";
        $upload->maxSize  = 5242880; //5M
        $upload->savePath =  $this->_getMDir($dir)."/";
        //同名文件不覆盖
        $upload->uploadReplace = false;
        if(!$upload->upload()) {
            return array('state'=>0, 'msg'=>$upload->getErrorMsg());
        } else {
        	$mdir = $this->_getMDir($dir);
            $info = $upload->getUploadFileInfo();
            if (file_exists($mdir."/".$info[0]['name'])) {
            	$this->fileDelete($dir,$info[0]['savename']);
            	return array('state'=>0, 'msg'=>'文件已存在！');
            } else {
            	$this->fileRename($dir,$info[0]['savename'],$info[0]['name']);
            	return array('state'=>1, 'msg'=>'文件上传成功！');
            }
        }
	}
}
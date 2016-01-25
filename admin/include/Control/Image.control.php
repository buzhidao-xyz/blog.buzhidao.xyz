<?php
/**
 * 图片控制器
 * by buzhidao 2013-03-26
 */
class ImageControl extends ArchiveControl
{
	//控制器名
	protected $_Control = "Image";

	//缩略图标准宽高
	static protected $_Width = 550;
	static protected $_Height = 350;

	//图集图片缩略图标准宽高
	static protected $_Album_Width = 480;
	static protected $_Album_Height = 320;

	//图片最大size
	static protected $_ImageSize = 5242880; //5M

	public function __construct()
	{
		parent::__construct();

		$this->assign("imageUploadAction", "");
	}

	//图集列表
	public function index()
	{
		$columnid = $this->_getColumnID();

		$columnids = array();
		if ($columnid) $columnids = array_merge(M("Column")->getSubColumnID($columnid),array($columnid));

		list($start,$length) = $this->getPages();
        $archiveList = M("Images")->getImages(null,$start,$length,1,$columnids,$this->_Control);
        $this->assign("total", $archiveList['total']);
        $this->assign("dataList", $archiveList['data']);

        $this->assign("page", getPage($archiveList['total'],$this->_pagesize));
		$this->display("Image/index.html");
	}

	//新建图集
	public function add()
	{
		$this->assign("accessStatus",1);

		$this->assign("columnTree", D("Column")->getColumnTree($this->_Control));

		$this->assign("imageUploadAction", "Album");
		$this->display("Image/add.html");
	}

	/**
	 * 保存图集入库
	 * @param $title string 图集标题 必须
	 */
	public function save()
	{
		$data = $this->dealArchiveSubmit();
		$archiveid = M("Archive")->saveArchive($data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		
		if ($archiveid) {
			//保存图集图片
			$imageids = $this->_getImageids();
			M("Archive")->addArchiveImages($archiveid,$imageids);

			$NextOperation = array(
				array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Image/edit&archiveid='.$archiveid)
			);
			$this->assign("NextOperation", $NextOperation);
			$this->display("Common/success.html");
		} else {
			$this->display("Common/error.html");
		}
	}

	//修改图集信息
	public function edit()
	{
		$this->assign("accessStatus", 1);

		$ArchiveID = $this->_getArchiveID();
		$ArchiveInfo = M("Product")->getProduct($ArchiveID,0,0,null);
		$ArchiveInfo = !empty($ArchiveInfo['data']) ? $ArchiveInfo['data'][0] : array();

		if (empty($ArchiveInfo)) $this->display("Common/error.html");

		$ArchiveInfo['archiveImage'] = M("Archive")->getArchiveImages($ArchiveID);

		$this->assign("ArchiveInfo", $ArchiveInfo);
		$this->assign("columnTree", D("Column")->getColumnTree());
		$this->display("Image/edit.html");
	}

	//保存修改的图集信息
	public function saveEdit()
	{
		$ArchiveID = $this->_getArchiveID();
		$data = $this->dealArchiveSubmit();
		$return = M("Archive")->upArchive($ArchiveID,$data['title'],$data['tag'],$data['source'],$data['author'],$data['columnid'],$data['status'],$data['seotitle'],$data['keyword'],$data['description'],$data['image'],$data['publishtime']);
		if ($return) {
			//保存图集图片
			$imageids = $this->_getImageids();
			M("Product")->deleteArchiveImages($ArchiveID);
			M("Product")->addArchiveImages($ArchiveID,$imageids);

			$NextOperation = array(
				array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=Image/edit&archiveid='.$ArchiveID)
			);
			$this->assign("NextOperation", $NextOperation);
			$this->display("Common/success.html");
		} else {
			$this->display("Common/error.html");
		}
	}

	//文档回收站
	public function recover()
	{
		$this->assign("accessStatus", 1);

		list($start,$length) = $this->getPages();
        $articleList = M("Images")->getImages(null,$start,$length,0,null,$this->_Control);
        $this->assign("total", $articleList['total']);
        $this->assign("dataList", $articleList['data']);

        $this->assign("page", getPage($articleList['total'],$this->_pagesize));
		$this->display("Article/recover.html");
	}

	//首页轮播图片管理
	public function HomeScrollImage()
	{
		$HomeScrollImageList = M("Images")->getHomeScrollImage();
		$this->assign("total",$HomeScrollImageList['total']);
		$this->assign("dataList",$HomeScrollImageList['data']);

		$this->display("Image/HomeScrollImage.html");
	}

	//获取轮播图片
	private function _getScrollImage()
	{
		$upload = new UploadHelper();
		$upload->inputName = "scrollImage";
		$upload->maxSize  = self::$_ImageSize;
		$upload->savePath =  C("UPLOAD_PATH")."/ScrollImage/";
		if(!$upload->upload()) {
			return false;
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			return $url;
		}
	}

	//保存首页轮播图片
	public function saveHomeScrollImage()
	{
		$path = $this->_getScrollImage();
		$title = q("title");
		$link = q("link");

		$data = array(
			'path'  => $path,
			'title' => $title,
			'link'  => $link
		);

		$return = M("Images")->saveHomeScrollImage($data);
		if ($return) {
			$this->showMessage('图片保存成功！',1);
		} else {
			$this->showMessage('图片保存失败！',0);
		}
	}

	//修改首页轮播图片
	public function updateHomeScrollImage()
	{
		$this->assign("accessStatus",1);

		$id = q('id');
		$HomeScrollImageInfo = M("Images")->getHomeScrollImage($id);
		$HomeScrollImageInfo = $HomeScrollImageInfo['data'][0];
		$this->assign("HomeScrollImageInfo",$HomeScrollImageInfo);

		$this->display("Image/updateHomeScrollImage.html");
	}

	//保存修改首页轮播图片
	public function saveUpdateHomeScrollImage()
	{
		$id = q('id');
		$path = $this->_getScrollImage();
		$title = q("title");
		$link = q("link");

		$data = array(
			'title' => $title,
			'link'  => $link
		);
		if ($path) $data['path'] = $path;

		$return = M("Images")->UpdateHomeScrollImage($id,$data);
		$this->showMessage('图片修改成功！',1);
	}

	//修改首页轮播图片状态
	public function UpdateHomeScrollImageStatus()
	{
		$id = q('id');
		$isshow = q('isshow');

		$data = array('isshow'=>$isshow);
		$return = M("Images")->UpdateHomeScrollImage($id,$data);
		if ($return) {
			$this->ajaxReturn(0,"状态切换成功！");
		} else {
			$this->ajaxReturn(1,"状态切换失败！");
		}
	}

	//删除首页轮播图片状态
	public function deleteHomeScrollImage()
	{
		$id = q('id');
		$return = M("Images")->deleteHomeScrollImage($id);
		if ($return) {
			$this->ajaxReturn(0,"删除成功！");
		} else {
			$this->ajaxReturn(1,"删除失败！");
		}
	}

	//图片上传
	public function saveUploadImage()
	{
		$imageUploadAction = q("imageUploadAction");
		$imageTitle = q("imageTitle");
		$imageLink = null;
		$archiveid = null;

		$upload = new UploadHelper();
		$upload->inputName = "images";
		$upload->thumb = true;
		if ($imageUploadAction = "Album") {
			$upload->thumbMaxWidth = self::$_Album_Width;
			$upload->thumbMaxHeight = self::$_Album_Height;
		} else {
			$upload->thumbMaxWidth = self::$_Width;
			$upload->thumbMaxHeight = self::$_Height;
		}
		$upload->maxSize  = self::$_ImageSize;
		$upload->savePath =  C("UPLOAD_PATH")."/Image/".date("Ym/d/");
		if (!$upload->upload()) {
			$this->ajaxReturn(1,"图片上传失败！");
		} else {
			$info = $upload->getUploadFileInfo();
			$imagepath = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			$thumbpath = str_replace(ROOT_DIR, "", $info[0]["thumb"]);
			$imageInfo = getimagesize(__APPM__.$imagepath);
			$imageid = M("Images")->saveUploadImage($imagepath,$thumbpath,$imageTitle,$imageLink,$archiveid,$info[0]['name'],$info[0]['savename'],$info[0]['size'],$imageInfo[0],$imageInfo[1],TIMESTAMP);
			if ($imageid) {
				$data = array(
					'imageid' => $imageid,
					'src'     => __APPM__.$thumbpath,
					'imageTitle' => $imageTitle
				);
				$this->ajaxReturn(0,"图片上传成功！",$data);
			} else {
				$this->ajaxReturn(1,"图片上传失败！");
			}
		}
	}
}
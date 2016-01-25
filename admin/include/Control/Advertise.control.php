<?php
/**
 * 广告控制器
 * by buzhidao 2013-03-26
 */
class AdvertiseControl extends CommonControl
{
	//广告分类
	protected $_flag = array(
		'HomeCenterAd' => 1,
		'TopAd'        => 2
	);

	public function __construct()
	{
		parent::__construct();
	}

	//入口
	public function index(){}

	//首页中部banner
	public function HomeCenterAd()
	{
		list($start,$length) = $this->getPages();
        $dataList = M("Advertise")->getAdvertise(null,$start,$length,$this->_flag['HomeCenterAd']);

        $this->assign("total", $dataList['total']);
        $this->assign("dataList", $dataList['data']);

        $this->assign("page", getPage($dataList['total'],$this->_pagesize));
		$this->display("Advertise/HomeCenterAd.html");
	}

	//保存首页中部banner
	public function saveHomeCenterAd()
	{
		$this->saveAdvertise($this->_flag['HomeCenterAd']);
	}

	//顶部广告
	public function TopAd()
	{
		list($start,$length) = $this->getPages();
        $dataList = M("Advertise")->getAdvertise(null,$start,$length,$this->_flag['TopAd']);

        $this->assign("total", $dataList['total']);
        $this->assign("dataList", $dataList['data']);

        $this->assign("page", getPage($dataList['total'],$this->_pagesize));
		$this->display("Advertise/TopAd.html");
	}

	//保存首页中部banner
	public function saveTopAd()
	{
		$this->saveAdvertise($this->_flag['TopAd']);
	}

	//获取banner图片
	private function _getAdImage()
	{
		$upload = new UploadHelper();
		$upload->inputName = "AdImage";
		$upload->savePath =  C("UPLOAD_PATH")."/AdImage/";
		if(!$upload->upload()) {
			return false;
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			return $url;
		}
	}

	//保存广告
	public function saveAdvertise($flag=1)
	{
		$title = q("title");
		if (empty($title)) $this->showMessage('请填写标题！',0);
		$link = q("link");
		$path = $this->_getAdImage();
		if (!$path) $this->showMessage('请选择上传图片！',0);
		$status = q("status");

		$return = M("Advertise")->saveAdvertise($title,$link,$path,$flag,$status,TIMESTAMP);
		if ($return) {
			$this->showMessage('图片保存成功！',1);
		} else {
			$this->showMessage('图片保存失败！',0);
		}
	}

	//修改广告信息
	public function AdvertiseEdit()
	{
		$id = q("id");
		if (empty($id)) $this->ajaxReturn(1,"广告不存在！");

		$AdvertiseInfo = M("Advertise")->getAdvertise($id);
		$this->assign("AdvertiseInfo", $AdvertiseInfo['data'][0]);

		$this->display("Advertise/AdvertiseEdit.html");
	}

	//保存修改后的广告信息
	public function AdvertiseEditSave()
	{
		$id = q("advertiseid");
		if (empty($id)) $this->ajaxReturn(1,"广告不存在！");

		$title = q("title");
		if (empty($title)) $this->showMessage('请填写标题！',0);
		$link = q("link");
		$status = q("status");

		$return = M("Advertise")->AdvertiseEditSave($id,$title,$link,$status);
		if ($return) {
			$this->ajaxReturn(0,"修改成功！");
		} else {
			$this->ajaxReturn(1,"修改失败！");
		}
	}

	//修改广告状态
	public function UpdateHomeCenterAdStatus()
	{
		$id = q("id");
		$status = q("status");
		$msg = $status ? '启用' : '禁用';

		$return = M("Advertise")->UpdateHomeCenterAdStatus($id,$status);
		if ($return) {
			$this->ajaxReturn(0,$msg.'成功！');
		} else {
			$this->ajaxReturn(1,$msg.'失败！');
		}
	}

	//删除广告
	public function DeleteAdvertise()
	{
		$id = q("id");
		$return = M("Advertise")->DeleteAdvertise($id);
		if ($return) {
			$this->ajaxReturn(0,"删除成功！");
		} else {
			$this->ajaxReturn(1,"删除失败！");
		}
	}
}
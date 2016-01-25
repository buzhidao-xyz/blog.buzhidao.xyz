<?php
/**
 * 附件控制器
 * by buzhidao 2013-04-10
 */
class AttachmentControl extends ArchiveControl
{
	//控制器名
	protected $_Control = "Attachment";

	//上传文件的最大SIZE
	static protected $_MaxFileSize = 5242880; //5M

	public function __construct()
	{
		parent::__construct();

		$this->assign("fileUploadAction", "");
	}

	public function index(){}

	//获取附件ID
	protected function _getAttachmentID()
	{
		$attachmentid = q("attachmentid");

		return $attachmentid;
	}

	//返回上传文件存放路径
    private function makeSavePath($folderpath=null)
    {
    	return C("UPLOAD_PATH")."/".$folderpath."/".date("Ym/d/");
    }

	//获取轮播图片
	private function _getAttachment()
	{
		$upload = new UploadHelper();
		$upload->inputName = "attachment";
		$upload->maxSize  = self::$_MaxFileSize;
		$upload->savePath = $this->makeSavePath("Attachment");
		if(!$upload->upload()) {
			return array();
		} else {
			return $upload->getUploadFileInfo();
		}
	}

	/**
	 * 保存附件文件
	 * @param int $archiveid 文档id
	 */
	public function saveAttachment($archiveid=null)
	{
		$imageUploadAction = q("imageUploadAction");

		$FileInfo = $this->_getAttachment();
		if (empty($FileInfo)) {
			return false;
		} else {
			$FileInfo = $FileInfo[0];
			$filepath = str_replace(ROOT_DIR, "", $FileInfo['savepath'].$FileInfo['savename']);
			$attachmentid = M("Attachment")->saveAttachment($archiveid,$filepath,$FileInfo['name'],$FileInfo['savename'],$FileInfo['size'],$FileInfo['extension'],0,TIMESTAMP);
			return $attachmentid ? $attachmentid : 0;
		}
	}

	/**
	 * 删除文档附件关联
	 * @param int $archiveid 文档id
	 */
	public function deleteArchiveAttachment($archiveid=null)
	{
		if (!$archiveid) return false;
		return M("Attachment")->deleteArchiveAttachment($archiveid);
	}
}
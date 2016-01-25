<?php
/**
 * 文件管理模型 包括附件模型
 * by buzhidao 2013-04-11
 */
class File extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
     * 获取附件文件
     * @param $attachmentid int 附件ID
     */
	public function getAttachment($attachmentid=null)
	{
		if (!$attachmentid) return false;
		$data = T("attachment")->where(array("id"=>$attachmentid))->find();
		return $data;
	}
}
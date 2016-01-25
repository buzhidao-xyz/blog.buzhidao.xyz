<?php
/**
 * 附件管理模型
 * by buzhidao 2013-03-26
 */
class Attachment extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 保存上传的图片 产品图片/图片集等
	 * @param $archiveid int 文档ID
	 * @param $filepath string 文件地址
	 * @param $filename string 文件原名
	 * @param $savename string 文件保存名称
	 * @param $filesize int 文件大小
	 * @param $filetype string 文件类型 后缀名
	 * @param $downloadnum int 下载次数
	 * @param $createtime int 创建时间
	 */
	public function saveAttachment($archiveid=0,$filepath=null,$filename=null,$savename=null,$filesize=0,$filetype=null,$downloadnum=0,$createtime=0)
	{
		if (!$filepath) return false;

		$data = array(
			'archiveid'  => $archiveid,
			'filepath'   => $filepath,
			'filename'   => $filename,
			'savename'   => $savename,
			'filesize'   => $filesize,
			'filetype'   => $filetype,
			'downloadnum'=> $downloadnum,
			'createtime' => $createtime
		);

		return T("attachment")->add($data);
	}

	/**
	 * 获取附件列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getAttachment($id=null,$start=0,$length=0,$state=1,$columnids=array(),$control=null)
	{
		$where = array();
	}

	/**
	 * 根据ArchiveID获取下载文件
	 * @param $archiveid int 文档ID
	 */
	public function getAttachmentByArchiveID($archiveid=null)
	{
		if (!$archiveid) return false;
		return T("attachment")->where(array("archiveid"=>$archiveid))->find();
	}

	/**
	 * 删除文档附件关联
	 * @param int $archiveid 文档id
	 */
	public function deleteArchiveAttachment($archiveid=null)
	{
		if (!$archiveid) return false;
		return T("attachment")->where(array("archiveid"=>$archiveid))->update(array("archiveid"=>0));
	}
}
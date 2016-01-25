<?php
/**
 * 下载模型
 * by laucen 2013-03-22
 */
class Download extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取下载列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getDownload($id=null,$start=0,$length=0,$state=1,$columnids=array(),$control=null)
	{
		$where = array();
		if ($state !== null) $where['state'] = $state;
		if (is_array($columnids) && !empty($columnids)) $where['columnid'] = array("in", $columnids);
		if ($control) $where['control'] = $control;

		$archiveList = $this->getArchive($id,$start,$length,$where);
		if (!empty($archiveList['data'])) {
			foreach ($archiveList['data'] as $k=>$d) {
				$attachmentInfo = M("Attachment")->getAttachmentByArchiveID($d['id']);
				$archiveList['data'][$k]['attachment'] = $attachmentInfo;
			}
		}

		return $archiveList;
	}
}
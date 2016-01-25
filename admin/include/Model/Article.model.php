<?php
/**
 * 文章模型
 * by laucen 2013-03-22
 */
class Article extends Archive
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 保存文章
	 * @param $archiveid int 文档ID
	 * @param $content string 文档内容
	 */
	public function saveArticle($archiveid=null,$content=null)
	{
		if (!$archiveid) return false;

		$data = array(
			'archiveid'  => $archiveid,
			'content'    => $content,
			"updatetime" => TIMESTAMP
		);
		return T("article")->add($data);
	}

	/**
	 * 获取文章列表
	 * @param string/array $id 文档ID
	 * @param int $start 分页开始记录号
	 * @param int $length 分页结束记录号
	 * @param array $where 条件数组
	 */
	public function getArticle($id=null,$start=0,$length=0,$state=1,$columnids=array(),$control=null)
	{
		$where = array();
		if ($state !== null) $where['state'] = $state;
		if (is_array($columnids) && !empty($columnids)) $where['columnid'] = array("in", $columnids);
		if ($control) $where['control'] = $control;

		return $this->getArchive($id,$start,$length,$where);
	}

	/**
	 * 获取文档内容
	 * @param int $archiveid 文档id
	 */
	public function getArticleContent($archiveid=null)
	{
		if (!$archiveid) return null;

        $data = T("article")->where(array("archiveid"=>$archiveid))->find();
		return isset($data['content']) ? $data['content'] : null;
	}

	/**
	 * 更新文档内容
	 * @param int $archiveid 文档id
	 */
	public function upArticleContent($archiveid=null,$content=null)
	{
		if (empty($archiveid)) return null;

		$data = array(
			"content"    => $content,
			"updatetime" => TIMESTAMP
        );
		return T("article")->where(array("archiveid"=>$archiveid))->update($data);
	}

	//删除文档
	public function deleteArticle($articleid=null)
	{
		$where = array();
		if (empty($articleid)) return null;

		$where['articleid'] = is_array($articleid) ? array("in",$articleid) : $articleid;
		T("article_index")->where($where)->delete();

		$where['id'] = is_array($articleid) ? array("in",$articleid) : $articleid;
		return T("article")->where($where)->delete();
	}
}
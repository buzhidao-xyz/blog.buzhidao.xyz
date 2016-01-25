<?php
/**
 * 评论逻辑模型
 * baoqing wang
 * 2013-03-28
 */
class CommentControl extends CommonControl
{
	//文档ID
	protected $_archiveid;

	public function __construct($query=null)
	{
		parent::__construct();
		$this->_query = $query;
	}

	//获取姓名
	private function _getUsername()
	{
		$username = q("username");
		if (!FilterHelper::C_CPCharacter($username) || getStringLength($username)<1 || getStringLength($username)>20) {
			$this->ajaxReturn(1,"姓名格式错误!");
		}

		return $username;
	}

	//获取邮箱
	private function _getEmail()
	{
		$email = q("email");
		if ($email && !FilterHelper::C_email($email)) {
			$this->ajaxReturn(1,"邮箱格式错误!");
		}

		return $email;
	}

	//获取站点
	private function _getSite()
	{
		$site = q("site");

		return $site;
	}

	//获取评论内容
	private function _getContent()
	{
		$content = q("content");
		if (getStringLength($content)<1 || getStringLength($content)>200) {
			$this->ajaxReturn(1,"评论内容长度在1-200个字!");
		}

		return $content;
	}

	//获取回复的源评论ID
	private function _getSourceCID()
	{
		$sourcecid = q("sourcecid");
		if ($sourcecid && !FilterHelper::C_int($sourcecid)) {
			$this->ajaxReturn(1,"未知错误!");
		}

		return $sourcecid;
	}

	//获取文档ID
	private function _getArchiveID()
	{
		$archiveid = q("archiveid");
		$archiveid = $archiveid ? $archiveid : $this->_query["params"][0];

		$archiveid ? $this->_archiveid = $archiveid : null;

		if (!FilterHelper::C_int($this->_archiveid) || !M("Archive")->isArchive($this->_archiveid)) $this->ajaxReturn(1,"未知博文!");

		$this->assign("archiveid", $this->_archiveid);
		return $this->_archiveid;
	}

	//AJAX保存评论内容
	public function ajaxSaveComment()
	{
		if (!$this->isAjax()) return false;

		//判断是否在评论时间周期内
		$commentFlag = cookie("commentFlag");
		if ($commentFlag) $this->ajaxReturn(1,"评论时间间隔30秒!");

		$username = $this->_getUsername();
		$email = $this->_getEmail();
		$site = $this->_getSite();
		$content = $this->_getContent();
		$avatar = null;

		$archiveid = $this->_getArchiveID();
		$sourcecid = $this->_getSourceCID();
		$data = array(
			"username" => $username,
			"email"    => $email,
			"site"     => $site,
			"content"  => $content,
			"avatar"   => $avatar,
			"archiveid"=> $archiveid,
			"sourcecid"=> $sourcecid,
			"createtime" => TIMESTAMP
		);
		$return = M("Comment")->saveComment($data);

		//文档评论数加1
		M("Archive")->upArchiveNumInfo($archiveid,"commentnum");

		if ($return) {
			//评论发表时间间隔30秒
			cookie("commentFlag",1,30);
			$this->ajaxReturn(0,"评论发表成功!");
		} else {
			$this->ajaxReturn(1,"评论发表失败!");
		}
	}
	//短名称
	public function asc()
	{
		$this->ajaxSaveComment();
	}
}
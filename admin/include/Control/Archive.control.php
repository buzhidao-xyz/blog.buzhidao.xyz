<?php
/**
 * 文档控制器
 * by buzhidao 2013-03-27
 */
class ArchiveControl extends CommonControl
{
	//控制器名
	protected $_Control = "Archive";

	public function __construct()
	{
		parent::__construct();
	}

	protected function _getArchiveID()
	{
		$archiveid = q("archiveid");

		return $archiveid;
	}

	protected function _getTitle()
	{
		$title = q("title");

		return $title;
	}

	protected function _getTag()
	{
		$tag = q("tag");

		return $tag;
	}

	protected function _getSource()
	{
		$source = q("source");

		return $source;
	}

	protected function _getAuthor()
	{
		$author = q("author");

		return $author;
	}

	protected function _getColumnID()
	{
		$columnid = q("columnid");

		return $columnid;
	}

	protected function _getStatus()
	{
		$status = q("status");

		return $status;
	}

	protected function _getSeotitle()
	{
		$seotitle = q("seotitle");

		return $seotitle;
	}

	protected function _getKeyword()
	{
		$keyword = q("keyword");

		return $keyword;
	}

	protected function _getDescription()
	{
		$description = q("description");

		return $description;
	}

	//获取图片id数组
	public function _getImageids()
	{
		$imageids = q("imageids");
		return $imageids;
	}

	//获取创建时间
	protected function _getPublishtime()
	{
		$publishtime = q("publishtime");
		$publishtime = explode(" ", $publishtime);
		$publishtime1 = explode("-", $publishtime[0]);
		$publishtime2 = explode(":", $publishtime[1]);
		$publishtime = mktime($publishtime2[0],$publishtime2[1],$publishtime2[2],$publishtime1[1],$publishtime1[2],$publishtime1[0]);

		return $publishtime;
	}

	//获取更新时间
	protected function _getUpdatetime()
	{
		$updatetime = q("updatetime");
		if ($updatetime) {
			$updatetime = explode(" ", $updatetime);
			$updatetime1 = explode("-", $updatetime[0]);
			$updatetime2 = explode(":", $updatetime[1]);
			$updatetime = mktime($updatetime2[0],$updatetime2[1],$updatetime2[2],$updatetime1[1],$updatetime1[2],$updatetime1[0]);
		}
		return $updatetime;
	}

	/**
	 * 获取缩略图
	 */
	protected function _getImage()
	{
		$upload = IS_SAE ? new SAEUploadHelper() : new UploadHelper();
		$upload->inputName = "image";
		$upload->thumb = true;
		$upload->thumbMaxWidth = self::$_Width;
		$upload->thumbMaxHeight = self::$_Height;
		$upload->thumbPrefix = "";
		// $upload->thumbRemoveOrigin = true;
		$upload->maxSize  = self::$_ImageSize;
		$upload->savePath =  C("UPLOAD_PATH")."/Image/".date("Ym/d/");
		if(!$upload->upload()) {
			return false;
		} else {
			$info = $upload->getUploadFileInfo();
			$url = str_replace(ROOT_DIR, "", $info[0]['savepath'].$info[0]['savename']);
			return $url;
		}
	}

	//获取文档内容
	protected function _getContent()
	{
		$content = q("content");

		return $content;
	}

	/**
	 * 处理前端提交过来的文章信息
	 * @param array $filter 被过滤的字段 不需要更新的
	 */
	public function dealArchiveSubmit($filter=array())
	{
		$title = $this->_getTitle();
		$tag = $this->_getTag();
		$source = $this->_getSource();
		$author = $this->_getAuthor();
		$columnid = $this->_getColumnID();
		$status = $this->_getStatus();
		$seotitle = $this->_getSeotitle();
		$keyword = $this->_getKeyword();
		$description = $this->_getDescription();
		$image = $this->_getImage();
		$publishtime = $this->_getPublishtime();
		$updatetime = $this->_getUpdatetime();

		return $data = array(
			'title' => $title,
			'tag'   => $tag,
			'source'   => $source,
			'author'   => $author,
			'columnid' => $columnid,
			'status'   => $status,
			'seotitle' => $seotitle,
			'keyword'  => $keyword,
			'description' => $description,
			'image'       => $image,
			'publishtime' => $publishtime,
			'updatetime'  => $updatetime ? $updatetime : TIMESTAMP
		);
	}

	//删除文档 进入回收站
	public function recoverArchive()
	{
		$ArchiveID = $this->_getArchiveID();
		$ArchiveID = explode(",", $ArchiveID);

		$return = M("Archive")->recoverArchive($ArchiveID);
		if ($return) {
			$this->ajaxReturn(0,"删除成功！");
		} else {
			$this->ajaxReturn(1,"删除失败！");
		}
	}

	//还原文档
	public function backArchive()
	{
		$ArchiveID = $this->_getArchiveID();
		$ArchiveID = explode(",", $ArchiveID);

		$return = M("Archive")->backArchive($ArchiveID);
		if ($return) {
			$this->ajaxReturn(0,"还原成功！");
		} else {
			$this->ajaxReturn(1,"还原失败！");
		}
	}

	//彻底删除文档
	public function deleteArchive()
	{
		$ArchiveID = $this->_getArchiveID();
		$ArchiveID = explode(",", $ArchiveID);

		$return = M("Archive")->deleteArchive($ArchiveID);
		if ($return) {
			$this->ajaxReturn(0,"删除成功！");
		} else {
			$this->ajaxReturn(1,"删除失败！");
		}
	}

	//移动文档
	public function moveArchive()
	{
		$ArchiveID = $this->_getArchiveID();
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		if ($action == 'save') {
			$ArchiveID = explode(",", $ArchiveID);
			$ColumnID = $this->_getColumnID();
			$return = M("Archive")->upArchiveColumn($ArchiveID,$ColumnID);
			if ($return) {
				$this->ajaxReturn(0,"移动成功！");
			} else {
				$this->ajaxReturn(1,"移动失败！");
			}
		} else {
			$this->assign("ArchiveID", $ArchiveID);
			$this->assign("columnTree", D("Column")->getColumnTree($this->_Control));
			$this->display("Archive/move.html");
		}
	}

	//标签统计分析
	public function archiveTagAnylize()
	{
		$archiveList = T("archive")->select();
		if (is_array($archiveList)&&!empty($archiveList)) {
			$tags = array();
			$tagc = array();
			foreach ($archiveList as $v) {
				$t = explode(",",$v["tag"]);
				foreach ($t as $tname) {
					$name = strtolower($tname);
					if (!in_array($name,$tags)) {
						$tags[] = $name;
						$tagc[$name] = array("tagname"=>$tname,"usecount"=>1);
					} else {
						$tagc[$name]["usecount"] += 1;
					}
				}
			}
			foreach ($tagc as $t) {
				// dump($tagc);exit;
				$c = T("tag")->where(array("tagname"=>$t["tagname"]))->count();
				if ($c) {
					T("tag")->where(array("tagname"=>$t["tagname"]))->update(array("usecount"=>$t["usecount"]));
				} else {
					T("tag")->add(array("tagname"=>$t["tagname"],"usecount"=>$t["usecount"]));
				}
			}
		}
	}

	//标签解析并入库
	public function parseAndSaveTag($tag=null)
	{
		if (!$tag||empty($tag)) return false;

		$tag = is_array($tag) ? $tag : explode(",",$tag);
		foreach ($tag as $name) {
			$c = T("tag")->where(array("tagname"=>$name))->count();
			if ($c) {
				T("tag")->where(array("tagname"=>$name))->update(array("usecount"=>array("aeq",1)));
			} else {
				T("tag")->add(array("tagname"=>$name,"usecount"=>1));
			}
		}

		return true;
	}
}
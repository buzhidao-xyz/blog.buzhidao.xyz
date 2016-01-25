<?php
/**
 * 内容模型控制器
 * by wbq 2012-9-6
 */
class CTModelControl extends CommonControl
{
    //控制器
    protected $_Control = 'CTModel';

    private $_CTModel;

    public function __construct()
    {
        parent::__construct();

        if (!$this->_CTModel) $this->_CTModel = M('CTModel');
    }

    //主入口
    public function index(){}

    //获取单页页面id
    public function _getSinglePageID()
    {
        $singlepageid = q("singlepageid");

        return $singlepageid;
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

    protected function _getFilename()
    {
        $filename = q("filename");

        return $filename;
    }

    protected function _getTemplate()
    {
        $template = q("template");

        return $template;
    }

    protected function _getAuthor()
    {
        $author = q("author");

        return $author;
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

    protected function _getPublishtime()
    {
        $publishtime = q("publishtime");
        $publishtime = explode(" ", $publishtime);
        $publishtime1 = explode("-", $publishtime[0]);
        $publishtime2 = explode(":", $publishtime[1]);
        $publishtime = mktime($publishtime2[0],$publishtime2[1],$publishtime2[2],$publishtime1[1],$publishtime1[2],$publishtime1[0]);

        return $publishtime;
    }

    /**
     * 获取缩略图
     */
    protected function _getImage()
    {
        $upload = new UploadHelper();
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

    //获取模型名称
    public function getName()
    {
        return q("name");
    }

    //获取模型描述
    public function getDescription()
    {
        return q("description");
    }

    //获取模型表名
    public function getTable()
    {
        return q("table");
    }

    //获取模型控制器名
    public function getControl()
    {
        return q("control");
    }

    //获取前台可调用字段
    public function getUseFields()
    {
        return q("usefields");
    }

    /**
     * 处理前端提交过来的单页页面信息
     * @param array $filter 被过滤的字段 不需要更新的
     */
    private function _dealSinglePageSubmit($filter=array())
    {
        $title = $this->_getTitle();
        $tag = $this->_getTag();
        $author = $this->_getAuthor();
        $status = $this->_getStatus();
        $seotitle = $this->_getSeotitle();
        $keyword = $this->_getKeyword();
        $description = $this->_getDescription();
        $filename = $this->_getFilename();
        $template = $this->_getTemplate();
        $image = $this->_getImage();
        $publishtime = $this->_getPublishtime();

        $content = $this->_getContent();

        $data = array(
            'title' => $title,
            'tag'   => $tag,
            'author'   => $author,
            'status'   => $status,
            'seotitle' => $seotitle,
            'keyword'  => $keyword,
            'description' => $description,
            'filename' => $filename,
            'template' => $template,
            'content'  => $content,
            'updatetime'  => TIMESTAMP
        );
        if ($image) $data['thumbimage'] = $image;
        if (!in_array("publishtime", $filter)) $data['publishtime'] = $publishtime;

        return $data;
    }

    //单页模型
    public function singlePage()
    {
        list($start,$length) = $this->getPages();
        $dataList = M("CTModel")->getSinglePage(null,$start,$length);
        $this->assign("total", $dataList['total']);
        $this->assign("dataList", $dataList['data']);

        $this->display("ColumnModel/singlePage.html");
    }

    //增加单页模型
    public function addSinglePage()
    {
        $this->assign("accessStatus",1);
        $this->display("ColumnModel/addSinglePage.html");
    }

    //保存单页模型
    public function saveSinglePage()
    {
        $data = $this->_dealSinglePageSubmit();
        $singlepageid = M("CTModel")->saveSinglePage($data);
        if ($singlepageid) {
            $NextOperation = array(
                array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=CTModel/editSinglePage&singlepageid='.$singlepageid)
            );
            $this->assign("NextOperation", $NextOperation);
            $this->display("Common/success.html");
        } else {
            $this->display("Common/error.html");
        }
    }

    //编辑单页页面
    public function editSinglePage()
    {
        $this->assign("accessStatus",1);
        $singlepageid = $this->_getSinglePageID();
        $singlepageInfo = M("CTModel")->getSinglePage($singlepageid);

        $this->assign("singlepageInfo", $singlepageInfo['data'][0]);
        $this->display("ColumnModel/editSinglePage.html");
    }

    //保存编辑的页面信息
    public function saveEditSinglePage()
    {
        $singlepageid = $this->_getSinglePageID();
        $data = $this->_dealSinglePageSubmit(array('publishtime'));
        $return = M("CTModel")->saveEditSinglePage($singlepageid,$data);
        if ($return) {
            $NextOperation = array(
                array('name'=>'查看修改', 'link'=>__APP__.'/index.php?s=CTModel/editSinglePage&singlepageid='.$singlepageid)
            );
            $this->assign("NextOperation", $NextOperation);
            $this->display("Common/success.html");
        } else {
            $this->display("Common/error.html");
        }
    }

    //删除单页页面
    public function deleteSinglePage()
    {
        $singlepageid = $this->_getSinglePageID();
        $return = M("CTModel")->deleteSinglePage($singlepageid);
        if ($return) {
            $this->showMessage("单页页面删除成功！",1);
        } else {
            $this->showMessage("单页页面删除失败！",0);
        }
    }

    //内容模型管理
    public function ColumnModel()
    {
        $ColumnModelList = M("CTModel")->ColumnModelList();

        $this->assign("total", $ColumnModelList['total']);
        $this->assign("dataList", $ColumnModelList['data']);

        $this->display("ColumnModel/index.html");
    }

    //添加内容模型
    public function addColumnModel()
    {
        $this->assign("accessStatus",1);
        $this->display("ColumnModel/newModel.html");
    }

    //保存新内容模型
    public function saveColumnModel()
    {
        $name = $this->getName();
        $description = $this->getDescription();
        $table = $this->getTable();
        $control = $this->getControl();
        $usefields = $this->getUseFields();

        $data = array(
            'name' => $name,
            'description' => $description,
            'table' => $table,
            'control' => $control,
            'usefields' => $usefields,
            'createtime' => TIMESTAMP
        );

        $return = M("CTModel")->saveColumnModel($data);
        if ($return) {
            $this->showMessage("新增内容模型成功！",1);
        } else {
            $this->showMessage("新增内容模型失败！",0);
        }
    }

    //模型字段
    public function ModelField()
    {
        $this->display("ColumnModel/ModelField.html");
    }

    //更新内容模型
    public function UpdateModel()
    {
        $this->display("ColumnModel/ModelField.html");
    }

    //获取模型的模板
    public function getCTModelTemplate()
    {
        $columnmodel = q("columnmodel");
        $columnModelInfo = M("CTModel")->ColumnModelList($columnmodel);

        $this->ajaxReturn(0,'',array(
            'template_index' => $columnModelInfo['data'][0]['template_index'],
            'template_list' => $columnModelInfo['data'][0]['template_list'],
            'template_body' => $columnModelInfo['data'][0]['template_body']
        ));
    }
}
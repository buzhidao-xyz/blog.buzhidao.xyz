<?php
// +----------------------------------------------------------------------
// | Laugh CMS 
// +----------------------------------------------------------------------
// | Copyright (c) 
// +----------------------------------------------------------------------
// | Licensed 
// +----------------------------------------------------------------------
// | Author: buzhidao <luochuan.wang@gmail.com>
// +----------------------------------------------------------------------
// $Id: Page.class.php 2012-11-21 $

class Page {
    // 分页栏每页显示的页数
    public $rollPage = 10;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow	;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');
    // 默认分页变量名
    protected $varPage;

    protected $_header_need = 0;

    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = empty($parameter) ? '' : $parameter;
        $this->varPage = C('VAR_PAGE') ? C('VAR_PAGE') : 'page' ;
        if(!empty($listRows)) {
            $this->listRows = intval($listRows);
        }
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
            $this->_header_need = 1;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     * 前台分页显示输出数组
     * @access public
     */
    public function getFrontPageArray() {
        if(0 == $this->totalRows) return '';
        $p = $this->varPage;
        $nowCoolPage = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
       
        $parse = parse_url($url);
        
        //定义path和query
        $parse['path'] = isset($parse['path'])&&$parse['path']!="/" ? $parse['path'] : "/";
        $parse['query'] = isset($parse['query']) ? $parse['query'] : "s=".CONTROL."/".ACTION;
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        ($params) && $url .= "&";
        
        $result = array(
            'firstpage' => 	array('row' => '1', 'href' => $url.$p."=1"),
            'endpage' 	=> 	array('row' => $this->totalPages, 'href' => $url.$p."=".$this->totalPages),
            'prev' 		=> 	array('row' => '', 'href' => ''),
            'next' 		=> 	array('row' => '', 'href' => ''),
            'prevXpage' => 	array('row' => '', 'href' => ''),
            'nextXpage' => 	array('row' => '', 'href' => ''),
            'page' 		=>	array(),
        );
        
        $upRow   = $this->nowPage-1; //上一页
        $downRow = $this->nowPage+1; //下一页

        if ($upRow>0) $result['prev'] = array('row' => $upRow, 'href' => $url.$p."=".$upRow);

        if ($downRow <= $this->totalPages) $result['next'] = array('row' => $downRow, 'href' => $url.$p."=".$downRow);

        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                	$result['page'][$page] = $url.$p."=".$page;
                }else{
                    break;
                }
            }else{
                $result['page'][$page] = '';
            }
        }
        $result['totalpage'] = $this->totalPages;
        return $result;
    }

    /**
     * 后台分页显示输出数组
     * @access public
     */
    public function getAdminPageArray() {
        if(0 == $this->totalRows) return '';
        $p = $this->varPage;
        $this->rollPage = 11;
        $nowCoolPage = ceil($this->nowPage/$this->rollPage);
        $parameter = '';
        if (is_array($parameter)) {
            foreach ($this->parameter as $k=>$v) {
                $parameter .= '&'.$k.'='.$v;
            }
        }
        $request_uri = request_uri();
        $url  =  $request_uri.(strpos($request_uri,'?')?'':"?").$parameter;

        $parse = parse_url($url);

        //定义path和query
        $parse['path'] = isset($parse['path'])&&$parse['path']!="/" ? $parse['path'] : "/";
        $parse['query'] = isset($parse['query']) ? $parse['query'] : "s=".CONTROL."/".ACTION;
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        ($params) && $url .= "&";
        
        $result = array(
			'firstpage' => 	array('row' => '1', 'href' => $url.$p."=1"),
			'endpage' 	=> 	array('row' => $this->totalPages, 'href' => $url.$p."=".$this->totalPages),
			'prev' 		=> 	array('row' => '', 'href' => ''),
			'next' 		=> 	array('row' => '', 'href' => ''),
            // 'prevXpage' => 	array('row' => '', 'href' => ''),
            // 'nextXpage' => 	array('row' => '', 'href' => ''),
			'page' 		=>	array(),
        );

        $upRow   = $this->nowPage-1; //上一页
        $downRow = $this->nowPage+1; //下一页

        if ($upRow>0) $result['prev'] = array('row' => $upRow, 'href' => $url.$p."=".$upRow);

        if ($downRow <= $this->totalPages) $result['next'] = array('row' => $downRow, 'href' => $url.$p."=".$downRow);

        $linkPage = "";
        for($i=1;$i<=$this->totalPages;$i++) {
        	$parr[] = $i;
        }
        $yu = $this->totalPages - $this->nowPage;
        if($this->nowPage >6 && $yu > 4) {
        	$newparr = array_slice($parr, array_search(($this->nowPage-5),$parr),11);
        } else if($this->nowPage >6 && $yu <= 4) {
        	$newparr = array_slice($parr, -11);
        } else if($this->nowPage <=6) {
        	$newparr = array_slice($parr, 0, 11);
        }
    	for($i=$newparr[0];$i<=$newparr[count($newparr)-1];$i++){
	        $result['page'][$i] = ($i!=$this->nowPage) ? ($url.$p."=".$i) : '';
	    }
	    $result['totalpage'] = $this->totalPages;
        if ($this->_header_need) header("location:".$result['endpage']['href']);
       	return $result;
    }
}
<?php
/**
 * 微信接口
 * baoqing wang
 * 2013-11-5
 * 验证URL接口调用 $this->wxValid();
 * 获取access_token $this->wxAccessToken();
 */
class WeixinControl extends BaseControl
{
	//类名
	private $_ClassName = "WeixinControl";

	//随机字符串TOKEN
	private $_token = "szsoehiincwxsystem";
	//AppID
	private $_AppID = "wx8606a7d419a58d03";
	//AppSecret
	private $_AppSecret = "7df2a27401ca7510bcccdd47551af819";

	//session前缀
	private $_session_prefix = "wx_";
	//session过期时间
	private $_session_expire = 1200;

	//接口调用全局错误码说明
	private $_error_code_explain = array(
		'-1'    => '系统繁忙',
		'0'     => '请求成功',
		'40001' => '获取access_token时AppSecret错误，或者access_token无效',
		'40002' => '不合法的凭证类型',
	);

	//微信post过来的xml数据对象
	private $_xmlDataObject = null;

	//返回内容 随机
	private $_content = array(
		'微信平台建设中！',
		'企业外包服务（ITO）',
		'企业管理软件（索惠E8）',
		'企业管理插件（索惠X-EIS）',
		'联系电话 : 0512-86669098',
		'公司地址 : 苏州市工业园区苏州大道西8号中银惠龙大厦2003室',
		'E-mail: SALES@SOEHI.COM',
		'<a href="http://www.soehi.com/">官方网站</a>'
	);

	//消息xml模板
	private $_xmlTpl = array(
		"text"  => "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
				    </xml>",
		"image" => "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Image>
					<MediaId><![CDATA[%s]]></MediaId>
					</Image>
					</xml>"
	);

	//菜单key绑定处理
	protected $_menuKeyFunc = array(
		"WEIXIN_MENU_ERP" => "MenuERP",
		"WEIXIN_MENU_SFS_IOS" => "MenuSFSIOS",
		"WEIXIN_MENU_SFS_ANDROID" => "MenuSFSANDROID",
		"WEIXIN_MENU_SFS_JAVA" => "MenuSFSJAVA",
		"WEIXIN_MENU_SFS_NET" => "MenuSFSNET",
		"WEIXIN_MENU_SFS_PHP" => "MenuSFSPHP"
	);

	//测试图片
	private $_image_test = "image_test_1.jpg";

	//错误日志文件
	private $_errorlogfile = "data/log/wxerror.log";

	//上传多媒体文件类型
	private $_mediatype = array("image","voice","video","thumb");

	public function __construct()
	{
		parent::__construct();
		// $this->wxValid();
	}

	//微信响应主URL接口
	public function index()
	{
		//检查签名 是否合法的微信请求
		if (!$this->checkSignature()) {
			echo "404 ERROR";
			return false;
		}

		//微信消息
    	$this->wxMessage();
	}

	//微信服务器验证valid
	public function wxValid()
	{
		$echostr = $_REQUEST["echostr"];

		if ($this->checkSignature()) {
			echo $echostr;
			exit;
		}
	}

	//检查加密签名 是否合法的微信请求
	private function checkSignature()
	{
		$signature = $_REQUEST["signature"];
		$timestamp = $_REQUEST["timestamp"];
		$nonce = $_REQUEST["nonce"];

		$tmpArray = array($this->_token,$timestamp,$nonce);
		sort($tmpArray);

		$tmpStr = implode($tmpArray);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取ACCESS_TOKEN
	 * 获取之后存储到session 过期时间为20分钟
	 */
	private function wxAccessToken()
	{
		$access_token = $this->_session("access_token");
		if ($access_token) {
			return $access_token;
		} else {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->_AppID."&secret=".$this->_AppSecret;

	        import("Lib.ORG.HttpClient");
			$HttpClient = new HttpClient();
			$HttpClient->_url = $url;
			$HttpClient->_https = true;
			$output = $HttpClient->HttpGet();

			$weixinArray = json_decode($output,true);
			if (!isset($weixinArray['access_token'])) {
				//接口返回错误 记录日志
				$this->_logerror($weixinArray['errcode'],$weixinArray['errmsg']);
				return false;
			} else {
				$this->_session("access_token", $weixinArray['access_token']);
				return $weixinArray['access_token'];
			}
		}
	}

	//接收微信事件和用户信息postdata并作出相应响应
	private function wxMessage()
	{
		//获取XML类型数据 微信post过来的数据
		$xmlData = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : null;
		if (!empty($xmlData)){
			//解析XML数据为XML数据对象
			$this->_xmlDataObject = new SimpleXMLElement($xmlData, LIBXML_NOCDATA);

			//判断消息或事件 处理
			switch ($this->_xmlDataObject->MsgType) {
				case "text":
				case "image":
				case "voice":
				case "video":
					$this->wxMessageResponse();
					break;
				//响应位置
				case "location":
					$this->wxLocationResponse();
					break;
				//链接消息
				case "link":
					$this->wxLinkResponse();
					break;
				//响应事件
				case "event":
					$this->wxEvtResponse();
					break;
				default:
					$this->wxMessageResponse();
					break;
			}
		} else {
			echo null; exit;
		}
	}

	//响应文本/图片/声音/视频消息
	private function wxMessageResponse()
	{
    	$xmlDataObject = $this->_xmlDataObject;

    	$msgContent = $xmlDataObject->Content;
    	if (empty($msgContent)) {
    		echo null;exit;
    	}

		if ($msgContent == "1") {
			$content = '<a href="http://www.soehi.com/">官方网站</a>';
			$this->textMsgResponse($content);
		} else if ($msgContent == "2") {
			$content = 'ERP平台建设中！';
			$this->textMsgResponse($content);
		} else if ($msgContent == "3") {
			$imageid = array();
			$this->imageMsgResponse($imageid);
		}
	}

	//响应位置消息
	private function wxLocationResponse()
	{
		$this->textMsgResponse();
	}

	//响应链接消息
	private function wxLinkResponse()
	{
		$this->textMsgResponse();
	}

	//响应事件
	private function wxEvtResponse()
	{
		$xmlDataObject = $this->_xmlDataObject;

		switch ($xmlDataObject->Event) {
			case "CLICK":
				$this->MenuClickEvent();
				break;
			case "subscribe":
				$this->subscribeEvent();
				break;
			case "unsubscribe":
				break;
			default:
				break;
		}
	}

	//订阅事件响应
	private function subscribeEvent()
	{
    	$content = "欢迎关注索惠信息官方微信平台！\r回复1:访问公司官网\r回复2:查询ERP信息\r回复3:最新图文资讯";
		$this->textMsgResponse($content);
	}

    //响应菜单点击事件
    public function MenuClickEvent()
    {
		$xmlDataObject = $this->_xmlDataObject;
		$EventKey = $xmlDataObject->EventKey;
		$EventKey = (string)$EventKey;
		//判断绑定的方法是否存在
		if (isset($this->_menuKeyFunc[$EventKey]) && method_exists($this->_ClassName, $this->_menuKeyFunc[$EventKey])) {
			$menuFunc = $this->_menuKeyFunc[$EventKey];
			$this->$menuFunc();
		}
    }

    //菜单ERP 点击事件
    public function MenuERP()
    {
		$this->textMsgResponse();
    }

    //菜单SERVICE 子菜单IOS 点击事件
    public function MenuSFSIOS()
    {
    	$content = "IOS软件平台建设中！";
		$this->textMsgResponse($content);
    }

    //菜单SERVICE 子菜单ANDROID 点击事件
    public function MenuSFSANDROID()
    {
    	$content = "ANDROID软件平台建设中！";
		$this->textMsgResponse($content);
    }

    //菜单SERVICE 子菜单JAVA 点击事件
    public function MenuSFSJAVA()
    {
    	$content = "JAVA软件平台建设中！";
		$this->textMsgResponse($content);
    }

    //菜单SERVICE 子菜单NET 点击事件
    public function MenuSFSNET()
    {
    	$content = ".NET软件平台建设中！";
		$this->textMsgResponse($content);
    }

    //菜单SERVICE 子菜单PHP 点击事件
    public function MenuSFSPHP()
    {
    	$content = "PHP软件平台建设中！";
		$this->textMsgResponse($content);
    }

    //回复文本消息 text
    private function textMsgResponse($content=null)
    {
    	$msgType = "text";
    	$xmlDataObject = $this->_xmlDataObject;

		$fromUsername = $xmlDataObject->FromUserName;
		$toUsername = $xmlDataObject->ToUserName;
		$msgTpl = $this->_xmlTpl[$msgType];
		
		$content = $content ? $content : $this->_content[rand(0,7)];
		$xmlResult = sprintf($msgTpl, $fromUsername, $toUsername, TIMESTAMP, $msgType, $content);
		echo $xmlResult;
    }

    //回复图片消息 image
    private function imageMsgResponse($imageid=array())
    {
    	if (!is_array($imageid) || empty($imageid)) return false;

    	$msgType = "image";
    	$xmlDataObject = $this->_xmlDataObject;

    	$fromUsername = $xmlDataObject->FromUserName;
		$toUsername = $xmlDataObject->ToUserName;
		$msgTpl = $this->_xmlTpl[$msgType];
		
		$imageid = $imageid[0];
		$xmlResult = sprintf($msgTpl, $fromUsername, $toUsername, TIMESTAMP, $msgType, $imageid);
		echo $xmlResult;
    }

    //创建菜单
    public function MenuCreate()
    {
    	$access_token = $this->wxAccessToken();
    	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;

    	$menuArray = array(
    		"button" => array(
    			array(
    				"type" => "view",
    				"name" => urlencode("官方网站"),
    				"url"  => "http://www.soehi.com/"
    			),
    			array(
    				"type" => "click",
    				"name" => "ERP",
    				"key"  => "WEIXIN_MENU_ERP"
    			),
    			array(
    				"name" => "SERVICE",
    				"sub_button" => array(
    					array(
    						"type" => "click",
		    				"name" => "IOS",
		    				"key"  => "WEIXIN_MENU_SFS_IOS"
    					),
    					array(
    						"type" => "click",
		    				"name" => "ANDROID",
		    				"key"  => "WEIXIN_MENU_SFS_ANDROID"
    					),
    					array(
    						"type" => "click",
		    				"name" => "JAVA",
		    				"key"  => "WEIXIN_MENU_SFS_JAVA"
    					),
    					array(
    						"type" => "click",
		    				"name" => ".NET",
		    				"key"  => "WEIXIN_MENU_SFS_NET"
    					),
    					array(
    						"type" => "view",
		    				"name" => "Mobile",
    						"url"  => "http://www.soehi.com/soehi/admin/index.php?s=Mobile/index"
    					)
    				)
    			)
    		)
    	);

        import("Lib.ORG.HttpClient");
		$HttpClient = new HttpClient();
		$HttpClient->_url = $url;
		$HttpClient->_https = true;
		$HttpClient->_postdata = urldecode(json_encode($menuArray));
		$output = $HttpClient->HttpPost();

		$weixinArray = json_decode($output,true);
		if (!empty($weixinArray)&&$weixinArray['errcode']==0) {
			echo "OK";
		} else {
			dump($weixinArray);exit;
			//接口返回错误 记录日志
			$this->_logerror($weixinArray['errcode'],$weixinArray['errmsg']);
			return false;
		}
    }

    //删除菜单
    public function MenuDelete()
    {

    }

    //上传图片
    public function wxImageUpload()
    {
    	$type = "image";
    	$mediafile = C("WX_DIR")."/".$this->_image_test;
    	$this->wxMediaUpload($type,$mediafile);
    }

    /**
     * 上传多媒体文件 image/voice/video/thumb
     * @param $type 多媒体文件类型 image/voice/video/thumb
     * @param $file 多媒体文件 绝对路径
     * @param $thumb 音乐/视频文件的缩略图
     */
    private function wxMediaUpload($type=null,$mediafile=null,$thumb=null)
    {
    	if (!in_array($type, $this->_mediatype) || !$mediafile) return false;

    	$access_token = $this->wxAccessToken();
    	$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$type;

    	import("Lib.ORG.HttpClient");
		$HttpClient = new HttpClient();
		$HttpClient->_url = $url;
		$HttpClient->_postdata = array(
			"media" => "@".$mediafile
		);
		$output = $HttpClient->HttpUpload();

		$weixinArray = json_decode($output,true);
		if (!empty($weixinArray)&&!isset($weixinArray['errcode'])) {
			$data = array(
				'path' => $mediafile,
				'type' => $weixinArray['type'],
				'mediaid' => $weixinArray['media_id'],
				'expiretime' => 252000,
				'createtime' => $weixinArray['created_at']
			);
			$sql = "insert into wx_media values('".$data['path']."','".$data['type']."','".$data['mediaid']."',".$data['expiretime'].",".$data['createtime'].")";
			T()->exec($sql);
		} else {
			//接口返回错误 记录日志
			$this->_logerror($weixinArray['errcode'],$weixinArray['errmsg']);
			echo $weixinArray['errcode'];exit;
		}
    }

	//微信session存取
	private function _session($name=null,$value='')
	{
		if (empty($name)) return null;

		$name = $this->_session_prefix.$name;
		$return = session($name,$value,$this->_session_expire);
		return $return;
	}

	//记录接口调用错误日志
	private function _logerror($errorcode=null,$errormsg=null)
	{
		if (empty($errorcode)) return false;

		$explain = isset($this->_error_code_explain[$errorcode]) ? $this->_error_code_explain[$errorcode] : null;

		$data = array(
			'errorcode' => $errorcode,
			'errormsg'  => $errormsg,
			'explain'   => $explain,
			'logtime'   => TIMESTAMP
		);

		$log = "[error] - ".$errorcode." | ".$errormsg." | ".$explain." - [".TIMESTAMP."]\r\n";

		$file = "data/log/wxerror.log";
		$content = file_exists($file) ? file_get_contents($file) : null;
		file_put_contents($file, $content.$log);

		return true;
	}
}
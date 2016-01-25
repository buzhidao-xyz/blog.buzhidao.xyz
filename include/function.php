<?php
/**
 * assign方法 注册全局变量 模板解析用
 * @param $key 全局数组 键值
 * @param $value 全局数组 数据值
 * @return null
 */
function assign($key, $value)
{
	global $data;

	$data[$key] = $value;
}

/**
 * 获取N位随机字符串
 * 字符串取值0-9 a-z之间
 * @param $n 要获取的随机字符串长度(1-100) 默认值6
 */
function getRandStrs($n=6,$type=2)
{
    $return = '';
	$totalStr = array(
        0 => '123456789',
        1 => 'abcdefghijklmnopqrstuvwxyz',
        2 => '123456789abcdefghijklmnopqrstuvwxyz',
    );
    
    $n = preg_match('/^[1-9][0-9]{0,1}$/', $n) ? $n : 0;
    $l = strlen($totalStr[$type])-1;
    for ($i= 0; $i< $n; $i++) {
        $return .= $totalStr[$type]{rand(0,$l)};
    }

	return $return;
}

/**
 * 分析函数返回值 ecode
 * @param $ecode model处理返回值号
 * @return true/false true:model返回错误并返回错误notice false:model正确处理
 */
function eCode($ecode)
{
    global $ecodes;

    if (is_array($ecode)) return false;

    $exp = "/^1[0-9]{3}$/";
    return preg_match($exp, $ecode)?$ecodes[$ecode]:'';
}

/**
 * 读取文件内容
 * @param 要读取的文件
 * @param 文件内容
 */
function getFile($f)
{
    return file_exists($f) ? file_get_contents($f) : false;
}

/**
 * 截取字符串 用指定编码来解决中文断字问题
 * @param $string 要截取的字符串
 * @param $start 开始截取的位置
 * @param $length 要截取的长度
 * @param $encoding 入口编码 默认值 UTF-8
 * @param $outcoding string 出口编码 默认值 GBK
 * @param $type int 截取方式 0单一字符截取 1双字符截取
 * @param $tag int 是否转义实体字符 默认0不转义 1转义
 */
function msbstr($string, $start, $length, $encoding='UTF-8', $outcoding='GBK', $type=0)
{
    $return = null;

    switch ($type) {
        case 0:
            $return = mb_substr($string, $start, $length, $encoding);
        break;
        case 1:
            $return = iconv($outcoding, $encoding, substr(iconv($encoding, $outcoding, $string), $start, $length));
        break;
        default:
        break;
    }

    return $return;
}

//截取字符串
function htmlSubString($content,$maxlen=300,$ext="..."){
    $contentString = $content;
    //把字符按HTML标签变成数组。
    $content = preg_split("/(<[^>]+?>)/si",$content, -1,PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
    $wordrows=0;   //中英字数
    $outstr="";     //生成的字串
    $wordend=false;   //是否符合最大的长度
    $beginTags=0;   //除<img><br><hr>这些短标签外，其它计算开始标签，如<div*>
    $endTags=0;     //计算结尾标签，如</div>，如果$beginTags==$endTags表示标签数目相对称，可以退出循环。
    //print_r($content);
    foreach($content as $value){
        if (trim($value)=="") continue;   //如果该值为空，则继续下一个值

        if (strpos(";$value","<")>0) {
            //如果与要载取的标签相同，则到处结束截取。
            if (trim($value)==$maxlen) {
                $wordend=true;
                continue;
            }

            if ($wordend==false) {
                $outstr.=$value;
                if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<br([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)) {
                    $beginTags++; //除img,br,hr外的标签都加1
                }
            } else if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
                $endTags++;
                $outstr.=$value;
                if ($beginTags==$endTags && $wordend==true) break;   //字已载完了，并且标签数相称，就可以退出循环。
            } else {
                if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<br([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)) {
                    $beginTags++; //除img,br,hr外的标签都加1
                    $outstr.=$value;
                }
            }
        } else {
            if (is_numeric($maxlen)) {   //截取字数
                $curLength=getStringLength($value);
                $maxLength=$curLength+$wordrows;
                if ($wordend==false) {
                    if ($maxLength>$maxlen) {   //总字数大于要截取的字数，要在该行要截取
                        $outstr.=subString($value,0,$maxlen-$wordrows).$ext;
                        $wordend=true;
                    } else {
                        $wordrows=$maxLength;
                        $outstr.=$value;
                    }
                }
            } else {
                if ($wordend==false) $outstr.=$value;
            }
        }
    }
    //循环替换掉多余的标签，如<p></p>这一类
    while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is",$outstr)){
        $outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is","strip_empty_html",$outstr);
    }
    //把误换的标签换回来
    if (strpos(";".$outstr,"[html_")>0){
        $outstr=str_replace("[html_<]","<",$outstr);
        $outstr=str_replace("[html_>]",">",$outstr);
    }

    //echo htmlspecialchars($outstr);
    return $outstr;
}

//去掉多余的空标签
function strip_empty_html($matches){
    $arr_tags1=explode(" ",$matches[1]);
    if ($arr_tags1[0]==$matches[2]){   //如果前后标签相同，则替换为空。
        return "";
    }else{
        $matches[0]=str_replace("<","[html_<]",$matches[0]);
        $matches[0]=str_replace(">","[html_>]",$matches[0]);
        return $matches[0];
   }
}

//取得字符串的长度，包括中英文。
function getStringLength($text){
    if (function_exists('mb_substr')) {
        $length=mb_strlen($text,'UTF-8');
    } elseif (function_exists('iconv_substr')) {
        $length=iconv_strlen($text,'UTF-8');
    } else {
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
        $length=count($ar[0]);
    }
    return $length;
}

/***********按一定长度截取字符串（包括中文）*********/
function subString($text, $start=0, $limit=12) {
    if (function_exists('mb_substr')) {
        $more = (mb_strlen($text,'UTF-8') > $limit) ? TRUE : FALSE;
        $text = mb_substr($text, 0, $limit, 'UTF-8');
        return $text;
    } elseif (function_exists('iconv_substr')) {
        $more = (iconv_strlen($text,'UTF-8') > $limit) ? TRUE : FALSE;
        $text = iconv_substr($text, 0, $limit, 'UTF-8');
        //return array($text, $more);
        return $text;
    } else {
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
        if(func_num_args() >= 3) {
            if (count($ar[0])>$limit) {
                $more = TRUE;
                $text = join("",array_slice($ar[0],0,$limit));
            } else {
                $more = FALSE;
                $text = join("",array_slice($ar[0],0,$limit));
            }
        } else {
            $more = FALSE;
            $text = join("",array_slice($ar[0],0));
        }
        return $text;
    }
}

/**
 * 打印调试函数 浏览器友好的变量输出
 * @param $data 要输出的变量
 */
function dump($var, $flag=0, $echo=true, $label=null, $strict=true) {
    if ($flag) {
        echo "<pre>";print_r($var);return null;
    }

    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else {
        return $output;
    }
}

/**
 * 获得浏览当前页面的用户的 IP 地址
 * @return ip
 */
function getIp() {
    static $ip = NULL;
    if ($ip !== NULL) return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos =  array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip   =  trim($arr[0]);
    } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}

//格式化ip为无符号整型数
function ip2longs($ip)
{
    if (!$ip) return null;
    return sprintf("%u",ip2long($ip));
}

//整型数格式化ip
function longs2ip($long)
{
    if (!$long) return null;
    return long2ip($long);
}

/**
 * 系统错误日志记录
 * @param $str 错误信息
 */
function Errorlog($str) {
	$log = "data/log/access.log";
	if (file_exists($log)) {
		if (filesize($log) >= 500 * 1024) {
			$log1 = str_replace(".log", "_".date("YmdHis", TIMESTAMP).".log", $log);

			@copy($log, $log1);
			file_put_contents($log, "");
		}
	}
    
	$ip = getIp();
    $access_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $referer_url = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$access_url;
	$logstr = mkdate()." | ".$str." <br>访问:<font color=blue>".$access_url."</font> <br>来源:<font color=green>".$referer_url."</font>(";

	file_put_contents($log, "^".substr(php_uname(), 0, 20)." | ".$logstr."<a href='http://www.ip138.com/ips.asp?ip=".$ip."&action=2' target=_blank>".$ip."</a>)\$\n", FILE_APPEND);
}

/**
 * 计算程序执行时间
 * @param $begin_time 开始时间 microtime
 * @param $end_time 结束时间 microtime
 * @return $runtime 精确到第八位小数
 */
function runtime($begin_time, $end_time)
{
	$begin_time = explode(" ",$begin_time);
	$end_time = explode(" ",$end_time);

	$runtime = $end_time[1] - $begin_time[1] + $end_time[0] - $begin_time[0];

	return number_format($runtime, 8, ".", "");
}

/**
 * session方法 存取
 * @param $sessionname string session名
 * @param $sessionvalue mixed session值 默认为'' 值为null则删除该session
 * @param $expiretime int session过期时间 单位秒 默认30分钟 1800秒
 */
function session($sessionname,$sessionvalue='',$expiretime=1800)
{
    $return = true;
    $expiretime = $expiretime ? $expiretime : C("SESSION_EXTIME");
    $sessionname = C("SESSION_ENCRYPT")."_".$sessionname;
    $session = &$_SESSION;

    //清除session
    if ($sessionvalue === null) {
        if (isset($session[$sessionname])) {
            unset($session[$sessionname]);
        }
    } else if (!$sessionvalue) {
        //获取session
        $sessionObj = isset($session[$sessionname]) ? $session[$sessionname] : array();
        if (!empty($sessionObj)) {
            if (TIMESTAMP <= ($sessionObj['createtime']+$sessionObj['expiretime'])) {
                $return = $sessionObj['value'];
                //更新session值创建时间
                $session[$sessionname]['createtime'] = TIMESTAMP;
            } else {
                if (isset($session[$sessionname])) {
                    unset($session[$sessionname]);
                }
                $return = null;
            }
        } else {
            $return = null;
        }
    } else {
        //设置session值
        $session[$sessionname] = array(
            'value' => $sessionvalue,
            'createtime' => TIMESTAMP,
            'expiretime' => $expiretime
        );
    }

    return $return;
}

/**
 * cookie方法 存取
 */
function cookie($cookiename,$cookievalue='',$time=0)
{
    $return = true;
    $cookiename = C("COOKIE_ENCRYPT")."_".$cookiename;
    $time = TIMESTAMP + $time;

    if ($cookievalue === null) {
        setcookie($cookiename,$cookievalue,-1);
        unset($_COOKIE[$cookiename]);
    } else if (!$cookievalue) {
        $return = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
    } else {
        setcookie($cookiename,$cookievalue,$time);
        $_COOKIE[$cookiename] = $cookievalue;
    }

    return $return;
}

/**
 * 压缩输出方法 gzip压缩函数
 * @param $content mixed 要压缩的页面内容
 */
function ob_gzip($content=null)
{
    if(!headers_sent() && // 如果页面头部信息还没有输出
    extension_loaded("zlib") && // 而且zlib扩展已经加载到PHP中
    strstr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip")){ //而且浏览器说它可以接受GZIP的页面
        //为准备压缩的内容贴上"此页已压缩"的注释标签，然后用zlib提供的gzencode()函数执行级别为9的压缩，这个参数值范围是0-9,0表示无压缩，9表示最大压缩，当然压缩程度越高越费CPU.
        $content = gzencode($content." 此页已压缩", 9);

        //然后用header()函数给浏览器发送一些头部信息，告诉浏览器这个页面已经用GZIP压缩过了！
        header("Content-Encoding: gzip");
        header("Vary: Accept-Encoding");
        header("Content-Length: ".strlen($content));
    }

    return $content; //返回压缩的内容
}

//-----------------------数据库操作方法-----------------------//

/**
 * 初始化DB操作类
 * @param $table string 表名
 * @param $id int 连接id
 */
function T($table=null,$id=0)
{
    global $DBOBJECTIVE;
    
    $DBOBJECTIVE[$id]::$_table = $table ? $table : '';
    
    return $DBOBJECTIVE[$id];
}

//-----------------------单字符名方法-----------------------//

/**
 * 获取变量GET
 * @param $param 变量名
 */
function g($param)
{
    $return = isset($_GET[$param]) ? $_GET[$param] : '';
    $types = array('string','integer','double');
    if (in_array(gettype($return), $types)) {
        $return = FilterHelper::F_htmlentities(trim($return));
    }

    return $return;
}

/**
 * 获取变量POST
 * @param $param 变量名
 */
function p($param)
{
    $return = isset($_POST[$param]) ? $_POST[$param] : '';
    $types = array('string','integer','double');
    if (in_array(gettype($return), $types)) {
        $return = FilterHelper::F_htmlentities(trim($return));
    }

    return $return;
}

/**
 * 获取变量(GET/POST通用)
 * @param $param 变量名
 */
function q($param)
{
    $return = isset($_REQUEST[$param]) ? $_REQUEST[$param] : '';
    $types = array('string','integer','double');
    if (in_array(gettype($return), $types)) {
        $return = FilterHelper::F_htmlentities(trim($return));
    }

    return $return;
}

//实例化控制器类
function D($class)
{
    if(empty($class)) return false;

    static $_control = array();
    if(isset($_control[$class])) return $_control[$class];

    $class .= "Control";
    $control = new $class();
    $_control[$class] = $control;

    return $control;
}

/**
 * 实例化数据模型类
 */
function M($class)
{
    if(empty($class)) return false;

    static $_model = array();
    if(isset($_model[$class])) return $_model[$class];

    $model = new $class();
    $_model[$class] = $model;

    return $model;
}

/**
 * 实例化数据模型类
 */
function N($class)
{
    if(empty($class)) return false;

    static $_model = array();
    if(isset($_model[$class])) return $_model[$class];

    $model = new $class();
    $_model[$class] = $model;

    return $model;
}

//-------------------------时间方法-------------------------//

/**
 * 获取格式化之后的时间
 * @param $time int UNIX时间戳 默认为1取当前时间
 * @param $type string d:返回日期 t:返回时间 默认Null
 * @return $datetime
 */
function mkdate($time=1,$type=null)
{
    $return = null;

    $time = $time === 1 ? TIMESTAMP : $time;
    if (!$time) return null;

    switch ($type) {
        case 'd':
            $return = date("Y-m-d", $time);
        break;
        case 'z':
            $return = date("Y年m月d日 H点i分s秒", $time);
            break;
        case 'u':
            $return = date("H:i:s m/d/Y", $time);
            break;
        default:
            $return = date("Y-m-d H:i:s", $time);
        break;
    }

    return $return;
}

//获取上周一时间戳
function lastMonday()
{
  $t = time()-((date("w")?date("w"):7)-1)*86400;
  return mktime(0,0,0,date("m", $t),date("d", $t),date("Y", $t))-7*24*3600;
}

//获取上周日时间戳
function lastSunday()
{
  $t = time()+(7-(date("w")?date("w"):7))*86400;
  return mktime(23,59,59,date("m", $t),date("d", $t),date("Y", $t))-7*24*3600;
}

//获取本周一时间戳
function thisMonday()
{
  $t = time()-((date("w")?date("w"):7)-1)*86400;
  return mktime(0,0,0,date("m", $t),date("d", $t),date("Y", $t));
}

//获取本周日时间戳
function thisSunday()
{
  $t = time()+(7-(date("w")?date("w"):7))*86400;
  return mktime(23,59,59,date("m", $t),date("d", $t),date("Y", $t));
}

//获取$_SERVER['REQUEST_URI']值
function request_uri()
{
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
    } else {
        if (isset($_SERVER['argv'])) {
            $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
        } else {
            if (isset($_SERVER['QUERY_STRING'])) {
                $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
            } else {
                $uri = $_SERVER['PHP_SELF'];
            }
        }
    }
    
    return $uri;
}

//计算文件大小
function formatBytes($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i <= 3; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}

//检查网址是否有http或https并添加
function httpType($site=null)
{
    if (!$site) return false;
    $flag = preg_match("/^(http[s]?:\/\/)/i",$site);

    return $flag ? $site : "http://".$site;
}
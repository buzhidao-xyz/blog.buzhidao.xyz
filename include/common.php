<?php
/**
 * 前端工程所需配置项及方法
 * by laucen 2012-10-09
 */

/**
 * 导入所需的类库 同java的Import
 * 本函数有缓存功能
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolen
 */
function import($class, $baseUrl = '', $ext='.class.php') {
    static $_file = array();
    $class = str_replace(array('.', '#'), array('/', '.'), $class);
    if ('' === $baseUrl && false === strpos($class, '/')) {
        // 检查别名导入
        return alias_import($class);
    }
    if (isset($_file[$class . $baseUrl]))
        return true;
    else
        $_file[$class . $baseUrl] = true;
    $class_strut = explode('/', $class);
    if (empty($baseUrl)) {
        if (in_array(strtolower($class_strut[0]), array('helper', 'lib'))) {
            // Lib 第三方公共类库 com 企业公共类库
            $baseUrl = __INC__;
        } else {
        	//加载其他项目应用类库
            //$class = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
            $baseUrl = C('INCLUDE_DIR') . '/';
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl .= '/';
    $classfile = $baseUrl . $class . $ext;
    if (!class_exists(basename($class),false)) {
        // 如果类不存在 则导入类库文件
        return require_cache($classfile);
    }
}

// 快速定义和导入别名
function alias_import($alias, $classfile='') {
    static $_alias = array();
    if (is_string($alias)) {
        if(isset($_alias[$alias])) {
            return require_cache($_alias[$alias]);
        }elseif ('' !== $classfile) {
            // 定义别名导入
            $_alias[$alias] = $classfile;
            return;
        }
    }elseif (is_array($alias)) {
        $_alias   =  array_merge($_alias,$alias);
        return;
    }
    return false;
}

// 优化的require_once
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

// 区分大小写的文件存在判断
function file_exists_case($filename) {
    if (is_file($filename)) {
        return true;
    }
    return false;
}

// 快速导入第三方框架类库
// 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
// 并且默认都是以.php后缀导入
function vendor($class, $baseUrl = '', $ext='.php') {
    if (empty($baseUrl))
        $baseUrl = VENDOR_PATH;
    return import($class, $baseUrl, $ext);
}

// 去除代码中的空白和注释
function strip_whitespace($content) {
    $stripStr = '';
    //分析php源码
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

// 循环创建目录
function mk_dir($dir, $mode = 0777) {
    if (is_dir($dir) || @mkdir($dir, $mode, true))
        return true;
    if (!mk_dir(dirname($dir), $mode))
        return false;
    return @mkdir($dir, $mode);
}

/**
 * 取分页数据，返回数组
 * @param $total int 总的记录数
 * @param $pagesize int 每页显示记录数
 * @param $flag int 0后台分页数组 1前台分页数组 默认0
 * @param $parameter array 分页跳转的参数
 */
function getPage($total, $pagesize=20, $flag=0, $parameter='')
{
    import('Lib.ORG.Page');
    $pageObj = new Page($total, $pagesize, $parameter);
    if ($flag === 0) return $pageObj->getAdminPageArray();
    if ($flag === 1) return $pageObj->getFrontPageArray();
}

//格式化二维数组加入AutoIndex
function DataListAutoIndex($dataList=array())
{
    if (empty($dataList)) return false;
    $n = 1;
    foreach ($dataList as $k=>$d) {
        $dataList[$k]['AutoIndex'] = $n;
        $n++;
    }

    return $dataList;
}

/**
 * 汉字转拼音
 * @param string $_String 要转码的汉字
 * @param string $_Code 字符编码 默认gb1232编码 随意设置(例UTF-8)则为utf-8编码
 */
function Pinyin($_String, $_Code='gb2312')
{
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
    "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
    "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
    "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
    "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
    "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
    "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
    "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
    "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
    "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
    "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
    "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
    "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
    "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
    "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
    "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";

    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
    "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
    "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
    "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
    "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
    "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
    "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
    "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
    "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
    "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
    "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
    "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
    "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
    "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
    "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
    "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
    "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
    "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
    "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
    "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
    "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
    "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
    "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
    "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
    "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
    "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
    "|-10270|-10262|-10260|-10256|-10254";

    $_TDataKey = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : _Array_Combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);

    if($_Code != 'gb2312') $_String = _U2_Utf8_Gb($_String);
    $_Res = '';
    for($i=0; $i<strlen($_String); $i++)
    {
        $_P = ord(substr($_String, $i, 1));
        if($_P>160) {
            $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536;
        }
        $_Res .= _Pinyin($_P, $_Data);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}

function _Pinyin($_Num, $_Data)
{
    if ($_Num>0 && $_Num<160 ) return chr($_Num);
    elseif ($_Num<-20319 || $_Num>-10247) return '';
    else {
        foreach($_Data as $k=>$v) {
            if($v<=$_Num) break;
        }
        return $k;
    }
}

function _U2_Utf8_Gb($_C)
{
    $_String = '';
    if ($_C < 0x80) $_String .= $_C;
    elseif ($_C < 0x800) {
        $_String .= chr(0xC0 | $_C>>6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000) {
        $_String .= chr(0xE0 | $_C>>12);
        $_String .= chr(0x80 | $_C>>6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C>>18);
        $_String .= chr(0x80 | $_C>>12 & 0x3F);
        $_String .= chr(0x80 | $_C>>6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GB2312', $_String);
}

/*
 * 函数名称：ipCity
 * 参数说明：$userip——用户IP地址
 * 函数功能：PHP通过IP地址判断用户所在城市
 */
function ipCity($userip,$charset='utf-8') {
    //IP数据库路径 QQ IP数据库 纯真版
    $dat_path = C("PUBLIC_PATH").'/dat/IPCity.dat';
    //判断IP地址是否有效
    if(!preg_match("/^([0-9]{1,3}.){3}[0-9]{1,3}$/", $userip)){
        return false;
    }
    //打开IP数据库
    if(!$fd = @fopen($dat_path, 'rb')){
        return 'IP data file not exists or access denied';
    }

    //explode函数分解IP地址，运算得出整数形结果
    $userip = explode('.', $userip);
    $useripNum = $userip[0] * 16777216 + $userip[1] * 65536 + $userip[2] * 256 + $userip[3];

    //获取IP地址索引开始和结束位置
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);

    $useripbegin = implode('', unpack('L', $DataBegin));
    if($useripbegin < 0) $useripbegin += pow(2, 32);

    $useripend = implode('', unpack('L', $DataEnd));
    if($useripend < 0) $useripend += pow(2, 32);

    $useripAllNum = ($useripend - $useripbegin) / 7 + 1;
    $BeginNum = 0;
    $EndNum = $useripAllNum;

    $userip1num = null; $userip2num = null;
    //使用二分查找法从索引记录中搜索匹配的IP地址记录
    while ($userip1num>$useripNum || $userip2num<$useripNum) {
        $Middle= intval(($EndNum + $BeginNum) / 2);
        //偏移指针到索引位置读取4个字节
        fseek($fd, $useripbegin + 7 * $Middle);
        $useripData1 = fread($fd, 4);
        if(strlen($useripData1) < 4) {
            fclose($fd);
            return 'File Error';
        }
        //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
        $userip1num = implode('', unpack('L', $useripData1));
        if($userip1num < 0) $userip1num += pow(2, 32);
        //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
        if($userip1num > $useripNum) {
            $EndNum = $Middle;
            continue;
        }

        //取完上一个索引后取下一个索引
        $DataSeek = fread($fd, 3);
        if(strlen($DataSeek) < 3) {
            fclose($fd);
            return 'File Error';
        }

        $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
        fseek($fd, $DataSeek);
        $useripData2 = fread($fd, 4);
        if(strlen($useripData2) < 4) {
            fclose($fd);
            return 'File Error';
        }

        $userip2num = implode('', unpack('L', $useripData2));
        if($userip2num < 0) $userip2num += pow(2, 32);
        //找不到IP地址对应城市
        if($userip2num < $useripNum) {
            if($Middle == $BeginNum) {
                fclose($fd);
                return 'No Data';
            }
            $BeginNum = $Middle;
        }
    }

    $useripFlag = fread($fd, 1);
    if($useripFlag == chr(1)) {
        $useripSeek = fread($fd, 3);
        if(strlen($useripSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $useripSeek = implode('', unpack('L', $useripSeek.chr(0)));
        fseek($fd, $useripSeek);
        $useripFlag = fread($fd, 1);
    }

    $useripAddr1 = null; $useripAddr2 = null;
    if($useripFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if(strlen($AddrSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }

        $useripFlag = fread($fd, 1);
        if($useripFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }

        while(($char = fread($fd, 1)) != chr(0))
            $useripAddr2 .= $char;
        $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
        fseek($fd, $AddrSeek);
        while(($char = fread($fd, 1)) != chr(0))
            $useripAddr1 .= $char;
    } else {
        fseek($fd, -1, SEEK_CUR);
        while(($char = fread($fd, 1)) != chr(0))
            $useripAddr1 .= $char;
        $useripFlag = fread($fd, 1);
        if($useripFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while(($char = fread($fd, 1)) != chr(0)){
            $useripAddr2 .= $char;
        }
    }

    fclose($fd);

    //返回IP地址对应的城市结果
    if(preg_match('/http/i', $useripAddr2)) {
        $useripAddr2 = '';
    }

    $useripaddr = "$useripAddr1 $useripAddr2";
    $useripaddr = preg_replace('/CZ88.Net/is', '', $useripaddr);
    $useripaddr = preg_replace('/^s*/is', '', $useripaddr);
    $useripaddr = preg_replace('/s*$/is', '', $useripaddr);
    if(preg_match('/http/i', $useripaddr) || $useripaddr == '') {
        $useripaddr = 'No Data';
    }
    
    $useripaddr = @iconv('GBK',$charset."//IGNORE",$useripaddr);
    $useripaddr = preg_match("/(保留地址)$/",$useripaddr) ? "" : $useripaddr;

    return $useripaddr;
}

/**
 * 获取浏览器请求头信息
 */
function getHeaders()
{
    if (!function_exists('getallheaders'))   
    {  
        function getallheaders()   
        {  
           foreach ($_SERVER as $name => $value)   
           {  
               if (substr($name, 0, 5) == 'HTTP_')   
               {  
                   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;  
               }  
           }  
           return $headers;  
        }  
    }

    $headers = getallheaders();
    
    return $headers;
}

/**
 * 获取Gravatar头像
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array())
{
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val)
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}
<?php
/**
 * 验证码生成控制器 生成五位验证码
 * by wbq 2011-12-20
 */
class VCode
{
    //控制器
    static protected $_control = 'VCode';
    
    //默认的验证码图片宽度 高度
    static private $_width = 60;
    static private $_height = 23;

    //验证码长度
    static private $_length = 5;

    static private $_code;

    //session名
    static private $_SessionName = 'vcode';

    //验证码字符选择区间
    static private $_codes = array(
        0 => '0123456789',
        1 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        2 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789'
    );

    static private $_back_color = array(
        array('0XFF','0XFF','0XFF'),
        array('0XFF','0XF5','0XEE'),
        array('0XCA','0XE0','0XF6'),
        array('0XFC','0XF8','0XE3'),
        array('0XEB','0XF9','0XE6'),
    );

    static private $_front_color = array(
        array('0XFF','0X30','0X30'),
        array('0XFF','0X00','0X00'),
        array('0XB8','0X86','0X0B'),
        array('0X00','0X00','0XCD'),
        array('0X00','0X8B','0X00'),
        array('0X33','0X33','0X33'),
        array('0X12','0X34','0X56'),
        array('0X4B','0X00','0X82'),
        array('0Xff','0X63','0X47'),
        array('0X99','0X00','0XFF'),
    );

    static private $_front_pot_color = array(
        array('0XFF','0XFF','0XFF'),
        array('0X66','0X66','0X66'),
        array('0X12','0X34','0X56'),
        array('0X83','0X6F','0XFF'),
        array('0X02','0X65','0XCF'),
    );

    static private $_front_line_color = array(
        array('0XFF','0XFF','0XFF'),
        array('0X33','0X33','0X33'),
        array('0X02','0X65','0XCF'),
        array('0Xff','0X63','0X47'),
        array('0X00','0X00','0XCD'),
    );
    
    public function __construct($SessionName=null)
    {
        self::$_SessionName .= $SessionName ? "_".$SessionName : "";
    }
    
    /**
     * 获取图片宽度
     */
    static private function getW()
    {
        $w = q('w');
        return preg_match("/^[1-9][0-9]{1,2}$/", $w) ? $w : self::$_width;
    }
    
    /**
     * 获取图片高度
     */
    static private function getH()
    {
        $h = q('h');
        return preg_match("/^[1-9][0-9]{1,2}$/", $h) ? $h : self::$_height;
    }

    static private function getCode()
    {
        $code = null;
        $codes = self::$_codes[0];
        $len = strlen($codes) - 1;
        
        for ($i= 0; $i< self::$_length; $i++) {
            $code .= $codes{rand(0,$len)};
        }

        self::$_code = $code;
        self::_setVcode();
        return $code;
    }
    
    /**
     * 生成验证码
     */
    static public function index()
    {
        $width = self::getW();
        $height = self::getH();
        $code = self::getCode();
    	
    	$img = imagecreate($width, $height);
    	
    	$w = imagesx($img);
    	$lpx = ($w - (strlen($code)*8.0))/2;
    	
    	$background = self::_getBackColor($img);
    	$frontcolor = self::_getFrontColor($img);
    	
    	imagerectangle($img,0,0,($width-1),($height-1),$frontcolor);
    	
        self::_drawPot($img,$width,$height);
        self::_drawLine($img,$width,$height,1,2);
    	
    	imagestring($img,5,$lpx,3,$code,$frontcolor);
    	
    	imagegif($img);
    	imagedestroy($img);
    }

    /**
     * 画噪点 随机
     * @param $img object 创建的图像对象句柄
     * @param $width int 图像宽度
     * @param $height int 图像高度
     */
    static private function _drawPot($img,$width=0,$height=0)
    {
        $potcount = $width+$height;
        for ($i= 1; $i< $potcount; $i++) {
            $potcolor = self::_getFrontPotColor($img);
            imagesetpixel($img,rand(1,$width),rand(1,$height),$potcolor);
        }
    }

    /**
     * 画噪线 随机
     * @param $img object 创建的图像对象句柄
     * @param $width int 图像宽度
     * @param $height int 图像高度
     * @param $m int 横向噪线条数 默认为0
     * @param $n int 纵向噪线条数 默认为0
     */
    static private function _drawLine($img,$width=0,$height=0,$m=0,$n=0)
    {
        $linecolor = self::_getFrontLineColor($img);

        for ($i= 0; $i< $m; $i++) {
            imageline($img,1,rand(1,($height-1)),($width-1),rand(1,($height-1)),$linecolor);
        }
        for ($i= 0; $i< $n; $i++) {
            imageline($img,rand(1,($width-1)),1,rand(1,($width-1)),($height-1),$linecolor);
        }
    }

    /**
     * 生成背景色
     */
    static private function _getBackColor($img=null)
    {
        $i = rand(0,count(self::$_back_color)-1);
        return imagecolorallocate($img,self::$_back_color[$i][0],self::$_back_color[$i][1],self::$_back_color[$i][2]);
    }

    /**
     * 生成前景色
     */
    static private function _getFrontColor($img=null)
    {
        $i = rand(0,count(self::$_front_color)-1);
        return imagecolorallocate($img,self::$_front_color[$i][0],self::$_front_color[$i][1],self::$_front_color[$i][2]);
    }

    /**
     * 获取噪点颜色
     */
    static private function _getFrontPotColor($img=null)
    {
        $i = rand(0,count(self::$_front_pot_color)-1);
        return imagecolorallocate($img,self::$_front_pot_color[$i][0],self::$_front_pot_color[$i][1],self::$_front_pot_color[$i][2]);
    }

    /**
     * 获取噪线颜色
     */
    static private function _getFrontLineColor($img=null)
    {
        $i = rand(0,count(self::$_front_line_color)-1);
        return imagecolorallocate($img,self::$_front_line_color[$i][0],self::$_front_line_color[$i][1],self::$_front_line_color[$i][2]);
    }

    /**
     * 调用Image库生成验证码
     */
    static public function imageCode()
    {
        import('ORG.Util.Image');
        Image::buildImageVerify();
        self::_setVcode();
    }

    /**
     * 缓存验证码
     */
    static private function _setVcode()
    {
        $code = self::$_code ? md5(self::$_code) : $_SESSION['verify'];
        session(self::$_SessionName, $code);
    }
}

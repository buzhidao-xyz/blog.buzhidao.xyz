<?php
/**
 * 验证码生成控制器 生成五位验证码
 * by wbq 2011-12-20
 */
class VCodeControl extends CommonControl
{
    /**
     * 控制器类名
     */
    static private $_class_name = 'VCode Control';
    
    /**
     * 默认的验证码图片宽度
     */
    static private $_width = 60;
    
    /**
     * 默认的验证码图片高度
     */
    static private $_height = 23;
    
    public function __construct()
    {
        
    }
    
    /**
     * 获取图片宽度
     */
    static private function getW()
    {
        $w = q('w');
        
        return preg_match("/^[1-9][0-9]{1,2}$/", $w)?$w:self::$_width;
    }
    
    /**
     * 获取图片高度
     */
    static private function getH()
    {
        $h = q('h');
        
        return preg_match("/^[1-9][0-9]{1,2}$/", $h)?$h:self::$_height;
    }
    
    /**
     * 生成验证码
     */
    static public function index()
    {
        $width = self::getW();
        $height = self::getH();
        $codes = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
	
    	$len = strlen($codes) - 1;
    	
    	$code1 = rand(0,$len);
    	$code2 = rand(0,$len);
    	$code3 = rand(0,$len);
    	$code4 = rand(0,$len);
    	$code5 = rand(0,$len);
    	$code = $codes{$code1}.$codes{$code2}.$codes{$code3}.$codes{$code4}.$codes{$code5};
    	
    	$_SESSION["vcode"] = $code;
    	
    	$img = imagecreate($width, $height);
    	
    	$w = imagesx($img);
    	$lpx = ($w - (strlen($code)*8.0))/2;
    	
    	$background = imagecolorallocate($img,0xEE,0xEE,0xEE);
    	$strcolor = imagecolorallocate($img,0x33,0x33,0x33);
    	$linecolor = imagecolorallocate($img,0x66,0x66,0x66);
    	
    	imagerectangle($img,0,0,($width-1),($height-1),$strcolor);
    	
    	imageline($img,1,rand(1,($height-1)),($width-1),rand(1,($height-1)),$linecolor);
    	imageline($img,rand(1,($width-1)),1,rand(1,($width-1)),($height-1),$linecolor);
    	imageline($img,rand(1,($width-1)),1,rand(1,($width-1)),($height-1),$linecolor);
    	
    	imagestring($img,5,$lpx,3,$code,$strcolor);
    	
    	imagegif($img);
    	
    	imagedestroy($img);
    }
}

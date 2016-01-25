<?php
/**
 * filter类
 * by laucen 2011-11-16
 */
class FilterHelper
{
    //控制器
    protected $_helper = "Filter";

    public function __construct()
    {
        
    }

    /**
     * 将html字符转成实体
     * @param $string string 字符串
     * @param $flag string 标志
     * @param $encode string 字符串编码
     */
    static public function F_htmlentities($string, $flag=ENT_QUOTES, $encode='UTF-8')
    {
        return htmlentities($string, $flag, $encode);
    }

    /**
     * 检查是否为大于0整数
     * @param int $var 检查变量
     * @return bool
     */
    public static function C_int($var)
    {
        if(empty($var) || !preg_match('/^[1-9]+[0-9]*$/', $var)) return false;
        return true;
    }

    /**
     * 检测是否数字
     * @param $var int 字符
     */
    static public function C_Numeric($var=null)
    {
        $return = is_numeric($var);

        return $return;
    }

    //匹配数字
    static public function C_Number($var=null)
    {
        if (empty($var)) return false;

        $regexp = "/^[0-9]+$/i";
        if (preg_match($regexp, $var)) return true;
        else return false;
    }

    //匹配字母
    static public function C_Character($var=null)
    {
        if (empty($var)) return false;

        $regexp = "/^[a-zA-Z]+$/i";
        if (preg_match($regexp, $var)) return true;
        else return false;
    }

    //匹配数字字母
    static public function C_Number_Character($var=null)
    {
        if (empty($var)) return false;

        $regexp = "/^[a-z0-9A-Z]+$/i";
        if (preg_match($regexp, $var)) return true;
        else return false;
    }

    /**
     * 检查是否正确的邮箱格式
     * @param string $string 变量
     */
    static public function C_email($var=null)
    {
        if (empty($var)) return false;

        $regexp = "/^[a-z0-9A-Z](([a-z0-9A-Z_-]*[\.])*[a-z0-9A-Z])*@([a-z0-9A-Z]+([-][a-z0-9A-Z])*[\.])+[a-z]{2,5}$/i";
        if (preg_match($regexp, $var)) return true;
        else return false;
    }

    //匹配中文汉字
    static public function C_Chinese_Character($var=null)
    {
        if (empty($var)) return false;

        $regexp = "/^[\x{4e00}-\x{9fa5}]+$/u";
        if (preg_match($regexp, $var)) return true;
        else return false;
    }

    //匹配普通字符串
    static public function C_PCharacter($var=null)
    {
        if (empty($var)) return false;

        $regexp = "/^[a-z0-9A-Z](([a-z0-9A-Z_-]|.)*[a-z0-9A-Z])*$/i";
        if (preg_match($regexp, $var)) return true;
        else return false;
    }

    //匹配汉字和普通字符串
    static public function C_CPCharacter($var=null)
    {
        if (empty($var)) return false;

        $regexp = "/^[\x{4e00}-\x{9fa5}a-z0-9A-Z][\x{4e00}-\x{9fa5}a-z0-9A-Z_-]*$/u";
        if (preg_match($regexp, $var)) return true;
        else return false;
    }
}
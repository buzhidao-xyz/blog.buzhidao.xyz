<?php
/**
 * 字体库
 * baoqing wang
 * 2013-12-26
 */
class Font
{
    //控制器
    static protected $_control = 'Font';
    
    public function __construct()
    {
        
    }
    
    public function iconfont()
    {
        echo "@font-face {
                font-family: 'iconfont';
                src: url('iconfont.eot'); /* IE9*/
                src: local('iconfont'), url('iconfont.eot?#iefix') format('embedded-opentype'), /* IE6-IE8 */
                url('iconfont.woff') format('woff'), /* chrome、firefox */
                url('iconfont.ttf') format('truetype'), /* chrome、firefox、opera、Safari, Android, iOS 4.2+*/
                url('iconfont.svg#uxiconfont') format('svg'); /* iOS 4.1- */
            }";
    }
}
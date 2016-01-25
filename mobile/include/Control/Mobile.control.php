<?php
/**
 * 手机web Demo项目
 * baoqing wang
 * 2013-11-20
 */
class MobileControl extends CommonControl
{
	public function __construct()
	{
		parent::__construct();
	}

	//Demo MainPage
	public function index()
	{
		$this->display("Mobile/index.html");
	}

	//gps
	public function gps()
	{
		$this->display("Mobile/gps.html");
	}

	//sign
	public function signin()
	{
		$this->display("Mobile/signin.html");
	}
}
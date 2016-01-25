<?php
class HttpClient
{
	//CURL句柄
	public $_curlHandle = null;

	//请求地址url
	public $_url = null;

	//是否SSL连接 true是 false否
	public $_https = false;

	//post数据 XML/JSON/Array
	public $_postdata = null;

	//是否启用文件上传
	public $_upload = null;

	//请求字符串编码
	public $_encode = "UTF-8";

	//请求头
	private $_header = null;

	//初始化
	public function __construct($url=null,$https=false)
	{
		$this->_url = $url;
		$this->_https = $https;

		$this->_curlHandle = curl_init();
	}

	//模拟GET请求
	public function HttpGet()
	{
		//https请求
		if ($this->_https) $this->SSL();

		curl_setopt($this->_curlHandle, CURLOPT_HEADER, false);

		//获取返回的文件流 不输出到浏览器
		curl_setopt($this->_curlHandle, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($this->_curlHandle, CURLOPT_URL, $this->_url);
		//30S后超时
		curl_setopt($this->_curlHandle, CURLOPT_TIMEOUT, 30);

		$output = curl_exec($this->_curlHandle);
		if ($output === false) {
			trigger_error(curl_error($this->_curlHandle));
		}

		curl_close($this->_curlHandle);

		return $output;
	}

	//模拟POST请求
	public function HttpPost()
	{
		//https请求
		if ($this->_https) $this->SSL();

		//请求头
		curl_setopt($this->_curlHandle, CURLOPT_HEADER, false);
		$this->_header = array("content-type: application/x-www-form-urlencoded; charset=".$this->_encode);
		curl_setopt($this->_curlHandle, CURLOPT_HTTPHEADER, $this->_header);

		//获取返回的文件流 不输出到浏览器
		curl_setopt($this->_curlHandle, CURLOPT_RETURNTRANSFER, true);

		//POST方式
		curl_setopt($this->_curlHandle, CURLOPT_POST, 1);
		//POST数据
		curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS, $this->_postdata);

		curl_setopt($this->_curlHandle, CURLOPT_URL, $this->_url);

		//30S后超时
		curl_setopt($this->_curlHandle, CURLOPT_TIMEOUT, 30);

		$output = curl_exec($this->_curlHandle);
		if ($output === false) {
			trigger_error(curl_error($this->_curlHandle));
		}

		curl_close($this->_curlHandle);

		return $output;
	}

	//上传文件
	public function HttpUpload()
	{
		//获取返回的文件流 不输出到浏览器
		curl_setopt($this->_curlHandle, CURLOPT_RETURNTRANSFER, true);

		//POST方式
		curl_setopt($this->_curlHandle, CURLOPT_POST, 1);
		//启用上传
		// curl_setopt($this->_curlHandle, CURLOPT_UPLOAD, 1);
		//上传文件
		curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS, $this->_postdata);

		curl_setopt($this->_curlHandle, CURLOPT_URL, $this->_url);
		curl_setopt($this->_curlHandle, CURLOPT_HEADER, 0);

		$output = curl_exec($this->_curlHandle);
		if ($output === false) {
			trigger_error(curl_error($this->_curlHandle));
		}

		curl_close($this->_curlHandle);

		return $output;
	}

	//SSL
	private function SSL()
	{
		curl_setopt($this->_curlHandle, CURLOPT_SSLVERSION,3); 
		curl_setopt($this->_curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($this->_curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
	}
}
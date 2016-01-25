<?php
/**
 * 数据库管理控制器
 * by wbq 2011-11-09
 */
class DataBaseControl extends CommonControl
{
    //定义类名
    protected $_Control = 'DataBase';

	//数据库操作句柄
	protected $_link;

	//数据库文件备份存储路径
	protected $_backup_dir = null;

	//显示最近多少条备份记录 默认70
	protected $_show_number = 70;

	//继承父类初始化
	public function __construct()
	{
		set_time_limit(0);
		parent::__construct();

		$this->_dbconfig = C("DB.db0");
		$this->_backup_dir = C("DATABASE_BACKUP_DIR");

		$this->_link = @mysql_connect($this->_dbconfig['host'].":".$this->_dbconfig['port'],$this->_dbconfig['username'],$this->_dbconfig['password']);
		mysql_select_db($this->_dbconfig['database']);
		mysql_query("SET NAMES UTF8");
	}

	//获取数据数组
	public function queryArray($sql=null)
	{
		if (!$sql) return array();

		$result = mysql_query($sql);
		if (!$result || mysql_num_rows($result)==0) return array();

		$return = array();
		while ($row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		}
		return $return;
	}

	//数据库备份
	public function BackUp()
	{
		// $SqlFileName = $this->ComputeSqlFileName();
		$this->assign("backupdir", $this->_backup_dir);

		$dataList = array();
		if (is_dir($this->_backup_dir)) {
			$dh = opendir($this->_backup_dir);
			while (($filename = readdir($dh)) !== false) {
				if (!in_array($filename, array('.','..'))) {
					$fileh = $this->_backup_dir."/".$filename;
					$filemtime = filemtime($fileh);
					$dataList[$filemtime] = array(
						'SqlFileName' => $filename,
						'FileSize'    => formatBytes(filesize($fileh)),
						'CreateTime'  => $filemtime
					);
				}
			}
			krsort($dataList);
			$dataList = array_slice($dataList, 0, $this->_show_number);
		}
		$this->assign("dataList",$dataList);
		$this->assign("total",count($dataList));

		$this->display("DataBase/BackUp.html");
	}

	//计算今天要备份的数据库文件名
	public function ComputeSqlFileName()
	{
		$SqlFileName = $this->_dbconfig['database']."_DataBase_".date("Ymd", TIMESTAMP).".sql";
		
		return $SqlFileName;
	}

	//备份数据库
	public function DataBaseBackup()
	{
		$SqlFileName = $this->ComputeSqlFileName();
		$SqlFile = $this->_backup_dir."/".$SqlFileName;

		$tables = $this->getTables();
		//备份表结构和数据
		$filesql = null;
		$filesql .= $this->DataBaseBackupHeadNote();
		$filesql .= $this->data2sql($tables);

		// file_put_contents($SqlFile, $filesql);
		@$fp = fopen($SqlFile, "w+");
		if ($fp) {
			@flock($fp, 3);
			if(@!fwrite($fp, $filesql)) {
				@fclose($fp);
				$this->showMessage("数据文件无法保存到服务器,请检查目录属性你是否有写的权限!",0);
			} else {
				T("backup")->add(array("SqlFile"=>$SqlFile,"CreateTime"=>TIMESTAMP));
				$this->showMessage("数据成功备份至服务器<br />".$SqlFile,1);
			}
		} else {
			$this->showMessage("无法打开指定的目录文件<br />".$SqlFile."<br />请确定该目录是否存在,或者是否有相应权限!",0);
		}
	}

	//还原数据库
	public function DataBaseRestore()
	{
		//载入升级sql文件
		$SqlFileName = q("SqlFileName");
		$SqlFile = $this->_backup_dir."/".$SqlFileName;
		if (!file_exists($SqlFile)) {
			$this->showMessage("数据库备份文件不存在!",0);
		} else {
			$sql = file_get_contents($SqlFile);

			//分析sql文件并拆分sql语句
			$ret = array();
			$num = 0;
			foreach(explode(";\r\n", trim($sql)) as $query) {
				$ret[$num] = '';
				$queries = explode("\r\n", trim($query));
				foreach($queries as $query) {
					//去掉语句开始是#或者--注释符的语句
					$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
				}
				$num++;
			}
			unset($sql);
			// dump($ret);exit;
			//遍历sql数组
			foreach ($ret as $query) {
				$query = trim($query);
				if ($query) {
					//执行sql语句
					mysql_query($query);
				}
			}

			$this->showMessage("数据库还原成功!还原版本:<br/>".$SqlFileName);
		}
	}

	//生成表头
	public function DataBaseBackupHeadNote()
	{
		$filesql = null;

		$filesql .= "-- ----------------------------------------------------\r\n";
		$filesql .= "-- DataBase Backup ".date("Y-m-d H:i:s", TIMESTAMP)."\r\n";
		$filesql .= "-- ----------------------------------------------------\r\n";

		return $filesql;
	}

	//获取库中所有数据表
	public function getTables()
	{
		$tables = array();

		$sql = "show tables";
		$data = $this->queryArray($sql);
		foreach ($data as $d) {
			$tables[] = $d['Tables_in_'.$this->_dbconfig['database']];
		}

		return $tables;
	}

	/**
	 * 备份表结构
	 * @param $tables array 数据表数组
	 */
	function table2sql($tables=array())
	{
		if (!is_array($tables) || empty($tables)) return null;

		$filesql = null;
		foreach ($tables as $table) {
			//备份表结构
			$filesql .= "--\r\n";
			$filesql .= "-- Create Table ".$table."\r\n";
			$filesql .= "--\r\n";
			$filesql .= "DROP TABLE IF EXISTS ".$table.";\r\n";
			$table_struct = $this->queryArray("SHOW CREATE TABLE ".$table);
			$filesql .= $table_struct[0]['Create Table'].";\r\n";
		}
		
		return $filesql;
	}

	/**
	 * 备份表结构和数据
	 * @param $tables array 数据表数组
	 */
	function data2sql($tables=array())
	{
		if (!is_array($tables) || empty($tables)) return null;

		$filesql = null;
		foreach ($tables as $table) {
			//备份表结构
			$filesql .= "--\r\n";
			$filesql .= "-- Create Table ".$table."\r\n";
			$filesql .= "--\r\n";
			$filesql .= "DROP TABLE IF EXISTS ".$table.";\r\n";
			$table_struct = $this->queryArray("SHOW CREATE TABLE ".$table);
			$filesql .= $table_struct[0]['Create Table'].";\r\n";

			//备份数据
			$data_results = $this->queryArray("SELECT * FROM ".$table);
			foreach ($data_results as $key=>$value) {
				$filesql .= "INSERT INTO `".$table."` VALUES ('";
				$value_array = array_values($value);
				$filesql .= implode("','" , $value_array);
				$filesql .= "');\r\n";
			}
		}
		
		return $filesql;
	}
}
<?php
/**
 * 数据库驱动基类
 * by wbq 2011-12-13
 * edit by buzhidao 2012-12-13
 * 提供读取配置并初始化连接数据库操作，内置部分PDO操作数据库方法，事务操作
 * 只有方法名并直接返回true的方法 子驱动类必须实现
 */
interface DBDriver_Interface
{
    
}
class DBDriver implements DBDriver_Interface
{
    //选择要连接的数据库
    private $dbs;
    //DB连接对象
    static protected $db;
    
    /**
     * 数据库连接信息
     */
    private $host;
    private $port;
    private $username;
    private $password;
    private $database;
    
    static protected $_prefix;   //表前缀
    static protected $_tbf = "#@__";   //表缓存前缀

    //表名
    static public $_table = '';
    //联合查询表
    static public $_join_table = '';
    
    //insert update delete 所影响的记录数 默认值为0
    static protected $_count = 0;

    public function __construct()
    {
        $this->setDBS();
        $this->setHost();
        $this->setPort();
        $this->setUsername();
        $this->setPassword();
        $this->setDatabase();
        $this->setTablePre();
        $this->connect();
    }
    
    /**
     * 设置选择哪个配置项 默认选择配置文件中的第一个库
     * 配置文件 /include/config/database.config.php
     */
    private function setDBS()
    {
        try {
            $this->dbs = C('DB');
            $this->dbs = $this->dbs['db0'];

            if (empty($this->dbs)) {
                throw new MyException("Databse is null", 1);
                exit;
            }
        } catch (MyException $e) {
            echo $e;
        }
    }

    /**
     * 设置连接字符串中的host值
     */
    private function setHost()
    {
        try {
            $this->host = $this->dbs['host'];

            if (!trim($this->host)) {
                throw new MyException("The DataBase Host can't be null", 1);
                exit;
            }
        } catch (MyException $e) {
            echo $e;
        }
    }

    /**
     * 设置连接字符串中的port值
     */
    private function setPort()
    {
        try {
            $this->port = $this->dbs['port'];

            if (!trim($this->port)) {
                throw new MyException("The DataBase port can't be null", 1);
                exit;
            }
        } catch (MyException $e) {
            echo $e;
        }
    }

    /**
     * 设置访问数据库的账号
     */
    private function setUsername()
    {
        try {
            $this->username = $this->dbs['username'];

            if (!trim($this->username)) {
                throw new MyException("The username can't be null", 1);
                exit;
            }
        } catch (MyException $e) {
            echo $e;
        }
    }

    /**
     * 设置访问数据库的账号密码
     */
    private function setPassword()
    {
        try {
            $this->password = $this->dbs['password'];

            if (!trim($this->password)) {
                //throw new MyException("The password can't be null", 3);exit;
            }
        }  catch (MyException $e) {
            echo $e;
        }
    }

    /**
     * 设置要访问的库
     */
    private function setDatabase()
    {
        try {
            $this->database = $this->dbs['database'];

            if (!trim($this->database)) {
                throw new MyException("The database can't be null", 1);
                exit;
            }
        } catch (MyException $e) {
            echo $e;
        }
    }
    
    /**
     * 设置数据库表的表前缀
     */
    private function setTablePre()
    {
        try {
            self::$_prefix = $this->dbs['prefix'];
            
            if (!trim(self::$_prefix)) {
                throw new MyException("The Prefix can't be null", 1);
                exit;
            }
        } catch (MyException $e) {
            echo $e;
        }
    }
    
    //连接数据库
    private function connect()
    {
        try {
            $this->_initConnect($this->host, $this->port, $this->username, $this->password, $this->database);
        } catch(PDOException $e) {
            echo $e;
            die('DB Connect Error!');
        }

        return self::$db;
    }

	static protected function tablePR($sql)
	{
		return str_replace(self::$_tbf, self::$_prefix, $sql);
	}

    /************************************SQL对象分析************************************/
    
    /**
     * 获取orm映射字段值 如果ORM里面配置的key获取该配置的key 没有配置直接返回key
     * @param $key 字段值
     * @param $sepflag1 字段名左符号
     * @param $sepflag2 字段名右符号
     */
    protected function orm($key,$sepflag1="`",$sepflag2="`")
    {
        global $orm;
        if (!$key) return $orm[self::$_table];


        $strAsArray = array(' as ',' As ',' aS ',' AS ');
        if (strpos($key, ' as ') || strpos($key, ' As ') || strpos($key, ' aS ') || strpos($key, ' AS ')) {
            $key = str_replace($strAsArray,' ',$key);
        }


        if (preg_match("/^a\./", $key)) {
            $key = str_replace('a.', '', $key);
            $pos = strpos($key, ' ');
            $alias = $pos !== false ? substr($key, $pos) : '';
            $key = $pos !== false ? substr($key, 0, $pos) : $key;
            if (!is_array($orm) || empty($orm) || !array_key_exists(self::$_table, $orm) || !array_key_exists($key, $orm[self::$_table])) {
                return $alias ? 'a.'.$sepflag1.$key.$sepflag2." AS ".$sepflag1.$alias.$sepflag2 : 'a.'.$sepflag1.$key.$sepflag2;
            } else {
                return $alias ? 'a.'.$sepflag1.$orm[self::$_table][$key].$sepflag2." AS ".$sepflag1.$alias.$sepflag2 : 'a.'.$sepflag1.$orm[self::$_table][$key].$sepflag2;
            }
        }
        if (preg_match("/^b\./", $key)) {
            $key = str_replace('b.', '', $key);
            $pos = strpos($key, ' ');
            $alias = $pos !== false ? substr($key, $pos) : '';
            $key = $pos !== false ? substr($key, 0, $pos) : $key;
            if (!is_array($orm) || empty($orm) || !array_key_exists(self::$_join_table, $orm) || !array_key_exists($key, $orm[self::$_join_table])) {
                return $alias ? 'b.'.$sepflag1.$key.$sepflag2." AS ".$sepflag1.$alias.$sepflag2 : 'b.'.$sepflag1.$key.$sepflag2;
            } else {
                return $alias ? 'b.'.$sepflag1.$orm[self::$_join_table][$key].$sepflag2." AS ".$sepflag1.$alias.$sepflag2 : 'b.'.$sepflag1.$orm[self::$_join_table][$key].$sepflag2;
            }
        }


        $pos = strpos($key, ' ');
        $alias = $pos !== false ? substr($key, $pos) : '';
        $key = $pos !== false ? substr($key, 0, $pos) : $key;
        if (!is_array($orm) || empty($orm) || !array_key_exists(self::$_join_table, $orm) || !array_key_exists($key, $orm[self::$_join_table])) {
            return $alias ? $sepflag1.$key.$sepflag2." AS ".$sepflag1.$alias.$sepflag2 : $sepflag1.$key.$sepflag2;
        } else {
            return $alias ? $sepflag1.$orm[self::$_table][$key].$sepflag2." AS ".$sepflag1.$alias.$sepflag2 : $sepflag1.$orm[self::$_table][$key].$sepflag2;
        }
    }

    
    /**
     * 字段解析
     * @param $field(mixed)
     * 查询某个字段直接传字段名
     * 查询多个字段用,隔开或以数组形式传递参数
     */
    public function field($fields = '*')
    {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        
        foreach ($fields as $k=>$v) {
            if (isset($field)) {
                $field .= ','.$this->orm($v);
            } else {
                $field = strpos($v,'*') !== false ? $v : $this->orm($v);
            }
        }
        
        $this->_field = $field;

        return $this;
    }

    /**
     * where子句构造
     * @param $where = array('field1'=>value1,'field2'=>valuw2,...,'or'=>array('field3'=>value3,'field4'=>value4,...))
     *        between操作 array('field'=>array('between',array(value1,value2)))
     * @param $op 操作符 AND/OR/BETWEEN
     */
    public function where($where=array(),$op='')
    {
        if (empty($where)) return $this;

        if (is_array($where) && !empty($where)) {
            $whereArray = array();
            $whereArray0 = array();
            foreach ($where as $k=>$v) {
                if (is_array($v) && !empty($v)) {
                    switch (strtolower($v[0])) {
                        case 'neq':
                            $w = " ".$this->orm($k)." != '".$v[1]."' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        case 'lt':
                            $w = " ".$this->orm($k)." < '".$v[1]."' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        case 'gt':
                            $w = " ".$this->orm($k)." > '".$v[1]."' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        case 'elt':
                            $w = " ".$this->orm($k)." <= '".$v[1]."' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        case 'egt':
                            $w = " ".$this->orm($k)." >= '".$v[1]."' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        case 'in':
                            $w = " ".$this->orm($k)." IN(".implode(',',$v[1]).") ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        case 'like':
                            $w = " ".$this->orm($k)." LIKE '%".$v[1]."%' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        case 'between':
                            $w = " ".$this->orm($k)." BETWEEN '".$v[1]."' AND '".$v[2]."' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                        default:
                            $w = " ".$this->orm($k)."='".$v."' ";
                            $whereArray[] = $w;
                            if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                            break;
                    }
                } else {
                    $w = " ".$this->orm($k)."='".$v."' ";
                    $whereArray[] = $w;
                    if (strpos($k, "a.")!==false) $whereArray0[] = $w;
                }
            }
            $where = implode(" AND ",$whereArray);
            $where0 = implode(" AND ",$whereArray0);
        }
        $this->_where = empty($where) ? "" : " WHERE ".$where;
        $this->_where0 = empty($where0) ? "" : " WHERE ".$where0;

        return $this;
    }

    /**
     * 排序语句 如果是数组array('key'=>sortway,'key1'=>sortway1...)
     * @param $field string/array 排序字段 
     * @param $orderway string ASC/DESC ASC 升序排列
     */
    public function order($field=null,$way='ASC')
    {
        if (!$field || empty($field) || !$way) return $this;

        if (is_array($field)) {
            foreach ($field as $k=>$v) {
                $sep = $this->_order ? ' , ' : ' ';
                switch($field) {
                    case "a.function":
                        $this->_order .= $sep.' '.$v.'() ';
                        break;
                    default:
                        $this->_order .= $sep.' '.$this->orm($k).' '.strtoupper($v).' ';
                        break;
                }
            }
        } else {
            switch($field) {
                case "a.function":
                    $this->_order = ' '.$way.'() ';
                    break;
                default:
                    $this->_order = ' '.$this->orm($field).' '.strtoupper($way).' ';
                    break;
            }
        }
        $this->_order = ' ORDER BY '.$this->_order.' ';

        return $this;
    }

    //sql语句处理之前要进行的操作
    protected function _before_sql($options)
    {
        $this->_parse_options($options);
    }

    //结束sql语句处理之后要进行的操作
    protected function _after_sql()
    {
        $this->_field = '*';
        $this->_join = '';
        $this->_where = '';
        $this->_where0 = '';
        $this->_order = '';
        $this->_limit = '';
        //$this->sql = null;
    }

    //解析option选项值
    protected function _parse_options($options)
    {

    }

    /************************************增删改查ADUS************************************/

    /**
     * 执行一条sql
     * @param sql语句
     * @return 成功返回true 失败返回false
     * 子类必须实现此方法
     */
    static public function Execute($sql)
    {
        return true;
    }

    /**
     * 执行一条sql语句
     * @param $sql 要执行的语句
     */
    public function exec($sql=null)
    {
        $sql = !empty($sql) ? $sql : $this->sql;
        
        return $this->Execute($sql);
    }

    /**
     * 插入数据到数据库中
     * @param $data 要插入的数据
     * @param $m false:插入一条数据 true:插入多条数据 默认为false
     */
    public function add($data,$m=false)
    {
        if ($m === true) {
            return $this->insertAll($data);
        } else if ($m === false) {
            return $this->insertOne($data);
        }

        return false;
    }

    /**
     * insert插入语法
     * @param $data(mixed)
     * 插入一条记录到数据库
     */
    protected function insertOne($data)
    {
        if (!is_array($data)) return false;
        
        foreach ($data as $k=>$v) {
            if (isset($keys)) {
                $keys .= ",".$this->orm($k);
                if (is_array($v) && $v[0]=="image") {
                    $values .= ",".$v[1];
                } else {
                    $values .= ",'".$v."'";
                }
            } else {
                $keys = $this->orm($k);
                if (is_array($v) && $v[0]=="image") {
                    $values = $v[1];
                } else {
                    $values = "'".$v."'";
                }
            }
        }
        
        $this->sql = "INSERT INTO ".self::$_tbf.self::$_table." (".$keys.") VALUES (".$values.")";
        if ($this->Execute($this->sql)) return $this->GetInsertID();
        else return false;
    }
    
    /**
     * insert插入语法
     * @param $data(mixed)
     * 插入多条记录到数据库
     */
    public function insertAll($data)
    {
        if (!is_array($data)) return false;
        
        foreach ($data as $d) {
            foreach ($d as $k=>$v) {
                if (isset($keys)) {
                    $keys .= ','.$this->orm($k);
                    if (is_array($v) && $v[0]=="image") {
                        $values .= ",".$v[1];
                    } else {
                        $values .= ",'".$v."'";
                    }
                } else {
                    $keys = $this->orm($k);
                    if (is_array($v) && $v[0]=="image") {
                        $values = $v[1];
                    } else {
                        $values = "'".$v."'";
                    }
                }
            }
            
            $key = " (".$keys.") ";
            
            if (isset($value)) {
                $value .= ","." (".$values.") ";
            } else {
                $value = " (".$values.") ";
            }
            unset($keys); unset($values);
        }
        
        $this->sql = "INSERT INTO ".self::$_tbf.self::$_table." ".$key." VALUES ".$value;

        if ($this->Execute($this->sql)) return $this->GetInsertID();
        else return false;
    }
    
    /**
     * 查询一条数据
     * @param $options array 参数数组
     */
    public function find($options=array())
    {
        $this->_before_sql($options);
        $this->sql = "SELECT ".$this->_field." FROM ".self::$_tbf.self::$_table." as a ".$this->_join.$this->_where.$this->_order.$this->_limit;
        $this->_after_sql();
        $return = $this->GetOne($this->sql);
        return $return;
    }

    /**
     * 查询多条数据
     * @param $options array 参数数组
     */
    public function select($options=array())
    {
        $this->_before_sql($options);
        $this->sql = "SELECT ".$this->_field." FROM ".self::$_tbf.self::$_table." as a ".$this->_join.$this->_where.$this->_order.$this->_limit;
        $this->_after_sql();
        $return = $this->GetAll($this->sql);
        return $return;
    }

    /**
     * 计算数据条数
     */
    public function count($options=array())
    {
        return true;
    }

    /**
     * update更新sql操作
     * @param $data(mixed) 要更新的字段的键值数组
     */
    public function update($data,$options=array())
    {
        return true;
    }

    /**
     * delete删除操作
     */
    public function delete($options=array())
    {
        return true;
    }

    /**
     * join字句
     * @param $join 联合查询字符串 
     * @param $flag 联合方式 0左连接 1右连接 默认0
     */
    public function join($join=null,$flag=0)
    {
        return true;
    }

    /**
     * 查找数据
     * @param $start int 数据结果的开始位置偏移 默认从0开始
     * @param $length int 数据结果的长度 默认取1条数据
     * @param $flag string 取范围关键字 top等 默认null
     */
    public function limit($start = 0, $length = 1, $flag = null)
    {
        return true;
    }

    /************************************pdo事务处理************************************/

    //开始事务
    static public function beginTransaction()
    {
        self::$db->beginTransaction();
    }

    //提交事务
    static public function commitTransaction()
    {
        self::$db->commit();
    }

    //回滚事务
    static public function rollBackTransaction()
    {
        self::$db->rollBack();
    }
    
    /**
     * 事务处理 update/delete
     * @param 要执行的sql语句数组 多条sql事务处理
     * @return 返回值 true/false
     */
    static public function Transaction($sql=array())
    {
        if (!is_array($sql) || empty($sql)) return false;
        $sql = self::tablePR($sql);
        
        $this->beginTransaction();
        foreach ($sql as $k=>$v) {
            if (!self::$db->exec($sql)) {
                $this->rollBackTransaction();
                return false;
            }
        }
        $this->commitTransaction();
        return true;
    }

    /************************************DB收尾操作************************************/

	/**
     * 关闭数据库连接
     */
    public function DBClose()
    {
		self::$db = null;
	}

    //获取sql语句 用于打印输出调试
    public function getSql()
    {
        return $this->sql;
    }
}

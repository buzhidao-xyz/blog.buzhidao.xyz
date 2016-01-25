<?php
/**
 * 数据库操作类 执行add delete update select操作
 * by wbq 2011-12-30
 */
class DBpdosqlserver extends DBDriver
{
    protected $_field = '*';
    protected $_join = '';
    protected $_union = '';
    protected $_where = '';
    protected $_group = '';
    protected $_order = '';
    protected $_limit = '';

    protected $_where0 = '';
    
    //sql语句
    protected $sql = null;
    
    static protected $_sql;

    //调用父类初始化数据库连接
    public function __construct()
    {
        parent::__construct();
    }

    /************************************以下方法必须实现************************************/

    //连接数据库
    public function _initConnect($host,$port,$username,$password,$database)
    {
        //pdo_sqlserver connect
        $dsn = "sqlsrv:Server=".$host.",".$port.";Database=".$database;
        if (!(@self::$db = new PDO($dsn, $username, $password, array(PDO::SQLSRV_ATTR_DIRECT_QUERY => true)))) {
            throw new PDOException("The connect is unvaliable", 1);
            exit;
        };
        // self::Execute("SET NAMES UTF8");

        //指定返回数据的格式化格式（带fieldname）
        self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NAMED);
    }

    /**
     * 执行一条sql
     * @param sql语句
     * @return 成功返回true 失败返回false
     */
    static public function Execute($sql)
    {
        $sql = self::tablePR($sql);

        return self::$db->exec($sql) ? true : false;
    }

    /**
     * 获取语句执行所影响的记录数
     * @param $sql 要执行的sql语句
     * @return 执行语句所影响的记录数，为空返回0
     */
    static public function GetCount($sql)
    {
        $sql = self::tablePR($sql);

        $sth = self::$db->prepare($sql);
        $sth->execute();

        return $sth->rowCount();
    }

    /**
     * 从数据库中获取一条记录
     * @param $sql 要执行的查询select语句
     * @return 结果集数组
     */
    static public function GetOne($sql)
    {
        $sql = self::tablePR($sql);

        $sth = self::$db->prepare($sql);
        $sth->execute();
        
        return $sth->rowCount() ? $sth->fetch(0) : array();
    }

    /**
     * 获取需要的全部数据库记录
     * @param $sql 要执行的查询select语句
     * @return 结果集数组
     */
    static public function GetAll($sql)
    {
        $sql = self::tablePR($sql);

        $sth = self::$db->prepare($sql);
        $sth->execute();

        return $sth->rowCount() ? $sth->fetchAll() : array();
    }

    /**
     * 获取最近一次insert操作的id
     * @param Null
     * @return id
     */
    static public function GetInsertID()
    {
        return self::$db->lastInsertId();
    }

    /************************************以上方法必须实现************************************/

    /**
     * 获取orm映射字段值 如果ORM里面配置的key获取该配置的key 没有配置直接返回key
     * @param $key 字段值
     */
    protected function orm($key)
    {
        return parent::orm($key,"[","]");
    }
    
    /**
     * 查询一条数据
     * @param $options array 参数数组
     */
    public function find($options=array())
    {
        $this->_before_sql($options);

        $main_table = $this->_union ? $this->_union : self::$_tbf.self::$_table;
        $this->sql = "SELECT TOP 1 ".$this->_field." FROM ".$main_table." as a ".$this->_join.$this->_where.$this->_group.$this->_order;
        
        $this->_after_sql();
        
        $return = $this->GetOne($this->sql);
        return $return;
    }

    /**
     * 查询多条数据 用row_number()给结果加行号 where条件里row_number取行号区间的方法获取limit数据
     * @param $options array 参数数组
     */
    public function select($options=array())
    {
        $this->_before_sql($options);
        
        $main_table = $this->_union ? $this->_union : self::$_tbf.self::$_table;
        if ($this->_limit) {
            if (!$this->_order) exit('*#SqlServer Limit need Order!!!');

            $this->_limit = $this->_where ? " AND ".$this->_limit : " WHERE ".$this->_limit;
            $_temp_table = " (SELECT *,ROW_NUMBER() OVER(".$this->_order.") AS ROW_NUMBER FROM ".$main_table." as a ".$this->_where0.") ";
        } else {
            $_temp_table = $main_table;
        }
        $this->sql = "SELECT ".$this->_field." FROM ".$_temp_table." as a ".$this->_join.$this->_where.$this->_limit.$this->_group.$this->_order;

        $this->_after_sql();

        $return = $this->GetAll($this->sql);
        return $return;
    }

    /**
     * 计算数据条数
     */
    public function count($options=array())
    {
        $this->_before_sql($options);

        $main_table = $this->_union ? $this->_union : self::$_tbf.self::$_table;
        $this->sql = "SELECT COUNT(".$this->_field.") as ResultRowCount FROM ".$main_table." as a ".$this->_join.$this->_where;
        
        $this->_after_sql();
        $data = $this->GetOne($this->sql);

        return $data['ResultRowCount'];
    }

    /**
     * update更新sql操作
     * @param $data(mixed) 要更新的字段的键值数组
     */
    public function update($data,$options=array())
    {
        if (!is_array($data)) return false;
        
        foreach ($data as $k=>$v) {
            if (isset($ups)) {
                if (is_array($v) && $v[0]=="image") {
                    $ups .= " , ".$this->orm($k)."=".$v[1]." ";
                } else {
                    $ups .= " , ".$this->orm($k)."='".$v."' ";
                }
            } else {
                if (is_array($v) && $v[0]=="image") {
                    $ups = $this->orm($k)."=".$v[1]." ";
                } else {
                    $ups = $this->orm($k)."='".$v."' ";
                }
            }
        }
        
        $this->_before_sql($options);
        $this->sql = "UPDATE ".self::$_tbf.self::$_table." SET ".$ups.$this->_where;
        $this->_after_sql();
        return $this->exec();
    }

    /**
     * delete删除操作
     */
    public function delete($options=array())
    {
        $this->_before_sql($options);
        $this->sql = "DELETE ".$this->_limit." FROM ".self::$_tbf.self::$_table." ".$this->_where.$this->_order;
        $this->_after_sql();
        return $this->exec($this->sql);
    }
    
    /**
     * join字句
     * @param $join 联合查询字符串 
     * @param $flag 联合方式 0左连接 1右连接 2内连接 3外连接 默认0
     */
    public function join($join=null,$flag=0)
    {
        if (!$join) return $this;
        $joinArray = explode(" ", $join);
        self::$_join_table = $joinArray[0] ? $joinArray[0] : $joinArray[1];
        self::$_join_table = str_replace(self::$_tbf, '', self::$_join_table);

        if (!$flag) {
            $join = ' LEFT JOIN '.$join.' ';
        } else if ($flag === 1) {
            $join = ' RIGHT JOIN '.$join.' ';
        } else if ($flag === 2) {
            $join = ' INNER JOIN '.$join.' ';
        } else if ($flag === 3) {
            $join = ' OUTER JOIN '.$join.' ';
        }

        $this->_join = $join;

        return $this;
    }

    /**
     * union联表
     * @param $table 表名
     */
    public function union($table=null)
    {
        if (!$table) return $this;
        $_union_table = self::$_tbf.$table;

        $this->_union = ' (SELECT * FROM '.self::$_tbf.self::$_table.' UNION ALL SELECT * FROM '.$_union_table.') ';

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

    //分组
    public function group($field=null)
    {
        if (!$field || empty($field)) return $this;

        if (is_array($field)&&!empty($field)) {
            foreach ($field as $k=>$v) {
                $sep = $this->_group ? ' , ' : ' ';
                $this->_group .= $sep.' '.$this->orm($v).' ';
            }
        } else {
            $this->_group = ' '.$this->orm($field).' ';
        }
        $this->_group = " GROUP BY ".$this->_group;

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
                $this->_order .= $sep.' '.$this->orm($k).' '.strtoupper($v).' ';
            }
        } else {
            $this->_order = ' '.$this->orm($field).' '.strtoupper($way).' ';
        }
        $this->_order = ' ORDER BY '.$this->_order.' ';

        return $this;
    }
    
    /**
     * 查找数据
     * @param $start 数据结果的开始位置偏移 默认从0开始
     * @param $length 数据结果的长度 默认取1条数据
     */
    public function limit($start = 0, $length = 1, $flag = null)
    {
        if ($flag == "top") {
            $this->_limit = " TOP ".(int)$length." ";
        } else {
            $this->_limit = " (a.ROW_NUMBER BETWEEN ".($start+1)." AND ".($start+$length).") ";
        }
        
        return $this;
    }
}

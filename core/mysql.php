<?php
class Mysql{

	protected $db;
	protected $table;
	protected $return_type;

	private $pdo;
	private $obj;
	private $pamas;

    static $pdos;

	/**
	 * 构造函数
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function __construct($db,$table)
	{
		$this->db           = $db;
		$this->table        = $table;
		$this->return_type  = PDO::FETCH_ASSOC;

		$this->getPdo($db);
	}

	/**
	 * 获取pdo对象
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	private function getPdo($db)
	{
		//获取配置
        global $config;

		$conf = $config['mysql'][$db];

        if(!empty(self::$pdos[$db]))
        {
            $this->pdo = self::$pdos[$db];
        }
        else
        {
		    $this->pdo = self::$pdos[$db] = new PDO('mysql:host='.$conf['host'].';dbname='.$db,$conf['user'],$conf['pass'],$conf['config']);
        }
	}

	/**
	 * where
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function where(array $where)
	{
		if(empty($this->obj['where']))
		{
			$this->obj['where'] = array();
		}

		$keys = array_keys($where);

		foreach($keys AS $k=>$row)
		{
			if(is_numeric($row))
			{
				$this->obj['where'][] = $where[$row];
			}
			else
			{
				$this->obj['where'] += array($row=>$where[$row]);
			}
		}

		return $this->obj;
	}

	/**
	 * field
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function field(array $field)
	{
		$this->obj['field'] = $field;

		return $this->obj;
	}

	/**
	 * limit
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function limit($offset,$limit)
	{
		$this->obj['limit'] = array($offset,$limit);

		return $this->obj;
	}

	/**
	 * group
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function group(array $field)
	{
		$this->obj['group'] = $field;

		return $this->obj;
	}

	/**
	 * order
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function order($field,$type)
	{
		$this->obj['order'] = array($field,$type);

		return $this->obj;
	}

	/**
	 * join
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function join($table,array $on,array $col,$join_type = 'left')
	{
		$this->obj['join'][] = array(
			'table'     =>$table,
			'on'        =>$on,
			'col'       =>$col,
			'join_type' =>$join_type,
		);
		return $this->obj;
	}

	/**
	 * update
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function update(array $where,array $set,$is_sql = FALSE)
	{
		$this->obj['update'] = array(
			'where'     =>$where,
			'set'       =>$set
		);

		$sql = $this->MergerSQL();

		if($is_sql === TRUE)
		{
			return $sql;
		}

		return $this->query($sql,'upd');
	}

	/**
	 * insert
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function insert(array $set,$is_sql = FALSE)
	{
		$this->obj['insert'] = $set;

		$sql = $this->MergerSQL();

		if($is_sql === TRUE)
		{
			return $sql;
		}

		return $this->query($sql,'ins');
	}

	/**
	 * replace
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function replace(array $set,$is_sql = FALSE)
	{
		$this->obj['replace'] = $set;

		$sql = $this->MergerSQL();

		if($is_sql === TRUE)
		{
			return $sql;
		}

		return $this->query($sql,'ins');
	}

	/**
	 * delete
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function delete(array $where,$is_sql = FALSE)
	{
		$this->obj['delete'] = $where;

		$sql = $this->MergerSQL();

		if($is_sql === TRUE)
		{
			return $sql;
		}

		return $this->query($sql,'del');
	}

	/**
	 * bind
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function bind($key,$val)
	{
		$this->pamas[$key] = $val;
	}

	/**
	 * query
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function query($sql,$cols = 'all')
	{
		//预处理
		$std = $this->pdo->prepare($sql);

		//执行
		$std->execute($this->pamas);
		
		$this->pamas = array();

		switch($cols)
		{
			case 'one':$q = 'fetchColumn';  break;
			case 'row':$q = 'fetch';        break;
			case 'all':$q = 'fetchAll';     break;
			case 'del':$q = 'rowCount';     break;
			case 'upd':$q = 'rowCount';     break;
			case 'ins':return $this->pdo->lastInsertId(); break;
			default: throw new \Exception('error query type');break;
		}

		$v = $cols == 'one'?NULL:$this->return_type;

		return $std->$q($v);
	}

	/**
	 * fetchOne
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function fetchOne($obj = NULL,$is_sql = FALSE)
	{
		//削减字段
		$this->obj['field'] = array_slice($this->obj['field'],0,1);;

		if(!empty($obj))
		{
			$this->obj = $obj;
		}

		$sql = $this->MergerSQL();

		if($is_sql === TRUE)
		{
			return $sql;
		}

		return $this->query($sql,'one');
	}

	/**
	 * fetchRow
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function fetchRow($obj = NULL,$is_sql = FALSE)
	{
		if(!empty($obj))
		{
			$this->obj = $obj;
		}

		$sql = $this->MergerSQL();

		if($is_sql === TRUE)
		{
			return $sql;
		}

		return $this->query($sql,'row');
	}

	/**
	 * fetchAll
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function fetchAll($obj = NULL,$is_sql = FALSE)
	{
		if(!empty($obj))
		{
			$this->obj = $obj;
		}

		$sql = $this->MergerSQL();

		if($is_sql === TRUE)
		{
			return $sql;
		}

		return $this->query($sql,'all');
	}

	/**
	 * 组合SQL
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function MergerSQL()
	{
		if(!empty($this->obj))
		{
			//更新
			if(!empty($this->obj['update']))
			{
				$sql = $this->get_update();
				$this->resets();
				return $sql;
			}
			//插入
			elseif(!empty($this->obj['insert']))
			{
				$sql =  $this->get_insert();
				$this->resets();
				return $sql;
			}
			//替换插入
			elseif(!empty($this->obj['replace']))
			{
				$sql =  $this->get_replace();
				$this->resets();
				return $sql;
			}
			//删除
			elseif(!empty($this->obj['delete']))
			{
				$sql =  $this->get_delete();
				$this->resets();
				return $sql;
			}
			//查询
			else
			{
				$sql =  $this->get_select();
				$this->resets();
				return $sql;
			}
		}
	}

	/**
	 * 组合UpdateSQL
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_update()
	{
		$update = array();

		foreach($this->obj['update']['set'] AS $k=>$row)
		{
			if(is_numeric($k))
			{
				$update[] = $row;
			}
			else
			{
				$update[] = $k.' = '.$this->get_keys($row);
			}
		}

		if(!empty($this->obj['update']['where']))
		{
			$where  = $this->get_where($this->obj['update']['where']);
		}
		else
		{
			die('empty update where');
		}

		return 'UPDATE '.$this->db.'.'.$this->table.' SET '.implode(',',$update).' WHERE '.implode(' AND ',$where);
	}

	/**
	 * 组合InsertSQL
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_insert()
	{
		$fil = array();
		$set = array();

		foreach($this->obj['insert'] AS $k=>$row)
		{
			$fil[] = $k;
			$set[] = $this->get_keys($row);
		}

		return 'INSERT INTO '.$this->db.'.'.$this->table.'('.implode(',',$fil).') VALUES('.implode(',',$set).')';
	}

	/**
	 * 组合ReplaceSQL
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_replace()
	{
		$fil = array();
		$set = array();

		foreach($this->obj['replace'] AS $k=>$row)
		{
			$fil[] = $k;
			$set[] = $this->get_keys($row);
		}

		return 'REPLACE INTO '.$this->db.'.'.$this->table.'('.implode(',',$fil).') VALUES('.implode(',',$set).')';
	}

	/**
	 * 组合DeleteSQL
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_delete()
	{
		$where = $this->get_where($this->obj['delete']);

		return 'DELETE FROM '.$this->db.'.'.$this->table.' WHERE '.implode(' AND ',$where);
	}

	/**
	 * 组合SelectSQL
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_select()
	{
		$sql    = '';

		//无条件不允许select*
		if(empty($this->obj['field']))
		{
			die('no select field');
		}

		$sql    .= 'SELECT ? FROM '.$this->db.'.'.$this->table;

		//join
		if(!empty($this->obj['join']))
		{
			$col = array();

			foreach($this->obj['join'] AS $k=>$row)
			{
				foreach($row['col'] AS $ks=>$rows)
				{
					$this->obj['field'][] = $row['table'].'.'.$rows;
				}

				$sql .= ' '.strtoupper($row['join_type']).' JOIN '.$this->db.'.'.$row['table'].' ON '.$this->table.'.'.$row['on'][0].' = '.$row['table'].'.'.$row['on'][1];
			}
		}

		//files
		$sql = str_replace('?',implode(',',$this->obj['field']),$sql);

		//where
		if(!empty($this->obj['where']))
		{
			$where  = $this->get_where($this->obj['where']);
			$sql    .= ' WHERE '.implode(' AND ',$where);
		}

		//group
		if(!empty($this->obj['group']))
		{
			$sql    .= ' GROUP BY '.implode(',',$this->obj['group']);
		}

		//order
		if(!empty($this->obj['order']))
		{
			$sql    .= ' ORDER BY  '.$this->obj['order'][0].' '.$this->obj['order'][1];
		}

		//limit
		if(!empty($this->obj['limit']))
		{
			$sql    .= ' LIMIT '.$this->obj['limit'][0].','.$this->obj['limit'][1];
		}

		return $sql;
	}

	/**
	 * 获得where条件组
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_where($where_input)
	{
		$where = array();

		foreach($where_input AS $k=>$row)
		{
			if(is_numeric($k))
			{
				$where[] = $row;
			}
			else
			{
				$row = $this->get_keys($row);

				if(is_array($row))
				{
					$where[] = $k.' IN ('.implode(',',$row).')';
				}
				else
				{
					$where[] = $k.' = '.$row;
				}
			}
		}

		return $where;
	}

	/**
	 * 判定字符串
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_keys($key)
	{
		if(is_array($key))
		{
			foreach($key AS $k=>$row)
			{
				$key[$k] = $this->get_key($row);
			}

			return $key;
		}
		else
		{
			return $this->get_key($key);
		}
	}

	/**
	 * 判定字符串
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function get_key($key)
	{
		$this->pamas[] = stripslashes($key);

		return '?';
	}

	/**
	 * 重置查询
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function resets()
	{
		$this->obj      = NULL;
	}

	/**
	 * 事务开启
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}

	/**
	 * 事务回滚
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function rollBack()
	{
		return $this->pdo->rollBack();
	}

	/**
	 * 事务提交
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function commit()
	{
		return $this->pdo->commit();
	}
}
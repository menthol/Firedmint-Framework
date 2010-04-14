<?php 
if (!defined('FM_SECURITY')) die();

class mysqlDriver
{
	private $host;
	private $username;
	private $password;
	private $database;
	private $persistent = false;
	private $link = 0;
	private $query = array();
	private $queryId = 0;

	function config($config)
	{
		$this->host       = $config['host'];
		$this->username   = $config['username'];
		$this->password   = $config['password'];
		$this->database   = $config['database'];
		$this->persistent = $config['persistent'];
	}
	
	private function connect()
	{
		if (!$this->link)
		{
			if ($this->persistent)
				$this->link = @mysql_pconnect($this->host,$this->username,$this->password);
			else
				$this->link = @mysql_connect($this->host,$this->username,$this->password);
			
			if (!$this->link)
			{
				log::error("[mysqlDriver] Could not connect to server $this->host");
				log::error("[mysqlDriver][".mysql_errno()."] ".mysql_error());
				header::setAlternateContent(view::start(array(config::$config['model']['die_route']) + route::$pageRoute),true);
				header::set('Status',500,true);
			}
		
			if(!@mysql_select_db($this->database, $this->link))
			{
				log::error("[mysqlDriver] Could not open database $this->database");
		        log::error("[mysqlDriver][".mysql_errno()."] ".mysql_error());
				header::setAlternateContent(view::start(array(config::$config['model']['die_route']) + route::$pageRoute),true);
				header::set('Status',500,true);
			}
		}
	}
	
	function query($sql)
	{
		$this->connect();
		
		if (!$id = @mysql_query($sql, $this->link))
		{
			log::error("[mysqlDriver] Could not execute query $sql on $this->database");
			log::error("[mysqlDriver][".mysql_errno($this->link)."] ".mysql_error($this->link));
			return 0;
		}
		
		$this->query[++$this->queryId] = array(
			'affected_rows' => @mysql_affected_rows($this->link),
			'insert_id'     => @mysql_insert_id($this->link),
			'query_id'      => $id,
		);
		return $this->queryId;	
	}
	
	function fetch($id,$className = null, $classArguments = array())
	{	
		if (isset($this->query[$id]['query_id']) && is_null($className)) {
			return @mysql_fetch_object($this->query[$id]['query_id']);
		}
		elseif (isset($this->query[$id]['query_id']))
		{
			return @mysql_fetch_object($this->query[$id]['query_id'],$className,$classArguments);
		}
		else
		{
			log::error("[mysqlDriver] Invalid query id : $id");
			return null;
		}
	}
	
	function fetchAll($id,$className = null, $classArguments = array())
	{
		$return = array();
		while ($row = $this->fetch($id,$className,$classArguments))
			$return[] = $row;
		
		return $return;
	}
	
	function fetchArray($id)
	{	
		if (isset($this->query[$id]['query_id'])) {
			return @mysql_fetch_assoc($this->query[$id]['query_id']);
		}
		else
		{
			log::error("[mysqlDriver] Invalid query id : $id");
			return null;
		}
	}
	
	function fetchAllArray($id)
	{
		$return = array();
		while ($row = $this->fetchArray($id))
			$return[] = $row;
		
		return $return;
	}
	
	function escape($string)
	{
		$this->connect();
		return @mysql_real_escape_string($string,$this->link);
	}
	
	function free($id)
	{
		if(isset($this->query[$id]['query_id']))
			@mysql_free_result($this->query[$id]['query_id']);
	}
	
	function getOperators()
	{
		return array('<=>','=','>=','>','<=','<','LIKE','!=','<>','REGEXP');
	}
	
	function __destruct()
	{
		@mysql_close($this->link);
	}
	
	// ModelMagic functions
	function select($table,$where = array(),$limit = null,$offset = null) //,$groupBy = array(),$orderBy=array(),$having = array()
	{
		$__where = array();
		foreach ($where as $field=>$params)
		{
			if (is_array($params))
				$params += array('sign' => '=','escape' => true,'value' => null);
			else
				$params = array('sign' => '=','escape' => true,'value' => $params);

			$__where[] = "`$field` {$params['sign']} '".$this->escape($params['value'])."'";
		}
		
		$sql = "SELECT * FROM `$table`".(count($__where)>0?' WHERE '.implode(' AND ',$__where):null).(is_int($limit)?" LIMIT ".(is_int($offset)?"$offset,":null)."$limit":null);
		
		return $this->query($sql);
	}
	
	function selectFetch($table,$where = array(),$limit = null,$offset = null)
	{
		$id = $this->select($table,$where,$limit,$offset);
		return $this->fetch($id);
	}
	
	function selectFetchAll($table,$where = array(),$limit = null,$offset = null)
	{
		$id = $this->select($table,$where,$limit,$offset);
		$return = $this->fetchAll($id);
		$this->free($id);
		return $return;
	}
	
	function update($table, $data,$where = array(),$limit = null,$offset = null)
	{
		$__where = array();
		foreach ($where as $field=>$params)
		{
			if (is_array($params))
				$params += array('sign' => '=','escape' => true,'value' => null);
			else
				$params = array('sign' => '=','escape' => true,'value' => $params);

			$__where[] = "`$field` {$params['sign']} '".$this->escape($params['value'])."'";
		}
		
		$values = array();
		
		foreach($data as $key=>$value)
		{
			if(is_null($value))
				$values[] = "`$key` = NULL";
			else
				$values[] = "`$key`='".$this->escape($value)."'";
		}
		
		$sql = "UPDATE `$table` SET ".implode(', ',$values).(count($__where)>0?' WHERE '.implode(' AND ',$__where):null).(is_int($limit)?" LIMIT ".(is_int($offset)?"$offset,":null)."$limit":null);
		print $sql;
		return $this->query($sql);		
	}
	
	function insert($table, $datas = array())
	{
		$__values = array();
		
		foreach ($datas as $field=>$params)
		{
			if (is_array($params))
				$__values[$field] = "'".$this->escape($params['value'])."'";
			elseif(is_null($value))
				$__values[$field] = "NULL";
			else
				$__values[$field] = "'".$this->escape($params)."'";
		}
		
		$sql = "INSERT INTO `$table` (`".implode('`, `',array_keys($__values))."`) VALUES (".implode(", ",$__values).')';
		
		return $this->query[$this->query($sql)]['insert_id'];  
	}
	
	function delete($table,$where = array(),$limit = null,$offset = null) //,$groupBy = array(),$orderBy=array(),$having = array()
	{
		$__where = array();
		foreach ($where as $field=>$params)
		{
			if (is_array($params))
				$params += array('sign' => '=','escape' => true,'value' => null);
			else
				$params = array('sign' => '=','escape' => true,'value' => $params);

			$__where[] = "`$field` {$params['sign']} '".$this->escape($params['value'])."'";
		}
		
		$sql = "DELETE FROM `$table`".(count($__where)>0?' WHERE '.implode(' AND ',$__where):null).(is_int($limit)?" LIMIT ".(is_int($offset)?"$offset,":null)."$limit":null);
		
		return $this->query($sql);
	}
}
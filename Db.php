<?php
	
	class DB
	{
		private static $db = NULL;
		private static $sqls = array();
		
		static function connect()
		{
			if (self::$db == NULL)
			{
				$db_host = 'localhost';
				$db_name = 'nestedsetmodel';
				$db_user = 'tester';
				$db_pass = 'p@ssw0rd';
				$db_charset = 'utf8';
				
				$dsn = "mysql:host={$db_host};dbname={$db_name}";
				$options = array(
									PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $db_charset
							);
				
				try {
					self::$db = new PDO($dsn, $db_user, $db_pass, $options);
				} catch (PDOException $e) {
					die('Failed to connect database : ' . $e->getMessage());
				}
			}
			
			return self::$db;
		}
		
		static function query($sql)
		{
			return self::$db->query($sql);
		}
		
		static function fetch($statement, $fetch_mode = PDO::FETCH_ASSOC)
		{
			return $statement->fetch($fetch_mode);
		}
		
		static function fetch_all($statement, $fetch_mode = PDO::FETCH_ASSOC)
		{
			return $statement->fetchAll($fetch_mode);
		}
		
		static function fetch_value($statement)
		{
			$value = self::fetch($statement);
			
			return $value[0];
		}
		
		static function quote($text)
		{
			return self::$db->quote($text);
		}
		
		static function inserted_id()
		{
			return self::$db->lastInsertId();
		}
		
		static function add_sql($sql)
		{
			self::$sqls[] = $sql;
		}
		
		static function commit()
		{
			self::$db->beginTransaction();
			try {
				foreach (self::$sqls as $sql)
				{
					self::$db->query($sql);
				}
				self::$sqls = array();
				self::$db->commit();
			} catch (PDOException $e) {
				self::$db->rollBack();
			}
		}
		
		static function prepare($sql)
		{
			return self::$db->prepare($sql);
		}
	}

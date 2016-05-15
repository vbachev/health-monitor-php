<?php

class Db{
	private $hostname = NULL;
	private $username = NULL;
	private $password = NULL;
	private $dbConnection = NULL;
	private $dbSelected = NULL;
	private $displayErrors = NULL;

	public function __construct($host = "localhost", $user = "root", $pass = "", $dbName = "", $errors = true){
		$backtrace = false;
		if($this->displayErrors) $backtrace = debug_backtrace(false);
		$this->hostname = $host;
		$this->username = $user;
		$this->password = $pass;
		$this->displayErrors = $errors;

		try{
			$this->dbConnection = @mysql_connect($this->hostname, $this->username, $this->password);
			if(!$this->dbConnection){
				throw new Exception("Can't connect to database.<br/>" . mysql_error());
			}
			if($dbName){
				$this->selectDb($dbName);
			}
		}
		catch(Exception $ex){
			$this->displayError($ex, $backtrace);
		}

		// set the connection to utf8 - utf8_unicode_ci, if it isn't already
		@mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'", $this->dbConnection);
	}

	public function __destruct(){
		$this->close();
		$this->hostname = NULL;
		$this->username = NULL;
		$this->password = NULL;
	}

	public function escape(&$sql){
		$sql = mysql_real_escape_string($sql, $this->dbConnection);
		return $sql;
	}

	public function lastInsertId(){
		return mysql_insert_id($this->dbConnection);
	}

	public function affectedRows(){
		return mysql_affected_rows($this->dbConnection);
	}

	/*
	 *
	 * @param type String $sql The SQL quety
	 * @param type String $type assoc for assoc array, full for fully qualified field names
	 * @return type array
	 */
	public function query($sql, $type = "assoc"){
		$result = null;
		$backtrace = false;
		if($this->displayErrors) $backtrace = debug_backtrace();
		$sql = preg_replace("/\s+/", " ", $sql);
		$sql = trim($sql);
		try{
			$result = @mysql_query($sql, $this->dbConnection);
			if(!$result){
				throw new Exception('Cannot execute query "' . $sql . '".<br/>' . mysql_error());
			}
		}
		catch(Exception $ex){
			$this->displayError($ex, $backtrace);
		}

		//ONLY select, show, describe and explain return a RESULT-SET !!!
		if(
		($type === "assoc") &&
		((0 === stripos($sql,'select')) ||
		(0 === stripos($sql,'show')) ||
		(0 === stripos($sql,'describe')) ||
		(0 === stripos($sql,'explain')))){
			$arr = $this->resultsetToArray($result);
			@mysql_free_result($result);
			return  $arr;
		}
		elseif(
		($type === "full") &&
		((0 === stripos($sql,'select')) ||
		(0 === stripos($sql,'show')) ||
		(0 === stripos($sql,'describe')) ||
		(0 === stripos($sql,'explain')))){
			$arr = $this->resultsetToFullArray($result);
			@mysql_free_result($result);
			return  $arr;
		}
		else{
			return $result;
		}
	}

	private function resultsetToArray($result){
		$arr = array();
		while($row = mysql_fetch_assoc($result)){
			$arr[] = $row;
		}
		return $arr;
	}

	private function resultsetToFullArray($result){
		$arrResult = array();
		$fields = mysql_num_fields($result);

		while($row = mysql_fetch_array($result, MYSQL_BOTH)){
			if(isset($fullArray)) unset($fullArray);
			$fullArray = array();

			for($i=0; $i < $fields; $i++){

				$field = mysql_field_name($result, $i);
				$table = mysql_field_table($result, $i);

				if($table){
					$fullArray[$table . "_" . $field] = $row[$i];
				}
				else{
					$fullArray[$field] = $row[$i];
				}
			}

			$arrResult[] = $fullArray;
		}
		return $arrResult;
	}

	public function selectDb($dbName){
		$backtrace = false;
		if($this->displayErrors) $backtrace = debug_backtrace();
		try{
			$this->dbSelected = @mysql_select_db($dbName, $this->dbConnection);
			if (!$this->dbSelected){
				throw new Exception('Cannot select "' . $dbName . '" database.<br/>' . mysql_error());
			}
		}
		catch(Exception $ex){
			$this->displayError($ex, $backtrace);
		}
	}

	public function close(){
		if($this->dbConnection){
			mysql_close($this->dbConnection);
		}
		$this->dbConnection = NULL;
		$this->dbSelected = NULL;
	}

	private function displayError(Exception $ex, $backtrace){
		if($this->displayErrors){
			echo '<p><b>ERROR:</b><br/>' .
				 '<b>file:</b> ' . ((isset($backtrace[0])) ? $backtrace[0]['file'] : $backtrace[1]['file']) . '<br/>' .
				 '<b>line:</b> ' . ((isset($backtrace[0])) ? $backtrace[0]['line'] : $backtrace[1]['line']) . '<br/>' .
				 $ex->getMessage() . "</p>";
			die();
		}
	}
}
<?
class MySQL {
	public $server;
	public $username;
	public $password;
	public $db;
	private $connectionID;
	private $queryString;
	public $result;
	public $debug;
	private $queryHistory = array();
	private $queryID;
	private $query;
	public $prefix;


	function connect() {
		$this->connectionID=@mysql_pconnect($this->server, $this->username, $this->password);
		if ($this->connectionID === false) {
			exit("Class MySQL, function connect: can't connect to the mysql server<br>".mysql_error());
		}

        $this->query("SET NAMES utf8");
        //$this->query("SET CHARACTER SET cp1251");
        //$this->query("SET character_set_connection = cp1251;");
        //$this->query("SET @@collation_connection = cp1251_general_ci");
        //$this->query("set character_set_client='cp1251'");
        //$this->query("set character_set_results='cp1251'");
        //$this->query("set collation_connection='cp1251_general_ci'");

		//exit(mysql_client_encoding($this->connectionID));
        
		if (mysql_select_db($this->db, $this->connectionID) === false) {
        	exit("Class MySQL, function connect: can't select database ".$this->db."<br>".mysql_error($this->connectionID));
        }

        //exit(mysql_client_encoding($this->connectionID));
        
	}

	function escape($data) {
		// Check connection
		if (!isset($this->connectionID)) {
			$this->connect();
		}
		
		if (!is_numeric($data)) {
			$data = mysql_real_escape_string($data, $this->connectionID);
		}
		//exit(mysql_client_encoding($this->connectionID));
		return $data;
	}
	
	
	function query($queryString, $setResult = false, $prefixMask="#__#") {
		// Check connection
		if (!isset($this->connectionID)) {
			$this->connect();
		}

		$queryString=str_replace($prefixMask, $this->prefix, $queryString);
		$this->query=$queryString;
		$this->queryHistory[]=$queryString;
		$this->queryID=@mysql_query($queryString);

		if ($this->queryID === false) {
			$this->errorQuery();
		}

		if ($setResult === true) {
			$this->next_row();
		}
	}

	function queryUnbuf($queryString, $setResult = false, $prefixMask="#__#") {
		// Check connection
		if (!isset($this->connectionID)) {
			$this->connect();
		}

		$queryString=str_replace($prefixMask, $this->prefix, $queryString);
		$this->query=$queryString;
		$this->queryHistory[]=$queryString;
		$this->queryID=@mysql_unbuffered_query($queryString);

		if ($this->queryID === false) {
			$this->errorQuery();
		}

		if ($setResult === true) {
			$this->next_row();
		}
	}


	function errorQuery() {
		$outString="Class mysql, function error.<br><br>Query: error while complate query<li>".htmlspecialchars($this->query)."<br>".mysql_error($this->connectionID)."<br><br>Query history: <br>";
		foreach ($this->queryHistory as $value) {
			$outString.=$value."<br>";

		}
		exit($outString);
	}

	function next_row() {
		return($this->result=mysql_fetch_array($this->queryID));
	}

	function field($key) {
		if (!isset($this->result) || !is_array($this->result)) {
			exit("Class MySQL, function field: \$this->result is unset (notArray)");
		}

		if (!isset($this->result[$key])) {
			exit("Class MySQL, function field: \$this->result[$key] is unset");
		}

		return $this->result[$key];
	}

	public function num_rows() {
		return mysql_num_rows($this->queryID);
	}

	public function lastId() {
		return mysql_insert_id($this->connectionID);
	}
	
	public function getList() {
		$returnArray = array();
		while ($this->next_row()) {
			$returnArray[] = $this->result;
		}
	return $returnArray;
	}
}
?>
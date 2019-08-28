<?

	class database {
		private static $instance = false;
		// database schema
		private $database = false;
		// query result reference vars
		public $sqlError = false;
		public $sqlErrorNumber = false;
		// when using SQL_CALC_FOUND_ROWS
		public $foundRows = false;
		public $rowCount = false;
		public $insertID = false;

		/**
		 *  Retrieve database schema, initialize connection
		 *  Args: none
		 *  Return: none
		 */
		public function __construct() {
			$this->database = systemSettings::get('DATABASE');
			if (!mysql_connect()) {
				$this->sqlError = 'Cannot connect to the database because: '.mysql_error();
				trigger_error('Database Error: '.$this->sqlError, E_USER_ERROR);
			} elseif (!mysql_select_db($this->database)) {
				$this->sqlError = 'Cannot select database because: '.mysql_error();
				trigger_error('Database Error: '.$this->sqlError, E_USER_ERROR);
			}
			self::$instance = &$this;
		} // function __construct

		/**
		 *  Return the current instance of the global database handler
		 *  Args: none
		 *  Return: (database) instance of database class
		 */
		public static function getInstance() {
			if (!self::$instance) {
				$dbh = new database;
			}
			return self::$instance;
		} // function getInstance

		/**
		 *  Performs database query, sets internval values error (if error), found rows (if sql_calc_found_rows)
		 *    affected rows/row count, insert id (if insert)
		 *  Args: (str) query
		 *  Return: (result) result object
		 */
		public function query($query) {
			$this->sqlError = false;
			$this->sqlErrorNumber = false;
			$this->foundRows = false;
			$this->rowCount = false;
			$this->insertID = false;
			if (substr($query, -1, 1) != ';') $query .= ';';
			$result = mysql_query($query);
			if (mysql_error()) {
				$this->sqlError = mysql_error();
				$this->sqlErrorNumber = mysql_errno();
				trigger_error('Query Failed: '.$this->sqlError.' <===> Query: '.htmlentities($query), E_USER_ERROR);
				$resultObj = new result(false, $this->rowCount, $this->foundRows, $this->insertID, $this->sqlError, $this->sqlErrorNumber);
				return $resultObj;
			} else {
				$this->rowCount = strpos($query, 'SELECT') === 0 ? mysql_num_rows($result) : mysql_affected_rows();
				if (strpos($query, 'INSERT') === 0) $this->insertID = mysql_insert_id();
				if (strpos($query, 'SQL_CALC_FOUND_ROWS') !== false) {
					$this->foundRows = mysql_result(mysql_query("SELECT FOUND_ROWS()"), 0);
				} else {
					$this->foundRows = $this->rowCount;
				}
				debug(htmlentities($query).'<br>Found Rows: '.$this->foundRows.' | Row Count: '.$this->rowCount.' | Insert ID: '.$this->insertID, 'Query Executed', 'echo', 'sql');
				$resultObj = new result($result, $this->rowCount, $this->foundRows, $this->insertID, $this->sqlError, $this->sqlErrorNumber);
				return $resultObj;
			}
		} // function query
	} // class database

?>
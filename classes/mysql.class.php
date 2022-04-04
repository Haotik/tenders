<?php
/**
 * MySQL Wrapper Class
 */
class MySQL
{
	// SET THESE VALUES TO MATCH YOUR DATA CONNECTION
	private $db_host    = "localhost";  // server name
	private $db_user    = "root";       // user name
	private $db_pass    = "root";     // password
	//private $db_user    = "tadmin";       // user name
	//private $db_pass    = "LampaDrop567"; // password
	private $db_dbname  = "tenders_php";       // database name
	private $db_charset = "utf8";       // optional character set (i.e. utf8)
	private $db_pcon    = false;        // use persistent connection?

	// constants for SQLValue function
	const SQLVALUE_BIT      = "bit";
	const SQLVALUE_BOOLEAN  = "boolean";
	const SQLVALUE_DATE     = "date";
	const SQLVALUE_DATETIME = "datetime";
	const SQLVALUE_NUMBER   = "number";
	const SQLVALUE_T_F      = "t-f";
	const SQLVALUE_TEXT     = "text";
	const SQLVALUE_TIME     = "time";
	const SQLVALUE_Y_N      = "y-n";

	// class-internal variables - do not change
	private $active_row     = -1;       // current row
	private $error_desc     = "";       // last mysql error string
	private $error_number   = 0;        // last mysql error number
	private $in_transaction = false;    // used for transactions
	private $last_insert_id;            // last id of record inserted
	private $last_result;               // last mysql query result
	private $last_sql       = "";       // last mysql query
	public $mysql_link     = 0;        // mysql link resource
	private $time_diff      = 0;        // holds the difference in time
	private $time_start     = 0;        // start time for the timer

	/**
	 * Determines if an error throws an exception
	 *
	 * @var boolean Set to true to throw error exceptions
	 */
	public $ThrowExceptions = false;

	/**
	 * Constructor: Opens the connection to the database
	 *
	 * @param boolean $connect (Optional) Auto-connect when object is created
	 * @param string $database (Optional) Database name
	 * @param string $server   (Optional) Host address
	 * @param string $username (Optional) User name
	 * @param string $password (Optional) Password
	 * @param string $charset  (Optional) Character set
	 */
	public function __construct($connect=true, $database="", $server="", $username="", $password="", $charset="") {
		if (strlen($database) > 0) $this->db_dbname  = $database;
		if (strlen($server)   > 0) $this->db_host    = $server;
		if (strlen($username) > 0) $this->db_user    = $username;
		if (strlen($password) > 0) $this->db_pass    = $password;
		if (strlen($charset)  > 0) $this->db_charset = $charset;

		if (strlen($this->db_host) > 0 &&
			strlen($this->db_user) > 0) {
			if ($connect) $this->Open();
		}
	}

	/**
	 * Destructor: Closes the connection to the database
	 *
	 */
	public function __destruct() {
		$this->Close();
	}

	/**
	 * [STATIC] Builds a comma delimited list of columns for use with SQL
	 *
	 * @param array $valuesArray An array containing the column names.
	 * @param boolean $addQuotes (Optional) TRUE to add quotes
	 * @param boolean $showAlias (Optional) TRUE to show column alias
	 * @return string Returns the SQL column list
	 */
	static private function BuildSQLColumns($columns, $addQuotes = true, $showAlias = true) {
		if ($addQuotes) {
			$quote = "'";
		} else {
			$quote = "";
		}
		switch (gettype($columns)) {
			case "array":
				$sql = "";
				foreach ($columns as $key => $value) {
					// Build the columns
					if (strlen($sql) == 0) {
						$sql = ( (gettype($value) == 'integer' || $value == 'NULL') ? $value : $quote . $value . $quote);
					} else {
						$sql .= ", " . ( (gettype($value) == 'integer' || $value == 'NULL') ? $value : $quote . $value . $quote);
					}
					if ($showAlias && is_string($key) && (! empty($key))) {
						$sql .= ' AS "' . $key . '"';
					}
				}
				return $sql;
				break;
			case "string":
				return $quote . $columns . $quote;
				break;
			case "integer":
				return $columns;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * [STATIC] Builds a SQL DELETE statement
	 *
	 * @param string $tableName The name of the table
	 * @param array $whereArray (Optional) An associative array containing the
	 *                           column names as keys and values as data. The
	 *                           values must be SQL ready (i.e. quotes around
	 *                           strings, formatted dates, ect). If not specified
	 *                           then all values in the table are deleted.
	 * @return string Returns the SQL DELETE statement
	 */
	static public function BuildSQLDelete($tableName, $whereArray = null) {
		$sql = "DELETE FROM `" . $tableName . "`";
		if (! is_null($whereArray)) {
			$sql .= self::BuildSQLWhereClause($whereArray);
		}
		return $sql;
	}

	/**
	 * [STATIC] Builds a SQL INSERT statement
	 *
	 * @param string $tableName The name of the table
	 * @param array $valuesArray An associative array containing the column
	 *                            names as keys and values as data. The values
	 *                            must be SQL ready (i.e. quotes around
	 *                            strings, formatted dates, ect)
	 * @return string Returns a SQL INSERT statement
	 */
	static public function BuildSQLInsert($tableName, $valuesArray) {
		$columns = self::BuildSQLColumns(array_keys($valuesArray), false);
		$values  = self::BuildSQLColumns($valuesArray, true, false);
		$sql = "INSERT INTO `" . $tableName .
			   "` (" . $columns . ") VALUES (" . $values . ")";
		return $sql;
	}

	/**
	 * Builds a simple SQL SELECT statement
	 *
	 * @param string $tableName The name of the table
	 * @param array $whereArray (Optional) An associative array containing the
	 *                          column names as keys and values as data. The
	 *                          values must be SQL ready (i.e. quotes around
	 *                          strings, formatted dates, ect)
	 * @param array/string $columns (Optional) The column or list of columns to select
	 * @param array/string $sortColumns (Optional) Column or list of columns to sort by
	 * @param boolean $sortAscending (Optional) TRUE for ascending; FALSE for descending
	 *                               This only works if $sortColumns are specified
	 * @param integer/string $limit (Optional) The limit of rows to return
	 * @return string Returns a SQL SELECT statement
	 */
	static public function BuildSQLSelect($tableName, $whereArray = null, $columns = null,
										  $sortColumns = null, $sortAscending = true, $limit = null) {
		if (! is_null($columns)) {
			$sql = self::BuildSQLColumns($columns);
		} else {
			$sql = "*";
		}
		$sql = "SELECT " . $sql . " FROM `" . $tableName . "`";
		if (is_array($whereArray)) {
			$sql .= self::BuildSQLWhereClause($whereArray);
		}
		if (! is_null($sortColumns)) {
			$sql .= " ORDER BY " .
					self::BuildSQLColumns($sortColumns, false, false) .
					" " . ($sortAscending ? "ASC" : "DESC");
		}
		if (! is_null($limit)) {
			$sql .= " LIMIT " . $limit;
		}
		return $sql;
	}

	/**
	 * [STATIC] Builds a SQL UPDATE statement
	 *
	 * @param string $tableName The name of the table
	 * @param array $valuesArray An associative array containing the column
	 *                            names as keys and values as data. The values
	 *                            must be SQL ready (i.e. quotes around
	 *                            strings, formatted dates, ect)
	 * @param array $whereArray (Optional) An associative array containing the
	 *                           column names as keys and values as data. The
	 *                           values must be SQL ready (i.e. quotes around
	 *                           strings, formatted dates, ect). If not specified
	 *                           then all values in the table are updated.
	 * @return string Returns a SQL UPDATE statement
	 */
	static public function BuildSQLUpdate($tableName, $valuesArray, $whereArray = null) {
		$sql = "";
		$quote = "'";
		foreach ($valuesArray as $key => $value) {
			if (strlen($sql) == 0) {
				$sql = "`" . $key . "` = " . ( (gettype($value) == 'integer' || $value == 'NULL') ? $value : $quote . $value . $quote);
			} else {
				$sql .= ", `" . $key . "` = " . ( (gettype($value) == 'integer' || $value == 'NULL') ? $value : $quote . $value . $quote);
			}
		}
		$sql = "UPDATE `" . $tableName . "` SET " . $sql;
		if (is_array($whereArray)) {
			$sql .= self::BuildSQLWhereClause($whereArray);
		}
		return $sql;
	}

	/**
	 * [STATIC] Builds a SQL WHERE clause from an array.
	 * If a key is specified, the key is used at the field name and the value
	 * as a comparison. If a key is not used, the value is used as the clause.
	 *
	 * @param array $whereArray An associative array containing the column
	 *                           names as keys and values as data. The values
	 *                           must be SQL ready (i.e. quotes around
	 *                           strings, formatted dates, ect)
	 * @return string Returns a string containing the SQL WHERE clause
	 */
	static public function BuildSQLWhereClause($whereArray) {
		$where = "";
		foreach ($whereArray as $key => $value) {
			if (strlen($where) == 0) {
				if (is_string($key)) {
					$where = " WHERE `" . $key . "` = " . $value;
				} else {
					$where = " WHERE " . $value;
				}
			} else {
				if (is_string($key)) {
					$where .= " AND `" . $key . "` = " . $value;
				} else {
					$where .= " AND " . $value;
				}
			}
		}
		return $where;
	}

	/**
	 * Close current MySQL connection
	 *
	 * @return object Returns TRUE on success or FALSE on error
	 */
	public function Close() {
		$this->ResetError();
		$this->active_row = -1;
		$success = $this->Release();
		if ($success) {
			$success = @mysql_close($this->mysql_link);
			if (! $success) {
				$this->SetError();
			} else {
				unset($this->last_sql);
				unset($this->last_result);
				unset($this->mysql_link);
			}
		}
		return $success;
	}

	/**
	 * Deletes rows in a table based on a WHERE filter
	 * (can be just one or many rows based on the filter)
	 *
	 * @param string $tableName The name of the table
	 * @param array $whereArray (Optional) An associative array containing the
	 *                          column names as keys and values as data. The
	 *                          values must be SQL ready (i.e. quotes around
	 *                          strings, formatted dates, ect). If not specified
	 *                          then all values in the table are deleted.
	 * @return boolean Returns TRUE on success or FALSE on error
	 */
	public function DeleteRows($tableName, $whereArray = null) {
		$this->ResetError();
		if (! $this->IsConnected()) {
			$this->SetError("No connection");
			return false;
		} else {
			$sql = self::BuildSQLDelete($tableName, $whereArray);
			// Execute the UPDATE
			if (! $this->Query($sql)) {
				return false;
			} else {
				return true;
			}
		}
	}

	/**
	 * Returns the last MySQL error as text
	 *
	 * @return string Error text from last known error
	 */
	public function Error() {
		$error = $this->error_desc;
		if (empty($error)) {
			if ($this->error_number <> 0) {
				$error = "Unknown Error (#" . $this->error_number . ")";
			} else {
				$error = false;
			}
		} else {
			if ($this->error_number > 0) {
				$error .= " (#" . $this->error_number . ")";
			}
		}
		return $error;
	}

	/**
	 * Returns the last MySQL error as a number
	 *
	 * @return integer Error number from last known error
	 */
	public function ErrorNumber() {
		if (strlen($this->error_desc) > 0)
		{
			if ($this->error_number <> 0)
			{
				return $this->error_number;
			} else {
				return -1;
			}
		} else {
			return $this->error_number;
		}
	}

	/**
	 * [STATIC] Converts any value of any datatype into boolean (true or false)
	 *
	 * @param mixed $value Value to analyze for TRUE or FALSE
	 * @return boolean Returns TRUE or FALSE
	 */
	static public function GetBooleanValue($value) {
		if (gettype($value) == "boolean") {
			if ($value == true) {
				return true;
			} else {
				return false;
			}
		} elseif (is_numeric($value)) {
			if ($value > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			$cleaned = strtoupper(trim($value));

			if ($cleaned == "ON") {
				return true;
			} elseif ($cleaned == "SELECTED" || $cleaned == "CHECKED") {
				return true;
			} elseif ($cleaned == "YES" || $cleaned == "Y") {
				return true;
			} elseif ($cleaned == "TRUE" || $cleaned == "T") {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * This function returns the number of columns or returns FALSE on error
	 *
	 * @param string $table (Optional) If a table name is not specified, the
	 *                      column count is returned from the last query
	 * @return integer The total count of columns
	 */
	public function GetColumnCount($table = "") {
		$this->ResetError();
		if (empty($table)) {
			$result = mysql_num_fields($this->last_result);
			if (! $result) $this->SetError();
		} else {
			$records = mysql_query("SELECT * FROM " . $table . " LIMIT 1");
			if (! $records) {
				$this->SetError();
				$result = false;
			} else {
				$result = mysql_num_fields($records);
				$success = @mysql_free_result($records);
				if (! $success) {
					$this->SetError();
					$result = false;
				}
			}
		}
		return $result;
	}

	/**
	 * Returns the last autonumber ID field from a previous INSERT query
	 *
	 * @return  integer ID number from previous INSERT query
	 */
	public function GetLastInsertID() {
		return $this->last_insert_id;
	}

	/**
	 * Returns the last SQL statement executed
	 *
	 * @return string Current SQL query string
	 */
	public function GetLastSQL() {
		return $this->last_sql;
	}

	/**
	 * Determines if a query contains any rows
	 *
	 * @param string $sql [Optional] If specified, the query is first executed
	 *                    Otherwise, the last query is used for comparison
	 * @return boolean TRUE if records exist, FALSE if not or query error
	 */
	public function HasRecords($sql = "") {
		if (strlen($sql) > 0) {
			$this->Query($sql);
			if ($this->Error()) return false;
		}
		if ($this->RowCount() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Inserts a row into a table in the connected database
	 *
	 * @param string $tableName The name of the table
	 * @param array $valuesArray An associative array containing the column
	 *                            names as keys and values as data. The values
	 *                            must be SQL ready (i.e. quotes around
	 *                            strings, formatted dates, ect)
	 * @return integer Returns last insert ID on success or FALSE on failure
	 */
	public function InsertRow($tableName, $valuesArray) {
		$this->ResetError();
		if (! $this->IsConnected()) {
			$this->SetError("No connection");
			return false;
		} else {
			// Execute the query
			$sql = self::BuildSQLInsert($tableName, $valuesArray);
			if (! $this->Query($sql)) {
				return false;
			} else {
				return $this->GetLastInsertID();
			}
		}
	}

	/**
	 * Determines if a valid connection to the database exists
	 *
	 * @return boolean TRUE idf connectect or FALSE if not connected
	 */
	public function IsConnected() {
		if (gettype($this->mysql_link) == "resource") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * [STATIC] Determines if a value of any data type is a date PHP can convert
	 *
	 * @param date/string $value
	 * @return boolean Returns TRUE if value is date or FALSE if not date
	 */
	static public function IsDate($value) {
		$date = date('Y', strtotime($value));
		if ($date == "1969" || $date == '') {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Stop executing (die/exit) and show last MySQL error message
	 *
	 */
	public function Kill($message='') {
		if (strlen($message) > 0) {
			exit($message);
		} else {
			exit($this->Error());
		}
	}

	/**
	 * Connect to specified MySQL server
	 *
	 * @param string $database (Optional) Database name
	 * @param string $server   (Optional) Host address
	 * @param string $username (Optional) User name
	 * @param string $password (Optional) Password
	 * @param string $charset  (Optional) Character set
	 * @param boolean $pcon    (Optional) Persistant connection
	 * @return boolean Returns TRUE on success or FALSE on error
	 */
	public function Open($database="", $server="", $username="",
						 $password="", $charset="", $pcon=false) {
		$this->ResetError();

		// Use defaults?
		if (strlen($database) == 0) $database = $this->db_dbname;
		if (strlen($server)   == 0) $server   = $this->db_host;
		if (strlen($username) == 0) $username = $this->db_user;
		if (strlen($password) == 0) $password = $this->db_pass;
		if (strlen($charset)  == 0) $charset  = $this->db_charset;
		if (strlen($pcon)     == 0) $pcon     = $this->db_pcon;

		$this->active_row = -1;

		// Open persistent or normal connection
		if ($pcon) {
			$this->mysql_link = @mysql_pconnect($server, $username, $password);
		} else {
			$this->mysql_link = @mysql_connect ($server, $username, $password);
		}
		// Connect to mysql server failed?
		if (! $this->IsConnected()) {
			$this->SetError();
			return false;
		} else {
			// Select a database (if specified)
			if (strlen($database) > 0) {
				if (strlen($charset) == 0) {
					if (! $this->SelectDatabase($database)) {
						return false;
					} else {
						return true;
					}
				} else {
					if (! $this->SelectDatabase($database, $charset)) {
						return false;
					} else {
						return true;
					}
				}
			} else {
				return true;
			}
		}
	}

	/**
	 * Executes the given SQL query and returns the records
	 *
	 * @param string $sql The query string should not end with a semicolon
	 * @return object PHP 'mysql result' resource object containing the records
	 *                on SELECT, SHOW, DESCRIBE or EXPLAIN queries and returns;
	 *                TRUE or FALSE for all others i.e. UPDATE, DELETE, DROP
	 *                AND FALSE on all errors (setting the local Error message)
	 */
	public function Query($sql) {
		$this->ResetError();
		$this->last_sql = $sql;
		$this->last_result = @mysql_query($sql, $this->mysql_link);
		if(! $this->last_result) {
			$this->active_row = -1;
			$this->SetError();
			return false;
		} else {
			if (preg_match("/^insert/", strtolower($sql))) {
				$this->last_insert_id = mysql_insert_id();
				if ($this->last_insert_id === false) {
					$this->SetError();
					return false;
				} else {
					$numrows = 0;
					$this->active_row = -1;
					return $this->last_result;
				}
			} else if(preg_match("/^select/", strtolower($sql))) {
				$numrows = mysql_num_rows($this->last_result);
				if ($numrows > 0) {
					$this->active_row = 0;
				} else {
					$this->active_row = -1;
				}
				$this->last_insert_id = 0;
				return $this->last_result;
			} else {
				return $this->last_result;
			}
		}
	}

	/**
	 * Executes the given SQL query and returns a multi-dimensional array
	 *
	 * @param string $sql The query string should not end with a semicolon
	 * @param integer $resultType (Optional) The type of array
	 *                Values can be: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	 * @return array A multi-dimensional array containing all the data
	 *               returned from the query or FALSE on all errors
	 */
	public function QueryArray($sql, $resultType = MYSQL_BOTH) {
		$this->Query($sql);
		if (! $this->Error()) {
			if ($this->RowCount() > 0) {
				return $this->RecordsArray($resultType);
			} else {
				return array();
			}
		} else {
			return false;
		}
	}

	/**
	 * Executes the given SQL query and returns only one (the first) row
	 *
	 * @param string $sql The query string should not end with a semicolon
	 * @return object PHP resource object containing the first row or
	 *                FALSE if no row is returned from the query
	 */
	public function QuerySingleRow($sql) {
		$this->Query($sql);
		if ($this->RowCount() > 0) {
			return $this->Row();
		} else {
			return false;
		}
	}

	/**
	 * Executes the given SQL query and returns the first row as an array
	 *
	 * @param string $sql The query string should not end with a semicolon
	 * @param integer $resultType (Optional) The type of array
	 *                Values can be: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	 * @return array An array containing the first row or FALSE if no row
	 *               is returned from the query
	 */
	public function QuerySingleRowArray($sql, $resultType = MYSQL_BOTH) {
		$this->Query($sql);
		if ($this->RowCount() > 0) {
			return $this->RowArray(null, $resultType);
		} else {
			return false;
		}
	}

	/**
	 * Executes a query and returns a single value. If more than one row
	 * is returned, only the first value in the first column is returned.
	 *
	 * @param string $sql The query string should not end with a semicolon
	 * @return mixed The value returned or FALSE if no value
	 */
	public function QuerySingleValue($sql) {
		$this->Query($sql);
		if ($this->RowCount() > 0 && $this->GetColumnCount() > 0) {
			$row = $this->RowArray(null, MYSQL_NUM);
			return $row[0];
		} else {
			return false;
		}
	}

	/**
	 * Returns the records from the last query
	 *
	 * @return object PHP 'mysql result' resource object containing the records
	 *                for the last query executed
	 */
	public function Records() {
		return $this->last_result;
	}

	/**
	 * Returns all records from last query and returns contents as array
	 * or FALSE on error
	 *
	 * @param integer $resultType (Optional) The type of array
	 *                Values can be: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	 * @return Records in array form
	 */
	public function RecordsArray($resultType=MYSQL_BOTH) {
		$this->ResetError();
		if ($this->last_result) {
			if (! mysql_data_seek($this->last_result, 0)) {
				$this->SetError();
				return false;
			} else {
				//while($member = mysql_fetch_object($this->last_result)){
				while($member = mysql_fetch_array($this->last_result, $resultType)){
					$members[] = $member;
				}
				mysql_data_seek($this->last_result, 0);
				$this->active_row = 0;
				return $members;
			}
		} else {
			$this->active_row = -1;
			$this->SetError("No query results exist", -1);
			return false;
		}
	}

	/**
	 * Frees memory used by the query results and returns the function result
	 *
	 * @return boolean Returns TRUE on success or FALSE on failure
	 */
	public function Release() {
		$this->ResetError();
		if (! $this->last_result) {
			$success = true;
		} else {
			$success = @mysql_free_result($this->last_result);
			if (! $success) $this->SetError();
		}
		return $success;
	}

	/**
	 * Clears the internal variables from any error information
	 *
	 */
	private function ResetError() {
		$this->error_desc = '';
		$this->error_number = 0;
	}

	/**
	 * Reads the current row and returns contents as a
	 * PHP object or returns false on error
	 *
	 * @param integer $optional_row_number (Optional) Use to specify a row
	 * @return object PHP object or FALSE on error
	 */
	public function Row($optional_row_number = null) {
		$this->ResetError();
		if (! $this->last_result) {
			$this->SetError("No query results exist", -1);
			return false;
		} elseif ($optional_row_number === null) {
			if (($this->active_row) > $this->RowCount()) {
				$this->SetError("Cannot read past the end of the records", -1);
				return false;
			} else {
				$this->active_row++;
			}
		} else {
			if ($optional_row_number >= $this->RowCount()) {
				$this->SetError("Row number is greater than the total number of rows", -1);
				return false;
			} else {
				$this->active_row = $optional_row_number;
				$this->Seek($optional_row_number);
			}
		}
		$row = mysql_fetch_object($this->last_result);
		if (! $row) {
			$this->SetError();
			return false;
		} else {
			return $row;
		}
	}

	/**
	 * Reads the current row and returns contents as an
	 * array or returns false on error
	 *
	 * @param integer $optional_row_number (Optional) Use to specify a row
	 * @param integer $resultType (Optional) The type of array
	 *                Values can be: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	 * @return array Array that corresponds to fetched row or FALSE if no rows
	 */
	public function RowArray($optional_row_number = null, $resultType = MYSQL_BOTH) {
		$this->ResetError();
		if (! $this->last_result) {
			$this->SetError("No query results exist", -1);
			return false;
		} elseif ($optional_row_number === null) {
			if (($this->active_row) > $this->RowCount()) {
				$this->SetError("Cannot read past the end of the records", -1);
				return false;
			} else {
				$this->active_row++;
			}
		} else {
			if ($optional_row_number >= $this->RowCount()) {
				$this->SetError("Row number is greater than the total number of rows", -1);
				return false;
			} else {
				$this->active_row = $optional_row_number;
				$this->Seek($optional_row_number);
			}
		}
		$row = mysql_fetch_array($this->last_result, $resultType);
		if (! $row) {
			$this->SetError();
			return false;
		} else {
			return $row;
		}
	}

	/**
	 * Returns the last query row count
	 *
	 * @return integer Row count or FALSE on error
	 */
	public function RowCount() {
		$this->ResetError();
		if (! $this->IsConnected()) {
			$this->SetError("No connection", -1);
			return false;
		} elseif (! $this->last_result) {
			$this->SetError("No query results exist", -1);
			return false;
		} else {
			$result = @mysql_num_rows($this->last_result);
			if (! $result) {
				$this->SetError();
				return false;
			} else {
				return $result;
			}
		}
	}

	/**
	 * Sets the internal database pointer to the
	 * specified row number and returns the result
	 *
	 * @param integer $row_number Row number
	 * @return object Fetched row as PHP object
	 */
	public function Seek($row_number) {
		$this->ResetError();
		$row_count = $this->RowCount();
		if (! $row_count) {
			return false;
		} elseif ($row_number >= $row_count) {
			$this->SetError("Seek parameter is greater than the total number of rows", -1);
			return false;
		} else {
			$this->active_row = $row_number;
			$result = mysql_data_seek($this->last_result, $row_number);
			if (! $result) {
				$this->SetError();
				return false;
			} else {
				$record = mysql_fetch_row($this->last_result);
				if (! $record) {
					$this->SetError();
					return false;
				} else {
					// Go back to the record after grabbing it
					mysql_data_seek($this->last_result, $row_number);
					return $record;
				}
			}
		}
	}

	/**
	 * Selects a different database and character set
	 *
	 * @param string $database Database name
	 * @param string $charset (Optional) Character set (i.e. utf8)
	 * @return boolean Returns TRUE on success or FALSE on error
	 */
	public function SelectDatabase($database, $charset = "") {
		$return_value = true;
		if (! $charset) $charset = $this->db_charset;
		$this->ResetError();
		if (! (mysql_select_db($database))) {
			$this->SetError();
			$return_value = false;
		} else {
			if ((strlen($charset) > 0)) {
				if (! (mysql_query("SET CHARACTER SET '{$charset}'", $this->mysql_link))) {
					$this->SetError();
					$return_value = false;
				}
			}
		}
		return $return_value;
	}

	/**
	 * Gets rows in a table based on a WHERE filter
	 *
	 * @param string $tableName The name of the table
	 * @param array $whereArray (Optional) An associative array containing the
	 *                          column names as keys and values as data. The
	 *                          values must be SQL ready (i.e. quotes around
	 *                          strings, formatted dates, ect)
	 * @param array/string $columns (Optional) The column or list of columns to select
	 * @param array/string $sortColumns (Optional) Column or list of columns to sort by
	 * @param boolean $sortAscending (Optional) TRUE for ascending; FALSE for descending
	 *                               This only works if $sortColumns are specified
	 * @param integer/string $limit (Optional) The limit of rows to return
	 * @return boolean Returns records on success or FALSE on error
	 */
	public function SelectRows($tableName, $whereArray = null, $columns = null,
							   $sortColumns = null, $sortAscending = true,
							   $limit = null) {
		$this->ResetError();
		if (! $this->IsConnected()) {
			$this->SetError("No connection");
			return false;
		} else {
			$sql = self::BuildSQLSelect($tableName, $whereArray,
					$columns, $sortColumns, $sortAscending, $limit);
			// Execute the UPDATE
			if (! $this->Query($sql)) {
				return $this->last_result;
			} else {
				return false;
			}
		}
	}

	/**
	 * Sets the local variables with the last error information
	 *
	 * @param string $errorMessage The error description
	 * @param integer $errorNumber The error number
	 */
	private function SetError($errorMessage = '', $errorNumber = 0) {
		try {
			if (strlen($errorMessage) > 0) {
				$this->error_desc = $errorMessage;
			} else {
				if ($this->IsConnected()) {
					$this->error_desc = mysql_error($this->mysql_link);
				} else {
					$this->error_desc = mysql_error();
				}
			}
			if ($errorNumber <> 0) {
				$this->error_number = $errorNumber;
			} else {
				if ($this->IsConnected()) {
					$this->error_number = @mysql_errno($this->mysql_link);
				} else {
					$this->error_number = @mysql_errno();
				}
			}
		} catch(Exception $e) {
			$this->error_desc = $e->getMessage();
			$this->error_number = -999;
		}
		if ($this->ThrowExceptions) {
			throw new Exception($this->error_desc);
		}
	}

	/**
	 * [STATIC] Formats any value into a string suitable for SQL statements
	 * (NOTE: Also supports data types returned from the gettype function)
	 *
	 * @param mixed $value Any value of any type to be formatted to SQL
	 * @param string $datatype Use SQLVALUE constants or the strings:
	 *                          string, text, varchar, char, boolean, bool,
	 *                          Y-N, T-F, bit, date, datetime, time, integer,
	 *                          int, number, double, float
	 * @return string
	 */
	static public function SQLValue($value, $datatype = self::SQLVALUE_TEXT) {
		$return_value = "";

		switch (strtolower(trim($datatype))) {
			case "text":
			case "string":
			case "varchar":
			case "char":
				if (strlen($value) == 0) {
					$return_value = "NULL";
				} else {
					$return_value = "'" . str_replace("'", "''", $value) . "'";
				}
				break;
			case "number":
			case "integer":
			case "int":
			case "double":
			case "float":
				if (is_numeric($value)) {
					$return_value = $value;
				} else {
					$return_value = "NULL";
				}
				break;
			case "boolean":  //boolean to use this with a bit field
			case "bool":
			case "bit":
				if (self::GetBooleanValue($value)) {
				   $return_value = "1";
				} else {
				   $return_value = "0";
				}
				break;
			case "y-n":  //boolean to use this with a char(1) field
				if (self::GetBooleanValue($value)) {
					$return_value = "'Y'";
				} else {
					$return_value = "'N'";
				}
				break;
			case "t-f":  //boolean to use this with a char(1) field
				if (self::GetBooleanValue($value)) {
					$return_value = "'T'";
				} else {
					$return_value = "'F'";
				}
				break;
			case "date":
				if (self::IsDate($value)) {
					$return_value = "'" . date('Y-m-d', strtotime($value)) . "'";
				} else {
					$return_value = "NULL";
				}
				break;
			case "datetime":
				if (self::IsDate($value)) {
					$return_value = "'" . date('Y-m-d H:i:s', strtotime($value)) . "'";
				} else {
					$return_value = "NULL";
				}
				break;
			case "time":
				if (self::IsDate($value)) {
					$return_value = "'" . date('H:i:s', strtotime($value)) . "'";
				} else {
					$return_value = "NULL";
				}
				break;
			default:
				exit("ERROR: Invalid data type specified in SQLValue method");
		}
		return $return_value;
	}

	/**
	 * Updates rows in a table based on a WHERE filter
	 * (can be just one or many rows based on the filter)
	 *
	 * @param string $tableName The name of the table
	 * @param array $valuesArray An associative array containing the column
	 *                            names as keys and values as data. The values
	 *                            must be SQL ready (i.e. quotes around
	 *                            strings, formatted dates, ect)
	 * @param array $whereArray (Optional) An associative array containing the
	 *                           column names as keys and values as data. The
	 *                           values must be SQL ready (i.e. quotes around
	 *                           strings, formatted dates, ect). If not specified
	 *                           then all values in the table are updated.
	 * @return boolean Returns TRUE on success or FALSE on error
	 */
	public function UpdateRows($tableName, $valuesArray, $whereArray = null) {
		$this->ResetError();
		if (! $this->IsConnected()) {
			$this->SetError("No connection");
			return false;
		} else {
			$sql = self::BuildSQLUpdate($tableName, $valuesArray, $whereArray);
			// Execute the UPDATE
			if (! $this->Query($sql)) {
				return false;
			} else {
				return true;
			}

		}
	}

}
?>

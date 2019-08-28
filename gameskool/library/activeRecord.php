<?

	/**
	 *  Active record for a database table
	 *    record values are arrays as: field name = array(new value, old value, enclose in quotes [update/insert])
	 */
	class activeRecord {
		// database handler
		protected $dbh;
		// active record table
		protected $table;
		// existing auto increment field, this should be a field's friendly name
		protected $autoincrement = false;
		// record editor information
		protected $recordEditor = false;
		protected $recordEditorID = false;
		// history table (optional)
		protected $historyTable = false;
		// history record time location fields, all history tables must have these two fields
		//   values (not index) must match the history table fields
		//   lastModified must also be the same as the last modified field for the main table
		protected $historyDateTimeFields = array(
			'lastModified' => 'lastModified',
			'effectiveThrough' => 'effectiveThrough'
		);
		// array: unique id fields
		protected $idFields;
		// field array
		//   array(external name => array(field name, field type, min chars, max chars, label))
		protected $fields;
		// error arrays
		protected $errors = array();
		protected $errorFields = array();

		/**
		 *  Constructor
		 *  Args: (mixed) id fields (construct new record if empty)
		 *  Return: none
		 */
		public function __construct($id = NULL) {
			$this->dbh = database::getInstance();
			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
			$this->initialize();
		} // function __construct

		/**
		 *  Reset field values
		 *    array(current value, original value, enclose quotes)
		 *  Args: none
		 *  Return: none
		 */
		public function reset() {
			foreach ($this->fields as $key => $vals) {
				$this->$vals[0] = array(NULL, NULL, true);
			}
			$this->clearErrors();
			$this->clearErrorFields();
		} // function reset

		/**
		 *  Load record by unique id
		 *  Args: (mixed) id can be array/str/int
		 *  Return: (boolean) success
		 */
		public function load($id) {
			$this->reset();
			if (!is_array($id)) {
				$id = array($id);
			}
			if (!array_diff(array_keys($this->idFields), array_keys($id))) {
				$identifier = array();
				foreach ($this->idFields as $key => $val) {
					$identifier[] = "`".$this->fields[$val][0]."` = '".prep($id[$key])."'";
				}
				$fields = array();
				foreach ($this->fields as $vals) {
					$fields[] = $vals[0];
				}
				$sql = "SELECT `".implode('`, `', $fields)."` 
						FROM `".$this->table."` 
						WHERE ".implode(' AND ', $identifier);
				$result = $this->dbh->query($sql);
				if ($result->rowCount > 0) {
					$row = $result->fetchRow();
					foreach ($row as $key => $val) {
						$this->$key = array($val, $val, true);
					}
					return true;
				}
			}
			return false;
		} // function load

		/**
		 *  Load record by data array
		 *  Args: (array) record data
		 *  Return: (boolean) success
		 */
		public function loadRecord($data) {
			$this->reset();
			foreach ($data as $key => $val) {
				if (isset($this->$key)) {
					$this->$key = array($val, $val, true);
				}
			}
			return true;
		} // function loadRecord

		/**
		 *  Save new record
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function save() {
			if (!$this->assertValidData()) {
				return false;
			}
			if ($this->isDuplicate()) {
				return false;
			}
			$this->assertDataFormats();
			$this->assertSaveDefaults();
			$insertFields = array();
			$insertValues = array();
			foreach ($this->fields as $key => $vals) {
				if (isset($this->{$vals[0]}[0])) {
					$insertFields[] = '`'.$vals[0].'`';
					if ($this->{$vals[0]}[2]) {
						$insertValues[] = "'".prep($this->{$vals[0]}[0])."'";
					} else {
						$insertValues[] = $this->{$vals[0]}[0];
					}
				}
			}
			$sql = "INSERT INTO `".$this->table."` (".implode(', ', $insertFields).") 
					VALUES (".implode(', ', $insertValues).")";
			$result = $this->dbh->query($sql);
			if ($result->rowCount == 1) {
				if ($this->autoincrement) {
					if ($result->insertID) {
						$this->set($this->autoincrement, $result->insertID);
					} else {
						trigger_error('Active Record Error: Unable to retrieve autoincrement from '.$this->table.' insert [sql: '.$sql.'] [error: '.$result->sqlError.']', E_USER_ERROR);
						return false;
					}
				}
				$id = array();
				foreach ($this->idFields as $key => $val) {
					$id[$key] = $this->{$val}[0];
				}
				if ($this->load($id)) {
					$this->logHistory('SAVE', 'New record');
					return true;
				}
			} else {
				trigger_error('Active Record Error: Save fail for '.$this->table.' [sql: '.$sql.'] [error: '.$result->sqlError.']', E_USER_ERROR);
			}
			return false;
		} // function save

		/**
		 *  Update current record
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function update() {
			if (!$this->assertValidData()) {
				return false;
			}
			$tableFields = array();
			foreach ($this->fields as $key => $vals) {
				$tableFields[] = '`'.$vals[0].'`';
			}
			$identifier = array();
			foreach ($this->idFields as $field) {
				$identifier[] = "`".$this->fields[$field][0]."` = '".prep($this->{$field}[1])."'";
			}
			$this->assertDataFormats();
			$updates = array();
			foreach ($this->fields as $key => $vals) {
				if ((string) $this->{$vals[0]}[0] !== (string) $this->{$vals[0]}[1]) {
					$updates[$vals[0]] = '`'.$vals[0].'` = '.($this->{$vals[0]}[2] ? "'".prep($this->{$vals[0]}[0])."'" : $this->{$vals[0]}[0]);
				}
			}
			if (!empty($updates)) {
				if ($this->isDuplicate()) {
					return false;
				}
				$this->assertUpdateDefaults();
				foreach ($this->fields as $key => $vals) {
					if (!isset($updates[$vals[0]]) && (string) $this->{$vals[0]}[0] !== (string) $this->{$vals[0]}[1]) {
						$updates[$vals[0]] = '`'.$vals[0].'` = '.($this->{$vals[0]}[2] ? "'".prep($this->{$vals[0]}[0])."'" : $this->{$vals[0]}[0]);
					}
				}
				$sql = "UPDATE `".$this->table."` 
						SET ".implode(', ', $updates)." 
						WHERE ".implode(' AND ', $identifier);
				$result = $this->dbh->query($sql);
				if ($result->rowCount > 0 || empty($result->sqlError)) {
					$id = array();
					foreach ($this->idFields as $key => $val) {
						$id[$key] = $this->{$val}[0];
					}
					if ($this->load($id)) {
						$comment = 'Fields updated: '.implode(', ', array_keys($updates));
						$this->logHistory('UPDATE', $comment);
						return true;
					}
				} else {
					trigger_error('Active Record Error: Update fail for '.$this->table.' [sql: '.$sql.'] [error: '.$result->sqlError.']', E_USER_ERROR);
				}
			} else {
				return true;
			}
			return false;
		} // function update

		/**
		 *  Log to history table if applicable
		 *    history tables must have the date time fields specified in the object vars
		 *  Args: (str) save or update type logging, (str) comments
		 *  Return: (boolean) success
		 */
		public function logHistory($type, $comments) {
			if ($this->historyTable) {
				$tableFields = array();
				foreach ($this->fields as $key => $vals) {
					if (isset($this->{$vals[0]}[0])) {
						$tableFields[] = '`'.$vals[0].'`';
					}
				}
				$identifier = array();
				foreach ($this->idFields as $field) {
					$identifier[] = "`".$this->fields[$field][0]."` = '".$this->{$field}[0]."'";
				}
				if ($type == 'UPDATE') {
					$effectiveThrough = date('Y-m-d H:i:s', strtotime('-1 second', strtotime($this->{$this->historyDateTimeFields['lastModified']}[0])));
					$sql = "UPDATE `".$this->historyTable."` 
							SET `".$this->historyDateTimeFields['effectiveThrough']."` = IF(
								'".$effectiveThrough."' < `".$this->historyDateTimeFields['lastModified']."`, 
								`".$this->historyDateTimeFields['lastModified']."`, 
								'".$effectiveThrough."'
							) WHERE ".implode(' AND ', $identifier)." 
							AND `".$this->historyDateTimeFields['effectiveThrough']."` = '9999-12-31 23:59:59'";
					$result = $this->dbh->query($sql);
					if ($result->rowCount < 1) {
						trigger_error('Active Record History Error: History log update failed for '.$this->table.' [sql: '.$sql.'] [error: '.$result->sqlError.']', E_USER_WARNING);
					}
				}
				list($editorType, $editorID) = $this->getRecordEditor();
				$sql = "INSERT INTO `".$this->historyTable."` (".implode(', ', $tableFields).", `".$this->historyDateTimeFields['effectiveThrough']."`, `recordEditor`, `recordEditorID`, `action`, `comments`) 
						SELECT ".implode(', ', $tableFields).", '9999-12-31 23:59:59', 
							'".$editorType."', '".$editorID."', '".$type."', '".prep($comments)."' 
						FROM `".$this->table."` 
						WHERE ".implode(' AND ', $identifier);
				$result = $this->dbh->query($sql);
				if ($result->rowCount < 1) {
					trigger_error('Active Record History Error: History log failed for '.$this->table.' [sql: '.$sql.'] [error: '.$result->sqlError.']', E_USER_WARNING);
					return false;
				}
			}
			return true;
		} // function logHistory

		/**
		 *  Set record editor values
		 *  Args: (str) editor type, (int) editor id
		 *  Return: none
		 */
		public function setRecordEditor($type, $id) {
			$this->recordEditor = $type;
			$this->recordEditorID = $id;
		} // function setRecordEditor

		/**
		 *  Get record editor information
		 *  Args: none
		 *  Return: (array) record editor information
		 */
		public function getRecordEditor() {
			if ($this->recordEditor) {
				return array($this->recordEditor, $this->recordEditorID);
			} elseif (adminCore::isLoggedIn()) {
				$user = adminCore::getAdminUser();
				return array('ADMIN', $user['adminUserID']);
			} elseif (userCore::isLoggedIn()) {
				$user = userCore::getUser();
				return array('USER', $user['userID']);
			}
			return array('SYSTEM', 0);
		} // function getRecordEditor

		/**
		 *  Retrieve a field value
		 *  Args: (str) field name
		 *  Return: (mixed) value
		 */
		public function get($fieldName) {
			if (isset($this->fields[$fieldName])) {
				return $this->{$this->fields[$fieldName][0]}[0];
			} else {
				return NULL;
			}
		} // function get

		/**
		 *  Retrieve old value of field
		 *  Args: (str) field name
		 *  Return: (mixed) old value
		 */
		public function getOldValue($fieldName) {
			if (isset($this->fields[$fieldName])) {
				return $this->{$this->fields[$fieldName][0]}[1];
			} else {
				return NULL;
			}
		} // function getOldValue

		/**
		 *  Set a field value, optionally clean according to field type
		 *  Args: (str) field name, (mixed) value, (boolean) clean field type
		 *  Return: none
		 */
		public function set($fieldName, $value, $clean = true) {
			if (isset($this->fields[$fieldName])) {
				if ($clean) {
					$this->{$this->fields[$fieldName][0]}[0] = clean($value, $this->fields[$fieldName][1]);
				} else {
					$this->{$this->fields[$fieldName][0]}[0] = $value;
				}
			}
		} // function set

		/**
		 *  Indicate whether to enclose a field value in quotes on database entry
		 *  Args: (str) field name, (boolean) enclose in quotes for database
		 *  Return: none
		 */
		public function enclose($fieldName, $enclose) {
			if (isset($this->fields[$fieldName])) {
				if ($enclose) {
					$this->{$this->fields[$fieldName][0]}[2] = true;
				} else {
					$this->{$this->fields[$fieldName][0]}[2] = false;
				}
			}
		} // function enclose

		/**
		 *  Return array of id field values
		 *  Args: none
		 *  Return: (array) id values
		 */
		public function getID() {
			if ($this->exists()) {
				$id = array();
				foreach ($this->idFields as $key => $field) {
					$id[$key] = $this->{$field}[0];
				}
				return $id;
			} else {
				return NULL;
			}
		} // function getID

		/**
		 *  Return field/value pairs for the active record
		 *  Args: none
		 *  Return: (array) active record field value pairs
		 */
		public function fetchArray() {
			$record = array();
			foreach ($this->fields as $key => $vals) {
				$record[$key] = $this->{$key}[0];
			}
			return $record;
		} // function fetchArray

		/**
		 *  Return true if record exists (id values are set)
		 *  Args: none
		 *  Return: (boolean) existing record
		 */
		public function exists() {
			$exists = true;
			foreach ($this->idFields as $field) {
				if (!$this->{$field}[0]) {
					$exists = false;
				}
			}
			return $exists;
		} // function exists

		/**
		 *  Return true if a field value has changed
		 *  Args: (str) field name
		 *  Return: (boolean) value changed
		 */
		public function isNewValue($fieldName) {
			if (isset($this->fields[$fieldName])) {
				return $this->{$this->fields[$fieldName][0]}[0] !== $this->{$this->fields[$fieldName][0]}[1];
			}
		} // function isNewValue

		/**
		 *  Add an item to the error array
		 *  Args: (str) error message, (str) error index
		 *  Return: none
		 */
		public function addError($error, $index = false) {
			if ($index !== false) {
				$this->errors[$index] = $error;
			} else {
				$this->errors[] = $error;
			}
		} // function addError

		/**
		 *  Add an item from the error array by a known index
		 *  Args: (str) error index
		 *  Return: none
		 */
		public function removeError($index) {
			if (isset($this->errors[$index])) {
				unset($this->errors[$index]);
			}
		} // function removeError

		/**
		 *  Clear the error array
		 *  Args: none
		 *  Return: none
		 */
		public function clearErrors() {
			$this->errors = array();
		} // function clearErrors

		/**
		 *  Retrieve error array
		 *  Args: none
		 *  Return: (array) error array
		 */
		public function getErrors() {
			return $this->errors;
		} // function getErrors

		/**
		 *  Add a field name to the error field array
		 *  Args: (str) field name
		 *  Return: none
		 */
		public function addErrorField($fieldName) {
			$this->errorFields[$fieldName] = true;
		} // function addErrorField

		/**
		 *  Remove a field name to the error field array
		 *  Args: (str) field name
		 *  Return: none
		 */
		public function removeErrorField($fieldName) {
			if (isset($this->errorFields[$fieldName])) {
				unset($this->errorFields[$fieldName]);
			}
		} // function removeErrorField

		/**
		 *  Clear the error fields array
		 *  Args: none
		 *  Return: none
		 */
		public function clearErrorFields() {
			$this->errorFields = array();
		} // function clearErrorFields

		/**
		 *  Retrieve error fields array
		 *  Args: none
		 *  Return: (array) error fields array
		 */
		public function getErrorFields() {
			return $this->errorFields;
		} // function getErrorFields

		/**
		 *  Push internal error or error fields array to system messages
		 *  Args: (str) array to push
		 *  Return: none
		 */
		public function updateSystemMessages($type = false) {
			if (!$type || $type == 'errors') {
				$errors = $this->getErrors();
				foreach ($errors as $error) {
					addError($error);
				}
			}
			if (!$type || $type == 'errorFields') {
				$errorFields = $this->getErrorFields();
				foreach ($errorFields as $errorField => $val) {
					addErrorField($errorField);
				}
			}
		} // function updateSystemMessages

		/**
		 *  Make a field required
		 *  Args: (str) field name
		 *  Return: none
		 */
		public function makeRequired($field) {
			if (isset($this->fields[$field])) {
				$this->fields[$field][2] = 1;
			}
		} // function makeRequired

		/**
		 *  Make a field not required
		 *  Args: (str) field name
		 *  Return: none
		 */
		public function unRequire($field) {
			if (isset($this->fields[$field])) {
				$this->fields[$field][2] = 0;
			}
		} // function unRequire

		/**
		 *  Assert all required fields are present are met for save/update
		 *  Args: none
		 *  Return: (boolean) validation result
		 */
		public function assertRequired() {
			$errors = array();
			foreach ($this->fields as $key => $vals) {
				if ($vals[2]) {
					if (isset($this->{$vals[0]}[0])) {
						$length = strlen($this->{$vals[0]}[0]);
						if ($length < $vals[2]) {
							$errors[$key] = $this->fields[$key][4].' is missing';
						} elseif ($length > $vals[3]) {
							$errors[$key] = $this->fields[$key][4].' has exceeded the character limit';
						}
					} else {
						$errors[$key] = $this->fields[$key][4].' is missing';
					}
				}
			}
			if (empty($errors)) {
				return true;
			} else {
				foreach ($errors as $field => $error) {
					$this->addErrorField($field);
					$this->addError($error);
				}
				return false;
			}
		} // function assertRequired

		/**
		 *  Set a field value to NULL for database entry if the field value matches the comparison value
		 *  Args: (str) field name, (str) comparison value
		 *  Return: none
		 */
		public function equateToNull($field, $compareValue) {
			if (isset($this->fields[$field])) {
				if ((string) $this->{$field}[0] === (string) $compareValue) {
					if (!is_null($this->{$field}[1])) {
						$this->set($field, 'NULL', false);
						$this->enclose($field, false);
					} else {
						$this->set($field, NULL, false);
					}
				}
			}
		} // function equateToNull

		/**
		 *  Perform any object setup on construct
		 *    OVERRIDE AS NEEDED
		 *  Args: none
		 *  Return: none
		 */
		public function initialize() {
		} // function initialize

		/**
		 *  Perform spectific data check, make sure data is as expected
		 *    OVERRIDE AS NEEDED
		 *  Args: none
		 *  Return: (boolean) validation result
		 */
		public function assertValidData() {
			return $this->assertRequired();
		} // function assertValidData

		/**
		 *  Format any data necessary before save/update
		 *    OVERRIDE AS NEEDED
		 *  Args: none
		 *  Return: none
		 */
		public function assertDataFormats() {
		} // function assertDataFormats

		/**
		 *  Set defaults for saving
		 *    OVERRIDE AS NEEDED
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
		} // function assertSaveDefaults

		/**
		 *  Set defaults for updating
		 *    OVERRIDE AS NEEDED
		 *  Args: none
		 *  Return: none
		 */
		public function assertUpdateDefaults() {
		} // function assertUpdateDefaults

		/**
		 *  Override with appropriate method if duplicate needs to be checked before save or update
		 *    OVERRIDE AS NEEDED
		 *  Args: none
		 *  Return: (boolean) duplicate record found
		 */
		public function isDuplicate() {
			return false;
		} // function isDuplicate
	} // class activeRecord

?>
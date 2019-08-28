<?

	class error extends activeRecord {
		// active record table
		protected $table = 'errors';
		// existing auto increment field
		protected $autoincrement = 'errorID';
		// array unique id fields
		protected $idFields = array(
			'errorID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'errorID'      => array('errorID', 'integer', 0, 10, 'Error ID'),
			'class'        => array('class', 'alphanum', 1, 10, 'Error Class'),
			'code'         => array('code', 'integer', 1, 10, 'Error Code'),
			'type'         => array('type', 'word', 1, 20, 'Error Type'),
			'file'         => array('file', 'filename', 1, 255, 'Error File'),
			'line'         => array('line', 'integer', 1, 10, 'Error Line'),
			'function'     => array('function', 'functionName', 1, 45, 'Error Function'),
			'message'      => array('message', 'comment', 0, 999999, 'Error Message'),
			'date'         => array('date', 'date', 1, 10, 'Error Date'),
			'count'        => array('count', 'integer', 1, 10, 'Error Count'),
			'status'       => array('status', 'alpha', 1, 20, 'Error Status'),
			'trace'        => array('trace', 'comment', 1, 999999, 'Error Trace'),
			'comments'     => array('comments', 'comment', 0, 999999, 'Comment'),
			'lastModified' => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('errorID', NULL, false);
			$this->set('date', 'NOW()', false);
			$this->enclose('date', false);
			$this->set('lastModified', 'NOW()', false);
			$this->enclose('lastModified', false);
		} // function assertSaveDefaults

		/**
		 *  Set defaults for updating
		 *  Args: none
		 *  Return: none
		 */
		public function assertUpdateDefaults() {
			$this->set('lastModified', 'NOW()', false);
			$this->enclose('lastModified', false);
		} // function assertUpdateDefaults

		/**
		 *  Check for duplicate error
		 *  Args: none
		 *  Return: (boolean) is duplicate error
		 */
		public function isDuplicate() {
			$duplicate = false;
			$sql = "SELECT `errorID` 
					FROM `".$this->table."` 
					WHERE `class` = '".prep($this->get('class'))."' 
					AND `code` = '".prep($this->get('code'))."' 
					AND `file` = '".prep($this->get('file'))."' 
					AND `line` = '".prep($this->get('line'))."' 
					AND `function` = '".prep($this->get('function'))."' 
					AND `message` = '".prep($this->get('message'))."' 
					AND `date` = '".prep($this->get('date'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('errorID');
				while ($row = $result->fetchRow()) {
					if ($row['errorID'] != $id) {
						$this->addError('This is an existing error', 'duplicate');
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate
	} // class error

?>
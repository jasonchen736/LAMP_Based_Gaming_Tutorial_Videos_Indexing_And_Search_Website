<?

	class email extends activeRecord {
		// active record table
		protected $table = 'emails';
		// existing auto increment field
		protected $autoincrement = 'emailID';
		// history table (optional)
		protected $historyTable = 'emailsHistory';
		// array unique id fields
		protected $idFields = array(
			'emailID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'emailID'      => array('emailID', 'integer', 0, 11, 'Email ID'),
			'name'         => array('name', 'alphanumspace', 1, 255, 'Email Name'),
			'subject'      => array('subject', 'emailSubject', 1, 255, 'Subject'),
			'html'         => array('html', 'html-email', 0, 999999, 'HTML'),
			'text'         => array('text', 'html-email', 0, 999999, 'Text'),
			'fromEmail'    => array('fromEmail', 'email', 0, 100, 'From Email'),
			'headerID'     => array('headerID', 'integer', 0, 10, 'Email Header'),
			'footerID'     => array('footerID', 'integer', 0, 10, 'Email Footer'),
			'dateAdded'    => array('dateAdded', 'datetime', 0, 19, 'Date Added'),
			'lastModified' => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('emailID', NULL, false);
			$this->set('dateAdded', 'NOW()', false);
			$this->enclose('dateAdded', false);
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
		 *  Check for duplicate email template based on unique email name
		 *  Args: none
		 *  Return: (boolean) is duplicate email
		 */
		public function isDuplicate() {
			$duplicate = false;
			$sql = "SELECT `emailID` FROM `".$this->table."` WHERE `name` = '".prep($this->get('name'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('emailID');
				while ($row = $result->fetchRow()) {
					if ($row['emailID'] != $id) {
						$this->addError('There is an existing email template with the same name', 'duplicate');
						$this->addErrorField('name');
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate
	} // class email

?>
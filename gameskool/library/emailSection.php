<?

	class emailSection extends activeRecord {
		// active record table
		protected $table = 'emailSections';
		// existing auto increment field
		protected $autoincrement = 'emailSectionID';
		// history table (optional)
		protected $historyTable = 'emailSectionsHistory';
		// array unique id fields
		protected $idFields = array(
			'emailSectionID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'emailSectionID' => array('emailSectionID', 'integer', 0, 11, 'Email Section ID'),
			'type'           => array('type', 'alpha', 1, 10, 'Type'),
			'name'           => array('name', 'alphanumspace', 1, 45, 'Name'),
			'html'           => array('html', 'html-email', 0, 999999, 'HTML'),
			'text'           => array('text', 'html-email', 0, 999999, 'Text'),
			'dateAdded'      => array('dateAdded', 'datetime', 0, 19, 'Date Added'),
			'lastModified'   => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('emailSectionID', NULL, false);
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
		 *  Check for duplicate email section template based on unique type and name
		 *  Args: none
		 *  Return: (boolean) is duplicate email section
		 */
		public function isDuplicate() {
			$duplicate = false;
			$sql = "SELECT `emailSectionID` FROM `".$this->table."` WHERE `type` = '".prep($this->get('type'))."' AND `name` = '".prep($this->get('name'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('emailSectionID');
				while ($row = $result->fetchRow()) {
					if ($row['emailSectionID'] != $id) {
						$this->addError('There is an existing email section with the same name', 'duplicate');
						$this->addErrorField('type');
						$this->addErrorField('name');
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate
	} // class emailSection

?>
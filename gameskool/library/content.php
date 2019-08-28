<?

	class content extends activeRecord {
		// active record table
		protected $table = 'content';
		// existing auto increment field
		protected $autoincrement = 'contentID';
		// history table (optional)
		protected $historyTable = 'contentHistory';
		// array unique id fields
		protected $idFields = array(
			'contentID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'contentID'       => array('contentID', 'integer', 0, 10, 'Content ID'),
			'name'            => array('name', 'contentName', 1, 45, 'Name'),
			'content'         => array('content', 'html', 1, 1000000, 'Content'),
			'metaDescription' => array('metaDescription', 'metainfo', 0, 1000000, 'Meta Description'),
			'metaKeywords'    => array('metaKeywords', 'metainfo', 0, 1000000, 'Meta Keywords'),
			'status'          => array('status', 'alpha', 1, 10, 'Status'),
			'dateAdded'       => array('dateAdded', 'datetime', 0, 19, 'Date Added'),
			'lastModified'    => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('contentID', NULL, false);
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
		 *  Check for duplicate email based on unique content name
		 *  Args: none
		 *  Return: (boolean) is duplicate content
		 */
		public function isDuplicate() {
			$duplicate = false;
			$sql = "SELECT `contentID` FROM `".$this->table."` WHERE `name` = '".prep($this->get('name'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('contentID');
				while ($row = $result->fetchRow()) {
					if ($row['contentID'] != $id) {
						$this->addError('There is an existing content article with the same name', 'duplicate');
						$this->addErrorField('name');
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate
	} // class content

?>
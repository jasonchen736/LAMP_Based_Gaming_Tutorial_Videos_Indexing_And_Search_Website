<?

	class siteTag extends activeRecord {
		// active record table
		protected $table = 'siteTags';
		// existing auto increment field
		protected $autoincrement = 'siteTagID';
		// history table (optional)
		protected $historyTable = 'siteTagsHistory';
		// array unique id fields
		protected $idFields = array(
			'siteTagID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'siteTagID'    => array('siteTagID', 'integer', 0, 10, 'Site Tag ID'),
			'referrer'     => array('referrer', 'alphanumspace', 1, 45, 'Referrer'),
			'description'  => array('description', 'alphanumspace', 1, 100, 'Description'),
			'matchType'    => array('matchType', 'alphanumspace', 1, 20, 'Match Type'),
			'matchValue'   => array('matchValue', 'regex', 1, 60, 'Match Value'),
			'placement'    => array('placement', 'alpha', 1, 10, 'Placement'),
			'weight'       => array('weight', 'integer', 1, 3, 'Weight'),
			'HTTP'         => array('HTTP', 'siteTag', 0, 999999, 'HTTP Tag'),
			'HTTPS'        => array('HTTPS', 'siteTag', 0, 999999, 'HTTPS Tag'),
			'status'       => array('status', 'alpha', 1, 10, 'Status'),
			'dateAdded'    => array('dateAdded', 'datetime', 0, 19, 'Date Added'),
			'lastModified' => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('siteTagID', NULL, false);
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
		 *  Check for duplicate site tag
		 *  Args: none
		 *  Return: (boolean) is duplicate site tag
		 */
		public function isDuplicate() {
			$duplicate = false;
			$sql = "SELECT `siteTagID` 
					FROM `".$this->table."` 
					WHERE `referrer` = '".prep($this->get('referrer'))."' 
					AND `description` = '".prep($this->get('description'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('siteTagID');
				while ($row = $result->fetchRow()) {
					if ($row['siteTagID'] != $id) {
						$this->addError('Duplicate site tag', 'duplicate');
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate
	} // class siteTag

?>
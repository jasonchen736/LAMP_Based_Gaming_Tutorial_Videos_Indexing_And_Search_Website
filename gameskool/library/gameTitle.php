<?

	class gameTitle extends activeRecord {
		// active record table
		protected $table = 'gameTitles';
		// existing auto increment field
		protected $autoincrement = 'gameTitleID';
		// array unique id fields
		protected $idFields = array(
			'gameTitleID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'gameTitleID'  => array('gameTitleID', 'integer', 0, 10, 'Game Title ID'),
			'gameTitle'    => array('gameTitle', 'gameTitle', 1, 255, 'Game Title'),
			'gameTitleURL' => array('gameTitleURL', 'gameTitle', 1, 255, 'Game Title URL'),
			'gameTitleKey' => array('gameTitleKey', 'alphanum', 1, 255, 'Game Title Key'),
			'dateAdded'    => array('dateAdded', 'datetime', 0, 19, 'Date Added')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('gameTitleID', NULL, false);
			$this->set('dateAdded', 'NOW()', false);
			$this->enclose('dateAdded', false);
		} // function assertSaveDefaults

		/**
		 *  Check for duplicate based on unique title and unique game title alphanumeric key
		 *  Args: none
		 *  Return: (boolean) is duplicate game title
		 */
		public function isDuplicate() {
			$duplicate = false;
			$id = $this->get('gameTitleID');
			$sql = "SELECT `gameTitleID` 
					FROM `".$this->table."` 
					WHERE `gameTitleURL` = '".prep($this->get('gameTitleURL'))."' 
						UNION 
					SELECT `gameTitleID` 
					FROM `".$this->table."` 
					WHERE `gameTitleKey` = '".prep(clean($this->get('gameTitle'), 'alphanum'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					if ($row['gameTitleID'] != $id) {
						$this->addError('this game title already exists', 'duplicate');
						$this->addErrorField('gameTitle');
						$duplicate = true;
						break;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate
	} // class gameTitle

?>

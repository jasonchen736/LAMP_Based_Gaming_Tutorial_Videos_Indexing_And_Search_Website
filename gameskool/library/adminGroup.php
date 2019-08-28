<?

	class adminGroup extends activeRecord {
		// active record table
		protected $table = 'adminGroups';
		// existing auto increment field
		protected $autoincrement = 'adminGroupID';
		// array unique id fields
		protected $idFields = array(
			'adminGroupID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'adminGroupID' => array('adminGroupID', 'integer', 0, 10, 'User ID'),
			'name'         => array('name', 'alphanumspace', 1, 45, 'Name')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('adminGroupID', NULL, false);
		} // function assertSaveDefaults

		/**
		 *  Check for duplicate admin group name
		 *  Args: none
		 *  Return: (boolean) is duplicate admin group name
		 */
		public function isDuplicate() {
			$duplicate = false;
			$sql = "SELECT `adminGroupID` FROM `".$this->table."` WHERE `name` = '".prep($this->get('name'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('adminGroupID');
				while ($row = $result->fetchRow()) {
					if ($row['adminGroupID'] != $id) {
						$this->addError('There is an existing admin group with the same name', 'duplicate');
						$this->addErrorField('name');
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate

		/**
		 *  Set admin group access
		 *  Args: (array) access
		 *  Return: none
		 */
		public function setAccess($access) {
			
			if ($this->exists()) {
				assertArray($access);
				$currentAccess = $this->getAccess();
				$newAccess = array();
				$removeAccess = array();
				foreach ($access as $key => $val) {
					if (isset(adminUser::$accessSections[$key]) && !$currentAccess[$key]) {
						$newAccess[] = $key;
					}
				}
				foreach ($currentAccess as $key => $val) {
					if (!isset($access[$key])) {
						$removeAccess[] = $key;
					}
				}
				$id = $this->get('adminGroupID');
				if (!empty($newAccess)) {
					$sql = "INSERT INTO `adminGroupAccess` (`adminGroupID`, `access`) 
							VALUES ('".$id."', '".implode("'), ('".$id."', '", $newAccess)."')";
					$this->dbh->query($sql);
				}
				if (!empty($removeAccess)) {
					$sql = "DELETE FROM `adminGroupAccess` 
							WHERE `adminGroupID` = '".$id."' 
							AND `access` IN ('".implode("', '", $removeAccess)."')";
					$this->dbh->query($sql);
				}
			}
		} // function setAccess

		/**
		 *  Retrieve admin group accesses
		 *  Args: none
		 *  Return: (array) admin user accesses
		 */
		public function getAccess() {
			$access = array();
			foreach (adminUser::$accessSections as $key => $val) {
				$access[$key] = false;
			}
			$sql = "SELECT `access` 
					FROM `adminGroupAccess` 
					WHERE `adminGroupID` = '".$this->get('adminGroupID')."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$access[$row['access']] = true;
				}
			}
			return $access;
		} // function getAccess
	} // class adminUser

?>
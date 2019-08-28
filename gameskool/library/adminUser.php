<?

	class adminUser extends activeRecord {
		// active record table
		protected $table = 'adminUsers';
		// existing auto increment field
		protected $autoincrement = 'adminUserID';
		// array unique id fields
		protected $idFields = array(
			'adminUserID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'adminUserID' => array('adminUserID', 'integer', 0, 10, 'User ID'),
			'name'        => array('name', 'alphanumspace', 1, 45, 'Name'),
			'email'       => array('email', 'email', 1, 100, 'Email'),
			'login'       => array('login', 'alphanum', 1, 45, 'Login'),
			'password'    => array('password', 'password', 1, 46, 'Password'),
			'status'      => array('status', 'alpha', 1, 10, 'Status'),
			'created'     => array('created', 'datetime', 0, 19, 'Date Created')
		);
		public static $accessSections = array(
			'SUPERADMIN' => 'Admin Users Access',
			'ADS'        => 'Ads Access',
			'CONTENT'    => 'Content Access',
			'DEVELOPER'  => 'Developer Access',
			'EMAIL'      => 'Emails Access',
			'POST'       => 'Posts Access',
			'REPAIRS'    => 'Repair Orders Access',
			'STATS'      => 'Reports Access',
			'USER'       => 'Users Access'
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('adminUserID', NULL, false);
			$this->set('created', 'NOW()', false);
			$this->enclose('created', false);
		} // function assertSaveDefaults

		/**
		 *  Format any data necessary before save/update
		 *  Args: none
		 *  Return: none
		 */
		public function assertDataFormats() {
			if ($this->isNewValue('password')) {
				$hash = generatePasswordHash($this->get('password'));
				$this->set('password', $hash);
			}
		} // function assertDataFormats

		/**
		 *  Check for duplicate admin user based on unique login
		 *  Args: none
		 *  Return: (boolean) is duplicate admin user
		 */
		public function isDuplicate() {
			$duplicate = false;
			$sql = "SELECT `adminUserID` FROM `".$this->table."` WHERE `login` = '".prep($this->get('login'))."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('adminUserID');
				while ($row = $result->fetchRow()) {
					if ($row['adminUserID'] != $id) {
						$this->addError('There is an existing admin user with the same login', 'duplicate');
						$this->addErrorField('login');
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate

		/**
		 *  Set admin user access
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
					if (isset(self::$accessSections[$key]) && !$currentAccess[$key]) {
						$newAccess[] = $key;
					}
				}
				foreach ($currentAccess as $key => $val) {
					if (!isset($access[$key])) {
						$removeAccess[] = $key;
					}
				}
				$id = $this->get('adminUserID');
				if (!empty($newAccess)) {
					$sql = "INSERT INTO `adminUserAccess` (`adminUserID`, `access`)
							VALUES ('".$id."', '".implode("'), ('".$id."', '", $newAccess)."')";
					$this->dbh->query($sql);
				}
				if (!empty($removeAccess)) {
					$sql = "DELETE FROM `adminUserAccess`
							WHERE `adminUserID` = '".$id."'
							AND `access` IN ('".implode("', '", $removeAccess)."')";
					$this->dbh->query($sql);
				}
			}
		} // function setAccess

		/**
		 *  Retrieve admin user accesses
		 *  Args: none
		 *  Return: (array) admin user accesses
		 */
		public function getAccess() {
			$access = array();
			foreach (self::$accessSections as $key => $val) {
				$access[$key] = false;
			}
			$sql = "SELECT `access`
					FROM `adminUserAccess`
					WHERE `adminUserID` = '".$this->get('adminUserID')."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$access[$row['access']] = true;
				}
			}
			return $access;
		} // function getAccess

		/**
		 *  Set admin groups
		 *  Args: (array) groups
		 *  Return: none
		 */
		public function setGroups($groups) {
			if ($this->exists()) {
				assertArray($groups);
				$allGroups = adminGroupsController::getAllGroups();
				$currentGroups = $this->getGroups();
				$newGroups = array();
				$removeGroups = array();
				foreach ($groups as $key => $val) {
					if (isset($allGroups[$key]) && !isset($currentGroups[$key])) {
						$newGroups[] = $key;
					}
				}
				foreach ($currentGroups as $key => $val) {
					if (!isset($groups[$key])) {
						$removeGroups[] = $key;
					}
				}
				$id = $this->get('adminUserID');
				if (!empty($newGroups)) {
					$sql = "INSERT INTO `adminUserGroupMap` (`adminUserID`, `adminGroupID`) 
							VALUES ('".$id."', '".implode("'), ('".$id."', '", $newGroups)."')";
					$this->dbh->query($sql);
				}
				if (!empty($removeGroups)) {
					$sql = "DELETE FROM `adminUserGroupMap` 
							WHERE `adminUserID` = '".$id."' 
							AND `adminGroupID` IN ('".implode("', '", $removeGroups)."')";
					$this->dbh->query($sql);
				}
			}
		} // function setGroups

		/**
		 *  Retrieve admin user groups
		 *  Args: none
		 *  Return: (array) admin user groups
		 */
		public function getGroups() {
			$groups = array();
			$sql = "SELECT `adminGroupID` 
					FROM `adminUserGroupMap` 
					WHERE `adminUserID` = '".$this->get('adminUserID')."'";
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$groups[$row['adminGroupID']] = true;
				}
			}
			return $groups;
		} // function getGroups

		/**
		 *  Retrieve admin user group accesses
		 *  Args: none
		 *  Return: (array) admin user group accesses
		 */
		public function getGroupAccess() {
			$access = array();
			foreach (self::$accessSections as $key => $val) {
				$access[$key] = false;
			}
			$sql = "SELECT `c`.`access` 
					FROM `adminUserGroupMap` `a`
					JOIN `adminGroups` `b` ON (`a`.`adminGroupID` = `b`.`adminGroupID`) 
					JOIN `adminGroupAccess` `c` ON (`b`.`adminGroupID` = `c`.`adminGroupID`) 
					WHERE `a`.`adminUserID` = '".$this->get('adminUserID')."'";
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

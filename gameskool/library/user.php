<?

	class user extends activeRecord {
		// active record table
		protected $table = 'users';
		// existing auto increment field
		protected $autoincrement = 'userID';
		// history table (optional)
		protected $historyTable = 'usersHistory';
		// array unique id fields
		protected $idFields = array(
			'userID'
		);
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'userID'           => array('userID', 'integer', 0, 10, 'User ID'),
			'externalProvider' => array('externalProvider', 'alpha', 0, 45, 'External Provider'),
			'externalID'       => array('externalID', 'url', 0, 255, 'External ID'),
			'email'            => array('email', 'email', 1, 60, 'email'),
			'userName'         => array('userName', 'userName', 1, 100, 'username'),
			'password'         => array('password', 'password', 1, 46, 'password'),
			'status'           => array('status', 'alphanum', 0, 15, 'Status'),
			'dateCreated'      => array('dateCreated', 'datetime', 0, 19, 'Date created'),
			'lastModified'     => array('lastModified', 'datetime', 0, 19, 'Last modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('userID', NULL, false);
			$this->set('dateCreated', 'NOW()', false);
			$this->enclose('dateCreated', false);
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
		 *  Perform spectific data check, make sure data is as expected
		 *  Args: none
		 *  Return: (boolean) validation result
		 */
		public function assertValidData() {
			$email = $this->get('email');
			if (!empty($email) && !validEmail($email)) {
				$this->addErrorField('email');
				$this->addError($this->fields['email'][4].' ain\'t right');
			}
			$this->assertRequired();
			if (!empty($this->errors) || !empty($this->errorFields)) {
				return false;
			} else {
				return true;
			}
		} // function assertValidData

		/**
		 *  Check for duplicate user based on unique email or user name
		 *  Args: none
		 *  Return: (boolean) is duplicate user
		 */
		public function isDuplicate() {
			$duplicate = false;
			$email = $this->get('email');
			if ($this->fields['externalID'][2] == 0) {
				$sql = "SELECT `userID`, `email`, `userName` FROM `".$this->table."` WHERE `email` = '".prep($this->get('email'))."' OR `userName` = '".prep($this->get('userName'))."'";
			} else {
				$sql = "SELECT `userID`, `email`, `userName`, `externalID` FROM `".$this->table."` WHERE `userName` = '".prep($this->get('userName'))."' OR `externalID` = '".prep($this->get('externalID'))."'";
				if (!is_null($email)) {
					$sql .= " OR `email` = '".prep($this->get('email'))."'";
				}
			}
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('userID');
				$userName = $this->get('userName');
				$externalID = $this->get('externalID');
				while ($row = $result->fetchRow()) {
					if ($row['userID'] != $id) {
						if ((!isset($row['externalID']) || !empty($email)) && $row['email'] == $email) {
							$this->addError('there is an existing account registered under the email address', 'email');
							$this->addErrorField('email');
						} elseif (isset($row['externalID']) && $row['externalID'] == $externalID) {
							$this->addError('there is an existing account registered under the external id', 'externalID');
							$this->addErrorField('externalID');
						} else {
							$this->addError('there is an existing account registered under the user name', 'userName');
							$this->addErrorField('userName');
						}
						$duplicate = true;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate

		/**
		 *  Matches argument password with existing
		 *  Args: (str) password
		 *  Return: (boolean) match
		 */
		public function confirmPassword($password) {
			$storedPassword = $this->get('password');
			$hash = generatePasswordHash($password, substr($storedPassword, -6));
			if ($hash === $storedPassword) {
				return true;
			} else {
				return false;
			}
		} // function confirmPassword

		/**
		 *  Reset user password
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function resetPassword() {
			if ($this->exists()) {
				$password = generatePassword(9, 4);
				$this->set('password', $password);
				if (!$this->update()) {
					return false;
				}
				$email = $this->get('email');
				if ($email) {
					$template = new template;
					$template->assign('password', $password);
					$template->assign('user', $this->fetchArray());
					$mailer = $template->getMailer('forgotPassword');
					if ($mailer->composeMessage()) {
						$mailer->addRecipient($email);
						if ($mailer->send()) {
							addSuccess('your new password has been sent to your email address');
						} else {
							$this->addError('password reset notification could not be sent', 'password');
						}
					} else {
						$this->addError('password reset notification could not be sent', 'password');
					}
				}
				return true;
			} else {
				$this->addError('user account does not exist');
				return false;
			}
		} // function resetPassword

		/**
		 *  Return current user's activation record
		 *  Args: none
		 *  Return: (userActivation) user activation object
		 */
		public function getActivationRecord() {
			$sql = "SELECT `userActivationID` 
					FROM `userActivations` 
					WHERE `userID` = '".$this->get('userID')."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				$userActivation = new userActivation($row['userActivationID']);
			} else {
				$userActivation = new userActivation;
			}
			return $userActivation;
		} // function getActivationRecord
	} // class user

?>

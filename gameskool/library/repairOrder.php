<?

	class repairOrder extends activeRecord {
		// active record table
		protected $table = 'repairOrders';
		// existing auto increment field
		protected $autoincrement = 'repairOrderID';
		// history table (optional)
		protected $historyTable = 'repairOrdersHistory';
		// array unique id fields
		protected $idFields = array(
			'repairOrderID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'repairOrderID'   => array('repairOrderID', 'integer', 0, 10, 'Repair Order ID'),
			'console'         => array('console', 'integer', 1, 10, 'Console'),
			'serial'          => array('serial', 'name', 1, 255, 'Serial Number'),
			'systemProblemID' => array('systemProblemID', 'integer', 1, 10, 'System Problem ID'),
			'description'     => array('description', 'comment', 1, 999999, 'Description'),
			'status'          => array('status', 'alphanumspace', 1, 12, 'Status'),
			'receiveMethod'   => array('receiveMethod', 'alphanumspace', 1, 8, 'Receive Method'),
			'returnMethod'    => array('returnMethod', 'alphanumspace', 1, 12, 'Return Method'),
			'user'            => array('user', 'alpha', 0, 10, 'User Type'),
			'userID'          => array('userID', 'integer', 0, 10, 'User ID'),
			'first'           => array('first', 'name', 1, 50, 'First Name'),
			'last'            => array('last', 'name', 1, 50, 'Last Name'),
			'email'           => array('email', 'email', 1, 255, 'Email'),
			'phone'           => array('phone', 'integer', 1, 20, 'Phone'),
			'address1'        => array('address1', 'address', 1, 255, 'Address 1'),
			'address2'        => array('address2', 'address', 0, 255, 'Address 2'),
			'city'            => array('city', 'address', 1, 50, 'City'),
			'state'           => array('state', 'address', 1, 50, 'State / Province'),
			'postal'          => array('postal', 'alphanumspace', 1, 10, 'Postal Code'),
			'country'         => array('country', 'alpha', 1, 3, 'Country'),
			'contact'         => array('contact', 'alpha', 1, 5, 'Contact Method'),
			'cost'            => array('cost', 'decimal', 1, 6, 'Repair Cost'),
			'orderDate'       => array('orderDate', 'datetime', 0, 19, 'Order Date'),
			'lastModified'    => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('repairOrderID', NULL, false);
			list($editorType, $editorID) = $this->getRecordEditor();
			$this->set('user', $editorType);
			$this->set('userID', $editorID);
			$this->set('orderDate', 'NOW()', false);
			$this->enclose('orderDate', false);
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
		 *  Get repair order comments
		 *  Args: none
		 *  Return: (array) comments
		 */
		public function getComments() {
			$sql = "SELECT `a`.*, `b`.`name` 
				FROM `repairOrderComments` `a` 
				JOIN `adminUsers` `b` ON (`a`.`userID` = `b`.`adminUserID`) 
				WHERE `a`.`repairOrderID` = '".$this->get('repairOrderID')."' 
				ORDER BY `a`.`dateAdded` DESC";
			$result = query($sql);
			return $result->fetchAll();
		} // function getComments

		/**
		 *  Send repair order confirmation
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function sendConfirmation() {
			if ($this->exists()) {
				$systems = systemsController::getSystems();
				$systemID = $this->get('console');
				$system = $systems[$systemID];
				$systemProblems = systemProblemsController::getSystemProblems();
				$systemProblemID = $this->get('systemProblemID');
				if (isset($systemProblems[$consoleID][$systemProblemID])) {
					$systemProblem = $systemProblems[$consoleID][$systemProblemID];
				} else {
					$systemProblem = $systemProblems[0][$systemProblemID];
				}
				$email = $this->get('email');
				if ($email) {
					$template = new template;
					$template->assign('system', $system);
					$template->assign('systemProblem', $systemProblem);
					$template->assign('repairOrder', $this->fetchArray());
					$mailer = $template->getMailer('repairOrderConfirmation');
					if ($mailer->composeMessage()) {
						$mailer->addRecipient($email);
						if ($mailer->send()) {
							$this->addError('your repair request confirmation has been sent to your email address');
						} else {
							$this->addError('your repair request confirmation could not be sent');
						}
					} else {
						$this->addError('your repair request confirmation could not be sent');
					}
				}
				return true;
			} else {
				$this->addError('repair request does not exist');
				return false;
			}
		} // function sendConfirmation
	} // class repairOrder

?>

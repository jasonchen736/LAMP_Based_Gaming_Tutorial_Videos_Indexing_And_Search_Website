<?

	class userActivation extends activeRecord {
		// active record table
		protected $table = 'userActivations';
		// existing auto increment field
		protected $autoincrement = 'userActivationID';
		// array unique id fields
		protected $idFields = array(
			'userActivationID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'userActivationID' => array('userActivationID', 'integer', 0, 10, 'User Activation ID'),
			'userID'           => array('userID', 'integer', 1, 10, 'User ID'),
			'activationCode'   => array('activationCode', 'alphanum', 1, 32, 'Activation Code'),
			'expiration'       => array('expiration', 'datetime', 0, 19, 'Expiration'),
			'activated'        => array('activated', 'datetime', 0, 19, 'Activation Date'),
			'posted'           => array('posted', 'datetime', 0, 19, 'Date Created')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('userActivationID', NULL, false);
			$this->set('expiration', date('Y-m-d', strtotime('1 week')));
			$this->set('posted', 'NOW()', false);
			$this->enclose('posted', false);
		} // function assertSaveDefaults
	} // class userActivation

?>
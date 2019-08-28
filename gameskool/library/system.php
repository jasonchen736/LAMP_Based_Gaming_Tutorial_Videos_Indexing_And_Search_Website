<?

	class system extends activeRecord {
		// active record table
		protected $table = 'systems';
		// existing auto increment field
		protected $autoincrement = 'systemID';
		// array unique id fields
		protected $idFields = array(
			'systemID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'systemID'  => array('systemID', 'integer', 0, 10, 'Repair System ID'),
			'name'      => array('name', 'name', 1, 50, 'Repair System Name'),
			'status'    => array('status', 'alphanumspace', 1, 12, 'Status'),
			'dateAdded' => array('dateAdded', 'datetime', 0, 19, 'Date Added')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('systemID', NULL, false);
			$this->set('dateAdded', 'NOW()', false);
			$this->enclose('dateAdded', false);
		} // function assertSaveDefaults
	} // class system

?>

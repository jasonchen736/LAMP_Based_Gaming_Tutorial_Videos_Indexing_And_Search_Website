<?

	class systemProblem extends activeRecord {
		// active record table
		protected $table = 'systemProblems';
		// existing auto increment field
		protected $autoincrement = 'systemProblemID';
		// array unique id fields
		protected $idFields = array(
			'systemProblemID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'systemProblemID' => array('systemProblemID', 'integer', 0, 10, 'System Problem ID'),
			'systemID'        => array('systemID', 'integer', 1, 10, 'Repair System ID'),
			'name'            => array('name', 'name', 1, 100, 'System Problem Name'),
			'cost'            => array('cost', 'decimal', 1, 6, 'Repair Cost'),
			'status'          => array('status', 'alphanumspace', 1, 12, 'Status'),
			'dateAdded'       => array('dateAdded', 'datetime', 0, 19, 'Date Added')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('systemProblemID', NULL, false);
			$this->set('dateAdded', 'NOW()', false);
			$this->enclose('dateAdded', false);
		} // function assertSaveDefaults
	} // class systemProblem

?>

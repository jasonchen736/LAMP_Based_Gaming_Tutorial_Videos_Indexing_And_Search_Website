<?

	class repairOrderComment extends activeRecord {
		// active record table
		protected $table = 'repairOrderComments';
		// existing auto increment field
		protected $autoincrement = 'repairOrderCommentID';
		// array unique id fields
		protected $idFields = array(
			'repairOrderCommentID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'repairOrderCommentID' => array('repairOrderCommentID', 'integer', 0, 10, 'Repair Order Comment ID'),
			'repairOrderID'        => array('repairOrderID', 'integer', 1, 10, 'Repair Order'),
			'comment'              => array('comment', 'comment', 0, 9999999, 'Comment'),
			'userID'               => array('userID', 'integer', 0, 10, 'User ID'),
			'dateAdded'            => array('dateAdded', 'datetime', 0, 19, 'Date Added'),
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('repairOrderCommentID', NULL, false);
			list($editorType, $editorID) = $this->getRecordEditor();
			$this->set('userID', $editorID);
			$this->set('dateAdded', 'NOW()', false);
			$this->enclose('dateAdded', false);
		} // function assertSaveDefaults
	} // class repairOrderComment

?>

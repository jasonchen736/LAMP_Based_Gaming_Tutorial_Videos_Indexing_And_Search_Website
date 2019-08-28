<?

	class postContentRevision extends activeRecord {
		// active record table
		protected $table = 'postContentRevisions';
		// existing auto increment field
		protected $autoincrement = 'postContentRevisionID';
		// array unique id fields
		protected $idFields = array(
			'postContentRevisionID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'postContentRevisionID' => array('postContentRevisionID', 'integer', 0, 10, 'Post Content Revision ID'),
			'postID'                => array('postID', 'integer', 1, 10, 'Post ID'),
			'content'               => array('content', 'postContent', 0, 9999999, 'Post Wiki'),
			'poster'                => array('poster', 'alpha', 1, 10, 'Poster'),
			'posterID'              => array('posterID', 'integer', 1, 10, 'Poster ID'),
			'posted'                => array('posted', 'datetime', 0, 19, 'Date Posted')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('postContentRevisionID', NULL, false);
			$this->set('posted', 'NOW()', false);
			$this->enclose('posted', false);
		} // function assertSaveDefaults
	} // class postContentRevision

?>
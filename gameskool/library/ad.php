<?

	class ad extends activeRecord {
		// active record table
		protected $table = 'ads';
		// existing auto increment field
		protected $autoincrement = 'adID';
		// history table (optional)
		protected $historyTable = 'adsHistory';
		// array unique id fields
		protected $idFields = array(
			'adID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'adID'         => array('adID', 'integer', 0, 10, 'Ad ID'),
			'name'         => array('name', 'alphanumspace', 1, 255, 'Ad Name'),
			'location'     => array('location', 'alphanumspace', 1, 20, 'Ad Location'),
			'url'          => array('url', 'url', 0, 999, 'Ad URL'),
			'content'      => array('content', 'postContent', 0, 9999999, 'Ad Content'),
			'status'       => array('status', 'alpha', 1, 10, 'Status'),
			'poster'       => array('poster', 'alpha', 0, 10, 'Poster'),
			'posterID'     => array('posterID', 'integer', 0, 10, 'Poster ID'),
			'posted'       => array('posted', 'datetime', 0, 19, 'Date Posted'),
			'lastModified' => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('adID', NULL, false);
			list($editorType, $editorID) = $this->getRecordEditor();
			$this->set('poster', $editorType);
			$this->set('posterID', $editorID);
			$this->set('posted', 'NOW()', false);
			$this->enclose('posted', false);
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
	} // class ad

?>

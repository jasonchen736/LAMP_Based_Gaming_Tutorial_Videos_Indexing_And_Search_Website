<?

	class feed extends activeRecord {
		// active record table
		protected $table = 'feeds';
		// existing auto increment field
		protected $autoincrement = 'feedID';
		// history table (optional)
		protected $historyTable = 'feedsHistory';
		// array unique id fields
		protected $idFields = array(
			'feedID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'feedID'          => array('feedID', 'integer', 0, 10, 'Feed ID'),
			'postType'        => array('postType', 'alpha', 1, 5, 'Post Type'),
			'url'             => array('url', 'url', 0, 255, 'Feed URL'),
			'source'          => array('source', 'alphanumspace', 0, 45, 'Feed Source'),
			'gameTitleID'     => array('gameTitleID', 'integer', 0, 10, 'Game Title'),
			'parameters'      => array('parameters', 'comment', 0, 255, 'Parameters'),
			'entryPath'       => array('entryPath', 'comment', 1, 255, 'Entry Path'),
			'gameTitlePath'   => array('gameTitlePath', 'comment', 0, 255, 'Game Title Path'),
			'identifierPath'  => array('identifierPath', 'comment', 0, 255, 'Identifier Path'),
			'postTitlePath'   => array('postTitlePath', 'comment', 1, 255, 'PostTitle Path'),
			'urlPath'         => array('urlPath', 'comment', 0, 255, 'URL Path'),
			'imagePath'       => array('imagePath', 'comment', 0, 255, 'Image Path'),
			'descriptionPath' => array('descriptionPath', 'comment', 1, 255, 'Description Path'),
			'contentPath'     => array('contentPath', 'comment', 0, 255, 'Content Path'),
			'require'         => array('require', 'comment', 0, 255, 'Require'),
			'reject'          => array('reject', 'comment', 0, 255, 'Reject'),
			'replace'         => array('replace', 'comment', 0, 255, 'Replace'),
			'status'          => array('status', 'alpha', 1, 10, 'Status'),
			'interval'        => array('interval', 'alphanumspace', 1, 10, 'Interval'),
			'priority'        => array('priority', 'integer', 1, 3, 'Priority'),
			'poster'          => array('poster', 'alpha', 0, 10, 'Poster'),
			'posterID'        => array('posterID', 'integer', 0, 10, 'Poster ID'),
			'dateAdded'       => array('dateAdded', 'datetime', 0, 19, 'Date Added'),
			'lastModified'    => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('feedID', NULL, false);
			$this->set('dateAdded', 'NOW()', false);
			$this->enclose('dateAdded', false);
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
			$type = $this->get('postType');
			switch ($type) {
				case 'video':
					$validSources = postsController::$supportedSources;
					$source = $this->get('source');
					if (!in_array($source, $validSources)) {
						$this->addErrorField('source');
						$this->addError('video source is not supported', 'source');
					}
					break;
				case 'link':
					if (!postsController::isValidURL($this->get('url'))) {
						$this->addErrorField('url');
						$this->addError('hey, that\'s not a valid url', 'url');
					}
					break;
				default:
					break;
			}
			$gameTitleID = $this->get('gameTitleID');
			$gameTitlePath = $this->get('gameTitlePath');
			if (empty($gameTitleID) && empty($gameTitlePath)) {
				$this->addErrorField('gameTitle');
				$this->addErrorField('gameTitlePath');
				$this->addError('either a game title or game title path is required', 'gameTitle');
			}
			$this->assertRequired();
			if (!empty($this->errors) || !empty($this->errorFields)) {
				return false;
			} else {
				return true;
			}
		} // function assertValidData

		/**
		 *  Set type specific requirements
		 *  Args: none
		 *  Return: none
		 */
		public function setTypeRequirements() {
			$source = $this->get('source');
			switch ($source) {
				case 'Youtube':
					$this->makeRequired('parameters');
					break;
				default:
					$this->makeRequired('url');
					break;
			}
			$type = $this->get('postType');
			switch ($type) {
				case 'link':
					$this->makeRequired('urlPath');
					break;
				case 'wiki':
				case 'blog':
					$this->makeRequired('contentPath');
					break;
				case 'video':
				default:
					$this->makeRequired('source');
					$this->makeRequired('identifierPath');
					$this->makeRequired('imagePath');
					break;
			}
		} // function setTypeRequirements

		/**
		 *  Assert type specific values
		 *  Args: none
		 *  Return: none
		 */
		public function assertTypeValues() {
			$type = $this->get('postType');
			switch ($type) {
				case 'link':
					$this->set('source', 'NULL');
					$this->set('identifierPath', 'NULL');
					$this->set('imagePath', 'NULL');
					$this->set('contentPath', 'NULL');
					$this->enclose('source', false);
					$this->enclose('identifierPath', false);
					$this->enclose('imagePath', false);
					$this->enclose('contentPath', false);
					break;
				case 'wiki':
				case 'blog':
					$this->set('source', 'NULL');
					$this->set('identifierPath', 'NULL');
					$this->set('imagePath', 'NULL');
					$this->set('urlPath', 'NULL');
					$this->enclose('source', false);
					$this->enclose('identifierPath', false);
					$this->enclose('imagePath', false);
					$this->enclose('urlPath', false);
					break;
				case 'video':
				default:
					$this->set('urlPath', 'NULL');
					$this->enclose('urlPath', false);
					break;
			}
			$gameTitleID = $this->get('gameTitleID');
			if (!empty($gameTitleID)) {
				$this->set('gameTitlePath', 'NULL');
				$this->enclose('gameTitlePath', false);
			} else {
				$this->set('gameTitleID', 'NULL');
				$this->enclose('gameTitleID', false);
			}
		} // function assertTypeValues
	} // class feed

?>
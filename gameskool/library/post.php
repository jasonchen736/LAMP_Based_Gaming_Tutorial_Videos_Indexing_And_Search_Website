<?

	class post extends activeRecord {
		// active record table
		protected $table = 'posts';
		// existing auto increment field
		protected $autoincrement = 'postID';
		// history table (optional)
		protected $historyTable = 'postsHistory';
		// array unique id fields
		protected $idFields = array(
			'postID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'postID'       => array('postID', 'integer', 0, 10, 'Post ID'),
			'type'         => array('type', 'alpha', 1, 5, 'post type'),
			'source'       => array('source', 'alphanumspace', 0, 45, 'video source'),
			'identifier'   => array('identifier', 'url', 0, 45, 'video id'),
			'url'          => array('url', 'url', 0, 999, 'link url'),
			'gameTitleID'  => array('gameTitleID', 'integer', 1, 10, 'game title'),
			'postTitle'    => array('postTitle', 'postTitle', 1, 255, 'post title'),
			'postTitleURL' => array('postTitleURL', 'postTitle', 1, 255, 'post title url'),
			'image'        => array('image', 'url', 0, 999, 'Image URL'),
			'description'  => array('description', 'comment', 1, 255, 'description'),
			'content'      => array('content', 'postContent', 0, 9999999, 'content'),
			'status'       => array('status', 'alpha', 1, 10, 'Status'),
			'poster'       => array('poster', 'alpha', 0, 10, 'Poster'),
			'posterID'     => array('posterID', 'integer', 0, 10, 'Poster ID'),
			'posted'       => array('posted', 'datetime', 0, 19, 'Date Posted'),
			'lastModified' => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Save new record, override to update delta index
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function save() {
			$saved = parent::save();
			if ($saved) {
				$this->logContentRevision();
				postsController::createAssociateRecords($this->get('postID'));
				if ($this->get('status') == 'active') {
					postsController::flagDeltaUpdate($this->get('postID'));
				}
			}
			return $saved;
		} // function save

		/**
		 *  Update current record, override to update delta index
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function update() {
			$oldType = $this->getOldValue('type');
			$wikiTypes = postsController::$wikiTypes;
			$contentRevision = $this->isNewValue('content') || ($this->isNewValue('type') && !isset($wikiTypes[$oldType]));
			$statusChange = $this->isNewValue('status');
			$indexUpdate = $this->isNewValue('status') || $this->isNewValue('gameTitleID');
			$updated = parent::update();
			if ($updated) {
				if ($contentRevision) {
					$this->logContentRevision();
				}
				if ($this->get('status') == 'active') {
					if ($indexUpdate) {
						postsController::updateIndexAttributes($this->get('postID'));
					}
					postsController::flagDeltaUpdate($this->get('postID'));
				} elseif ($statusChange) {
					postsController::removePostFromIndex($this->get('postID'));
				}
			}
			return $updated;
		} // function update

		/**
		 *  Save revision record for current post content
		 *  Args: none
		 *  Return: none
		 */
		private function logContentRevision() {
			if ($this->isWiki()) {
				list($editorType, $editorID) = $this->getRecordEditor();
				$revision = new postContentRevision;
				$revision->set('postID', $this->get('postID'));
				$revision->set('content', $this->get('content'));
				$revision->set('poster', $editorType);
				$revision->set('posterID', $editorID);
				if (!$revision->save()) {
					trigger_error('Unable to log post revision for post '.$this->get('postID').' made '.$this->get('lastModified'), E_USER_WARNING);
				}
			}
		} // function logContentRevision

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('postID', NULL, false);
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

		/**
		 *  Perform spectific data check, make sure data is as expected
		 *  Args: none
		 *  Return: (boolean) validation result
		 */
		public function assertValidData() {
			$type = $this->get('type');
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
			$this->assertRequired();
			if (!empty($this->errors) || !empty($this->errorFields)) {
				return false;
			} else {
				return true;
			}
		} // function assertValidData

		/**
		 *  Check for duplicate post based on unique game/post title or source/identifier combo
		 *  Args: none
		 *  Return: (boolean) is duplicate post
		 */
		public function isDuplicate() {
			$duplicate = false;
			$type = $this->get('type');
			$queries = array();
			switch ($type) {
				case 'video':
					$queries[] = "SELECT `postID`, 'video' AS `type`, NULL AS `data` FROM `".$this->table."` WHERE `source` = '".prep($this->get('source'))."' AND `identifier` = '".prep($this->get('identifier'))."'";
					break;
				case 'link':
					$queries[] = "SELECT `postID`, 'url' AS `type`, `url` AS `data` FROM `".$this->table."` WHERE `url` LIKE '".prep($this->get('url'))."%'";
					break;
				default:
					break;
			}
			$queries[] = "SELECT `postID`, 'title' AS `type`, NULL AS `data` FROM `".$this->table."` WHERE `gameTitleID` = '".$this->get('gameTitleID')."' AND `postTitleURL` = '".prep($this->get('postTitleURL'))."'";
			$sql = implode(' UNION ', $queries);
			$result = $this->dbh->query($sql);
			if ($result->rowCount > 0) {
				$id = $this->get('postID');
				while ($row = $result->fetchRow()) {
					if ($row['postID'] != $id) {
						switch ($row['type']) {
							case 'video':
								$this->addError('this video has already been posted', 'duplicate');
								$this->addErrorField('source');
								$this->addErrorField('identifier');
								break;
							case 'url':
								$this->addError('this link has already been submitted', 'duplicate');
								$this->addErrorField('url');
								break;
							case 'title':
								$this->addError('there is already a post with the same title', 'duplicate');
								$this->addErrorField('postTitle');
								break;
							default:
								$this->addError('this post has already been submitted', 'duplicate');
								break;
						}
						$duplicate = true;
						break;
					}
				}
			}
			return $duplicate;
		} // function isDuplicate

		/**
		 *  Check if the post is a wiki type
		 *  Args: none
		 *  Return: (boolean) is wiki type
		 */
		public function isWiki() {
			$type = $this->get('type');
			$wikiTypes = postsController::$wikiTypes;
			return isset($wikiTypes[$type]);
		} // function isWiki

		/**
		 *  Set type specific requirements
		 *  Args: none
		 *  Return: none
		 */
		public function setTypeRequirements() {
			$type = $this->get('type');
			switch ($type) {
				case 'link':
					$this->makeRequired('url');
					break;
				case 'wiki':
				case 'blog':
					$this->makeRequired('content');
					break;
				case 'video':
				default:
					$this->makeRequired('source');
					$this->makeRequired('identifier');
					break;
			}
		} // function setTypeRequirements

		/**
		 *  Assert type specific values
		 *  Args: none
		 *  Return: none
		 */
		public function assertTypeValues() {
			$type = $this->get('type');
			switch ($type) {
				case 'link':
					$this->set('source', 'NULL');
					$this->set('identifier', 'NULL');
					$this->set('image', 'NULL');
					$this->set('content', 'NULL');
					$this->enclose('source', false);
					$this->enclose('identifier', false);
					$this->enclose('image', false);
					$this->enclose('content', false);
					break;
				case 'wiki':
				case 'blog':
					$this->set('source', 'NULL');
					$this->set('identifier', 'NULL');
					$this->set('image', 'NULL');
					$this->set('url', 'NULL');
					$this->enclose('source', false);
					$this->enclose('identifier', false);
					$this->enclose('image', false);
					$this->enclose('url', false);
					break;
				case 'video':
				default:
					$this->set('url', 'NULL');
					$this->enclose('url', false);
					break;
			}
		} // function assertTypeValues
	} // class post

?>

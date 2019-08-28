<?

	class postComment extends activeRecord {
		// active record table
		protected $table = 'postComments';
		// existing auto increment field
		protected $autoincrement = 'postCommentID';
		// history table (optional)
		protected $historyTable = 'postCommentsHistory';
		// array unique id fields
		protected $idFields = array(
			'postCommentID'
		);
		// field array
		//   array(friendly name => array(field name, field type, min chars, max chars, label))
		protected $fields = array(
			'postCommentID'   => array('postCommentID', 'integer', 0, 10, 'Post Comment ID'),
			'postID'          => array('postID', 'integer', 1, 10, 'post'),
			'parentCommentID' => array('parentCommentID', 'integer', 1, 10, 'parent comment'),
			'comment'         => array('comment', 'postComment', 1, 999999, 'comment text'),
			'upVotes'         => array('upVotes', 'integer', 0, 10, 'up votes'),
			'downVotes'       => array('downVotes', 'integer', 0, 10, 'down votes'),
			'totalVotes'      => array('totalVotes', 'integer', 0, 10, 'total votes'),
			'poster'          => array('poster', 'alpha', 0, 10, 'Poster'),
			'posterID'        => array('posterID', 'integer', 0, 10, 'Poster ID'),
			'posted'          => array('posted', 'datetime', 0, 19, 'Date Posted'),
			'lastModified'    => array('lastModified', 'datetime', 0, 19, 'Last Modified')
		);

		/**
		 *  Constructor, override to set appropriate table
		 *  Args: (int) post id, (mixed) id fields (construct new record if empty)
		 *  Return: none
		 */
		public function __construct($id = NULL, $postID = NULL) {
			$this->dbh = database::getInstance();
			if ($id && !is_null($postID)) {
				$this->load($id, $postID);
			} else {
				$this->reset();
			}
			$this->initialize();
		} // function __construct

		/**
		 *  Load record by unique id, override to set appropriate table
		 *  Args: (mixed) id can be array/str/int, (int) post id
		 *  Return: (boolean) success
		 */
		public function load($id, $postID = NULL) {
			if (is_null($postID)) {
				$postID = $this->get('postID');
			} else {
				$postID = clean($postID, 'integer');
			}
			if (empty($postID)) {
				$postID = 0;
			}
			$tableNumber = substr($postID, -1, 1);
			if (!preg_match('/'.$tableNumber.'$/', $this->table)) {
				$this->table = preg_replace('/\d+$/', '', $this->table);
				$this->table = $this->table.$tableNumber;
			}
			if (!preg_match('/'.$tableNumber.'$/', $this->historyTable)) {
				$this->historyTable = preg_replace('/\d+$/', '', $this->historyTable);
				$this->historyTable = $this->historyTable.$tableNumber;
			}
			return parent::load($id);
		} // function load

		/**
		 *  Load record by data array, override to set appropriate table
		 *  Args: (array) record data
		 *  Return: (boolean) success
		 */
		public function loadRecord($data) {
			$load = parent::loadRecord($data);
			$postID = $this->get('postID');
			$tableNumber = substr($postID, -1, 1);
			if (!preg_match('/'.$tableNumber.'$/', $this->table)) {
				$this->table = preg_replace('/\d+$/', '', $this->table);
				$this->table = $this->table.$tableNumber;
			}
			if (!preg_match('/'.$tableNumber.'$/', $this->historyTable)) {
				$this->historyTable = preg_replace('/\d+$/', '', $this->historyTable);
				$this->historyTable = $this->historyTable.$tableNumber;
			}
			return $load;
		} // function loadRecord

		/**
		 *  Save new record, override to set appropriate table and clear comments cache
		 *  Args: none
		 *  Return: (boolean) success
		 */
		public function save() {
			$postID = $this->get('postID');
			$tableNumber = substr($postID, -1, 1);
			$this->table = $this->table.$tableNumber;
			$this->historyTable = $this->historyTable.$tableNumber;
			$saved = parent::save();
			if ($saved) {
				$this->expireCommentsCache();
				$this->updatePostStatistics();
			}
			return $saved;
		} // function save

		/**
		 *  Log to history table if applicable
		 *    history tables must have the date time fields specified in the object vars
		 *    modified to not record comment votes
		 *  Args: (str) save or update type logging, (str) comments
		 *  Return: (boolean) success
		 */
		public function logHistory($type, $comments) {
			if ($this->historyTable) {
				$tableFields = array();
				foreach ($this->fields as $key => $vals) {
					if ($key != 'upVotes' && $key != 'downVotes' && $key != 'totalVotes') {
						if (isset($this->{$vals[0]}[0])) {
							$tableFields[] = '`'.$vals[0].'`';
						}
					}
				}
				$identifier = array();
				foreach ($this->idFields as $field) {
					$identifier[] = "`".$this->fields[$field][0]."` = '".$this->{$field}[0]."'";
				}
				if ($type == 'UPDATE') {
					$effectiveThrough = date('Y-m-d H:i:s', strtotime('-1 second', strtotime($this->{$this->historyDateTimeFields['lastModified']}[0])));
					$sql = "UPDATE `".$this->historyTable."` 
							SET `".$this->historyDateTimeFields['effectiveThrough']."` = IF(
								'".$effectiveThrough."' < `".$this->historyDateTimeFields['lastModified']."`, 
								`".$this->historyDateTimeFields['lastModified']."`, 
								'".$effectiveThrough."'
							) WHERE ".implode(' AND ', $identifier)." 
							AND `".$this->historyDateTimeFields['effectiveThrough']."` = '9999-12-31 23:59:59'";
					$result = $this->dbh->query($sql);
					if ($result->rowCount < 1) {
						trigger_error('Active Record History Error: History log update failed for '.$this->table.' [sql: '.$sql.'] [error: '.$result->sqlError.']', E_USER_WARNING);
					}
				}
				list($editorType, $editorID) = $this->getRecordEditor();
				$sql = "INSERT INTO `".$this->historyTable."` (".implode(', ', $tableFields).", `".$this->historyDateTimeFields['effectiveThrough']."`, `recordEditor`, `recordEditorID`, `action`, `comments`) 
						SELECT ".implode(', ', $tableFields).", '9999-12-31 23:59:59', 
							'".$editorType."', '".$editorID."', '".$type."', '".prep($comments)."' 
						FROM `".$this->table."` 
						WHERE ".implode(' AND ', $identifier);
				$result = $this->dbh->query($sql);
				if ($result->rowCount < 1) {
					trigger_error('Active Record History Error: History log failed for '.$this->table.' [sql: '.$sql.'] [error: '.$result->sqlError.']', E_USER_WARNING);
					return false;
				}
			}
			return true;
		} // function logHistory

		/**
		 *  Expire database comments cache
		 *  Args: none
		 *  Return: none
		 */
		public function expireCommentsCache() {
			$sql = "UPDATE `postCommentsCache` SET `expired` = 1 WHERE `postID` = '".$this->get('postID')."'";
			query($sql);
		} // function expireCommentsCache

		/**
		 *  Update post statistics comments count
		 *  Args: none
		 *  Return: none
		 */
		public function updatePostStatistics() {
			$sql = "UPDATE `postStatistics` SET `comments` = `comments` + 1 WHERE `postID` = '".$this->get('postID')."'";
			query($sql);
		} // function updatePostStatistics

		/**
		 *  Set defaults for saving
		 *  Args: none
		 *  Return: none
		 */
		public function assertSaveDefaults() {
			$this->set('postCommentID', NULL, false);
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
		 *  Format any data necessary before save/update
		 *  Args: none
		 *  Return: none
		 */
		public function assertDataFormats() {
			$this->set('comment', preg_replace('/\r\n/', '<br />', $this->get('comment')));
		} // function assertDataFormats
	} // class post

?>
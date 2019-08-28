<?

	class postSearch extends searchClient {
		public $matchMode = 'any';
		public $sortMode = 'top';
		public $postTypes = false;
		public $postType = false;

		/**
		 *  Set up client
		 */
		public function __construct() {
			parent::__construct();
			$this->indexes = array(
				systemSettings::get('POSTINDEX'),
				systemSettings::get('POSTDELTAINDEX')
			);
			$this->postTypes = postsController::$postTypes;
		} // function __construct

		/**
		 *  Set query parameters
		 *  Args: none
		 *  Return: none
		 */
		public function buildQuery() {
			if (isset($this->postTypes[$this->postType])) {
				$this->SetFilter('type', array($this->postTypes[$this->postType] + 1));
			}
			$this->SetFilter('status', array(1));
			switch ($this->matchMode) {
				case 'any';
				default:
					$this->SetMatchMode(SPH_MATCH_ALL);
					break;
			}
			switch ($this->sortMode) {
				case 'posted':
					$this->SetSortMode(SPH_SORT_EXTENDED, 'posted DESC');
					break;
				case 'relevance':
					$this->SetSortMode(SPH_SORT_EXTENDED, '@relevance DESC, posted DESC');
					break;
				case 'top':
				default:
					$this->SetSortMode(SPH_SORT_EXPR, 'upvotes - downvotes + @weight + LN(posted) * 1000');
					break;
			}
			$this->SetLimits((int) $this->currentPage, (int) $this->pageLimit, (int) $this->resultLimit);
		} // function buildQuery

		/**
		 *  Perform post search query and return post data array
		 *  Args: (str) query expression
		 *  Return: (array) post data
		 */
		public function query($expression) {
			$posts = array();
			$results = parent::query($expression);
			if ($results) {
				if (isset($results['matches'])) {
					foreach ($results['matches'] as $postID => $data) {
						$posts[$postID] = $postID;
					}
				}
				$this->totalFound = $results['total_found'];
			}
			if (!empty($posts)) {
				$sql = "SELECT `a`.`postID`, `a`.`type`, `a`.`source`, `a`.`identifier`, 
							`b`.`gameTitle`, `b`.`gameTitleURL`, `a`.`url`, `a`.`image`, `a`.`postTitle`, `a`.`postTitleURL`, `a`.`description`, 
							IFNULL(`d`.`userName`, IFNULL(`e`.`name`, 'anonymous')) AS `posterName`, 
							`a`.`poster`, `a`.`posterID`, `a`.`posted`, `c`.`comments`, 
							`c`.`upVotes`, `c`.`downVotes` 
						FROM `posts` `a` 
						JOIN `gameTitles` `b` ON (`a`.`gameTitleID` = `b`.`gameTitleID`) 
						JOIN `postStatistics` `c` ON (`a`.`postID` = `c`.`postID`) 
						LEFT JOIN `users` `d` ON (`a`.`poster` = 'USER' AND `a`.`posterID` = `d`.`userID`) 
						LEFT JOIN `adminUsers` `e` ON (`a`.`poster` = 'ADMIN' AND `a`.`posterID` = `e`.`adminUserID`) 
						WHERE `a`.`postID` IN ('".implode("', '", $posts)."') 
						ORDER BY `a`.`postID` = '".implode("' DESC, `a`.`postID` = '", $posts)."' DESC";
				$result = query($sql);
				if ($result->rowCount > 0) {
					while ($row = $result->fetchRow()) {
						$row['poster'] = strtolower($row['poster']);
						$row['timeSubmitted'] = time() - strtotime($row['posted']);
						if ($row['timeSubmitted'] < 0) {
							$row['timeSubmitted'] = 0;
						}
						if ($row['timeSubmitted'] < 60) {
							$period = 'second';
						} elseif ($row['timeSubmitted'] < 3600) {
							$row['timeSubmitted'] = round($row['timeSubmitted'] / 60);
							$period = 'minute';
						} elseif ($row['timeSubmitted'] < 86400) {
							$row['timeSubmitted'] = round($row['timeSubmitted'] / 3600);
							$period = 'hour';
						} else {
							$row['timeSubmitted'] = round($row['timeSubmitted'] / 86400);
							$period = 'day';
						}
						if ($row['timeSubmitted'] > 1) {
							$row['timeSubmitted'] = $row['timeSubmitted'].' '.$period.'s';
						} else {
							$row['timeSubmitted'] = $row['timeSubmitted'].' '.$period;
						}
						$posts[$row['postID']] = $row;
					}
				} else {
					$posts = array();
				}
			}
			return $posts;
		} // function query
	} // class postSearch

?>

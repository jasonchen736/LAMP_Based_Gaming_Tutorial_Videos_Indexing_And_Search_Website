<?

	class gameTitleSearch extends searchClient {
		public $matchMode = 'any';
		public $sortMode = 'relevance';

		/**
		 *  Set up client
		 */
		public function __construct() {
			parent::__construct();
			$this->indexes = array(
				systemSettings::get('GAMETITLEINDEX')
			);
		} // function __construct

		/**
		 *  Build query
		 *  Args: none
		 *  Return: none
		 */
		public function buildQuery() {
			switch ($this->matchMode) {
				case 'any';
				default:
					$this->SetMatchMode(SPH_MATCH_ANY);
					break;
			}
			switch ($this->sortMode) {
				case 'relevance':
				default:
					$this->SetSortMode(SPH_SORT_RELEVANCE);
					break;
			}
			$this->SetLimits((int) $this->currentPage, (int) $this->pageLimit, (int) $this->resultLimit);
		} // function buildQuery

		/**
		 *  Perform game title search query and return game title data array
		 *  Args: (str) query expression
		 *  Return: (array) game titles data
		 */
		public function query($expression) {
			$gameTitles = array();
			$results = parent::query($expression);
			if ($results) {
				if (isset($results['matches'])) {
					foreach ($results['matches'] as $gameTitleID => $data) {
						$gameTitles[$gameTitleID] = $gameTitleID;
					}
				}
				$this->totalFound = $results['total_found'];
			}
			if (!empty($gameTitles)) {
				$sql = "SELECT `gameTitleID`, `gameTitle`, `gameTitleURL` 
						FROM `gameTitles` 
						WHERE `gameTitleID` IN ('".implode("', '", $gameTitles)."') 
						ORDER BY `gameTitleID` = '".implode("' DESC, `gameTitleID` = '", $gameTitles)."' DESC";
				$result = query($sql);
				if ($result->rowCount > 0) {
					while ($row = $result->fetchRow()) {
						$gameTitles[$row['gameTitleID']] = $row;
					}
				} else {
					$gameTitles = array();
				}
			}
			return $gameTitles;
		} // function query
	} // class gameTitleSearch

?>

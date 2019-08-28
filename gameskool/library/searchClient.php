<?

	require_once 'sphinx/sphinxapi.php';

	class searchClient extends SphinxClient {
		public $indexes = array();
		public $matchMode = false;
		public $sortMode = false;
		public $currentPage = 0;
		public $pageLimit = 25;
		public $resultLimit = 1000;
		public $totalFound = 0;

		/**
		 *  Return array of search query options
		 *  Args: none
		 *  Return: (array) search query options
		 */
		public static function getSearchQuery() {
			$queryString = array(
				'type' => '',
				'search' => ''
			);
			$type = getRequest('type');
			if ($type) {
				$queryString['type'] .= '/type/'.$type;
			}
			$search = getRequest('search');
			if ($search) {
				$queryString['search'] .= '/search/'.$search;
			}
			return $queryString;
		} // function getSearchQuery

		/**
		 *  Set up client
		 */
		public function __construct() {
			$this->SphinxClient();
			$this->SetServer(systemSettings::get('SPHINXHOST'), systemSettings::get('SPHINXPORT'));
			$this->SetConnectTimeout(systemSettings::get('SPHINXTIMEOUT'));			
		} // function __construct

		/**
		 *  Set query parameters
		 *  Args: none
		 *  Return: none
		 */
		public function buildQuery() {
			$this->SetMatchMode(SPH_MATCH_ANY);
			$this->SetSortMode(SPH_SORT_RELEVANCE);
			$this->SetLimits((int) $this->currentPage, (int) $this->pageLimit, (int) $this->resultLimit);
		} // function buildQuery

		/**
		 *  Build query and perform search
		 *  Args: (str) query expression
		 *  Return: (array) search result
		 */
		public function query($expression) {
			$this->buildQuery();
			return parent::query($expression, implode(',', $this->indexes));
		} // function query
	} // class searchClient

?>

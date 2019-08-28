<?

	class controller {
		// database handler
		protected $dbh;
		// controller for specified table
		protected $table;
		// db fields that map to select inputs (enum db fields), these are automatically detected and set
		protected $selectInputs;
		// search operators can be passed in the form of "[search field]_operator"
		//    search criteria will then be built according to operator type if specified
		protected $searchOperators = array(
			'equal' => '=',
			'not equal' => '!=',
			'greater than' => '>',
			'greater than or equal' => '>=',
			'less than' => '<',
			'less than or equal' => '<=',
			'contains' => 'LIKE',
		);
		// requests to disregard when constructing a query string
		protected $ignoreRequests = array(
			'submit', 
			'_nextPage', 
			'_previousPage', 
			'_sortField',
			'_sortOrder',
			'records', 
			'action', 
			'updateAction',
			'takeAction',
			'selectPost'
		);
		// fields available to search: array(field name => array(type, range))
		protected $searchFields;
		// holds search criteria: array(field name => array(value, imposed)
		//   index corresponds to a search field, while the value is an array where:
		//     0 is the search value
		//     1 is the search operator
		//     2 denotes whether the search value is used only as default (no search action: false) 
		//       or to impose it (always use: true)
		protected $searchValues = array();
		// valid sort orders
		protected $validSortOrders = array(
			'ASC',
			'DESC'
		);
		// default sorting field
		protected $defaultSortField = false;
		// stored results
		protected $records = array();
		protected $recordsFound = 0;
		// errors array
		protected static $errors = array();
		protected static $errorFields = array();

		/**
		 *  Add an item to the error array
		 *  Args: (str) error message, (str) error index
		 *  Return: none
		 */
		public static function addError($error, $index = false) {
			if ($index !== false) {
				self::$errors[$index] = $error;
			} else {
				self::$errors[] = $error;
			}
		} // function addError

		/**
		 *  Add an item from the error array by a known index
		 *  Args: (str) error index
		 *  Return: none
		 */
		public static function removeError($index) {
			if (isset(self::$errors[$index])) {
				unset(self::$errors[$index]);
			}
		} // function removeError

		/**
		 *  Clear the error array
		 *  Args: none
		 *  Return: none
		 */
		public static function clearErrors() {
			self::$errors = array();
		} // function clearErrors

		/**
		 *  Retrieve error array
		 *  Args: none
		 *  Return: (array) error array
		 */
		public static function getErrors() {
			return self::$errors;
		} // function getErrors

		/**
		 *  Add a field name to the error field array
		 *  Args: (str) field name
		 *  Return: none
		 */
		public static function addErrorField($fieldName) {
			self::$errorFields[$fieldName] = true;
		} // function addErrorField

		/**
		 *  Remove a field name to the error field array
		 *  Args: (str) field name
		 *  Return: none
		 */
		public static function removeErrorField($fieldName) {
			if (isset(self::$errorFields[$fieldName])) {
				unset(self::$errorFields[$fieldName]);
			}
		} // function removeErrorField

		/**
		 *  Clear the error fields array
		 *  Args: none
		 *  Return: none
		 */
		public static function clearErrorFields() {
			self::$errorFields = array();
		} // function clearErrorFields

		/**
		 *  Retrieve error fields array
		 *  Args: none
		 *  Return: (array) error fields array
		 */
		public static function getErrorFields() {
			return self::$errorFields;
		} // function getErrorFields

		/**
		 *  Push internal error or error fields array to system messages
		 *  Args: (str) array to push
		 *  Return: none
		 */
		public static function updateSystemMessages($type = false) {
			if (!$type || $type == 'errors') {
				$errors = self::$getErrors();
				foreach ($errors as $error) {
					addError($error);
				}
			}
			if (!$type || $type == 'errorFields') {
				$errorFields = self::$getErrorFields();
				foreach ($errorFields as $errorField => $val) {
					addErrorField($errorField);
				}
			}
		} // function updateSystemMessages

		/**
		 *  Detect and set select input options for enum fields
		 *  Args: none
		 *  Return: none
		 */
		public function __construct() {
			$this->records = array();
			$this->recordsFound = 0;
			$this->dbh = database::getInstance();
			$this->selectInputs = array();
			$result = $this->dbh->query('DESC `'.$this->table.'`');
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					if (preg_match('/^enum\(/', $row['Type'])) {
						$values = explode(',', rtrim(ltrim($row['Type'], 'enum('), ')'));
						$values = preg_replace('/\'/', '', $values);
						$options = array();
						foreach ($values as $key => $val) {
							$options[$val] = $val;
						}
						$this->selectInputs[$row['Field']] = $options;
					}
				}
			}
			$this->initialize();
		} // function __construct

		/**
		 *  Perform any controller specific initialization actions
		 *    OVERRIDE AS NEEDED
		 *  Args: none
		 *  Return: none
		 */
		public function initialize() {
		} // function initialize

		/**
		 *  Get records retrieved from search
		 *  Args: none
		 *  Return: (array) retrieved records
		 */
		public function getRecords() {
			return $this->records;
		} // function getRecords

		/**
		 *  Get number of records found in search
		 *  Args: none
		 *  Return: (int) record count
		 */
		public function getRecordCount() {
			return $this->recordsFound;
		} // function getRecordCount

		/**
		 *  Retrieve options from an enum data field
		 *  Args: (str) field name
		 *  Return: (array) value options
		 */
		public function getOptions($field) {
			if (array_key_exists($field, $this->selectInputs)) {
				return $this->selectInputs[$field];
			} else {
				return NULL;
			}
		} // function getOptions

		/**
		 *  Construct a get query string from get and post requests formatted for web friendly urls
		 *  Args: none
		 *  Return: (str) query string
		 */
		public function retrieveQueryString($ignore = array()) {
			assertArray($ignore);
			$ignore = array_merge($ignore, $this->ignoreRequests);
			$querystring = array();
			$request = array_merge($_GET, $_POST);
			foreach ($request as $key => $val) {
				if (!in_array($key, $ignore) && $val !== '') {
					// triple encode:
					//   double encode to compensate for mod rewrite's handling of auto (double) decoding encoded character
					//   extra encode for friendly urls (handle forward slashes)
					$querystring[] = $key.'/'.urlencode(urlencode(urlencode($val)));
				}
			}
			return implode('/', $querystring);
		} // function retrieveQueryString

		/**
		 *  Return the current table location coordinates made from request
		 *  Args: none
		 *  Return: (array) start record, number of records shown, current page
		 */
		public function getTableLocation() {
			$start = getRequest('_start', 'integer');
			$show = getRequest('_show', 'integer') ? getRequest('_show', 'integer') : 100;
			$page = getRequest('_page', 'integer');
			if (!getRequest('search') || getRequest('_nextPage') || getRequest('_previousPage') || getRequest('_page')) {
				if (is_null($start)) {
					$start = 0;
				}
				if (getRequest('_nextPage')) {
					$start += $show;
				} elseif (getRequest('_previousPage')) {
					$start -= $show;
					if ($start < 0) {
						$start = 0;
					}
				} elseif ($page) {
					$start = ($page - 1) * $show;
				}
				$page = floor(($start + $show) / $show);
			} else {
				$page = 1;
				$start = 0;
			}
			// update post request array for function retrieveQueryString
			$_POST['_page'] = $page;
			$_POST['_start'] = $start;
			$_POST['_show'] = $show;
			return array($start, $show, $page);
		} // function getTableLocation

		/**
		 *  Retrieve search sorting configuration
		 *  Args: none
		 *  Return: (array) sort field, sort order
		 */
		public function getSearchOrder() {
			$sortField = getRequest('_sortField', 'alphanum');
			$sortOrder = getRequest('_sortOrder', 'alpha');
			if (empty($sortOrder) || !in_array($sortOrder, $this->validSortOrders)) {
				$sortOrder = 'ASC';
			}
			if (empty($sortField) || !isset($this->searchFields[$sortField])) {
				if ($this->defaultSortField) {
					$sortField = $this->defaultSortField;
				} else {
					$fields = array_keys($this->searchFields);
					$sortField = current($fields);
				}
			}
			return array($sortField, $sortOrder);
		} // function getSearchOrder

		/**
		 *  Retrieve search query components
		 *    - table location
		 *    - search order
		 *  Args: none
		 *  Return: (array) query components
		 */
		public function getQueryComponents() {
			$query = array();
			list($start, $show, $page) = $this->getTableLocation();
			list($sortField, $sortOrder) = $this->getSearchOrder();
			$query['start'] = $start;
			$query['show'] = $show;
			$query['page'] = $page;
			$query['pages'] = ceil($this->recordsFound / $show);
			$query['sortField'] = $sortField;
			$query['sortOrder'] = $sortOrder;
			$query['revSortOrder'] =  $sortOrder == 'ASC' ? 'DESC' : 'ASC';
			$query['querystring'] = $this->retrieveQueryString();
			return $query;
		} // function getQueryComponents

		/**
		 *  Set default search criteria (used only when there is no search action)
		 *  Args: (str) field name, (mixed) default value - if ranged, used array with index 0 and 1
		 *  Args: (str) search operator
		 *  Return: none
		 */
		public function setDefaultSearch($field, $value, $operator = false) {
			if (array_key_exists($field, $this->searchFields)) {
				if (!isset($this->searchValues[$field])) {
					$this->searchValues[$field] = array();
				}
				if (!is_array($value)) {
					$value = clean($value, $this->searchFields[$field]['type']);
				} else {
					array_walk_recursive($value, 'cleanWalk', $this->searchFields[$field]['type']);
				}
				$this->searchValues[$field][0] = $value;
				if ($operator && array_key_exists($operator, $this->searchOperators)) {
					$this->searchValues[$field][1] = $this->searchOperators[$operator];
				} else {
					$this->searchValues[$field][1] = false;
				}
				$this->searchValues[$field][2] = false;
			}
		} // function setDefaultSearch

		/**
		 *  Impose a search criteria, will always be used
		 *  Args: (str) field name, (mixed) default value - if ranged, used array with index 0 and 1
		 *  Args: (str) search operator
		 *  Return: none
		 */
		public function imposeSearch($field, $value, $operator = false) {
			if (array_key_exists($field, $this->searchFields)) {
				if (!isset($this->searchValues[$field])) {
					$this->searchValues[$field] = array();
				}
				if (!is_array($value)) {
					$value = clean($value, $this->searchFields[$field]['type']);
				} else {
					array_walk_recursive($value, 'cleanWalk', $this->searchFields[$field]['type']);
				}
				$this->searchValues[$field][0] = $value;
				if ($operator && array_key_exists($operator, $this->searchOperators)) {
					$this->searchValues[$field][1] = $this->searchOperators[$operator];
				} else {
					$this->searchValues[$field][1] = false;
				}
				$this->searchValues[$field][2] = true;
			}
		} // function imposeSearch

		/**
		 *  Return array of search values
		 *    Override as needed
		 *  Args: none
		 *  Return: (array) search values
		 */
		public function getSearchValues() {
			$search = array();
			$searchAction = getRequest('runSearch');
			if ($searchAction) {
				$defaultSearch = false;
			} else {
				$defaultSearch = true;
			}
			foreach ($this->searchFields as $field => $vals) {
				if (isset($this->selectInputs[$field])) {
					$fieldOptions = array_merge(array('' => 'All'), $this->selectInputs[$field]);
				} else {
					$fieldOptions = false;
				}
				if (!$vals['range']) {
					$search[$field] = array();
					if ($defaultSearch) {
						if (isset($this->searchValues[$field])) {
							$search[$field]['value'] = $this->searchValues[$field][0];
							$search[$field]['operator'] = $this->searchValues[$field][1];
						} else {
							$search[$field]['value'] = '';
							$search[$field]['operator'] = false;
						}
					} else {
						if (isset($this->searchValues[$field]) && $this->searchValues[$field][2]) {
							$search[$field]['value'] = $this->searchValues[$field][0];
							$search[$field]['operator'] = $this->searchValues[$field][1];
						} else {
							$search[$field]['value'] = clean(getRequest($field), $vals['type']);
							$search[$field]['operator'] = clean(getRequest($field.'_operator'), 'alphanumspace');
						}
					}
					$search[$field]['options'] = $fieldOptions;
				} else {
					$search[$field.'From'] = array();
					$search[$field.'To'] = array();
					if ($defaultSearch) {
						if (isset($this->searchValues[$field])) {
							$search[$field.'From']['value'] = isset($this->searchValues[$field][0][0]) ? $this->searchValues[$field][0][0] : '';
							$search[$field.'To']['value'] = isset($this->searchValues[$field][0][1]) ? $this->searchValues[$field][0][1] : '';
						} else {
							$search[$field.'From']['value'] = '';
							$search[$field.'To']['value'] = '';
						}
					} else {
						if (isset($this->searchValues[$field]) && $this->searchValues[$field][2]) {
							$search[$field.'From']['value'] = isset($this->searchValues[$field][0][0]) ? $this->searchValues[$field][0][0] : '';
							$search[$field.'To']['value'] = isset($this->searchValues[$field][0][1]) ? $this->searchValues[$field][0][1] : '';
						} else {
							$search[$field.'From']['value'] = clean(getRequest($field.'From'), $vals['type']);
							$search[$field.'To']['value'] = clean(getRequest($field.'To'), $vals['type']);
						}
					}
					$search[$field.'From']['options'] = $fieldOptions;
					$search[$field.'To']['options'] = $fieldOptions;
				}
			}
			return $search;
		} // function getSearchValues

		/**
		 *  Return array of search components
		 *    Override as needed
		 *  Args: none
		 *  Return: (array) search components
		 */
		public function getSearchComponents() {
			$search = array();
			list($start, $show, $page) = $this->getTableLocation();
			list($sortField, $sortOrder) = $this->getSearchOrder();
			$search['select'] = '*';
			$search['start'] = $start;
			$search['show'] = $show;
			$search['tables'] = array(
				0 => '`'.$this->table.'`'
			);
			$search['where'] = array();
			$search['order'] = array();
			$search['order'][] = $sortField.' '.$sortOrder;
			$defaultSearch = false;
			$performSearch = getRequest('runSearch');
			// impose default search criteria when search action is not explicitly called
			if (!$performSearch && !empty($this->searchValues)) {
				$performSearch = true;
				$defaultSearch = true;
			}
			if ($performSearch) {
				foreach ($this->searchFields as $field => $val) {
					if (!$val['range']) {
						if ($defaultSearch) {
							if (isset($this->searchValues[$field])) {
								$value = $this->searchValues[$field][0];
								$operator = $this->searchValues[$field][1];
							} else {
								$value = false;
							}
						} else {
							if (isset($this->searchValues[$field]) && $this->searchValues[$field][2]) {
								$value = $this->searchValues[$field][0];
								$operator = $this->searchValues[$field][1];
							} else {
								$value = clean(urldecode(getRequest($field)), $val['type']);
								$operator = getRequest($field.'_operator');
								$operator = isset($this->searchOperators[$operator]) ? $this->searchOperators[$operator] : false;
							}
						}
						if ($value) {
							if ($operator) {
								if ($operator == 'LIKE') {
									$value = preg_replace('/\*/', '%', prep($value));
								} else {
									$value = prep($value);
								}
							} else {
								$operator = '=';
								$value = prep($value);
							}
							$search['where'][$field] = "AND `".$field."` ".$operator." '".$value."'";
						}
					} else {
						if ($defaultSearch) {
							if (isset($this->searchValues[$field])) {
								$valueFrom = isset($this->searchValues[$field][0][0]) ? $this->searchValues[$field][0][0] : '';
								$valueTo = isset($this->searchValues[$field][0][1]) ? $this->searchValues[$field][0][1] : '';
							} else {
								$valueFrom = '';
								$valueTo = '';
							}
						} else {
							if (isset($this->searchValues[$field]) && $this->searchValues[$field][2]) {
								$valueFrom = isset($this->searchValues[$field][0][0]) ? $this->searchValues[$field][0][0] : '';
								$valueTo = isset($this->searchValues[$field][0][1]) ? $this->searchValues[$field][0][1] : '';
							} else {
								$valueFrom = clean(getRequest($field.'From'), $val['type']);
								$valueTo = clean(getRequest($field.'To'), $val['type']);
							}
						}
						if ($valueFrom != '' || $valueTo != '') {
							if ($val['type'] == 'date') {
								$valueFrom = $valueFrom ? dateToSql($valueFrom) : $valueFrom;
								$valueTo = $valueTo ? dateToSql($valueTo) : $valueTo;
							}
							if ($valueFrom != '' && $valueTo != '') {
								$search['where'][$field] = "AND `".$field."` BETWEEN '".$valueFrom."' AND '".$valueTo."'";
							} elseif ($valueFrom != '') {
								$search['where'][$field] = "AND `".$field."` >= '".$valueFrom."'";
							} else {
								$search['where'][$field] = "AND `".$field."` <= '".$valueTo."'";
							}
						}
					}
				}
			}
			if (!empty($search['where'])) {
				$key = key($search['where']);
				$search['where'][$key] = preg_replace('/^(AND|OR) /', '', $search['where'][$key]);
			}
			return $search;
		} // function getSearchComponents

		/**
		 *  Return records found from a general search using $this->getSearchComponents()
		 *    Override as needed
		 *  Args: none
		 *  Return: (array) found records
		 */
		public function performSearch() {
			$searchCriteria = $this->getSearchComponents();
			$sql = "SELECT ".$searchCriteria['select']." FROM ".implode(" ", $searchCriteria['tables'])." ".(!empty($searchCriteria['where']) ? "WHERE ".implode(" ", $searchCriteria['where'])." " : "").(!empty($searchCriteria['order']) ? "ORDER BY ".implode(", ", $searchCriteria['order'])." " : "")."LIMIT ".$searchCriteria['start'].", ".$searchCriteria['show'];
			$result = $this->dbh->query($sql);
			$this->records = $result->fetchAll();
			return $this->records;
		} // function performSearch

		/**
		 *  Count total records found from general search
		 *    Override as needed
		 *  Args: none
		 *  Return: (int) records found
		 */
		public function countRecordsFound() {
			$searchCriteria = $this->getSearchComponents();
			$sql = "SELECT COUNT(*) AS `count` FROM ".implode(" ", $searchCriteria['tables'])." ".(!empty($searchCriteria['where']) ? "WHERE ".implode(" ", $searchCriteria['where'])." " : "");
			$result = $this->dbh->query($sql);
			$row = $result->fetchRow();
			$this->recordsFound = $row['count'];
			return $this->recordsFound;
		} // function countRecordsFound
	} // class controller

?>

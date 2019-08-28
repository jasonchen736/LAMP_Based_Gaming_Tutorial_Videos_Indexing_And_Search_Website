<?

	class trafficSourceHitsController extends controller {
		// controller for specified table
		protected $table = 'trafficSourceHits';
		// requests to disregard when constructing a query string
		protected $ignoreRequests = array(
			'submit', 
			'_nextPage', 
			'_previousPage', 
			'_sortField',
			'_sortOrder',
			'records'
		);
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'ID' => array('type' => 'integer', 'range' => false),
			'subID' => array('type' => 'alphanumspace', 'range' => false),
			'extra' => array('type' => 'alphanumspace', 'range' => false),
			'hits' => array('type' => 'integer', 'range' => true),
			'uniqueHits' => array('type' => 'integer', 'range' => true),
			'date' => array('type' => 'date', 'range' => true)
		);
		// default sorting field
		protected $defaultSortField = 'ID';
	} // class trafficSourceHitsController

?>
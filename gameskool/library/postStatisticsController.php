<?

	class postStatisticsController extends controller {
		// controller for specified table
		protected $table = 'postStatistics';
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
			'postID' => array('type' => 'integer', 'range' => false),
			'views' => array('type' => 'integer', 'range' => true),
			'comments' => array('type' => 'integer', 'range' => true),
			'upVotes' => array('type' => 'integer', 'range' => true),
			'downVotes' => array('type' => 'integer', 'range' => true)
		);
		// default sorting field
		protected $defaultSortField = 'postID';
	} // class postStatisticsController

?>
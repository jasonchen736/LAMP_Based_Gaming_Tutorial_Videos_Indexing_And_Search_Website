<?

	class errorsController extends controller {
		// controller for specified table
		protected $table = 'errors';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'errorID' => array('type' => 'integer', 'range' => false),
			'class' => array('type' => 'alphanum-search', 'range' => false),
			'type' => array('type' => 'world-search', 'range' => false),
			'message' => array('type' => 'alphanumspace-search', 'range' => false),
			'date' => array('type' => 'date', 'range' => true),
			'count' => array('type' => 'integer', 'range' => true),
			'status' => array('type' => 'alpha', 'range' => false)
		);
	} // class errorsController

?>
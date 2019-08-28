<?

	class contentController extends controller {
		// controller for specified table
		protected $table = 'content';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'contentID' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'contentName-search', 'range' => false),
			'content' => array('type' => 'alphanumspace-search', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'dateAdded' => array('type' => 'date', 'range' => true),
			'lastModified' => array('type' => 'date', 'range' => true)
		);
	} // class contentController

?>
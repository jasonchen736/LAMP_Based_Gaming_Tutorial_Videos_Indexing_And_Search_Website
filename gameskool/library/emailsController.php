<?

	class emailsController extends controller {
		// controller for specified table
		protected $table = 'emails';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'emailID' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'alphanumspace-search', 'range' => false),
			'subject' => array('type' => 'name-search', 'range' => false),
			'headerID' => array('type' => 'integer', 'range' => false),
			'footerID' => array('type' => 'integer', 'range' => false),
			'dateAdded' => array('type' => 'date', 'range' => true),
			'lastModified' => array('type' => 'date', 'range' => true)
		);
	} // class emailsController

?>
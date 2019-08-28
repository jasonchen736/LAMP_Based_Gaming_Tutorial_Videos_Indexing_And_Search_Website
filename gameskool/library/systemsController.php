<?

	class systemsController extends controller {
		// controller for specified table
		protected $table = 'systems';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'systemID' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'name-search', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'dateAdded' => array('type' => 'date', 'range' => true)
		);

		/**
		 *  Get systems
		 *  Args: none
		 *  Return: (array) systems
		 */
		public static function getSystems() {
			$sql = "SELECT `systemID`, `name` 
				FROM `systems` 
				WHERE `status` = 'active'";
			$result = query($sql);
			$systems = array();
			while ($row = $result->fetchRow()) {
				$systems[$row['systemID']] = $row['name'];
			}
			return $systems;
		} // function getSystems
	} // class systemsController

?>

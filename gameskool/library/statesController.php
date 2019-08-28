<?

	class statesController extends controller {
		// controller for specified table
		protected $table = 'stateCodes';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'stateCode' => array('type' => 'alpha-search', 'range' => false),
			'stateName' => array('type' => 'name-search', 'range' => false)
		);

		/**
		 *  Get states
		 *  Args: none
		 *  Return: (array) states
		 */
		public static function getStates() {
			$sql = "SELECT * FROM `stateCodes`";
			$result = query($sql);
			$states = array();
			while ($row = $result->fetchRow()) {
				$states[$row['stateCode']] = $row['stateName'];
			}
			return $states;
		} // function getStates
	} // class statesController

?>
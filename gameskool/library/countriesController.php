<?

	class countriesController extends controller {
		// controller for specified table
		protected $table = 'countryCodes';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'number' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'name-search', 'range' => false),
			'A2' => array('type' => 'alpha', 'range' => false),
			'A3' => array('type' => 'alpha', 'range' => false)
		);

		/**
		 *  Get countries
		 *  Args: none
		 *  Return: (array) countries
		 */
		public static function getCountries() {
			$sql = "SELECT * FROM `countryCodes`";
			$result = query($sql);
			$countries = array();
			while ($row = $result->fetchRow()) {
				$countries[$row['number']] = $row;
			}
			return $countries;
		} // function getCountries
	} // class countriesController

?>
<?

	class systemProblemsController extends controller {
		// controller for specified table
		protected $table = 'systemProblems';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'systemProblemID' => array('type' => 'integer', 'range' => false),
			'systemID' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'name-search', 'range' => false),
			'cost' => array('type' => 'decimal', 'range' => true),
			'status' => array('type' => 'alpha', 'range' => false),
			'dateAdded' => array('type' => 'date', 'range' => true)
		);

		/**
		 *  Get system problems
		 *  Args: none
		 *  Return: (array) system problems
		 */
		public static function getSystemProblems() {
			$sql = "SELECT `systemProblemID`, `systemID`, `name`, `cost` 
				FROM `systemProblems` 
				WHERE `status` = 'active'";
			$result = query($sql);
			$systemProblems = array();
			while ($row = $result->fetchRow()) {
				if (!isset($systemProblems[$row['systemID']])) {
					$systemProblems[$row['systemID']] = array($row['systemProblemID'] => $row);
				} else {
					 $systemProblems[$row['systemID']][$row['systemProblemID']] = $row;
				}
			}
			return $systemProblems;
		} // function getSystemProblems
	} // class systemProblemsController

?>

<?

	class adminGroupsController extends controller {
		// controller for specified table
		protected $table = 'adminGroups';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'adminGroupID' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'alphanumspace-search', 'range' => false)
		);
		// default sorting field
		protected $defaultSortField = 'adminGroupID';

		/**
		 *  Retrieve all admin groups
		 *  args: none
		 *  return: (array) admin groups
		 */
		public static function getAllGroups() {
			$groups = array();
			$sql = "SELECT `adminGroupID`, `name` FROM `adminGroups` ORDER BY `name` ASC";
			$result = query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$groups[$row['adminGroupID']] = $row['name'];
				}
			}
			return $groups;
		} // function getAllGroups
	} // class adminGroupsController

?>
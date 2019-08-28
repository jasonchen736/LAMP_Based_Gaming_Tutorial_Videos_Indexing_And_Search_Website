<?

	class emailSectionsController extends controller {
		// controller for specified table
		protected $table = 'emailSections';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'emailSectionID' => array('type' => 'integer', 'range' => false),
			'type' => array('type' => 'alpha', 'range' => false),
			'name' => array('type' => 'alphanumspace-search', 'range' => false),
			'dateAdded' => array('type' => 'date', 'range' => true),
			'lastModified' => array('type' => 'date', 'range' => true)
		);

		/**
		 *  Retrieve email sections of specified type
		 *  Args: (str) email section type
		 *  Return: (array) email sections array([id] => name, ... )
		 */
		public static function getSections($sectionType) {
			$sections = array();
			$sectionType = clean($sectionType, 'alpha');
			$sql = "SELECT `emailSectionID`, `name` 
					FROM `emailSections` 
					WHERE `type` = '".$sectionType."' 
					ORDER BY `name`";
			$results = query($sql);
			if ($results->rowCount > 0) {
				while ($row = $results->fetchRow()) {
					$sections[$row['emailSectionID']] = $row['name'];
				}
			}
			return $sections;
		} // function getSections
	} // class emailSectionsController

?>
<?

	class gameTitlesController extends controller {
		// controller for specified table
		protected $table = 'gameTitles';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'gameTitleID' => array('type' => 'integer', 'range' => false),
			'gameTitle' => array('type' => 'alphanumspace-search', 'range' => false),
			'dateAdded' => array('type' => 'date', 'range' => true)
		);

		/**
		 *  Get game title ID
		 *  Args: (str) game title
		 *  Return: (mixed) game title id or false
		 */
		public static function getTitleID($title) {
			$sql = "SELECT `gameTitleID` 
					FROM `gameTitles` 
					WHERE `gameTitleURL` = '".prep($title)."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				return $row['gameTitleID'];
			}
			return false;
		} // function getTitleID

		/**
		 *  Retrieve sorted game titles
		 *  Args: (str) sort method, (int) retrieve limit, (int) from, (str) seek query
		 *  Return: (array) game titles
		 */
		public static function getTitles($sortMethod, $limit = 15, $from = 0, $seek = false) {
			$titles = array();
			$limit = clean($limit, 'integer');
			$from = clean($from, 'integer');
			switch ($sortMethod) {
				case 'alpha':
					$seek = clean($seek, 'alphanum');
					$sql = "SELECT `gameTitleID`, `gameTitle`, `gameTitleURL`
						FROM `gameTitles` 
						WHERE `gameTitleKey` LIKE '".$seek."%' 
						ORDER BY `gameTitleKey` ASC 
						LIMIT ".$from.", ".$limit;
					break;
				case 'weight':
				default:
					$sql = "SELECT `b`.`gameTitleID`, `b`.`gameTitle`, `b`.`gameTitleURL` 
						FROM `gameTitleStatistics` `a` 
						JOIN `gameTitles` `b` USING (`gameTitleID`) 
						WHERE `a`.`status` = 'current'
						ORDER BY `a`.`weight` DESC 
						LIMIT ".$from.", ".$limit;
					break;
			}
			$result = query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$titles[$row['gameTitleID']] = $row;
				}
			}
			return $titles;
		} // function getTitles

		/**
		 *  Count game titles
		 *  Args: (str) sort method, (str) seek query
		 *  Return: (int) total found
		 */
		public static function getTitlesCount($sortMethod, $seek = false) {
			switch ($sortMethod) {
				case 'alpha':
					$seek = clean($seek, 'alphanum');
					$sql = "SELECT COUNT(*) AS `totalGames` FROM `gameTitles` WHERE `gameTitleKey` LIKE '".$seek."%'";
					break;
				case 'weight':
				default:
					$sql = "SELECT COUNT(*) AS `totalGames` FROM `gameTitles`";
					break;
			}
			$result = query($sql);
			$row = $result->fetchRow();
			return $row['totalGames'];
		} // function getTitlesCount
	} // class gameTitlesController

?>

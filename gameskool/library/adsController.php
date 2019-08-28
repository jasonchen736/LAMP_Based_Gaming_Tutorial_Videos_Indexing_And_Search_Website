<?

	class adsController extends controller {
		// controller for specified table
		protected $table = 'ads';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'adID' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'alphanumspace-search', 'range' => false),
			'location' => array('type' => 'alphanumspace', 'range' => false),
			'url' => array('type' => 'url-search', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'poster' => array('type' => 'alpha', 'range' => false),
			'userName' => array('type' => 'alphanumspace-search', 'range' => false),
			'posted' => array('type' => 'date', 'range' => true)
		);

		/**
		 *  Return array of search components
		 *    Organize search components for join to user table
		 *    Override
		 *  Args: none
		 *  Return: (array) search components
		 */
		public function getSearchComponents() {
			$search = parent::getSearchComponents();
			$search['select'] = "`a`.*, IFNULL(`b`.`userName`, `c`.`name`) AS `userName`";
			$search['tables'][0] = '`'.$this->table.'` `a`';
			$search['tables'][] = "LEFT JOIN `users` `b` ON (`a`.`poster` = 'USER' AND `a`.`posterID` = `b`.`userID`)";
			$search['tables'][] = "LEFT JOIN `adminUsers` `c` ON (`a`.`poster` = 'ADMIN' AND `a`.`posterID` = `c`.`adminUserID`)";
			foreach ($search['where'] as $field => &$val) {
				switch ($field) {
					case 'userName':
						$tableAlias = false;
						$userSearch = preg_replace('/^(AND |OR )?/', '$1(`b`.', $val);
						$userSearch .= ' OR '.preg_replace('/`userName`/', '`name`', preg_replace('/^(AND |OR )?/', '`c`.', $val)).')';
						$val = $userSearch;
						break;
					default:
						$tableAlias = 'a';
						break;
				}
				if ($tableAlias) {
					$val = preg_replace('/^(AND |OR )?/', '$1`'.$tableAlias.'`.', $val);
				}
			}
			return $search;
		} // function getSearchComponents

		/**
		 *  Retrieve ad content for specific pages
		 *  Args: none
		 *  Return: (array) ads
		 */
		public function getAds($location) {
			$sql = "SELECT * FROM `ads` WHERE `location` = '".$location."' AND `status` = 'active'";
			$result = query($sql);
			return $result->fetchAll();
		} // function getAds
	} // class adsController

?>

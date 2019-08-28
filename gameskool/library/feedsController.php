<?

	class feedsController extends controller {
		// controller for specified table
		protected $table = 'feeds';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'feedID' => array('type' => 'integer', 'range' => false),
			'postType' => array('type' => 'alpha', 'range' => false),
			'gameTitle' => array('type' => 'alphanumspace-search', 'range' => false),
			'source' => array('type' => 'alphanumspace', 'range' => false),
			'url' => array('type' => 'url-search', 'range' => false),
			'interval' => array('type' => 'alphanumspace', 'range' => false),
			'priority' => array('type' => 'integer', 'range' => true),
			'userName' => array('type' => 'alphanumspace-search', 'range' => false),
			'poster' => array('type' => 'alpha', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'dateAdded' => array('type' => 'date', 'range' => true)
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
			$search['select'] = "`a`.*, `b`.`gameTitle`, IFNULL(`c`.`userName`, IFNULL(`d`.`name`, 'anonymous')) AS `userName`";
			$search['tables'][0] = '`'.$this->table.'` `a`';
			$search['tables'][] = 'JOIN `gameTitles` `b` ON (`a`.`gameTitleID` = `b`.`gameTitleID`)';
			$search['tables'][] = "LEFT JOIN `users` `c` ON (`a`.`poster` = 'USER' AND `a`.`posterID` = `c`.`userID`)";
			$search['tables'][] = "LEFT JOIN `adminUsers` `d` ON (`a`.`poster` = 'ADMIN' AND `a`.`posterID` = `d`.`adminUserID`)";
			foreach ($search['where'] as $field => &$val) {
				switch ($field) {
					case 'gameTitle':
						$tableAlias = 'b';
						break;
					case 'userName':
						$tableAlias = false;
						$userSearch = preg_replace('/^(AND |OR )?/', '$1(`c`.', $val);
						$userSearch .= ' OR '.preg_replace('/`userName`/', '`name`', preg_replace('/^(AND |OR )?/', '`d`.', $val)).')';
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
	} // class feedsController

?>
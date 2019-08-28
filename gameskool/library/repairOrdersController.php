<?

	class repairOrdersController extends controller {
		// controller for specified table
		protected $table = 'repairOrders';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'repairOrderID' => array('type' => 'integer', 'range' => false),
			'console' => array('type' => 'integer', 'range' => false),
			'serial' => array('type' => 'name-search', 'range' => false),
			'first' => array('type' => 'name-search', 'range' => false),
			'last' => array('type' => 'name-search', 'range' => false),
			'email' => array('type' => 'email-search', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'user' => array('type' => 'alpha', 'range' => false),
			'userName' => array('type' => 'alphanumspace-search', 'range' => false),
			'orderDate' => array('type' => 'date', 'range' => true)
		);
		// status categories
		public static $statuses = array(
			'open' => array(
				'new' => true,
				'received' => true,
				'3 days in' => true,
				'7 days in' => true
			),
			'completed' => array(
				'repaired' => true,
				'irreparable' => true,
				'invoice sent' => true,
				'paid' => true,
				'shipped' => true,
				'completed' => true
			),
			'cancelled' => array(
				'cancelled' => true
			)
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
			$search['tables'][] = "LEFT JOIN `users` `b` ON (`a`.`user` = 'USER' AND `a`.`userID` = `b`.`userID`)";
			$search['tables'][] = "LEFT JOIN `adminUsers` `c` ON (`a`.`user` = 'ADMIN' AND `a`.`userID` = `c`.`adminUserID`)";
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
	} // class repairOrdersController

?>

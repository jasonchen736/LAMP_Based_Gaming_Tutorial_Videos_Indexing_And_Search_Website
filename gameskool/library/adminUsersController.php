<?

	class adminUsersController extends controller {
		// controller for specified table
		protected $table = 'adminUsers';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'adminUserID' => array('type' => 'integer', 'range' => false),
			'name' => array('type' => 'alphanumspace-search', 'range' => false),
			'login' => array('type' => 'alphanum-search', 'range' => false),
			'email' => array('type' => 'email-search', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'created' => array('type' => 'date', 'range' => true),
		);
		// default sorting field
		protected $defaultSortField = 'adminUserID';

		/**
		 *  Retrieve emails of admins designated as developers
		 *  args: none
		 *  return: (array) admin emails
		 */
		public static function getDevAdminEmails() {
			$emails = array();
			$sql = "SELECT `b`.`email` 
					FROM `adminUserAccess` `a` 
					JOIN `adminUsers` `b` USING (`adminUserID`) 
					WHERE `a`.`access` = 'DEVELOPER'
					UNION 
					SELECT `c`.`email` 
					FROM `adminGroupAccess` `a` 
					JOIN `adminUserGroupMap` `b` ON (`a`.`adminGroupID` = `b`.`adminGroupID`) 
					JOIN `adminUsers` `c` ON (`b`.`adminUserID` = `c`.`adminUserID`) 
					WHERE `a`.`access` = 'DEVELOPER'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$emails[] = $row['email'];
				}
			}
			return $emails;
		} // function getDevAdminEmails

		/**
		 *  Retrieve an admin user id by login
		 *  Args: (str) login
		 *  Return: (mixed) admin user id or false
		 */
		public static function getUserByLogin($login) {
			$sql = "SELECT `adminUserID` FROM `adminUsers` WHERE `login` = '".prep($login)."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				return $row['adminUserID'];
			}
			return false;
		} // function getUserByLogin

		/**
		 *  Retrieve an admin user id by name
		 *  Args: (str) name
		 *  Return: (mixed) admin user id or false
		 */
		public static function getUserByName($name) {
			$sql = "SELECT `adminUserID` FROM `adminUsers` WHERE `name` = '".prep($name)."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				return $row['adminUserID'];
			}
			return false;
		} // function getUserByName
	} // class adminUsersController

?>
<?

	class adminCore {
		// stores admin user and login info
		private static $admin;

		/**
		 *  Initialize global admin variables
		 *  Args: none
		 *  Return: none
		 */
		public static function initialize() {
			if (!isset($_SESSION['admin'])) {
				$_SESSION['admin'] = array();
			}
			self::$admin = &$_SESSION['admin'];
		} // function initialize

		/**
		 *  Retrieve an admin variable
		 *  Args: (str) variable name
		 *  Return: (mixed) variable value
		 */
		public static function get($variable) {
			if (isset(self::$$variable)) {
				return self::$$variable;
			}
			return NULL;
		} // function get

		/**
		 *  Return sessioned admin user
		 *  Args: none
		 *  Return: (mixed) array admin user or NULL
		 */
		public static function getAdminUser() {
			self::initialize();
			if (isset(self::$admin['user'])) {
				return self::$admin['user'];
			}
			return NULL;
		} // function getAdminUser

		/**
		 *  Validate and store admin login info
		 *  Args: (str) admin login, (str) password
		 *  Return: (boolean) successful login
		 */
		public static function login($login, $pass) {
			self::initialize();
			$login = clean($login, 'alphanum');
			$pass = clean($pass, 'password');
			if ($login && $pass) {
				self::$admin['user'] = array();
				$result = query("SELECT * FROM `adminUsers` WHERE `login` = '".prep($login)."' AND `status` = 'active'");
				if ($result->rowCount > 0) {
					$row = $result->fetchRow();
					$hash = generatePasswordHash($pass, substr($row['password'], -6));
					if ($hash === $row['password']) {
						self::$admin['user'] = $row;
						$adminUser = new adminUser(self::$admin['user']['adminUserID']);
						$userAccess = $adminUser->getAccess();
						self::$admin['user']['access'] = $userAccess;
						$groupAccess = $adminUser->getGroupAccess();
						foreach (self::$admin['user']['access'] as $key => $val) {
							if (!$val && isset($groupAccess[$key]) && $groupAccess[$key]) {
								self::$admin['user']['access'][$key] = true;
							}
						}
						return true;
					} else {
						addError('Login/Password combination does not match');
					}
				} else {
					addError('Login/Password combination does not match');
				}
			} else {
				addError('Invalid login or password provided');
			}
			return false;
		} // function login

		/**
		 *  Admin logout, redirect to admin index
		 *  Args: none
		 *  Return: none
		 */
		public static function logout() {
			self::initialize();
			self::$admin = NULL;
			redirect('/admin');
		} // function logout

		/**
		 *  Verify admin login
		 *  Args: none
		 *  Return: (boolean) valid admin user
		 */
		public static function validate() {
			self::initialize();
			if (isset(self::$admin['user']['adminUserID']) && self::$admin['user']['adminUserID']) {
				return true;
			} else {
				self::$admin = array();
				if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']) {
					self::$admin['loginPage'] = $_SERVER['REQUEST_URI'];
				}
				return false;
			}
		} // function validate

		/**
		 *  Return admin section access
		 *  Args: (str) access section
		 *  Return: (boolean) has access
		 */
		public static function hasAccess($section) {
			if ($adminUser = self::getAdminUser()) {
				return isset($adminUser['access'][$section]) && $adminUser['access'][$section];
			}
			return false;
		} // function hasAccess

		/**
		 *  Verify admin is logged in, redirect if not
		 *  Args: (str) access section
		 *  Return: none
		 */
		public static function checkAccess($section) {
			if (!self::validate()) {
				redirect('/admin/login');
			} elseif ($section !== 'GENERAL') {
				if (!self::hasAccess($section)) {
					addError('You do not have access to that section');
					redirect('/admin');
				}
			}
		} // function checkAccess

		/**
		 *  Check if admin user is logged in
		 *  Args: none
		 *  Return: (boolean) admin user logged in
		 */
		public static function isLoggedIn() {
			self::initialize();
			if (isset(self::$admin['user']['adminUserID']) && self::$admin['user']['adminUserID']) {
				return true;
			} else {
				return false;
			}
		} // function isLoggedIn
	} // class adminCore

?>

<?

	class userCore {
		// stores user and login info
		private static $user = false;

		/**
		 *  Initialize global admin variables
		 *  Args: none
		 *  Return: none
		 */
		public static function initialize() {
			if (!isset($_SESSION['user'])) {
				$_SESSION['user'] = array();
			}
			self::$user = &$_SESSION['user'];
		} // function initialize

		/**
		 *  Retrieve a user variable
		 *  Args: (str) variable name
		 *  Return: (mixed) variable value
		 */
		public static function get($variable) {
			self::initialize();
			if (isset(self::$$variable)) {
				return self::$$variable;
			}
			return NULL;
		} // function get

		/**
		 *  Return sessioned user user info
		 *  Args: none
		 *  Return: (mixed) array user info or NULL
		 */
		public static function getUser() {
			self::initialize();
			if (isset(self::$user['user'])) {
				return self::$user['user'];
			}
			return NULL;
		} // function getUser

		/**
		 *  Validate and store user login info
		 *  Args: (str) user username, (str) password
		 *  Return: (boolean) successful login
		 */
		public static function login($userName, $pass) {
			self::initialize();
			$userName = clean($userName, 'name');
			$pass = clean($pass, 'password');
			if ($userName && $pass) {
				self::$user['user'] = array();
				$result = query("SELECT * FROM `users` WHERE `userName` = '".prep($userName)."'");
				if ($result->rowCount > 0) {
					$row = $result->fetchRow();
					$hash = generatePasswordHash($pass, substr($row['password'], -6));
					if ($hash === $row['password']) {
						if ($row['status'] == 'active') {
							self::$user['user'] = $row;
							usersController::writeSubscriptionCookie($row['userID']);
							if (isset(self::$user['timedLoginPage'])) {
								if (time() <= self::$user['timedLoginPage']['timeout']) {
									self::$user['loginPage'] = self::$user['timedLoginPage']['url'];
								}
								unset(self::$user['timedLoginPage']);
							}
							return true;
						} else {
							addError('your account is not active');
						}
					} else {
						addError('bad login and password combination');
					}
				} else {
					addError('bad login and password combination');
				}
			} else {
				addError('invalid login or password provided');
			}
			return false;
		} // function login

		/**
		 *  Check for existing external id, login if found
		 *  Args: (str) external id
		 *  Return: (boolean) successful login
		 */
		public static function externalLogin($externalID) {
			self::initialize();
			$externalID = clean($externalID, 'url');
			if ($externalID) {
				self::$user['user'] = array();
				$result = query("SELECT * FROM `users` WHERE `externalID` = '".prep($externalID)."'");
				if ($result->rowCount > 0) {
					$row = $result->fetchRow();
					if ($row['status'] == 'active') {
						self::$user['user'] = $row;
						usersController::writeSubscriptionCookie($row['userID']);
						if (isset(self::$user['timedLoginPage'])) {
							if (time() <= self::$user['timedLoginPage']['timeout']) {
								self::$user['loginPage'] = self::$user['timedLoginPage']['url'];
							}
							unset(self::$user['timedLoginPage']);
						}
						return true;
					} else {
						addError('the account you logged into is not active, please contact support for more information');
					}
				}
			}
			return false;
		} // function externalLogin

		/**
		 *  Set up core for a given user, bypass login, will log out previous user
		 *  Args: none
		 *  Return: none
		 */
		public static function setCore($userID) {
			self::initialize();
			self::$user['user'] = array();
			$userID = clean($userID, 'integer');
			if ($userID) {
				$result = query("SELECT * FROM `users` WHERE `userID` = '".$userID."'");
				if ($result->rowCount > 0) {
					$row = $result->fetchRow();
					self::$user['user'] = $row;
				}
			}
		} // function setCore

		/**
		 *  user logout, redirect to homepage
		 *  Args: none
		 *  Return: none
		 */
		public static function logout() {
			self::initialize();
			self::$user = NULL;
			require_once 'fbconnect/facebook.php';
			$fb = new Facebook(systemSettings::get('FBAPIKEY'), systemSettings::get('FBSECRET'));
			$fb->clear_cookie_state();
			redirect('/');
		} // function logout

		/**
		 *  Set a page to return to on login with a timeout period
		 *  Args: (str) login page
		 *  Return: none
		 */
		public static function setTimedLoginPage($url) {
			self::initialize();
			self::$user['timedLoginPage'] = array('url' => $url, 'timeout' => time() + 300);
		} // function setTimedLoginPage

		/**
		 *  Verify user login
		 *  Args: none
		 *  Return: (boolean) valid user
		 */
		public static function validate() {
			self::initialize();
			if (isset(self::$user['user']['userID']) && self::$user['user']['userID']) {
				return true;
			} else {
				self::$user = array();
				if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] && $_SERVER['REQUEST_URI'] != '/user/login') {
					self::$user['loginPage'] = $_SERVER['REQUEST_URI'];
				} else {
					self::$user['loginPage'] = '/';
				}
				return false;
			}
		} // function validate

		/**
		 *  Verify user is logged in, redirect if not
		 *  Args: none
		 *  Return: none
		 */
		public static function checkAccess() {
			if (!self::validate()) {
				if (!haveMessages()) {
					addMessage('oops, you have to log in to do that');
				}
				redirect('/user/login');
			}
		} // function checkAccess

		/**
		 *  Check if user is logged in
		 *  Args: none
		 *  Return: (boolean) user logged in
		 */
		public static function isLoggedIn() {
			self::initialize();
			if (isset(self::$user['user']['userID']) && self::$user['user']['userID']) {
				return true;
			} else {
				return false;
			}
		} // function isLoggedIn
	} // class userCore

?>

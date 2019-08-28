<?

	class tracker {
		// referral data cookie name
		public static $cookie = 'tsref';
		// fields mapped to request field names
		public static $requestFields = array(
			'ID'    => 'ref',
			'subID' => 'sub',
			'extra' => 'eref'
		);
		// fields stored in session, mapped to session field names
		public static $sessionFields = array(
			'ID'    => 'ID',
			'subID' => 'subID',
			'extra' => 'extra'
		);
		// fields stored in cookie
		public static $cookieFields = array(
			'ID',
			'subID',
			'extra'
		);
		// fields tracked in tracking table
		public static $trackingFields = array(
			'ID',
			'subID',
			'extra'
		);
		public static $fieldData = array(
			'ID'    => array('ID', 'integer', 0, 11),
			'subID' => array('subID', 'clean', 0, 100),
			'extra' => array('extra', 'clean', 0, 100)
		);

		/**
		 *  Clean and store a tracking value
		 *  Args: (string) field name, (mixed) value
		 *  Return: none
		 */
		public static function set($field, $value) {
			if (array_key_exists($field, self::$sessionFields)) {
				$fieldData = self::$fieldData[$field];
				$_SESSION['_tracker'][self::$sessionFields[$field]] = clean($value, $fieldData[1], $fieldData[3]);
			}
		} // function set

		/**
		 *  Retrieve a stored tracking value
		 *  Args: (string) field name
		 *  Return: (mixed) field value
		 */
		public static function get($field) {
			if (array_key_exists($field, self::$sessionFields)) {
				return $_SESSION['_tracker'][self::$sessionFields[$field]];
			} else {
				return NULL;
			}
		} // function get

		/**
		 *  Detect if user has just landed on the site
		 *  Args: none
		 *  Return: (boolean) just landed
		 */
		public static function justLanded() {
			return !isset($_SESSION['_tracker'][self::$sessionFields['ID']]);
		} // function justLanded

		/**
		 *  Detect if a visitor was referred
		 *  Args: none
		 *  Return: (boolean) was referred
		 */
		public static function wasReferred() {
			if (self::justLanded()) {
				if (getRequest(self::$requestFields['ID']) || getRequest(self::$requestFields['subID'])) {
					return true;
				} else {
					return false;
				}
			} else {
				if ($_SESSION['_tracker'][self::$sessionFields['ID']] || $_SESSION['_tracker'][self::$sessionFields['subID']]) {
					return true;
				} else {
					return false;
				}
			}
		} // function wasReferred

		/**
		 *  On user landing, retrieve reference data (including offer payout id), track data, set cookie
		 *  Args: (boolean) set reference cookie
		 *  Return: none
		 */
		public static function trackLanding($setCookie = true) {
			if (self::justLanded()) {
				// establish reference data
				$isUniqueVisitor = self::retrieveReferral();
				// record reference data
				self::logReference($isUniqueVisitor);
				if ($setCookie) {
					// set reference cookie
					self::setReferralCookie();
				}
			}
		} // function trackLanding

		/**
		 *  Retrieve reference data from the query string
		 *    set referral id, sub id, extra inforamation
		 *  Args: none
		 *  Return: (boolean) unique visitor
		 */
		public static function retrieveReferral() {
			$isUnique = true;
			$fieldData = self::$fieldData;
			// check request parameters first
			$referrer = getRequest(self::$requestFields['ID']);
			if (validNumber($referrer, 'integer')) {
				$ID = $referrer;
			} else {
				$ID = 0;
			}
			$subID = getRequest(self::$requestFields['subID'], $fieldData['subID'][1], $fieldData['subID'][3]);
			$extra = getRequest(self::$requestFields['extra'], $fieldData['extra'][1], $fieldData['extra'][3]);
			// check if user has visted before
			$cookie = getCookie(systemSettings::get('COOKIEPREFIX').self::$cookie);
			if ($cookie) {
				// if user already had a cookie,
				//   not a unique visitor
				$isUnique = false;
				//   but came back to the site without referral id, the previous referral will be credited
				//   ID;subID;extra
				if ($ID == 0) {
					$cookieData = explode(';', $cookie);
					$ID = isset($cookieData[0]) ? $cookieData[0] : 0;
					if (!validNumber($ID, 'integer')) {
						$ID = 0;
					}
					$subID = isset($cookieData[1]) ? clean($cookieData[1], $fieldData['subID'][1], $fieldData['subID'][3]) : '';
					$extra = isset($cookieData[2]) ? clean($cookieData[2], $fieldData['extra'][1], $fieldData['extra'][3]) : '';
				}
			}
			// cleanse and set sessions
			foreach (self::$sessionFields as $field => $val) {
				self::set($field, $$field);
			}
			return $isUnique;
		} // function retrieveReferral

		/**
		 *  Enter tracking information into table (will track site id by default)
		 *  Args: (boolean) unique visitor
		 *  Return: none
		 */
		public static function logReference($isUnique) {
			$insertFields = '';
			$insertValues = '';
			$referralData = getSession('_tracker');
			foreach (self::$trackingFields as $field) {
				$insertFields .= '`'.self::$fieldData[$field][0].'`, ';
				$insertValues .= "'".prep($referralData[self::$sessionFields[$field]])."', ";
			}
			$insertFields .= '`date`, `hits`, `uniqueHits`';
			$insertValues .= 'CURDATE(), 1, 1';
			$sql = "INSERT INTO `trafficSourceHits` 
					(".$insertFields.") 
					VALUES (".$insertValues.") 
					ON DUPLICATE KEY UPDATE `hits` = `hits` + 1";
			if ($isUnique) {
				$sql .= ', `uniqueHits` = `uniqueHits` + 1';
			}
			query($sql);
		} // function logReference

		/**
		 *  Write reference cookie, set time to expire in 30 days available to all pages in default domain
		 *    cookie: ID;subID;extra
		 *  Args: none
		 *  Return: none
		 */
		public static function setReferralCookie() {
			$cookie = '';
			$referralData = getSession('_tracker');
			foreach (self::$cookieFields as $field) {
				if (isset($referralData[self::$sessionFields[$field]])) {
					$cookie .= $referralData[self::$sessionFields[$field]];
				}
				$cookie .= ';';
			}
			$cookie = substr($cookie, 0, -1);
			setcookie(systemSettings::get('COOKIEPREFIX').self::$cookie, $cookie, time() + systemSettings::get('COOKIEDURATION'), '/', systemSettings::get('COOKIEDOMAIN'));
		} // function setReferralCookie
	} // class tracker

?>
<?

	class usersController extends controller {
		// controller for specified table
		protected $table = 'users';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'userID' => array('type' => 'integer', 'range' => false),
			'userName' => array('type' => 'userName-search', 'range' => false),
			'email' => array('type' => 'email-search', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'dateCreated' => array('type' => 'date', 'range' => true)
		);
		// default sorting field
		protected $defaultSortField = 'userID';

		/**
		 *  Set defaults for saving
		 *  Args: (object) template object, (str) recipient email address
		 *  Return: (boolean) mail sent
		 */
		public static function sendActivationEmail($template, $email) {
			$mailer = $template->getMailer('userActivation');
			if ($mailer->composeMessage()) {
				if ($email) {
					$mailer->addRecipient($email);
					return $mailer->send();
				}
			}
			return false;
		} // function sendActivationEmail

		/**
		 *  Retrieve user activation object
		 *  Args: (int) user id
		 *  Return: (object) user activation object
		 */
		public static function getActivation($userID) {
			$userActivation = new userActivation;
			$userID = clean($userID, 'integer');
			$sql = "SELECT * FROM `userActivations` WHERE `userID` = '".$userID."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				$userActivation->loadRecord($row);
			}
			return $userActivation;
		} // function getActivation

		/**
		 *  Retrieve a user id by name
		 *  Args: (str) user name
		 *  Return: (mixed) user id or false
		 */
		public static function getUserByName($userName) {
			$sql = "SELECT `userID` FROM `users` WHERE `userName` = '".prep($userName)."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				return $row['userID'];
			}
			return false;
		} // function getUserByName

		/**
		 *  Retrieve game subscriptions for user
		 *  Args: (int) user id
		 *  Return: (array) game titles
		 */
		public static function getGameSubscriptions($userID) {
			$gameTitles = array();
			$sql = "SELECT `a`.`gameTitleID`, `b`.`gameTitle`, `b`.`gameTitleURL` 
					FROM `userSubscriptions` `a` 
					JOIN `gameTitles` `b` USING (`gameTitleID`) 
					WHERE `a`.`userID` = '".$userID."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$gameTitles[$row['gameTitleID']] = $row;
				}
			}
			uasort($gameTitles, array('usersController', 'gameTitlesSortAlpha'));
			return $gameTitles;
		} // function getGameSubscriptions

		/**
		 *  Sort game titles alphabetically
		 *  Args: (array) game title array 1, (array) game title array 2
		 *  Return: (int) comparison value
		 */
		public static function gameTitlesSortAlpha($a, $b) {
			if ($a['gameTitle'] == $b['gameTitle']) {
				return 0;
			}
			return ($a['gameTitle'] < $b['gameTitle']) ? -1 : 1;
		} // function gameTitlesSortAlpha

		/**
		 *  Add user game title subscription
		 *  Args: (int) user id, (int) game title id
		 *  Return: (boolean) success
		 */
		public static function addSubscription($userID, $gameTitleID) {
			$sql = "INSERT IGNORE INTO `userSubscriptions` VALUES ('".$userID."', '".$gameTitleID."')";
			$result = query($sql);
			return $result->sqlErrorNumber === false;
		} // function addSubscription

		/**
		 *  Remove user game title subscription
		 *  Args: (int) user id, (int) game title id
		 *  Return: (boolean) success
		 */
		public static function removeSubscription($userID, $gameTitleID) {
			$sql = "DELETE FROM `userSubscriptions` 
					WHERE `userID` = '".$userID."' 
					AND `gameTitleID` = '".$gameTitleID."'";
			$result = query($sql);
			return $result->rowCount > 0;
		} // function removeSubscription

		/**
		 *  Write user game subscription cookie
		 *  Args: (int) user id
		 *  Return: none
		 */
		public static function writeSubscriptionCookie($userID) {
			$subscriptions = self::getGameSubscriptions($userID);
			$gameTitles = array_keys($subscriptions);
			$a = setcookie(systemSettings::get('COOKIEPREFIX').'gsubs', implode(',', $gameTitles), time() + systemSettings::get('COOKIEDURATION'), '/', systemSettings::get('COOKIEDOMAIN'));
		} // function writeSubscriptionCookie

		/**
		 *  Retrieve user post votes
		 *  Args: (int) user id, (array) post ids
		 *  Return: (array) user post votes
		 */
		public static function getPostVotes($userID = false, $posts = false) {
			if (empty($userID)) {
				$user = userCore::getUser();
				if (isset($user['userID'])) {
					$userID = $user['userID'];
				}
			} else {
				$userID = clean($userID, 'integer');
			}
			$postVotes = array();
			if (!empty($userID)) {
				$tableNumber = substr($userID, -1, 1);
				$sql = "SELECT `postID`, `vote` 
						FROM `userPostVotes".$tableNumber."` 
						WHERE `userID` = '".$userID."'";
				if (is_array($posts) && !empty($posts)) {
					$sql .= " AND `postID` IN ('".implode("', '", $posts)."')";
				}
				$result = query($sql);
				if ($result->rowCount > 0) {
					while ($row = $result->fetchRow()) {
						$postVotes[$row['postID']] = $row['vote'];
					}
				}
			}
			return $postVotes;
		} // function getPostVotes

		/**
		 *  Retrieve user comment votes
		 *  Args: (int) user id, (int) post id, (array) post comment ids
		 *  Return: (array) user post votes
		 */
		public static function getCommentVotes($userID = false, $post = false, $commentIDs = false) {
			if (empty($userID)) {
				$user = userCore::getUser();
				if (isset($user['userID'])) {
					$userID = $user['userID'];
				}
			} else {
				$userID = clean($userID, 'integer');
			}
			$post = clean($post, 'integer');
			$commentVotes = array();
			if (!empty($userID)) {
				$tableNumber = substr($userID, -1, 1);
				$sql = "SELECT `postID`, `postCommentID`, `vote` 
						FROM `userCommentVotes".$tableNumber."` 
						WHERE `userID` = '".$userID."'";
				if (!empty($post)) {
					$sql .= " AND `postID` = '".$post."'";
				}
				if (is_array($commentIDs) && !empty($commentIDs)) {
					$sql .= " AND `postCommentID` IN ('".implode("', '", $commentIDs)."')";
				}
				$result = query($sql);
				if ($result->rowCount > 0) {
					while ($row = $result->fetchRow()) {
						$commentVotes[$row['postID'].'_'.$row['postCommentID']] = $row['vote'];
					}
				}
			}
			return $commentVotes;
		} // function getCommentVotes
	} // class usersController

?>

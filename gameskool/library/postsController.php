<?

	class postsController extends controller {
		// controller for specified table
		protected $table = 'posts';
		// fields available to search: array(field name => array(type, range))
		protected $searchFields = array(
			'postID' => array('type' => 'integer', 'range' => false),
			'type' => array('type' => 'alpha', 'range' => false),
			'gameTitle' => array('type' => 'alphanumspace-search', 'range' => false),
			'postTitle' => array('type' => 'alphanum-search', 'range' => false),
			'source' => array('type' => 'alphanumspace', 'range' => false),
			'status' => array('type' => 'alpha', 'range' => false),
			'posted' => array('type' => 'date', 'range' => true),
			'userName' => array('type' => 'alphanumspace-search', 'range' => false),
			'poster' => array('type' => 'alpha', 'range' => false)
		);
		// post types
		public static $postTypes = array(
			'video' => 0,
			'link' => 1,
			'blog' => 3,
			'wiki' => 2
		);
		// post submit user types mapped to int values
		public static $userTypes = array(
			'user' => 2,
			'admin' => 1,
			'anonymous' => 0
		);
		// supported video sources
		public static $supportedSources = array(
			'Youtube' => 'Youtube',
			'Google Video' => 'Google Video',
			'Gamevideos' => 'Gamevideos',
			'Vimeo' => 'Vimeo'
		);
		// types that have content revisions
		public static $wikiTypes = array(
			'video' => 0,
			'wiki' => 2,
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
			$search['select'] = "`a`.*, `b`.`gameTitle`, `b`.`gameTitleURL`, IFNULL(`c`.`userName`, IFNULL(`d`.`name`, 'anonymous')) AS `userName`";
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

		/**
		 *  Get post types as a value => value array
		 *  Args: none
		 *  Return: (array) post types
		 */
		public static function getPostTypes() {
			$postTypes = array();
			foreach (self::$postTypes as $type => $id) {
				$postTypes[$type] = $type;
			}
			return $postTypes;
		} // function getPostTypes

		/**
		 *  Validate a url on format and resolution
		 *  Args: (str) url
		 *  Return: (boolean) valid url
		 */
		public static function isValidURL($url) {
			if (filter_var($url, FILTER_VALIDATE_URL)) {
				$headers = getHeadersCurl($url);
				return !empty($headers[0]) && strpos($headers[0], '404') === false;
			}
			return false;
		} // function isValidURL

		/**
		 *  Isolate a video id from web url
		 *  Args: (str) video source, (str) video url
		 *  Return: (str) video id
		 */
		public static function isolateVideoID($source, $url) {
			$id = $url;
			if (preg_match('/http/', $url)) {
				$regex = false;
				switch ($source) {
					case 'Gamevideos':
						$regex = '/post\/id\/([^&\/"\']+)/';
						break;
					case 'Google Video':
						$regex = '/docid=([^&\/"\']+)/';
						break;
					case 'Vimeo':
						$regex = '/vimeo.com\/(\d+)/';
						break;
					case 'Youtube':
						$regex = '/v[=\/]([^&\/"\']+)/';
						break;
					default:
						break;
				}
				if ($regex) {
					preg_match($regex, $url, $match);
					if (isset($match[1])) {
						$id = $match[1];
					}
				}
			}
			return $id;
		} // function isolateVideoID

		/**
		 *  Make a curl web request to post info url
		 *  Args: (str) post info url
		 *  Return: (str) curl response
		 */
		public static function requestPostInfo($url) {
			$ch = curl_init($url);
			curl_setopt ($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		} // function requestPostInfo

		/**
		 *  Retrieve the thumbnail image for video from source/identifier
		 *  Args: (str) video source, (str) video identifier
		 *  Return: (str) thumbnail image url
		 */
		public static function getThumbnailLocation($source, $identifier) {
			$url = '/images/'.systemSettings::get('SOURCEDIR').'/site/noThumb.gif';
			switch ($source) {
				case 'Gamevideos':
					$url = 'http://download.gamevideos.com/'.$identifier.'/thumbnail.jpg';
					break;
				case 'Google Video':
					$postInfoLoc = 'http://post.google.com/postfeed?docid='.$identifier;
					$response = self::requestPostInfo($postInfoLoc);
					preg_match('/<media:thumbnail url="([^"]+)"/', $response, $match);
					if (isset($match[1]) && !empty($match[1])) {
						$url = html_entity_decode($match[1]);
					}
					break;
				case 'Vimeo':
					$postInfoLoc = 'http://vimeo.com/api/clip/'.$identifier.'/php/';
					$response = self::requestPostInfo($postInfoLoc);
					if (!empty($response)) {
						$postInfo = unserialize($response);
						$postInfo = isset($postInfo[0]) ? $postInfo[0] : false;
						if (isset($postInfo['thumbnail_medium'])) {
							$url = html_entity_decode($postInfo['thumbnail_medium']);
						} else if (isset($postInfo['thumbnail_small'])) {
							$url = html_entity_decode($postInfo['thumbnail_small']);
						}
					}
					break;
				case 'Youtube':
					$url = 'http://img.youtube.com/vi/'.$identifier.'/default.jpg';
					break;
				default:
					break;
			}
			return $url;
		} // function getThumbnailLocation

		/**
		 *  Retrieve the thumbnail image for video from source/identifier
		 *  Args: (str) video source, (str) video identifier
		 *  Return: (str) thumbnail image url
		 */
		public static function getEmbedSource($source, $identifier) {
			$embedSource = '';
			switch ($source) {
				case 'Gamevideos':
					$embedSource = 'http://gamevideos.1up.com/swf/gamevideos12.swf?embedded=1&amp;fullscreen=1&amp;autoplay=0&amp;src=http://gamevideos.1up.com/do/postListXML%3Fid%3D'.$identifier.'%26adPlay%3Dtrue';
					break;
				case 'Google Video':
					$embedSource = 'http://post.google.com/googleplayer.swf?docId='.$identifier.'&amp;hl=en';
					break;
				case 'Vimeo':
					$embedSource = 'http://vimeo.com/moogaloop.swf?clip_id='.$identifier.'&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1';
					break;
				case 'Youtube':
					$embedSource = 'http://www.youtube.com/v/'.$identifier.'&amp;h1=en&amp;fs=1';
					break;
				default:
					break;
			}
			return $embedSource;
		} // function getEmbedSource

		/**
		 *  Create post statistics and comment cache records
		 *  Args: (int) post id
		 *  Return: none
		 */
		public static function createAssociateRecords($postID) {
			$sql = "INSERT INTO `postStatistics` (`postID`) 
					VALUES ('".clean($postID, 'integer')."')";
			$result = query($sql);
			$sql = "INSERT INTO `postCommentsCache` (`postID`, `expired`) 
					VALUES ('".clean($postID, 'integer')."', 1)";
			$result = query($sql);
		} // function createAssociateRecords

		/**
		 *  Update delta index table
		 *  Args: (int) post id
		 *  Return: none
		 */
		public static function flagDeltaUpdate($postID) {
			$sql = "INSERT INTO `postIndexDelta` (`postID`, `status`)
					VALUES ('".clean($postID, 'integer')."', 'updated') 
					ON DUPLICATE KEY UPDATE `status` = 'updated'";
			query($sql);
		} // function flagDeltaUpdate

		/**
		 *  Update index attribute values
		 *  Args: (int) post id
		 *  Return: none
		 */
		public static function updateIndexAttributes($postID) {
			$sql = "SELECT `a`.`postID`, `a`.`gameTitleID`, UNIX_TIMESTAMP(`a`.`lastModified`) AS `lastModified`, 
						`a`.`type` + 0 AS `type`, `a`.`status` + 0 AS `status`, 
						IF(`a`.`poster` = 'ADMIN', 1, 0) AS `poster`, UNIX_TIMESTAMP(`a`.`posted`) AS `posted`, 
						`a`.`posterID`, `b`.`views`, `b`.`comments`, `b`.`upVotes`, `b`.`downVotes`, 
						`c`.`status` AS `delta` 
					FROM `posts` `a` 
					JOIN `postStatistics` `b` ON (`a`.`postID` = `b`.`postID`) 
					LEFT JOIN `postIndexDelta` `c` ON (`b`.`postID` = `c`.`postID`) 
					WHERE `a`.`postID` = '".$postID."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				$attributes = array(
					'gametitleid',
					'lastmodified',
					'type',
					'status',
					'poster',
					'posted',
					'posterid',
					'views',
					'comments',
					'upvotes',
					'downvotes'
				);
				$values = array(
					$row['postID'] => array(
						(int) $row['gameTitleID'],
						(int) $row['lastModified'],
						(int) $row['type'],
						(int) $row['status'],
						(int) $row['poster'],
						(int) $row['posted'],
						(int) $row['posterID'],
						(int) $row['views'],
						(int) $row['comments'],
						(int) $row['upVotes'],
						(int) $row['downVotes']
					)
				);
				$sphinxClient = new postSearch;
				if ($row['delta'] == 'indexed') {
					// update delta index
					$sphinxClient->UpdateAttributes($sphinxClient->indexes[1], $attributes, $values);
				} else {
					// update main index
					$sphinxClient->UpdateAttributes($sphinxClient->indexes[0], $attributes, $values);
				}
			}
		} // function updateIndexAttributes

		/**
		 *  Remove post from indexes
		 *  Args: (int) post id
		 *  Return: none
		 */
		public static function removePostFromIndex($postID) {
			$attributes = array('status');
			$values = array($postID => array(0));
			$sphinxClient = new postSearch;
			$sphinxClient->UpdateAttributes($sphinxClient->indexes[0], $attributes, $values);
			$sphinxClient->UpdateAttributes($sphinxClient->indexes[1], $attributes, $values);
			$sql = "DELETE FROM `postIndexDelta` WHERE `postID` = '".$postID."'";
			query($sql);
		} // function removePostFromIndex

		/**
		 *  Check for delta updates to be performed
		 *  Args: none
		 *  Return: none
		 */
		public static function hasDeltaUpdates() {
			$sql = "SELECT `postID` FROM `postIndexDelta` WHERE `status` = 'updated' LIMIT 1";
			$result = query($sql);
			return $result->rowCount > 0;
		} // function hasDeltaUpdates

		/**
		 *  Remove delta indexed posts from main index
		 *  Args: none
		 *  Return: none
		 */
		public static function removeIndexedDeltas() {
			$sql = "SELECT `postID` FROM `postIndexDelta` WHERE `status` = 'indexed'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$attributes = array('status');
				$values = array();
				while ($row = $result->fetchRow()) {
					$values[$row['postID']] = array(0);
				}
				$sphinxClient = new postSearch;
				$sphinxClient->UpdateAttributes($sphinxClient->indexes[0], $attributes, $values);
			}
		} // function removeIndexedDeltas

		/**
		 *  Record a post page view
		 *  Args: (int) post id
		 *  Return: none
		 */
		public static function addPageView($postID) {
			$sql = "INSERT INTO `postViews` (`postID`, `date`, `views`) 
					SELECT `postID`, '".date('Y-m-d')."', 1 
					FROM `posts` 
					WHERE `postID` = '".$postID."' 
					ON DUPLICATE KEY UPDATE `views` = `views` + 1";
			$result = query($sql);
			if (!empty($result->rowCount)) {
				$sql = "UPDATE `postStatistics` 
						SET `views` = `views` + 1 
						WHERE `postID` = '".$postID."'";
				query($sql);
			}
		} // function addPageView

		/**
		 *  Record a post vote
		 *  Args: (int) post id, (int) vote type
		 *  Return: none
		 */
		public static function postVote($postID, $vote) {
			if ($vote == 'up' || $vote == 'down') {
				if ($vote == 'up') {
					$value = 1;
					$updateCode = "IF(`vote` = 1, 1, `vote` + 1)";
					$statisticsField = 'upVotes';
					$oppositeField = 'downVotes';
				} else {
					$value = -1;
					$updateCode = "IF(`vote` = -1, -1, `vote` - 1)";
					$statisticsField = 'downVotes';
					$oppositeField = 'upVotes';
				}
				$user = userCore::getUser();
				if (!empty($user)) {
					$userID = $user['userID'];
					$tableNumber = substr($userID, -1, 1);
					$sql = "INSERT INTO `userPostVotes".$tableNumber."` (`userID`, `postID`, `vote`) 
							SELECT '".$userID."', `postID`, ".$value." 
							FROM `posts` 
							WHERE `postID` = '".$postID."' 
							ON DUPLICATE KEY UPDATE `vote` = ".$updateCode;
					$result = query($sql);
					if (!empty($result->rowCount)) {
						if ($result->rowCount == 1) {
							$sql = "UPDATE `postStatistics` 
									SET `".$statisticsField."` = `".$statisticsField."` + 1 
									WHERE `postID` = '".$postID."'";
						} else {
							$sql = "UPDATE `postStatistics` `a`, `userPostVotes".$tableNumber."` `b` 
									SET `a`.`".$statisticsField."` = IF(`b`.`vote` = 0, `a`.`".$statisticsField."`, `a`.`".$statisticsField."` + 1), 
									`a`.`".$oppositeField."` = IF(`b`.`vote` = 0, `a`.`".$oppositeField."` - 1, `a`.`".$oppositeField."`) 
									WHERE `a`.`postID` = '".$postID."' 
									AND `b`.`userID` = '".$userID."' 
									AND `b`.`postID` = '".$postID."'";
						}
						query($sql);
						self::flagDeltaUpdate($postID);
					}
				}
			}
		} // function postVote

		/**
		 *  Record a post comment vote
		 *  Args: (int) post id, (int) comment id, (int) vote type
		 *  Return: none
		 */
		public static function postCommentVote($postID, $commentID, $vote) {
			if ($vote == 'up' || $vote == 'down') {
				if ($vote == 'up') {
					$value = 1;
					$updateCode = "IF(`vote` = 1, 1, `vote` + 1)";
					$statisticsField = 'upVotes';
					$oppositeField = 'downVotes';
				} else {
					$value = -1;
					$updateCode = "IF(`vote` = -1, -1, `vote` - 1)";
					$statisticsField = 'downVotes';
					$oppositeField = 'upVotes';
				}
				$user = userCore::getUser();
				$postComment = new postComment($commentID, $postID);
				if (!empty($user) && $postComment->exists()) {
					$userID = $user['userID'];
					$voteTableNumber = substr($userID, -1, 1);
					$commentTableNumber = substr($postID, -1, 1);
					$sql = "INSERT INTO `userCommentVotes".$voteTableNumber."` (`userID`, `postID`, `postCommentID`, `vote`) 
							SELECT '".$userID."', `postID`, `postCommentID`, ".$value." 
							FROM `postComments".$commentTableNumber."` 
							WHERE `postID` = '".$postID."' 
							AND `postCommentID` = '".$commentID."' 
							ON DUPLICATE KEY UPDATE `vote` = ".$updateCode;
					$result = query($sql);
					if (!empty($result->rowCount)) {
						if ($result->rowCount == 1) {
							$sql = "UPDATE `postComments".$commentTableNumber."` 
									SET `".$statisticsField."` = `".$statisticsField."` + 1, 
									`totalVotes` = `upVotes` - `downVotes` 
									WHERE `postID` = '".$postID."' 
									AND `postCommentID` = '".$commentID."'";
						} else {
							$sql = "UPDATE `postComments".$commentTableNumber."` `a`, `userCommentVotes".$voteTableNumber."` `b` 
									SET `a`.`".$statisticsField."` = IF(`b`.`vote` = 0, `a`.`".$statisticsField."`, `a`.`".$statisticsField."` + 1), 
									`a`.`".$oppositeField."` = IF(`b`.`vote` = 0, `a`.`".$oppositeField."` - 1, `a`.`".$oppositeField."`), 
									`a`.`totalVotes` = `a`.`upVotes` - `a`.`downVotes` 
									WHERE `a`.`postID` = '".$postID."' 
									AND `a`.`postCommentID` = '".$commentID."' 
									AND `b`.`userID` = '".$userID."' 
									AND `b`.`postID` = '".$postID."' 
									AND `b`.`postCommentID` = '".$commentID."'";
						}
						query($sql);
						$postComment->expireCommentsCache();
					}
				}
			}
		} // function postCommentVote

		/**
		 *  Retrieve post data by game/post title
		 *  Args: (str) game title, (str) post title
		 *  Return: (mixed) array or false
		 */
		public static function retrievePostData($game, $post) {
			$sql = "SELECT `b`.`postID`, `b`.`type`, `b`.`source`, `b`.`identifier`, 
						`a`.`gameTitle`, `a`.`gameTitleURL`, `b`.`url`, `b`.`image`, `b`.`postTitle`, `b`.`postTitleURL`, 
						`b`.`description`, `b`.`content`, `b`.`status`, 
						IFNULL(`d`.`userName`, IFNULL(`e`.`name`, 'anonymous')) AS `posterName`, 
						`b`.`poster`, `b`.`posterID`, `b`.`posted`, `c`.`comments`, 
						`c`.`upVotes`, `c`.`downVotes` 
					FROM `gameTitles` `a` 
					JOIN `posts` `b` ON (`a`.`gameTitleID` = `b`.`gameTitleID`) 
					JOIN `postStatistics` `c` ON (`b`.`postID` = `c`.`postID`) 
					LEFT JOIN `users` `d` ON (`b`.`poster` = 'USER' AND `b`.`posterID` = `d`.`userID`) 
					LEFT JOIN `adminUsers` `e` ON (`b`.`poster` = 'ADMIN' AND `b`.`posterID` = `e`.`adminUserID`) 
					WHERE `a`.`gameTitleURL` = '".prep($game)."' 
					AND `b`.`postTitleURL` = '".prep($post)."'";
			$result = query($sql);
			if ($result->rowCount > 0) {
				$row = $result->fetchRow();
				$row['poster'] = strtolower($row['poster']);
				$row['timeSubmitted'] = time() - strtotime($row['posted']);
				if ($row['timeSubmitted'] < 0) {
					$row['timeSubmitted'] = 0;
				}
				if ($row['timeSubmitted'] < 60) {
					$period = 'second';
				} elseif ($row['timeSubmitted'] < 3600) {
					$row['timeSubmitted'] = round($row['timeSubmitted'] / 60);
					$period = 'minute';
				} elseif ($row['timeSubmitted'] < 86400) {
					$row['timeSubmitted'] = round($row['timeSubmitted'] / 3600);
					$period = 'hour';
				} else {
					$row['timeSubmitted'] = round($row['timeSubmitted'] / 86400);
					$period = 'day';
				}
				if ($row['timeSubmitted'] > 1) {
					$row['timeSubmitted'] = $row['timeSubmitted'].' '.$period.'s';
				} else {
					$row['timeSubmitted'] = $row['timeSubmitted'].' '.$period;
				}
				return $row;
			}
			return false;
		} // function retrievePostData

		/**
		 *  Retrieve post content revision history
		 *  Args: (int) post id
		 *  Return: (array) post content revision history
		 */
		public static function getRevisionHistory($postID) {
			$revisions = array();
			$sql = "SELECT `a`.`postContentRevisionID`, `a`.`posted`, `a`.`poster`, 
						IFNULL(`b`.`login`, `c`.`userName`) AS `posterName` 
					FROM `postContentRevisions` `a` 
					LEFT JOIN `adminUsers` `b` ON (`a`.`poster` = 'ADMIN' AND `a`.`posterID` = `b`.`adminUserID`) 
					LEFT JOIN `users` `c` ON (`a`.`poster` = 'USER' AND `a`.`posterID` = `c`.`userID`) 
					WHERE `a`.`postID` = '".$postID."' 
					ORDER BY `a`.`posted` DESC";
			$result = query($sql);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$row['poster'] = strtolower($row['poster']);
					$revisions[] = $row;
				}
			}
			return $revisions;
		} // function getRevisionHistory
	} // class postsController

?>

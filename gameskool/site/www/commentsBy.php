<?

	if ($userName = getRequest('user', 'name')) {
		$userType = 'user';
	} elseif ($userName = getRequest('admin', 'name')) {
		$userType = 'admin';
	} elseif ($userName = getRequest('anonymous', 'alpha')) {
		$userType = 'anonymous';
	} else {
		$userType = false;
	}
	// track landing, if no additional reference, set to indicate user search landing
	if (!getRequest('eref')) {
		if ($userType) {
			$_REQUEST['eref'] = 'comments by '.$userType.' '.$userName;
		}
	}
	tracker::trackLanding();

	// pagination
	$start = getRequest('from');
	$limit = 200;
	if ($start < 1) {
		$start = 0;
	} else {
		$start = $start - 1;
	}
	$next = $start + $limit + 1;
	if ($start > 0) {
		$prev = $start - $limit + 1;
		if ($prev < 1) {
			$prev = 1;
		}
	} else {
		$prev = 0;
	}

	if ($userType != 'anonymous') {
		if ($userType == 'user') {
			$userTable = 'users';
			$idField = 'userID';
			$nameField = 'userName';
			$userID = usersController::getUserByName($userName);
		} else {
			$userTable = 'adminUsers';
			$idField = 'adminUserID';
			$nameField = 'name';
			$userID = adminUsersController::getUserByName($userName);
		}
		if ($userID === false) {
			$userID = 0;
		}
		$countSegmentA = "SELECT COUNT(*) 
							FROM `postComments";
		$countSegmentB = "` `a` 
							WHERE `a`.`poster` = '".strtoupper($userType)."' 
							AND `a`.`posterID` = '".$userID."'";
		$querySegmentA = "SELECT `a`.*, `b`.`".$nameField."` AS `posterName`, `c`.`postTitle`, `c`.`postTitleURL`, `c`.`type` AS `postType`, `d`.`gameTitle`, `d`.`gameTitleURL` 
							FROM `postComments";
		$querySegmentB = "` `a` 
							JOIN `".$userTable."` `b` ON (`a`.`posterID` = `b`.`".$idField."`) 
							JOIN `posts` `c` USING (`postID`) 
							JOIN `gameTitles` `d` USING (`gameTitleID`) 
							WHERE `a`.`poster` = '".strtoupper($userType)."' 
							AND `a`.`posterID` = '".$userID."'";
	} else {
		$countSegmentA = "SELECT COUNT(*) 
							FROM `postComments";
		$countSegmentB = "` `a` 
							WHERE `a`.`poster` = '".strtoupper($userType)."' 
							AND `a`.`posterID` = 0";
		$querySegmentA = "SELECT `a`.*, 'anonymous' AS `posterName`, `b`.`postTitle`, `b`.`postTitleURL`, `b`.`type` AS `postType`, `c`.`gameTitle`, `c`.`gameTitleURL` 
							FROM `postComments";
		$querySegmentB = "` `a` 
							JOIN `posts` `b` USING (`postID`) 
							JOIN `gameTitles` `c` USING (`gameTitleID`) 
							WHERE `a`.`poster` = '".strtoupper($userType)."' 
							AND `a`.`posterID` = 0";
	}
	$queries = array();
	for ($i = 0; $i < 10; $i++) {
		$countQueries[] = $countSegmentA.$i.$countSegmentB;
		$queries[] = $querySegmentA.$i.$querySegmentB;
	}
	$countsql = "SELECT (".implode(") + (", $countQueries).") AS `totalComments`";
	$sql = implode(" UNION ", $queries)." ORDER BY `posted` DESC LIMIT ".$start.", ".$limit;

	$totalComments = 0;
	$comments = array();
	$commentIDs = array();
	$result = query($countsql);
	if ($result->rowCount > 0) {
		$row = $result->fetchRow();
		$totalComments = $row['totalComments'];
		if ($totalComments > 0) {
			$result = query($sql);
			if ($result->rowCount > 0) {
				// place comments into array, use comment id as key
				while ($row = $result->fetchRow()) {
					$row['postCommentKey'] = $row['postID'].'_'.$row['postCommentID'];
					$row['poster'] = strtolower($row['poster']);
					$row['timeSubmitted'] = time() - strtotime($row['posted']);
					$row['timePosted'] = time() - strtotime($row['posted']);
					if ($row['timePosted'] < 60) {
						$period = 'second';
					} elseif ($row['timePosted'] < 3600) {
						$row['timePosted'] = round($row['timePosted'] / 60);
						$period = 'minute';
					} elseif ($row['timePosted'] < 86400) {
						$row['timePosted'] = round($row['timePosted'] / 3600);
						$period = 'hour';
					} else {
						$row['timePosted'] = round($row['timePosted'] / 86400);
						$period = 'day';
					}
					if ($row['timePosted'] > 1) {
						$row['timePosted'] = $row['timePosted'].' '.$period.'s';
					} else {
						$row['timePosted'] = $row['timePosted'].' '.$period;
					}
					$comments[] = $row;
					$commentIDs[] = $row['postCommentID'];
				}
			}
		}
	}

	$topUser = array(
		'userType' => false,
		'userID' => false
	);
	$user = userCore::getUser();
	$adminUser = adminCore::getAdminUser();
	$loggedin = userCore::isLoggedIn();
	$adminLoggedin = adminCore::isLoggedIn();
	if ($adminLoggedin) {
		$topUser['userType'] = 'admin';
		$topUser['userID'] = $adminUser['adminUserID'];
	} elseif ($loggedin) {
		$topUser['userType'] = 'user';
		$topUser['userID'] = $user['userID'];
	}

	$template = new template;
	$template->addMeta('<meta name="description" content="'.systemSettings::get('METADESCRIPTION').'" />', 'description');
	$template->addMeta('<meta name="keywords" content="comments, '.systemSettings::get('METAKEYWORDS').'" />', 'keywords');
	$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
	$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
	$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/postComments.js');
	$template->assignClean('_TITLE', systemSettings::get('SITENAME').' comments by '.($userType != 'anonymous' ? $userName : 'anonymous'));
	$template->assign('comments', $comments);
	$template->assignClean('totalComments', $totalComments);
	$template->assignClean('prev', $prev);
	$template->assignClean('next', $next);
	$template->assignClean('userType', $userType);
	$template->assignClean('userName', $userName);
	$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
	$template->assignClean('loggedin', userCore::isLoggedIn());
	$template->assignClean('topUser', $topUser);
	$template->assignClean('user', $user);
	$template->assignClean('commentVotes', usersController::getCommentVotes(false, false, $commentIDs));
	$template->assignClean('location', 'commentsBy');
	$template->setSystemDataGateway();
	$template->getSystemMessages();
	$template->display('site/commentsBy.htm');

?>

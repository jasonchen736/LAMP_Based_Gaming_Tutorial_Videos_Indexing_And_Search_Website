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
			$_REQUEST['eref'] = $userType.' '.$userName;
		}
	}
	tracker::trackLanding();

	$userQuery = '';
	$poster = false;
	$posterType = false;
	$userTypes = postsController::$userTypes;
	if (isset($userTypes[$userType])) {
		$posterType = $userTypes[$userType];
		if ($posterType == 2) {
			$poster = usersController::getUserByName($userName);
			$userQuery = 'user/'.$userName;
			$searchText = 'search by '.$userName;
		} elseif ($posterType == 1) {
			$poster = adminUsersController::getUserByLogin($userName);
			$userQuery = 'admin/'.$userName;
			$searchText = 'search by '.$userName;
		} else {
			$poster = 0;
			$userQuery = 'anonymous/user';
			$searchText = 'search by anonymous';
		}
	} else {
		$searchText = 'search user posts';
	}
	if (strlen($searchText) > 25) {
		$searchText = substr($searchText, 0, 25).'...';
	}

	// pagination
	if (!$from = getRequest('from', 'integer')) {
		$from = 0;
	}
	// search
	$posts = array();
	$searchQuery = getRequest('search');
	$search = new postSearch;
	$search->postType = getRequest('type');
	if ($poster !== false && $posterType !== false) {
		if ($searchQuery) {
			$search->sortMode = 'relevance';
		} elseif (is_null($searchQuery)) {
			$search->sortMode = 'posted';
		}
		$search->SetFilter('posterID', array($poster));
		$search->SetFilter('poster', array($posterType));
		$search->currentPage = $from;
		$posts = $search->query($searchQuery);
		
	}
	$limit = $search->pageLimit;

	$userInfo = userCore::getUser();
	$template = new template;
	$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
	$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
	$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
	$template->assignClean('_TITLE', systemSettings::get('SITENAME').' posts by '.($userType != 'anonymous' ? $userName : 'anonymous'));
	$template->assignClean('userName', $userName);
	$template->assignClean('userType', $userType);
	$template->assignClean('searchURL', '/by/'.$userQuery);
	$template->assignClean('searchQuery', searchClient::getSearchQuery());
	$template->assignClean('searchText', $searchQuery ? $searchQuery : $searchText);
	$template->assignClean('pageURL', '/by/'.$userQuery);
	$template->assignClean('from', $from);
	$template->assignClean('prev', $from - $limit);
	$template->assignClean('next', $from + $limit);
	$template->assignClean('postsFound', isset($search) ? $search->totalFound : 0);
	$template->assignClean('posts', $posts);
	$template->assignClean('postVotes', usersController::getPostVotes(false, array_keys($posts)));
	$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
	$template->assignClean('loggedin', userCore::isLoggedIn());
	$template->assignClean('user', $userInfo);
	$template->assignClean('location', $userInfo['userName'] == $userName ? 'submittedby' : '');
	$template->setSystemDataGateway();
	$template->getSystemMessages();
	$template->display('site/postSearch.htm');

?>

<?

	userCore::checkAccess();

	$actions = array(
		'home'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		home();
	}

	/**
	 *  Display the user homepage
	 *  Args: none
	 *  Return: none
	 */
	function home() {
		$userInfo = userCore::getUser();
		$subscriptions = usersController::getGameSubscriptions($userInfo['userID']);
		if (empty($subscriptions)) {
			redirect('/user/mygames/');
		}
		$gameTitles = array_keys($subscriptions);
		// pagination
		if (!$from = getRequest('from', 'integer')) {
			$from = 0;
		}
		// search
		$posts = array();
		$searchQuery = getRequest('search');
		$search = new postSearch;
		$search->SetFilter('gameTitleID', $gameTitles);
		$search->postType = getRequest('type');
		$search->currentPage = $from;
		$posts = $search->query($searchQuery);
		$limit = $search->pageLimit;
		$template = new template;
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' My Games');
		$template->assignClean('searchURL', '/user/mygames');
		$template->assignClean('searchQuery', searchClient::getSearchQuery());
		$template->assignClean('searchText', $searchQuery ? $searchQuery : 'search my games');
		$template->assignClean('pageURL', '/user/mygames');
		$template->assignClean('from', $from);
		$template->assignClean('prev', $from - $limit);
		$template->assignClean('next', $from + $limit);
		$template->assignClean('postsFound', isset($search) ? $search->totalFound : 0);
		$template->assignClean('posts', $posts);
		$template->assignClean('postVotes', usersController::getPostVotes(false, array_keys($posts)));
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight', 25));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->assignClean('user', $userInfo);
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('site/postSearch.htm');
	} // function home

?>

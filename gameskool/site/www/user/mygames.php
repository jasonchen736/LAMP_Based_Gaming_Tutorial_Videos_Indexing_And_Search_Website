<?

	$action = getRequest('action');
	if ($action && $action != 'post') {
		userCore::checkAccess();
	}

	$actions = array(
		'posts',
		'manage',
		'search',
		'add',
		'remove'
	);

	if (in_array($action, $actions)) {
		$action();
	} else {
		posts();
	}

	/**
	 *  List subscription posts
	 *  Args: none
	 *  Return: none
	 */
	function posts() {
		$userInfo = userCore::getUser();
		$loggedin = userCore::isLoggedIn();
		$subscriptions = array();
		if ($loggedin) {
			$subscriptions = usersController::getGameSubscriptions($userInfo['userID']);
		} else {
			$cookie = getCookie(systemSettings::get('COOKIEPREFIX').'gsubs');
			$cookie = explode(',', $cookie);
			foreach ($cookie as $gameTitleID) {
				if (ctype_digit($gameTitleID)) {
					$subscriptions[$gameTitleID] = $gameTitleID;
				}
			}
		}
		if (empty($subscriptions)) {
			if ($loggedin) {
				manage();
				exit;
			} else {
				addMessage('hey, you need to sign up or log in to customize your game subscriptions');
				redirect('/user');
			}
		} else {
			if (!$loggedin) {
				addMessage('hey, you need to sign up or log in to customize your game subscriptions');
			}
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
		$template->assign('ads', adsController::getAds('right column'));
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', $loggedin);
		$template->assignClean('user', $userInfo);
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('site/postSearch.htm');
	} // function posts

	/**
	 *  Show titles subscribed to and game titles sorted by hottest
	 *  Args: none
	 *  Return: none
	 */
	function manage() {
		// pagination
		if (!$from = getRequest('from', 'integer')) {
			$from = 0;
		}
		$limit = 25;
		// retrieve game titles
		$gameTitles = gameTitlesController::getTitles('weight', $limit, $from);
		$userInfo = userCore::getUser();
		$subscriptions = usersController::getGameSubscriptions($userInfo['userID']);
		$result = query("SELECT COUNT(*) AS `totalGames` FROM `gameTitles`");
		$row = $result->fetchRow();
		$totalGames = $row['totalGames'];
		$template = new template;
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addStyle('/js/jquery/plugins/autocomplete/auto.complete.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/jquery/plugins/autocomplete/auto.complete.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' My Games');
		$template->assignClean('user', $userInfo);
		$template->assignClean('searchURL', '/user/mygames/action/search');
		$template->assignClean('searchQuery', searchClient::getSearchQuery());
		$template->assignClean('pageURL', '/user/mygames/action/search');
		$template->assignClean('from', $from);
		$template->assignClean('prev', $from - $limit);
		$template->assignClean('next', $from + $limit);
		$template->assignClean('totalGames', $totalGames);
		$template->assignClean('subscriptions', $subscriptions);
		$template->assignClean('gameTitles', $gameTitles);
		$template->assign('ads', adsController::getAds('right column'));
		$template->assignClean('location', 'mygames');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/subscriptionsManagement.htm');
	} // function manage

	/**
	 *  Search game titles
	 *  Args: none
	 *  Return: none
	 */
	function search() {
		// pagination
		if (!$from = getRequest('from', 'integer')) {
			$from = 0;
		}
		// search
		$gameTitles = array();
		$searchQuery = getRequest('search');
		if (empty($searchQuery)) {
			manage();
			exit;
		}
		$search = new gameTitleSearch;
		$search->currentPage = $from;
		$gameTitles = $search->query($searchQuery);
		$limit = $search->pageLimit;
		$userInfo = userCore::getUser();
		$subscriptions = usersController::getGameSubscriptions($userInfo['userID']);
		$template = new template;
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addStyle('/js/jquery/plugins/autocomplete/auto.complete.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/jquery/plugins/autocomplete/auto.complete.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' My Games');
		$template->assignClean('user', $userInfo);
		$template->assignClean('searchURL', '/user/mygames/action/search');
		$template->assignClean('searchQuery', searchClient::getSearchQuery());
		$template->assignClean('pageURL', '/user/mygames/action/search');
		$template->assignClean('from', $from);
		$template->assignClean('prev', $from - $limit);
		$template->assignClean('next', $from + $limit);
		$template->assignClean('totalGames', isset($search) ? $search->totalFound : 0);
		$template->assignClean('subscriptions', $subscriptions);
		$template->assignClean('gameTitles', $gameTitles);
		$template->assignClean('location', 'mygames');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/subscriptionsManagement.htm');
	} // function search

	/**
	 *  Subscribe to game title
	 *  Args: none
	 *  Return: none
	 */
	function add() {
		$gameTitleID = getRequest('gameID', 'integer');
		$gameTitle = new gameTitle($gameTitleID);
		if ($gameTitle->exists()) {
			$userInfo = userCore::getUser();
			if (usersController::addSubscription($userInfo['userID'], $gameTitle->get('gameTitleID'))) {
				addSuccess($gameTitle->get('gameTitle').' added');
				usersController::writeSubscriptionCookie($userInfo['userID']);
			} else {
				addError('there was a problem adding your subscription');
			}
		} else {
			addError('that game title was not found');
		}
		manage();
	} // function add

	/**
	 *  Unsubscribe to game title
	 *  Args: none
	 *  Return: none
	 */
	function remove() {
		$gameTitleID = getRequest('gameID', 'integer');
		$gameTitle = new gameTitle($gameTitleID);
		if ($gameTitle->exists()) {
			$userInfo = userCore::getUser();
			if (usersController::removeSubscription($userInfo['userID'], $gameTitle->get('gameTitleID'))) {
				addSuccess($gameTitle->get('gameTitle').' removed');
				usersController::writeSubscriptionCookie($userInfo['userID']);
			} else {
				addError('there was a problem removing your subscription');
			}
		} else {
			addError('that game title was not found');
		}
		manage();
	} // function remove

?>

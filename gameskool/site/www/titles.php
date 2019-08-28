<?

	if (userCore::isLoggedIn() && !isset($_REQUEST['browse'])) {
		redirect('/user/mygames/action/manage');
	}

	$action = getRequest('action');

	$actions = array(
		'display',
		'search'
	);

	if (in_array($action, $actions)) {
		$action();
	} else {
		display();
	}

	/**
	 *  Show titles sorted by hottest
	 *  Args: none
	 *  Return: none
	 */
	function display() {
		if (!$from = getRequest('from', 'integer')) {
			$from = 0;
		}
		if (isset($_REQUEST['browse'])) {
			$sort = 'alpha';
			$seek = getRequest('browse', 'alphanum');
			$limit = 1000;
		} else {
			$sort = 'weight';
			$seek = false;
			$limit = 1000;
		}
		// retrieve game titles
		$gameTitles = gameTitlesController::getTitles($sort, $limit, $from, $seek);
		$totalGames = gameTitlesController::getTitlesCount($sort, $seek);
		$template = new template;
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addStyle('/js/jquery/plugins/autocomplete/auto.complete.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/jquery/plugins/autocomplete/auto.complete.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Game Titles');
		$template->assignClean('searchURL', '/');
		$template->assignClean('searchQuery', searchClient::getSearchQuery());
		$template->assignClean('pageURL', '/titles/action/search');
		$template->assignClean('titleQuery', '');
		$template->assignClean('from', $from);
		$template->assignClean('prev', $from - $limit);
		$template->assignClean('next', $from + $limit);
		$template->assignClean('totalGames', $totalGames);
		$template->assignClean('gameTitles', $gameTitles);
		$template->assignClean('location', 'titles');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->assignClean('user', userCore::getUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('site/titles.htm');
	} // function display

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
			display();
			exit;
		}
		$search = new gameTitleSearch;
		$search->currentPage = $from;
		$gameTitles = $search->query($searchQuery);
		$limit = $search->pageLimit;
		$template = new template;
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addStyle('/js/jquery/plugins/autocomplete/auto.complete.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/jquery/plugins/autocomplete/auto.complete.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Games Titles');
		$template->assignClean('searchURL', '/');
		$template->assignClean('searchQuery', searchClient::getSearchQuery());
		$template->assignClean('pageURL', '/titles/action/search');
		$template->assignClean('titleQuery', $searchQuery);
		$template->assignClean('from', $from);
		$template->assignClean('prev', $from - $limit);
		$template->assignClean('next', $from + $limit);
		$template->assignClean('totalGames', isset($search) ? $search->totalFound : 0);
		$template->assignClean('gameTitles', $gameTitles);
		$template->assignClean('location', 'titles');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->assignClean('user', userCore::getUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('site/titles.htm');
	} // function search

?>

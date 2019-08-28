<?

	$game = getRequest('gameTitle', 'gameTitle');
	// track landing, if no additional reference, set to indicate game title search landing
	if (!getRequest('eref')) {
		$_REQUEST['eref'] = 'game '.$game;
	}
	tracker::trackLanding();

	// pagination
	if (!$from = getRequest('from', 'integer')) {
		$from = 0;
	}
	// search
	$posts = array();
	$gameTitleID = gameTitlesController::getTitleID($game);
	$searchQuery = getRequest('search');
	$search = new postSearch;
	$search->postType = getRequest('type');
	if ($gameTitleID) {
		$gameTitle = new gameTitle($gameTitleID);
		$search->SetFilter('gameTitleID', array($gameTitleID));
		$search->currentPage = $from;
		$posts = $search->query($searchQuery);
		$searchText = 'search '.$gameTitle->get('gameTitle');
		if (strlen($searchText) > 25) {
			$searchText = substr($searchText, 0, 25).'...';
		}
	} else {
		$searchText = 'search game protege';
	}
	$limit = $search->pageLimit;

	$template = new template;
	$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
	$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
	$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
	$template->assignClean('_TITLE', systemSettings::get('SITENAME').' '.($gameTitleID ? ' - '.$gameTitle->get('gameTitle') : ''));
	$template->assignClean('searchURL', '/game/'.$game);
	$template->assignClean('searchQuery', searchClient::getSearchQuery());
	$template->assignClean('searchText', $searchQuery ? $searchQuery : $searchText);
	$template->assignClean('pageURL', '/game/'.$game);
	$template->assignClean('from', $from);
	$template->assignClean('prev', $from - $limit);
	$template->assignClean('next', $from + $limit);
	$template->assignClean('postsFound', isset($search) ? $search->totalFound : 0);
	$template->assignClean('posts', $posts);
	$template->assignClean('postVotes', usersController::getPostVotes(false, array_keys($posts)));
	$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
	$template->assign('ads', adsController::getAds('right column'));
	$template->assignClean('loggedin', userCore::isLoggedIn());
	$template->assignClean('user', userCore::getUser());
	$template->setSystemDataGateway();
	$template->getSystemMessages();
	$template->display('site/postSearch.htm');

?>

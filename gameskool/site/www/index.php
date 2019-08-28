<?

	// track landing
	tracker::trackLanding();

	// pagination
	if (!$from = getRequest('from', 'integer')) {
		$from = 0;
	}
	// search	
	$posts = array();
	$searchQuery = getRequest('search');
	$search = new postSearch;
	$search->postType = getRequest('type');
	$search->currentPage = $from;
	$posts = $search->query($searchQuery);
	$limit = $search->pageLimit;

	$template = new template;
	$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
	$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
	$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
	$template->assignClean('_TITLE', systemSettings::get('SITENAME'));
	$template->assignClean('searchURL', '/');
	$template->assignClean('searchQuery', searchClient::getSearchQuery());
	$template->assignClean('searchText', $searchQuery ? $searchQuery : 'search game protege');
	$template->assignClean('pageURL', '/home');
	$template->assignClean('from', $from);
	$template->assignClean('prev', $from - $limit);
	$template->assignClean('next', $from + $limit);
	$template->assignClean('postsFound', $search->totalFound);
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

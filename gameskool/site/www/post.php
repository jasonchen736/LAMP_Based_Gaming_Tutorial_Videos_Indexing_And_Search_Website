<?

	$game = getRequest('gameTitle', 'gameTitle');
	$title = getRequest('postTitle', 'postTitle');
	// track landing, if no additional reference, set to indicate post page landing
	if (!getRequest('eref')) {
		$_REQUEST['eref'] = 'game '.$game.', post '.$title;
	}
	tracker::trackLanding();

	$embedSource = '';
	$content = '';
	$post = postsController::retrievePostData($game, $title);
	if (!empty($post)) {
		if ($post['status'] != 'active') {
			if (adminCore::isLoggedIn()) {
				$post['status'] = 'active';
			} else {
				$post = false;
			}
		}
		if (!empty($post)) {
			switch ($post['type']) {
				case 'link':
					redirect('/comments/'.$post['gameTitleURL'].'/'.$post['postTitleURL']);
					break;
				case 'video':
					$embedSource = postsController::getEmbedSource($post['source'], $post['identifier']);
					$content = $post['content'];
					unset($post['content']);
					break;
				default:
					$content = $post['content'];
					unset($post['content']);
					break;
			}
		}
	}

	// handle messages separately to fit layout
	$postPageErrorMessages = getErrors();
	$postPageSuccessMessages = getSuccess();
	$postPageGeneralMessages = getMessages();
	$havePostPageMessages = haveMessages() || haveSuccess() || haveErrors();
	clearAllMessages();

	$template = new template;
	$template->addMeta('<meta name="description" content="'.$post['description'].' | '.systemSettings::get('METADESCRIPTION').'" />', 'description');
	$template->addMeta('<meta name="keywords" content="'.$post['gameTitle'].', '.systemSettings::get('METAKEYWORDS').'" />', 'keywords');
	$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
	$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
	$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/postPage.js');
	$template->assignClean('_TITLE', systemSettings::get('SITENAME').' '.$post['gameTitle'].' - '.$post['postTitle']);
	$template->assignClean('searchURL', '/');
	$template->assignClean('searchText', 'search game protege');
	$template->assignClean('post', $post);
	$template->assign('embedSource', $embedSource);
	$template->assign('content', $content);
	if ($post['type'] == 'blog' || $post['type'] == 'wiki') {
		$template->assign('ads', adsController::getAds('right column'));
	}
	$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
	$template->assignClean('loggedin', userCore::isLoggedIn());
	$template->assignClean('user', userCore::getUser());
	$template->assignClean('havePostPageMessages', $havePostPageMessages);
	$template->assignClean('postPageErrors', $postPageErrorMessages);
	$template->assignClean('postPageSuccesses', $postPageSuccessMessages);
	$template->assignClean('postPageMessages', $postPageGeneralMessages);
	$template->assignClean('postVotes', usersController::getPostVotes(false, array($post['postID'])));
	$template->assignClean('location', 'post');
	$template->setSystemDataGateway();
	$template->getSystemMessages();
	$template->display('site/postPage.htm');

?>

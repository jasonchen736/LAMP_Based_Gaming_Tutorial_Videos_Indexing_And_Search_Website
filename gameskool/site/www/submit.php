<?

	// track landing
	tracker::trackLanding();

	$actions = array(
		'submitForm',
		'preview',
		'save'
	);

	$action = getPost('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		submitForm();
	}

	/**
	 *  Display the submit form
	 *  Args: (post) post object, (str) game title
	 *  Return: none
	 */
	function submitForm($post = false, $gameTitle = false) {
		if (empty($post)) {
			$post = new post;
			// check if editing a post submit
			if ($postData = getSession('postData')) {
				$post->loadRecord($postData);
				unset($_SESSION['postData']);
				$gameTitle = new gameTitle($post->get('gameTitleID'));
				$gameTitle = $gameTitle->get('gameTitle');
			} else {
				$post->set('type', getRequest('type'));
			}
		}
		$postTypes = postsController::$postTypes;
		if (!isset($postTypes[$post->get('type')])) {
			$post->set('type', 'video');
		}
		$template = new template;
		$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addStyle('/js/jquery/plugins/autocomplete/auto.complete.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/tiny_mce/tiny_mce.js');
		$template->addScript('/js/jquery/plugins/autocomplete/auto.complete.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Submit a new post');
		$template->assignClean('postTypes', $postTypes);
		$template->assignClean('sources', postsController::$supportedSources);
		$template->assignClean('post', $post->fetchArray());
		$template->assign('content', $post->get('content'));
		$template->assignClean('gameTitle', $gameTitle);
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->assignClean('user', userCore::getUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('site/submit.htm');
	} // function submitForm

	/**
	 *  Preview the post submit
	 *  Args: none
	 *  Return: none
	 */
	function preview() {
		$post = new post;
		$post->set('type', getPost('type'));
		$post->setTypeRequirements();
		$post->set('postTitle', preg_replace('/\s+/', ' ', getPost('postTitle')));
		$post->set('postTitleURL',  friendlyURL($post->get('postTitle')));
		$post->set('description', getPost('description'));;
		$post->set('url', getPost('url'));
		$post->set('source', getPost('source'));
		$post->set('identifier', postsController::isolateVideoID($post->get('source'), getPost('identifier')));
		if ($post->get('type') == 'video') {
			$post->set('image', postsController::getThumbnailLocation($post->get('source'), $post->get('identifier')));
		}
		$post->set('content', getPost('content'));
		$post->set('status', 'active');
		$game = preg_replace('/\s+/', ' ', getPost('gameTitleID', 'gameTitle'));
		$gameURL = friendlyURL($game);
		$postTypes = postsController::$postTypes;
		$validPostType = isset($postTypes[$post->get('type')]);
		$validCaptcha = captcha::validateCaptcha(getPost('captcha'));
		// verify post type and captcha
		if ($validPostType && (isset($_SESSION['postData']) || $validCaptcha)) {
			// retrieve or create new game title
			$gameTitleID = gameTitlesController::getTitleID($gameURL);
			if (!$gameTitleID) {
				if (!empty($game)) {
					$gameTitle = new gameTitle;
					$gameTitle->set('gameTitle', $game);
					$gameTitle->set('gameTitleURL', $gameURL);
					$gameTitle->set('gameTitleKey', strtolower($game));
					if ($gameTitle->save()) {
						$gameTitleID = $gameTitle->get('gameTitleID');
						$game = $gameTitle->get('gameTitle');
					} else {
						addError('there was a problem saving your post, please try again later');
					}
				} else {
					addError('game title is missing');
					addErrorField('gameTitleID');
				}
			}
			if ($gameTitleID) {
				$post->set('gameTitleID', $gameTitleID);
				if ($post->assertValidData() && !$post->isDuplicate()) {
					$data = $post->fetchArray();
					$_SESSION['postData'] = $data;
					$content = $data['content'];
					unset($data['content']);
					if ($data['type'] == 'video') {
						$embedSource = postsController::getEmbedSource($data['source'], $data['identifier']);
					} else {
						$embedSource = '';
					}
					$data['gameTitle'] = $game;
					$data['gameTitleURL'] = $gameURL;
					$template = new template;
					$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
					$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
					$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
					$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
					$template->addScript('/js/tiny_mce/tiny_mce.js');
					$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Submit a new post');
					$template->assignClean('post', $data);
					$template->assign('embedSource', $embedSource);
					$template->assign('content', $content);
					$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
					$template->assignClean('loggedin', userCore::isLoggedIn());
					$template->assignClean('user', userCore::getUser());
					$template->setSystemDataGateway();
					$template->getSystemMessages();
					$template->display('site/submitPreview.htm');
					exit;
				} else {
					$post->updateSystemMessages();
					if (!haveErrors()) {
						addError('there was a problem saving your post, please try again later');
					}
				}
			}
		}
		if (!$validPostType) {
			addError('hey, just what are you trying to submit?');
		}
		if (!$validCaptcha) {
			addError('hey, that image text ain\'t right');
			addErrorField('captcha');
		}
		if (!empty($game)) {
			$post->set('gameTitleID', 0);
		}
		$post->assertValidData();
		$post->updateSystemMessages();
		submitForm($post, $game);
	} // function preview

	/**
	 *  Save the post
	 *  Args: none
	 *  Return: none
	 */
	function save() {
		$post = new post;
		if ($postData = getSession('postData')) {
			$post->loadRecord($postData);
			$post->setTypeRequirements();
			$post->assertTypeValues();
			$gameTitle = new gameTitle($post->get('gameTitleID'));
			if (!adminCore::isLoggedIn() && !userCore::isLoggedIn()) {
				$post->setRecordEditor('ANONYMOUS', 0);
			}
			if ($post->save()) {
				unset($_SESSION['postData']);
				addSuccess('thanks for submitting, you rock');
				redirect('/post/'.$gameTitle->get('gameTitleURL').'/'.$post->get('postTitleURL'));
			} else {
				$post->updateSystemMessages();
				if (!haveErrors()) {
					addError('there was a problem saving your post');
				}
			}
		} else {
			addError('there was a problem saving your post');
		}
		redirect('/submit');
	} // function save

?>

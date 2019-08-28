<?

	userCore::checkAccess();

	$actions = array(
		'edit',
		'update',
		'revisions',
		'compare',
		'revert'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		edit();
	}

	/**
	 *  Edit post strategy wiki
	 *  Args: (array) post data
	 *  Return: none
	 */
	function edit($postData = false) {
		if (!$postData) {
			if (!$postData = retrievePostData()) {
				addError('this post doesn\'t exist, what\'s going on here?');
				redirect('/user');
			}
		}
		if (isEditable($postData)) {
			$content = $postData['content'];
			unset($postData['content']);
			$wikiTypes = postsController::$wikiTypes;
			$template = new template;
			$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
			$template->addStyle('/js/jquery/plugins/autocomplete/auto.complete.css');
			$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
			$template->addScript('/js/tiny_mce/tiny_mce.js');
			$template->addScript('/js/jquery/plugins/autocomplete/auto.complete.js');
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' User Posts');
			$template->assignClean('post', $postData);
			$template->assignClean('isWiki', isset($wikiTypes[$postData['type']]));
			$template->assign('content', $content);
			$template->assignClean('user', userCore::getUser());
			$template->assignClean('location', 'edit');
			$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
			$template->assignClean('loggedin', userCore::isLoggedIn());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('user/editPostContent.htm');
		} else {
			addError('you don\'t have the authoritah to edit that post');
			redirect('/user');
		}
	} // function edit

	/**
	 *  Update strategy
	 *  Args: none
	 *  Return: none
	 */
	function update() {
		if (!$postData = retrievePostData()) {
			addError('this post doesn\'t exist, what\'s going on here?');
			redirect('/user');
		}
		if (isEditable($postData)) {
			$userInfo = userCore::getUser();
			$user = new user($userInfo['userID']);
			$post = new post($postData['postID']);
			$post->set('content', getPost('content'));
			$postData['content'] = $post->get('content');
			if ($postData['poster'] == 'user' && $postData['posterID'] == $userInfo['userID']) {
				$post->set('postTitle', preg_replace('/\s+/', ' ', getPost('postTitle')));
				$post->set('postTitleURL', friendlyURL($post->get('postTitle')));
				$postData['postTitle'] = $post->get('postTitle');
				$postData['postTitleURL'] = $post->get('postTitleURL');
				$post->set('description', getPost('description'));
				$postData['description'] = $post->get('description');
				$game = preg_replace('/\s+/', ' ', getPost('gameTitleID', 'gameTitle'));
				$gameURL = friendlyURL($game);
				$postData['gameTitle'] = $game;
				$postData['gameTitleURL'] = $gameURL;
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
						} else {
							addError('there was a problem updating this post, please try again later');
						}
						$postData['gameTitle'] = $gameTitle->get('gameTitle');
						$postData['gameTitleURL'] = $gameTitle->get('gameTitleURL');
					} else {
						addError('game title is missing');
						addErrorField('gameTitleID');
					}
				} else {
					$gameTitle = new gameTitle($gameTitleID);
				}
			} else {
				$gameTitleID = $post->get('gameTitleID');
				$gameTitle = new gameTitle($gameTitleID);
			}
			$post->setTypeRequirements();
			if ($gameTitleID) {
				$post->set('gameTitleID', $gameTitleID);
				if ($post->assertValidData() && !$post->isDuplicate()) {
					$post->assertTypeValues();
					if ($post->update()) {
						addSuccess('post updated');
						redirect('/user/edit/game/'.$gameTitle->get('gameTitleURL').'/post/'.$post->get('postTitleURL'));
					}
				}
				$post->updateSystemMessages();
			} elseif (!$post->assertValidData() || $post->isDuplicate()) {
				$post->updateSystemMessages();
			}
			if (!haveErrors()) {
				addError('there was a problem updating this post, please try again later');
			}
			edit($postData);
		} else {
			addError('you don\'t have the authoritah to edit that post');
			redirect('/user');
		}
	} // function update

	/**
	 *  Display content revision history for post
	 *  Args: none
	 *  Return: none
	 */
	function revisions() {
		$postID = getRequest('postID', 'integer');
		$post = new post($postID);
		if ($post->isWiki()) {
			$revisionRecords = postsController::getRevisionHistory($postID);
			$gameTitle = new gameTitle($post->get('gameTitleID'));
			$postData = retrievePostData($gameTitle->get('gameTitleURL'), $post->get('postTitleURL'));
			$template = new template;
			$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
			$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Revision History');
			$template->assignClean('post', $postData);
			$template->assignClean('revisionRecords', $revisionRecords);
			$template->assignClean('user', userCore::getUser());
			$template->assignClean('location', 'edit');
			$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
			$template->assignClean('loggedin', userCore::isLoggedIn());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('user/postRevisionHistory.htm');
		} else {
			addError('that post does not have a revision history');
			redirect('/user');
		}
	} // function revisions

	/**
	 *  Compare two different versions of post content
	 *  Args: none
	 *  Return: none
	 */
	function compare() {
		require_once 'Text/Diff.php';
		require_once 'Text/Diff/Renderer/inline.php';
		$postID = getRequest('postID', 'integer');
		$post = new post($postID);
		if ($post->isWiki()) {
			$title = $post->get('postTitleURL');
			$gameTitle = new gameTitle($post->get('gameTitleID'));
			$game = $gameTitle->get('gameTitleURL');
			$r1 = getRequest('r1', 'integer');
			$r2 = getRequest('r2', 'integer');
			if ($r1 && $r2) {
				$revision1 = new postContentRevision($r1);
				$revision2 = new postContentRevision($r2);
				if ($revision1->exists() && $revision2->exists()) {
					if ($revision1->get('postID') == $postID && $revision2->get('postID') == $postID) {
						$content1 = explode("\n", $revision1->get('content'));
						$content2 = explode("\n", $revision2->get('content'));
						$engine = &new Text_Diff($content1, $content2);
						$renderer = &new Text_Diff_Renderer_inline();
						$diff = $renderer->render($engine);
						$template = new template;
						$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
						$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
						$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Revision History');
						$template->assign('post',  postsController::retrievePostData($game, $title));
						$template->assign('diff', $diff);
						$template->assignClean('user', userCore::getUser());
						$template->assignClean('location', 'edit');
						$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
						$template->assignClean('loggedin', userCore::isLoggedIn());
						$template->setSystemDataGateway();
						$template->getSystemMessages();
						$template->display('user/postRevisionDiff.htm');
						exit;
					}
				}
			}
			addError('that is an invalid revision number');
		} else {
			addError('that post does not have a revision history');
		}
		redirect('/user');
	} // function compare

	/**
	 *  Revert post content version
	 *  Args: none
	 *  Return: none
	 */
	function revert() {
		$postID = getRequest('postID', 'integer');
		$post = new post($postID);
		if ($post->isWiki()) {
			$version = getRequest('version', 'integer');
			$revision = new postContentRevision($version);
			if ($revision->get('postID') == $postID) {
				$post->set('content', $revision->get('content'));
				if ($post->update()) {
					$gameTitle = new gameTitle($post->get('gameTitleID'));
					addSuccess('post updated');
					redirect('/user/edit/game/'.$gameTitle->get('gameTitleURL').'/post/'.$post->get('postTitleURL'));
				} else {
					addError('there was a problem reverting the post content, please try again later');
					revisions();
				}
			} else {
				addError('invalid revision number');
			}
		} else {
			addError('that post does not have a revision history');
		}
		redirect('/user');
	} // function revert

	/**
	 *  Retrieve post data from request
	 *  Args: (str) game name, (str) post name
	 *  Return: (mixed) array or false
	 */
	function retrievePostData($game = false, $title = false) {
		if ($game === false) {
			$game = getRequest('game', 'gameTitle');
			$title = getRequest('post', 'postTitle');
		}
		return postsController::retrievePostData($game, $title);
	} // function retrievePostData

	/**
	 *  Check if post is editable, either a wiki or by original poster
	 *  Args: (array) post data
	 *  Return: (boolean) editable
	 */
	function isEditable($postData) {
		$wikiTypes = postsController::$wikiTypes;
		$userInfo = userCore::getUser();
		return isset($wikiTypes[$postData['type']]) || ($postData['poster'] == 'user' && $postData['posterID'] == $userInfo['userID']);
	} // function isEditable

?>

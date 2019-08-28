<?

	adminCore::checkAccess('POST');

	$actions = array(
		'postsAdmin',
		'addPost',
		'savePost',
		'editPost',
		'updatePost',
		'disable'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		postsAdmin();
	}

	/**
	 *  Show the post admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function postsAdmin() {
		ini_set('memory_limit', '300M');
		if ($action = getRequest('takeAction')) {
			$selected = getPost('selectPost');
			if (!empty($selected)) {
				switch ($action) {
					case 'Activate Selected':
						$updated = 0;
						foreach ($selected as $postID) {
							$post = new post($postID);
							if ($post->get('status') == 'disabled') {
								$post->set('status', 'active');
								if ($post->update()) {
									++$updated;
								}
							}
						}
						addSuccess($updated.' post(s) activated successfully');
						break;
					case 'Disable Selected':
						$updated = 0;
						foreach ($selected as $postID) {
							$post = new post($postID);
							if ($post->get('status') == 'active') {
								$post->set('status', 'disabled');
								if ($post->update()) {
									++$updated;
								}
							}
						}
						addSuccess($updated.' post(s) disabled successfully');
						break;
					default:
						break;
				}
			}
		}
		$controller = new postsController;
		if (getRequest('runSearch')) {
			$records = $controller->performSearch();
			$recordsFound = $controller->countRecordsFound();
		} else {
			$records = array();
			$recordsFound = 0;
		}
		$userTypes = postsController::$userTypes;
		$userTypeOptions = array(
			'' => ''
		);
		foreach ($userTypes as $userType => $typeValue) {
			$userTypeOptions[strtoupper($userType)] = ucfirst($userType);
		}
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
		$template->assignClean('postTypes', postsController::getPostTypes());
		$template->assignClean('sources', postsController::$supportedSources);
		$template->assignClean('userTypes', $userTypeOptions);
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/postsAdmin.htm');	
	} // function postsAdmin

	/**
	 *  Add post section
	 *  Args: none
	 *  Return: none
	 */
	function addPost() {
		$post = new post;
		$postData = $post->fetchArray();
		$postData['gameTitle'] = '';
		$postData['gameTitleURL'] = '';
		$controller = new postsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
		$template->assignClean('post', $postData);
		$template->assignClean('postTypes', postsController::getPostTypes());
		$template->assignClean('sources', postsController::$supportedSources);
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/postEdit.htm');
	} // function addPost

	/**
	 *  Save a new post record
	 *  Args: none
	 *  Return: none
	 */
	function savePost() {
		$post = new post;
		$post->set('type', getPost('type'));
		$post->setTypeRequirements();
		$post->set('postTitle', preg_replace('/\s+/', ' ', getPost('postTitle')));
		$post->set('postTitleURL',  friendlyURL($post->get('postTitle')));
		$post->set('description', getPost('description'));
		$post->set('url', getPost('url'));
		$post->set('source', getPost('source'));
		$post->set('identifier', postsController::isolateVideoID($post->get('source'), getPost('identifier')));
		$post->set('content', getPost('content'));
		$post->set('status', getPost('status'));
		if ($image = getPost('image')) {
			$post->set('image', $image);
		} elseif ($post->get('type') == 'video') {
			$post->set('image', postsController::getThumbnailLocation($post->get('source'), $post->get('identifier')));
		}
		$game = preg_replace('/\s+/', ' ', getPost('gameTitleID', 'gameTitle'));
		$gameURL = friendlyURL($game);
		$gameTitleID = gameTitlesController::getTitleID($gameURL);
		if (!$gameTitleID && !empty($game)) {
			$gameTitle = new gameTitle;
			$gameTitle->set('gameTitle', $game);
			$gameTitle->set('gameTitleURL', $gameURL);
			$gameTitle->set('gameTitleKey', strtolower($game));
			if ($gameTitle->save()) {
				$gameTitleID = $gameTitle->get('gameTitleID');
				$game = $gameTitle->get('gameTitle');
				
			} else {
				addError('An error occurred while saving the game title');
				$gameTitle->updateSystemMessages();
			}
		}
		if ($gameTitleID) {
			$post->set('gameTitleID', $gameTitleID);
		}
		if ($post->assertValidData() && !$post->isDuplicate()) {
			$post->assertTypeValues();
			if ($post->save()) {
				addSuccess('Post saved successfully');
				if (getRequest('submit') == 'Add and Edit') {
					editPost($post->get('postID'));
				} else {
					addPost();
				}
				exit;
			}
		}
		addError('An error occurred while saving the post');
		$post->updateSystemMessages();
		$postData = $post->fetchArray();
		$postData['gameTitle'] = $game;
		$postData['gameTitleURL'] = $gameURL;
		$controller = new postsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
		$template->assignClean('postTypes', postsController::getPostTypes());
		$template->assignClean('post', $postData);
		$template->assignClean('sources', postsController::$supportedSources);
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/postEdit.htm');
	} // function savePost

	/**
	 *  Edit post section
	 *  Args: (int) post id
	 *  Return: none
	 */
	function editPost($postID = false) {
		if (!$postID) {
			$postID = getRequest('postID', 'integer');
		}
		$post = new post($postID);
		$gameTitle = new gameTitle($post->get('gameTitleID'));
		if ($post->exists()) {
			switch ($post->get('poster')) {
				case 'USER':
					$user = new user($post->get('posterID'));
					$userName = $user->get('userName');
					break;
				case 'ADMIN':
					$user = new adminUser($post->get('posterID'));
					$userName = $user->get('login');
					break;
				default:
					$userName = 'anonymous';
					break;
			}
			$postData = $post->fetchArray();
			$postData['gameTitle'] = $gameTitle->get('gameTitle');
			$postData['gameTitleURL'] = $gameTitle->get('gameTitleURL');
			$controller = new postsController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
			$template->assignClean('post', $postData);
			$template->assignClean('userName', $userName);
			$template->assignClean('postTypes', postsController::getPostTypes());
			$template->assignClean('sources', postsController::$supportedSources);
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/postEdit.htm');
		} else {
			addError('Post does not exist');
			postsAdmin();
		}
	} // function editPost

	/**
	 *  Update an existing post record
	 *  Args: none
	 *  Return: none
	 */
	function updatePost() {
		$post = new post(getRequest('postID', 'integer'));
		if ($post->exists()) {
			$post->set('type', getPost('type'));
			$post->setTypeRequirements();
			$post->set('postTitle', preg_replace('/\s+/', ' ', getPost('postTitle')));
			$post->set('postTitleURL',  friendlyURL($post->get('postTitle')));
			$post->set('description', getPost('description'));
			$post->set('url', getPost('url'));
			$post->set('source', getPost('source'));
			$post->set('identifier', postsController::isolateVideoID($post->get('source'), getPost('identifier')));
			$post->set('content', getPost('content'));
			$post->set('status', getPost('status'));
			$image = getPost('image');
			if ($image && $image != $post->get('image')) {
				$post->set('image', $image);
			} elseif ($post->isNewValue('source') || $post->isNewValue('identifier')) {
				$post->set('image', postsController::getThumbnailLocation($post->get('source'), $post->get('identifier')));
			}
			$game = preg_replace('/\s+/', ' ', getPost('gameTitleID', 'gameTitle'));
			$gameURL = friendlyURL($game);
			$gameTitleID = gameTitlesController::getTitleID($gameURL);
			if (!$gameTitleID && !empty($game)) {
				$gameTitle = new gameTitle;
				$gameTitle->set('gameTitle', $game);
				$gameTitle->set('gameTitleURL', $gameURL);
				$gameTitle->set('gameTitleKey', strtolower($game));
				if ($gameTitle->save()) {
					$gameTitleID = $gameTitle->get('gameTitleID');
					$game = $gameTitle->get('gameTitle');
				} else {
					addError('An error occurred while saving the game title');
				}
			}
			if ($gameTitleID) {
				$post->set('gameTitleID', $gameTitleID);
			}
			$post->assertTypeValues();
			if ($post->update()) {
				addSuccess('Post updated successfully');
				editPost($post->get('postID'));
			} else {
				addError('An error occurred while updating the post');
				$post->updateSystemMessages();
				switch ($post->get('poster')) {
					case 'USER':
						$user = new user($post->get('posterID'));
						$userName = $user->get('userName');
						break;
					case 'ADMIN':
						$user = new adminUser($post->get('posterID'));
						$userName = $user->get('login');
						break;
					default:
						$userName = 'anonymous';
						break;
				}
				$postData = $post->fetchArray();
				$postData['gameTitle'] = $game;
				$postData['gameTitleURL'] = $gameURL;
				$controller = new postsController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
				$template->assignClean('post', $postData);
				$template->assignClean('userName', $userName);
				$template->assignClean('postTypes', postsController::getPostTypes());
				$template->assignClean('sources', postsController::$supportedSources);
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/postEdit.htm');
			}
		} else {
			addError('Post does not exist');
			postsAdmin();
		}
	} // function updatePost

	/**
	 *  Update an existing post record
	 *  Args: none
	 *  Return: none
	 */
	function disable() {
		if (getPost('submit')) {
			$posts = explode(',', preg_replace('/[^\d,\-]/', '', getPost('posts')));
			$rangePostIDs = array();
			foreach ($posts as $key => $value) {
				if (!empty($value) && $value > 0) {
					if (!is_numeric($value)) {
						$parts = explode('-', $value);
						if (is_numeric($parts[0]) && is_numeric($parts[1]) && $parts[0] < $parts[1]) {
							for ($i = $parts[0]; $i <= $parts[1]; $i++) {
								$rangePostIDs[] = $i;
							}
						}
						unset($posts[$key]);
					}
				} else {
					unset($posts[$key]);
				}
			}
			$posts = array_unique(array_merge($posts, $rangePostIDs));
			$disabled = 0;
			foreach ($posts as $postID) {
				$post = new post($postID);
				if ($post->get('status') == 'active') {
					$post->set('status', 'disabled');
					if ($post->update()) {
						++$disabled;
					}
				}
			}
			addSuccess($disabled.' post(s) disabled successfully');
		}
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/postDisable.htm');
	} // function disable

?>

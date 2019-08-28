<?

	adminCore::checkAccess('POST');

	$actions = array(
		'feedsAdmin',
		'addFeed',
		'saveFeed',
		'editFeed',
		'updateFeed',
		'importYoutube'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		feedsAdmin();
	}

	/**
	 *  Show the feed admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function feedsAdmin() {
		$controller = new feedsController;
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Feeds Admin');
		$template->assignClean('postTypes', postsController::getPostTypes());
		$template->assignClean('sources', postsController::$supportedSources);
		$template->assignClean('userTypes', getUserTypeOptions());
		$template->assignClean('records', $controller->performSearch());
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/feedsAdmin.htm');	
	} // function feedsAdmin

	/**
	 *  Add feed section
	 *  Args: none
	 *  Return: none
	 */
	function addFeed() {
		$feedID = getRequest('feedID', 'integer');
		if ($feedID) {
			$feed = new feed($feedID);
			$gameTitle = new gameTitle($feed->get('gameTitleID'));
		} else {
			$feed = new feed;
			$gameTitle = new gameTitle;
		}
		switch ($feed->get('poster')) {
			case 'USER':
				$user = new user($feed->get('posterID'));
				$userName = $user->get('userName');
				break;
			case 'ADMIN':
				$user = new adminUser($feed->get('posterID'));
				$userName = $user->get('login');
				break;
			default:
				$userName = 'anonymous';
			break;
		}
		$controller = new feedsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Feeds Admin');
		$template->assignClean('feed', $feed->fetchArray());
		$template->assignClean('gameTitle', $gameTitle->get('gameTitle'));
		$template->assignClean('userName', $userName);
		$template->assignClean('postTypes', postsController::getPostTypes());
		$template->assignClean('sources', postsController::$supportedSources);
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('intervalOptions', $controller->getOptions('interval'));
		$template->assignClean('userTypes', getUserTypeOptions());
		$template->assignClean('timeOptions', false);
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/feedEdit.htm');
	} // function addFeed

	/**
	 *  Save a new feed record
	 *  Args: none
	 *  Return: none
	 */
	function saveFeed() {
		$feed = new feed;
		$feed->set('postType', getPost('postType'));
		$feed->set('source', getPost('source'));
		$feed->setTypeRequirements();
		$feed->set('url', getPost('url'));
		$feed->set('parameters', getPost('parameters'));
		$feed->set('entryPath', getPost('entryPath'));
		$feed->set('gameTitlePath', getPost('gameTitlePath'));
		$feed->set('identifierPath', getPost('identifierPath'));
		$feed->set('postTitlePath', getPost('postTitlePath'));
		$feed->set('urlPath', getPost('urlPath'));
		$feed->set('imagePath', getPost('imagePath'));
		$feed->set('descriptionPath', getPost('descriptionPath'));
		$feed->set('contentPath', getPost('contentPath'));
		$feed->set('require', getPost('require'));
		$feed->set('reject', getPost('reject'));
		$feed->set('replace', getPost('replace'));
		$feed->set('status', getPost('status'));
		$feed->set('interval', getPost('interval'));
		$feed->set('priority', getPost('priority'));
		$feed->set('poster', getPost('poster'));
		$feed->set('posterID', getPost('posterID'));
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
			$feed->set('gameTitleID', $gameTitleID);
		}
		if ($feed->assertValidData() && !$feed->isDuplicate()) {
			$feed->assertTypeValues();
			if ($feed->save()) {
				addSuccess('Feed saved successfully');
				if (getRequest('submit') == 'Add and Edit') {
					editFeed($feed->get('feedID'));
				} else {
					addFeed();
				}
				exit;
			}
		}
		addError('An error occurred while saving the feed');
		$feed->updateSystemMessages();
		$controller = new feedsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' feeds Admin');
		$template->assignClean('postTypes', postsController::getPostTypes());
		$template->assignClean('feed', $feed->fetchArray());
		$template->assignClean('gameTitle', $game);
		$template->assignClean('sources', postsController::$supportedSources);
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('intervalOptions', $controller->getOptions('interval'));
		$template->assignClean('userTypes', getUserTypeOptions());
		$template->assignClean('timeOptions', false);
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/feedEdit.htm');
	} // function saveFeed

	/**
	 *  Edit feed section
	 *  Args: (int) feed id
	 *  Return: none
	 */
	function editFeed($feedID = false) {
		if (!$feedID) {
			$feedID = getRequest('feedID', 'integer');
		}
		$feed = new feed($feedID);
		$gameTitle = new gameTitle($feed->get('gameTitleID'));
		if ($feed->exists()) {
			switch ($feed->get('poster')) {
				case 'USER':
					$user = new user($feed->get('posterID'));
					$userName = $user->get('userName');
					break;
				case 'ADMIN':
					$user = new adminUser($feed->get('posterID'));
					$userName = $user->get('login');
					break;
				default:
					$userName = 'anonymous';
					break;
			}
			$controller = new feedsController;
			$api = new youtubeAPI;
			$timeOptions = $api->getOptions('time');
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Feeds Admin');
			$template->assignClean('feed', $feed->fetchArray());
			$template->assignClean('gameTitle', $gameTitle->get('gameTitle'));
			$template->assignClean('userName', $userName);
			$template->assignClean('postTypes', postsController::getPostTypes());
			$template->assignClean('sources', postsController::$supportedSources);
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('intervalOptions', $controller->getOptions('interval'));
			$template->assignClean('userTypes', getUserTypeOptions());
			$template->assignClean('timeOptions', $timeOptions);
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/feedEdit.htm');
		} else {
			addError('Feed does not exist');
			feedsAdmin();
		}
	} // function editFeed

	/**
	 *  Update an existing feed record
	 *  Args: none
	 *  Return: none
	 */
	function updateFeed() {
		$feed = new feed(getRequest('feedID', 'integer'));
		if ($feed->exists()) {
			if (getPost('submit') == 'Run Feed') {
				$source = $feed->get('source');
				switch ($source) {
					case 'Youtube':
						runYoutubeFeed($feed);
						break;
					default:
						runFeed($feed);
						break;
				}
				return;
			}
			$feed->set('postType', getPost('postType'));
			$feed->set('source', getPost('source'));
			$feed->setTypeRequirements();
			$feed->set('url', getPost('url'));
			$feed->set('parameters', getPost('parameters'));
			$feed->set('entryPath', getPost('entryPath'));
			$feed->set('gameTitlePath', getPost('gameTitlePath'));
			$feed->set('identifierPath', getPost('identifierPath'));
			$feed->set('postTitlePath', getPost('postTitlePath'));
			$feed->set('urlPath', getPost('urlPath'));
			$feed->set('imagePath', getPost('imagePath'));
			$feed->set('descriptionPath', getPost('descriptionPath'));
			$feed->set('contentPath', getPost('contentPath'));
			$feed->set('require', getPost('require'));
			$feed->set('reject', getPost('reject'));
			$feed->set('replace', getPost('replace'));
			$feed->set('status', getPost('status'));
			$feed->set('interval', getPost('interval'));
			$feed->set('priority', getPost('priority'));
			$feed->set('poster', getPost('poster'));
			$feed->set('posterID', getPost('posterID'));
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
				$feed->set('gameTitleID', $gameTitleID);
			}
			$feed->assertTypeValues();
			if ($feed->update()) {
				addSuccess('Feed updated successfully');
				editFeed($feed->get('feedID'));
			} else {
				addError('An error occurred while updating the feed');
				$feed->updateSystemMessages();
				switch ($feed->get('poster')) {
					case 'USER':
						$user = new user($feed->get('posterID'));
						$userName = $user->get('userName');
						break;
					case 'ADMIN':
						$user = new adminUser($feed->get('posterID'));
						$userName = $user->get('login');
						break;
					default:
						$userName = 'anonymous';
						break;
				}
				$controller = new feedsController;
				$api = new youtubeAPI;
				$timeOptions = $api->getOptions('time');
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
				$template->assignClean('feed', $feed->fetchArray());
				$template->assignClean('gameTitle', $game);
				$template->assignClean('userName', $userName);
				$template->assignClean('postTypes', postsController::getPostTypes());
				$template->assignClean('sources', postsController::$supportedSources);
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('intervalOptions', $controller->getOptions('interval'));
				$template->assignClean('userTypes', getUserTypeOptions());
				$template->assignClean('timeOptions', $timeOptions);
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/feedEdit.htm');
			}
		} else {
			addError('Feed does not exist');
			feedsAdmin();
		}
	} // function updateFeed

	/**
	 *  Process and post for given youtube feed
	 *  Args: (object) feed object
	 *  Return: none
	 */
	function runYoutubeFeed($feed) {
		$timeFrame = getRequest('timeFrame');
		$api = new youtubeAPI;
		$timeOptions = $api->getOptions('time');
		if (!isset($timeOptions[$timeFrame])) {
			$timeFrame = 'today';
		}
		$startOn = getRequest('startOn', 'integer');
		if (empty($startOn)) {
			$startOn = 1;
		}
		$processThrough = getRequest('processThrough', 'integer');
		if (empty($processThrough)) {
			$processThrough = 50;
		}
		$feedArray = $feed->fetchArray();
		if (preg_match('/(^|,)time\->/', $feedArray['parameters'])) {
			$feedArray['parameters'] = preg_replace('/(^|,)time\->[^,]+/', '$1time->'.$timeFrame, $feedArray['parameters']);
		} else {
			if (!empty($feedArray['parameters'])) {
				$feedArray['parameters'] .= ',';
			}
			$feedArray['parameters'] .= 'time->'.$timeFrame;
		}
		if (preg_match('/(^|,)start\-index\->/', $feedArray['parameters'])) {
			$feedArray['parameters'] = preg_replace('/(^|,)start\-index\->[^,]+/', '$1start-index->'.$startOn, $feedArray['parameters']);
		} else {
			$feedArray['parameters'] .= ',start-index->'.$startOn;
		}
		$posted = 0;
		while ($startOn <= $processThrough) {
			$posted += feedAPI::runFeeds(array($feedArray));
			$startOn += 50;
			$feedArray['parameters'] = preg_replace('/(^|,)start\-index\->[^,]+/', '$1start-index->'.$startOn, $feedArray['parameters']);
		}
		addSuccess($posted.' post(s) made successfully');
		editFeed($feed->get('feedID'));
	} // function runYoutubeFeed

	/**
	 *  Process and post for given feed
	 *  Args: (object) feed object
	 *  Return: none
	 */
	function runFeed($feed) {
		$feedArray = $feed->fetchArray();
		$posted = feedAPI::runFeeds(array($feedArray));
		addSuccess($posted.' post(s) made successfully');
		editFeed($feed->get('feedID'));
	} // function runFeed

	/**
	 *  Get user type options
	 *  Args: none
	 *  Return: (array) user type options
	 */
	function getUserTypeOptions() {
		$userTypes = postsController::$userTypes;
		$userTypeOptions = array(
			'' => ''
		);
		foreach ($userTypes as $userType => $typeValue) {
			$userTypeOptions[strtoupper($userType)] = ucfirst($userType);
		}
		return $userTypeOptions;
	} // function getUserTypeOptions

	/**
	 *  Import from youtube by specific video ids
	 *  Args: none
	 *  Return: none
	 */
	function importYoutube() {
		$poster = '';
		$posterID = '';
		$gameTitle = '';
		$ids = '';
		if (getPost('submit')) {
			$poster = getPost('poster');
			$posterID = getPost('posterID', 'integer');
			$gameTitle = getPost('gameTitle', 'gameTitle');
			$ids = preg_replace('/\s/', '', getPost('ids'));
			if ($posterID) {
				if ($gameTitle) {
					$game = preg_replace('/\s+/', ' ', $gameTitle);
					$gameURL = friendlyURL($game);
					$gameTitleID = gameTitlesController::getTitleID($gameURL);
					if (!$gameTitleID) {
						$gameTitle = new gameTitle;
						$gameTitle->set('gameTitle', $game);
						$gameTitle->set('gameTitleURL', $gameURL);
						$gameTitle->set('gameTitleKey', strtolower($game));
						if ($gameTitle->save()) {
							$gameTitleID = $gameTitle->get('gameTitleID');
						}
					}
					if ($gameTitleID) {
						$normalIds = array();
						$specialIds = array();
						$idsArray = explode(',', $ids);
						foreach ($idsArray as $id) {
							if (preg_match('/\-|_/', $id)) {
								$specialIds[] = $id;
							} else {
								$normalIds[] = $id;
							}
						}
						$feed = array(
							'postType'        => 'video',
							'url'             => '',
							'source'          => 'Youtube',
							'gameTitleID'     => $gameTitleID,
							'parameters'      => 'time->all_time,query->',
							'entryPath'       => 'entry',
							'gameTitlePath'   => '',
							'identifierPath'  => 'id',
							'postTitlePath'   => 'title',
							'urlPath'         => '',
							'imagePath'       => 'media_group->media_thumbnail',
							'descriptionPath' => 'media_group->media_description',
							'contentPath'     => 'media_group->media_description',
							'require'         => '',
							'reject'          => '',
							'replace'         => 'media:->media_',
							'status'          => 'active',
							'interval'        => 'daily',
							'priority'        => 1,
							'poster'          => $poster,
							'posterID'        => $posterID
						);
						$posted = 0;
						if (!empty($normalIds)) {
							$idsQuery = implode('|', $normalIds);
							$feed['parameters'] = 'time->all_time,query->'.$idsQuery;
							$posted = feedAPI::runFeeds(array($feed), 'importYoutube');
						}
						if (!empty($specialIds)) {
							foreach ($specialIds as $id) {
								$feed['parameters'] = 'time->all_time,query->'.$id;
								$posted += feedAPI::runFeeds(array($feed), 'importYoutube');
							}
						}
						addSuccess($posted.' post(s) made successfully');
					} else {
						addError('An error occurred while creating the game title, please try again');
					}
				} else {
					addError('You must enter a valid game title');
				}
			} else {
				addError('You must enter a user to post as');
			}
		}
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Feeds Admin');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->assignClean('userTypes', getUserTypeOptions());
		$template->assignClean('poster', $poster);
		$template->assignClean('posterID', $posterID);
		$template->assignClean('gameTitle', $gameTitle);
		$template->assignClean('ids', $ids);
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/feedsImportYoutube.htm');
	} // function importYoutube
?>

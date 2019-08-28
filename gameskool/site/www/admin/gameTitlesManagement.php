<?

	adminCore::checkAccess('POST');

	$actions = array(
		'gameTitlesAdmin',
		'addGameTitle',
		'saveGameTitle',
		'editGameTitle',
		'updateGameTitle'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		gameTitlesAdmin();
	}

	/**
	 *  Show the game titles admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function gameTitlesAdmin() {
		$controller = new gameTitlesController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/gameTitlesAdmin.htm');
	} // function gameTitlesAdmin

	/**
	 *  Add game title section
	 *  Args: none
	 *  Return: none
	 */
	function addGameTitle() {
		$gameTitle = new gameTitle;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
		$template->assignClean('gameTitle', $gameTitle->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/gameTitleEdit.htm');
	} // function addGameTitle

	/**
	 *  Save a new game title record
	 *  Args: none
	 *  Return: none
	 */
	function saveGameTitle() {
		$gameTitle = new gameTitle;
		$gameTitle->set('gameTitle', preg_replace('/\s+/', ' ', getPost('gameTitle')));
		$gameTitle->set('gameTitleURL', friendlyURL($gameTitle->get('gameTitle')));
		$gameTitle->set('gameTitleKey', strtolower($gameTitle->get('gameTitle')));
		if ($gameTitle->save()) {
			addSuccess('Game title added');
			if (getRequest('submit') == 'Add and Edit') {
				editGameTitle($gameTitle->get('gameTitleID'));
			} else {
				addGameTitle();
			}
			exit;
		} else {
			addError('An error occurred while saving the game title');
			$gameTitle->updateSystemMessages();
		}
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
		$template->assignClean('gameTitle', $gameTitle->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/gameTitleEdit.htm');
	} // function saveGameTitle

	/**
	 *  Edit game title section
	 *  Args: (int) game title id
	 *  Return: none
	 */
	function editGameTitle($gameTitleID = false) {
		if (!$gameTitleID) {
			$gameTitleID = getRequest('gameTitleID', 'integer');
		}
		$gameTitle = new gameTitle($gameTitleID);
		if ($gameTitle->exists()) {
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
			$template->assignClean('gameTitle', $gameTitle->fetchArray());
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/gameTitleEdit.htm');
		} else {
			addError('Game title does not exist');
			gameTitlesAdmin();
		}
	} // function editGameTitle

	/**
	 *  Update an existing game title record
	 *  Args: none
	 *  Return: none
	 */
	function updateGameTitle() {
		$gameTitle = new gameTitle(getRequest('gameTitleID', 'integer'));
		if ($gameTitle->exists()) {
			$gameTitle->set('gameTitle', preg_replace('/\s+/', ' ', getPost('gameTitle')));
			$gameTitle->set('gameTitleURL', friendlyURL($gameTitle->get('gameTitle')));
			$gameTitle->set('gameTitleKey', strtolower($gameTitle->get('gameTitle')));
			if ($gameTitle->update()) {
				addSuccess('Game title updated successfully');
				editGameTitle($gameTitle->get('gameTitleID'));
			} else {
				addError('An error occurred while updating the game title');
				$gameTitle->updateSystemMessages();
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Posts Admin');
				$template->assignClean('gameTitle', $gameTitle->fetchArray());
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/gameTitleEdit.htm');
			}
		} else {
			addError('Game title does not exist');
			gameTitlesAdmin();
		}
	} // function updateGameTitle

?>

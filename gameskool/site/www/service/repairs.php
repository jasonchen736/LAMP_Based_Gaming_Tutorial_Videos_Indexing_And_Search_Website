<?

	// track landing
	tracker::trackLanding();

	$actions = array(
		'repairForm',
		'submit',
		'completed'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		repairForm();
	}

	/**
	 * Display repair form
	 * Args: (repairOrder) repair order object
	 * Return: none
	 */
	function repairForm($repairOrder = false) {
		if (!$repairOrder) {
			$repairOrder = new repairOrder;
		}
		$controller = new repairOrdersController;
		$template = new template;
		$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/repairs.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Video Game Repairs');
		$template->assignClean('searchURL', '/');
		$template->assignClean('searchQuery', searchClient::getSearchQuery());
		$template->assignClean('searchText', $searchQuery ? $searchQuery : 'search game protege');
		$template->assignClean('pageURL', '/home');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->assignClean('user', userCore::getUser());
		$template->assignClean('consoles', systemsController::getSystems());
		$template->assignClean('systemProblems', systemProblemsController::getSystemProblems());
		$template->assignClean('receiveMethods', $controller->getOptions('receiveMethod'));
		$template->assignClean('returnMethods', $controller->getOptions('returnMethod'));
		$template->assignClean('contactOptions', $controller->getOptions('contact'));
		$template->assignClean('states', statesController::getStates());
		$template->assignClean('repairOrder', $repairOrder->fetchArray());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('site/repairForm.htm');
	} // function repairForm

	/**
	 * Submit repair request
	 * Args: none
	 * Return: none
	 */
	function submit() {
		$validCaptcha = captcha::validateCaptcha(getPost('captcha'));
		$repairOrder = new repairOrder;
		if (getPost('submitRequest')) {
			$repairOrder->set('console', getPost('console'));
			$repairOrder->set('serial', getPost('serial'));
			$systemProblemID = getPost('systemProblemID');
			$repairOrder->set('systemProblemID', $systemProblemID);
			$repairOrder->set('description', getPost('description'));
			$repairOrder->set('receiveMethod', getPost('receiveMethod'));
			$repairOrder->set('returnMethod', getPost('returnMethod'));
			$repairOrder->set('first', getPost('first'));
			$repairOrder->set('last', getPost('last'));
			$repairOrder->set('email', getPost('email'));
			$repairOrder->set('phone', getPost('phone'));
			$repairOrder->set('address1', getPost('address1'));
			$repairOrder->set('address2', getPost('address2'));
			$repairOrder->set('city', getPost('city'));
			$repairOrder->set('state', getPost('state'));
			$repairOrder->set('postal', getPost('postal'));
			$repairOrder->set('contact', getPost('contact'));
			$systemProblem = new systemProblem($systemProblemID);
			$repairOrder->set('cost', $systemProblem->get('cost'));
			$repairOrder->set('country', 'USA');
			$repairOrder->set('status', 'new');
			list($userType, $userID) = $repairOrder->getRecordEditor();
			if ($userType == 'SYSTEM') {
				$repairOrder->setRecordEditor('ANONYMOUS', 0);
				$repairOrder->set('user', 'ANONYMOUS');
				$repairOrder->set('userID', 0);
			} else {
				$repairOrder->set('user', $userType);
				$repairOrder->set('userID', $userID);
			}
			if ($validCaptcha) {
				if ($repairOrder->save()) {
					$_SESSION['repairOrderID'] = $repairOrder->get('repairOrderID');
					redirect('/service/repairs/action/completed/');
				} else {
					$repairOrder->updateSystemMessages();
				}
			} else {
				$repairOrder->assertValidData();
				$repairOrder->updateSystemMessages();
				addError('hey, that image text ain\'t right');
				addErrorField('captcha');
			}
		}
		repairForm($repairOrder);
	} // function submit

	/**
	 * Completed repair request
	 * Args: none
	 * Return: none
	 */
	function completed() {
		$repairOrder = new repairOrder(getSession('repairOrderID'));
		if (isset($_SESSION['repairOrderID'])) {
			unset($_SESSION['repairOrderID']);
		}
		if ($repairOrder->exists()) {
			$repairOrder->sendConfirmation();
			$systems = systemsController::getSystems();
			$systemID = $repairOrder->get('console');
			$system = $systems[$systemID];
			$systemProblems = systemProblemsController::getSystemProblems();
			$systemProblemID = $repairOrder->get('systemProblemID');
			if (isset($systemProblems[$consoleID][$systemProblemID])) {
				$systemProblem = $systemProblems[$consoleID][$systemProblemID];
			} else {
				$systemProblem = $systemProblems[0][$systemProblemID];
			}
			$template = new template;
			$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
			$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
			$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
			$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Video Game Repairs');
			$template->assignClean('searchURL', '/');
			$template->assignClean('searchQuery', searchClient::getSearchQuery());
			$template->assignClean('searchText', $searchQuery ? $searchQuery : 'search game protege');
			$template->assignClean('pageURL', '/home');
			$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
			$template->assignClean('loggedin', userCore::isLoggedIn());
			$template->assignClean('user', userCore::getUser());
			$template->assignClean('system', $system);
			$template->assignClean('systemProblem', $systemProblem);
			$template->assignClean('states', statesController::getStates());
			$template->assignClean('repairOrder', $repairOrder->fetchArray());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('site/repairOrderComplete.htm');
		} else {
			addError('oops, we couldn\'t find your repair order');
			repairForm();
		}
	} // function completed

?>

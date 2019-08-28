<?

	if (userCore::isLoggedIn()) {
		redirect('/user');
	} elseif (!isset($_SESSION['auth']) || $_SESSION['auth']['time'] + 300 < time()) {
		if (isset($_SESSION['auth'])) {
			unset($_SESSION['auth']);
		}
		redirect('/user/login');
	}

	$actions = array(
		'setupForm',
		'setup'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		setupForm();
	}

	/**
	 *  Set up account after initial external login
	 *  Args: none
	 *  Return: none
	 */
	function setupForm() {
		$template = new template;
		$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' User Setup');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/setupForm.htm');
	} // function setupForm

	/**
	 *  Process account setup
	 *  Args: none
	 *  Return: none
	 */
	function setup() {
		$user = new user();
		$user->unRequire('email');
		$user->unRequire('password');
		$user->makeRequired('externalProvider');
		$user->makeRequired('externalID');
                $user->set('userName', getPost('userName'));
		$user->set('email', getPost('email'));
		$user->set('externalProvider', $_SESSION['auth']['provider']);
		$user->set('externalID', $_SESSION['auth']['externalID']);
		$user->equateToNull('email', '');
		$user->set('status', 'active');
		// confirm user agreement
		if (!$userAgreement = getPost('userAgreement')) {
			addError('hey c\'mon, agree to the user conditions');
			addErrorField('userAgreement');
		}
		if ($userAgreement) {
			if ($user->save()) {
				addSuccess('thanks for signing up');
				unset($_SESSION['auth']);
				userCore::setCore($user->get('userID'));
				redirect('/');
			} else {
				$user->updateSystemMessages();
			}
		} else {
			$user->assertValidData();
			$user->updateSystemMessages();
		}
		$template = new template;
		$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' User Setup');
		$template->assignClean('user', $user->fetchArray());
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/setupForm.htm');
	} // function setup

?>

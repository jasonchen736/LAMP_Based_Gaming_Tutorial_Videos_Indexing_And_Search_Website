<?

	if (userCore::isLoggedIn()) {
		redirect('/user');
	}

	$actions = array(
		'registrationForm',
		'register'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		registrationForm();
	}

	/**
	 *  Show the registration form
	 *  Args: none
	 *  Return: none
	 */
	function registrationForm() {
		if (getRequest('to')) {
			addMessage('oops, you have to log in to do that');
		};
		$template = new template;
		$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/openid.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' User Registration');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/registrationForm.htm');
	} // function registrationForm

	/**
	 *  Register and save a user user
	 *  Args: none
	 *  Return: none
	 */
	function register() {
		$user = new user;
		$user->set('userName', getPost('userName'));
		$user->set('password', getPost('password'));
		$user->set('email', getPost('email'));
		// perform password check
		$password = $user->get('password');
		$confirmPassword = getPost('passwordConfirm');
		if ($password == $confirmPassword) {
			$passwordMatch = true;
		} else {
			$passwordMatch = false;
			addError('your password confirmation does not match');
			addErrorField('password');
			addErrorField('passwordConfirm');
		}
		// verify captcha
		if (captcha::validateCaptcha(getPost('captcha'))) {
			$captcha = true;
		} else {
			$captcha = false;
			addError('that image text ain\'t right!');
			addErrorField('captcha');
		}
		// confirm user agreement
		if (!$userAgreement = getPost('userAgreement')) {
			addError('hey c\'mon, agree to the user conditions');
			addErrorField('userAgreement');
		}
		if ($passwordMatch && $userAgreement && $captcha) {
			if ($user->save()) {
				addSuccess('thanks for signing up');
				// set user activation record
				$hashstring = $user->get('userID').$user->get('email').time();
				$userActivation = new userActivation;
				$userActivation->set('userID', $user->get('userID'));
				$userActivation->set('activationCode', md5($hashstring));
				if ($userActivation->save()) {
					$template = new template;
					$template->assignClean('user', $user->fetchArray());
					$template->assignClean('activationCode', $userActivation->get('activationCode'));
					if (!usersController::sendActivationEmail($template, $user->get('email'))) {
						addError('there was a problem sending your user activation email');
						addError('please contact support to complete your registration');
					} else {
						addSuccess('you will receive an email shortly to confirm your sign up');
					}
				} else {
					addError('there was a problem creating your user activation record');
					addError('please contact support to complete your registration');
				}
				redirect('/');
			} else {
				$user->updateSystemMessages();
			}
		} else {
			$user->assertValidData();
			$user->updateSystemMessages();
		}
		$user->set('password', getPost('password'));
		$template = new template;
		$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/openid.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' User Registration');
		$template->assignClean('user', $user->fetchArray());
		$template->assignClean('confirmPassword', $confirmPassword);
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/registrationForm.htm');
	} // function register

?>

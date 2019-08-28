<?

	$actions = array(
		'sendActivation',
		'activate'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		redirect('/user/login');
	}

	/**
	 *  
	 *  Args: none
	 *  Return: none
	 */
	function sendActivation() {
		$userID = getRequest('user', 'integer');
		$user = new user($userID);
		$template = new template;
		if (!$user->exists()) {
			addError('invalid user account');
		} elseif ($user->get('status') != 'new') {
			addError('the user account has already been activated');
		} else {
			$userActivation = $user->getActivationRecord();
			$hashstring = $user->get('userID').$user->get('email').time();
			$userActivation->set('activationCode', md5($hashstring));
			if ($userActivation->exists()) {
				$userActivation->set('expiration', date('Y-m-d', strtotime('1 week')));
				$userActivation->set('activated', 0);
				$set = $userActivation->update();
			} else {
				$userActivation->set('userID', $user->get('userID'));
				$set = $userActivation->save();
			}
			if ($set) {
				$template->assignClean('user', $user->fetchArray());
				$template->assignClean('activationCode', $userActivation->get('activationCode'));
				if (!usersController::sendActivationEmail($template, $user->get('email'))) {
					addSuccess('your activation code has been emailed to you');
				} else {
					addError('an error occurred while sending your activation code, please try again later');
				}
			} else {
				addError('an error occurred while setting the activation code, please try again later');
			}
		}
		$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/activation.htm');
	} // function sendActivation

	/**
	 *  
	 *  Args: none
	 *  Return: none
	 */
	function activate() {
		$activationCode = getRequest('activationCode', 'alphanum');
		$sql = "SELECT * FROM `userActivations` WHERE `activationCode` = '".$activationCode."'";
		$result = query($sql);
		if ($result->rowCount > 0) {
			$row = $result->fetchRow();
			$userActivation = new userActivation;
			$userActivation->loadRecord($row);
			if (strtotime($userActivation->get('expiration')) < time()) {
				// expired, send new activation
				addError('your activation timeframe has expired, a new activation code will be emailed to you');
				redirect('/user/activation/action/sendActivation/user/'.$userActivation->get('userID'));
			} elseif (strtotime($userActivation->get('activated'))) {
				// already activated
				addError('your account has already been activated');
			} elseif ($activationCode == $userActivation->get('activationCode')) {
				// activate
				$user = new user($userActivation->get('userID'));
				$user->set('status', 'active');
				if (!$user->update()) {
					addError('an error has occurred while activating your account, please try again later');
				} else {
					addSuccess('your account has been activated');
					addSuccess('you may log in with your username and password');
					$userActivation->set('activated', 'NOW()', false);
					$userActivation->enclose('activated', false);
					if (!$userActivation->update()) {
						trigger_error('Unable to update activation date for user activation record '.$userActivation->get('userActivationID'.' on '.date('Y-m-d H:i:s')), E_USER_NOTICE);
					}
					redirect('/');
				}
			}
		} else {
			addError('invalid activation code');
		}
		$template = new template;
		$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/activation.htm');
	} // function activate

?>

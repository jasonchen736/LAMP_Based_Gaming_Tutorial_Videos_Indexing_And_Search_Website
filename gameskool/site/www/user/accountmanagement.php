<?

	userCore::checkAccess();

	$actions = array(
		'accountManagement',
		'updateAccount'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		accountManagement();
	}

	/**
	 *  Display account info and edit fields
	 *  Args: none
	 *  Return: none
	 */
	function accountManagement() {
		$user = userCore::getUser();
		$template = new template;
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' User Account');
		$template->assignClean('hasPassword', !empty($user['password']));
		$template->assignClean('external', !empty($user['externalID']));
		$template->assignClean('user', $user);
		$template->assignClean('location', 'accountmanagement');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/accountManagement.htm');
	} // function accountManagement

	/**
	 *  Update an existing user account record
	 *  Args: none
	 *  Return: none
	 */
	function updateAccount() {
		$newPasswordSet = false;
		$newEmailSet = false;
		$userInfo = userCore::getUser();
		$external = !empty($userInfo['externalID']);
		$hasPassword = !empty($userInfo['password']);
		$noPasswordExternalUser = $external && !$hasPassword;
		$user = new user($userInfo['userID']);
		if ($noPasswordExternalUser || $user->confirmPassword(getPost('password', 'password'))) {
			$newPassword = getPost('newPassword');
			$confirmPassword = getPost('confirmPassword');
			if ($newPassword || $confirmPassword) {
				$newPassword = clean($newPassword, 'password');
				$confirmPassword = clean($confirmPassword, 'password');
				if ($newPassword && $newPassword == $confirmPassword) {
					$user->set('password', $newPassword);
					$newPasswordSet = true;
				} elseif ($newPassword != $confirmPassword) {
					addError('your new password has to match the confirmation');
					addErrorField('newPassword');
					addErrorField('confirmPassword');
				}
			}
			if ($noPasswordExternalUser && empty($newPassword)) {
				$user->unRequire('password');
			}
			$existingEmail = $user->get('email');
			$email = getPost('email');
			if ($email && $email != $existingEmail) {
				if (validEmail($email)) {
					$user->set('email', $email);
					if (!$user->isDuplicate()) {
						$newEmailSet = true;
					} else {
						$user->set('email', $existingEmail);
						addError('that new email address is registered under a different account, are you doing something shady?');
						addErrorField('email');
					}
				} else {
					addError('that new email address ain\'t right');
					addErrorField('email');
				}
			}
			if ($external && empty($userInfo['email']) && empty($email)) {
				$user->unRequire('email');
				$user->equateToNull('email', '');
			}
			if (!haveErrors() && ($newEmailSet || $newPasswordSet)) {
				if ($user->update()) {
					userCore::setCore($user->get('userID'));
					addSuccess('congratulations! your account was updated');
				} else {
					$user->updateSystemMessages();
				}
			}
		} else {
			addError('your current password isn\'t right, are you sure you own this account?');
			addErrorField('password');
		}
		accountManagement();
	} // function updateAccount

?>

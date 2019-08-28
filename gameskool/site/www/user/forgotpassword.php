<?

	$actions = array(
		'showForm',
		'resetPassword'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		showForm();
	}

	/**
	 *  Show the forgot password form
	 *  Args: none
	 *  Return: none
	 */
	function showForm() {
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
		$template->display('user/forgotPassword.htm');
	} // function showForm

	/**
	 *  Reset password and send email
	 *  Args: none
	 *  Return: none
	 */
	function resetPassword() {
		$email = getPost('email', 'email');
		$sql = "SELECT `userID` FROM `users` WHERE `email` = '".prep($email)."'";
		$result = query($sql);
		if ($result->rowCount > 0) {
			$row = $result->fetchRow();
			$user = new user($row['userID']);
			if (!$user->resetPassword()) {
				addError('there was a problem resetting your password, please try again later');
			}
		} else {
			addError('that is not a registered email');
		}
		showForm();
	} // function resetPassword

?>

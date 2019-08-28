<?

	if (userCore::isLoggedIn()) {
		redirect('/user');
	}

	$actions = array(
		'displayLogin',
		'login'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		displayLogin();
	}

	/**
	 *  Show the login form
	 *  Args: (str) login name, (str) password
	 *  Return: none
	 */
	function displayLogin($login = false, $pass = false) {
		$template = new template;
		$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/openid.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users');
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', userCore::isLoggedIn());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('user/login.htm');
	} // function displayLogin

	/**
	 *  Attempt to log in
	 *  Args: none
	 *  Return: none
	 */
	function login() {
		if (userCore::isLoggedIn()) {
			redirect('/user');
		}
		$login = getPost('login', 'name');
		$pass = getPost('pass', 'password');
		if (userCore::login($login, $pass)) {
			$user = userCore::get('user');
			if (isset($user['loginPage']) && $user['loginPage']) {
				$url = $user['loginPage'];
			} else {
				$url = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '/';
			}
		} else {
			$url = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '/';
		}
		redirect(appendSearchParams($url));
	} // function login

?>

<?

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
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Administration');
		$template->assignClean('login', $login);
		$template->assignClean('pass', $pass);
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/login.htm');
	} // function displayLogin

	/**
	 *  Attempt to log in
	 *  Args: none
	 *  Return: none
	 */
	function login() {
		$login = getPost('login', 'alphanum');
		$pass = getPost('pass', 'password');
		if (adminCore::login($login, $pass)) {
			$admin = adminCore::get('admin');
			if (isset($admin['loginPage']) && $admin['loginPage']) {
				redirect($admin['loginPage']);
			} else {
				redirect('/admin');
			}
		} else {
			displayLogin($login, $pass);
		}
	} // function login

?>
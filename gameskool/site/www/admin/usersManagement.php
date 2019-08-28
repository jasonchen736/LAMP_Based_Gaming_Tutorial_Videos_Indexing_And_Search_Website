<?

	adminCore::checkAccess('USER');

	$actions = array(
		'usersAdmin',
		'addUser',
		'saveUser',
		'editUser',
		'updateUser'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		usersAdmin();
	}

	/**
	 *  Show the users admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function usersAdmin() {
		$controller = new usersController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users Admin');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/usersAdmin.htm');	
	} // function usersAdmin

	/**
	 *  Add user section
	 *  Args: none
	 *  Return: none
	 */
	function addUser() {
		$user = new user;
		$controller = new usersController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users Admin');
		$template->assignClean('user', $user->fetchArray());
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/userEdit.htm');
	} // function addUser

	/**
	 *  Save a new user record
	 *  Args: none
	 *  Return: none
	 */
	function saveUser() {
		$user = new user;
		$user->set('userName', getPost('userName'));
		$user->set('status', getPost('status'));
		$user->set('password', getPost('password'));
		$user->set('email', getPost('email'));
		if ($user->save()) {
			if ($user->get('status') == 'new') {
				// set user activation record
				$hashstring = $user->get('userID').$user->get('email').time();
				$userActivation = new userActivation;
				$userActivation->set('userID', $user->get('userID'));
				$userActivation->set('activationCode', md5($hashstring));
				if ($userActivation->save()) {
					$template = new template;
					$template->assign('user', $user->fetchArray());
					$template->assign('activationCode', $userActivation->get('activationCode'));
					if (!usersController::sendActivationEmail($template, $user->get('email'))) {
						addError('There was an error sending the user activation email');
					} else {
						addSuccess('User activation email was sent');
					}
				} else {
					addError('There was an error creating the user activation record');
				}
			}
			addSuccess('User saved successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editUser($user->get('userID'));
			} else {
				addUser();
			}
			exit;
		} else {
			addError('An error occurred while saving the user');
			$user->updateSystemMessages();
		}
		$user->set('password', getPost('password'));
		$controller = new usersController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users Admin');
		$template->assignClean('user', $user->fetchArray());
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/userEdit.htm');
	} // function saveUser

	/**
	 *  Edit user section
	 *  Args: (int) user id
	 *  Return: none
	 */
	function editUser($userID = false) {
		if (!$userID) {
			$userID = getRequest('userID', 'integer');
		}
		$user = new user($userID);
		if ($user->exists()) {
			$controller = new usersController;
			$userArray = $user->fetchArray();
			$userArray['password'] = '';
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users Admin');
			$template->assignClean('user', $userArray);
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/userEdit.htm');
		} else {
			addError('User does not exist');
			usersAdmin();
		}
	} // function editUser

	/**
	 *  Update an existing user record
	 *  Args: none
	 *  Return: none
	 */
	function updateUser() {
		$user = new user(getRequest('userID', 'integer'));
		if ($user->exists()) {
			$user->set('userName', getPost('userName'));
			$user->set('status', getPost('status'));
			$password = getPost('password');
			if ($password) {
				$user->set('password', $password);
			}
			$existingEmail = $user->get('email');
			$email = getPost('email');
			if (validEmail($email)) {
				$user->set('email', getPost('email'));
			} else {
				$user->set('email', '');
			}
			if ($user->update()) {
				if ($user->get('status') == 'new') {
					// set user activation record
					$hashstring = $user->get('userID').$user->get('email').time();
					$userActivation = $user->getActivationRecord();
					$userActivation->set('userID', $user->get('userID'));
					$userActivation->set('activationCode', md5($hashstring));
					$userActivation->set('expiration', date('Y-m-d', strtotime('1 week')));
					$userActivation->set('activated', 0);
					if ($userActivation->exists()) {
						$mode = 'update';
					} else {
						$mode = 'save';
					}
					if ($userActivation->$mode()) {
						$template = new template;
						$template->assign('user', $user->fetchArray());
						$template->assign('activationCode', $userActivation->get('activationCode'));
						if (!usersController::sendActivationEmail($template, $user->get('email'))) {
							addError('There was an error sending the user activation email');
						} else {
							addSuccess('User activation email was sent');
						}
					} else {
						addError('There was an error creating the user activation record');
					}
				}
				addSuccess('User updated successfully');
				editUser($user->get('userID'));
				exit;
			} else {
				addError('An error occurred while updating the user');
				$user->updateSystemMessages();
				$user->set('email', getPost('email'));
				$user->set('password', getPost('password'));
				$controller = new usersController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Users Admin');
				$template->assignClean('user', $user->fetchArray());
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/userEdit.htm');
			}
		} else {
			addError('User does not exist');
			usersAdmin();
		}
	} // function updateUser

?>
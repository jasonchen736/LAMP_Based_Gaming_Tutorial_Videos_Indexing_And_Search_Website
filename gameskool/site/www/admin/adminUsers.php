<?

	adminCore::checkAccess('SUPERADMIN');

	$actions = array(
		'adminUsersAdmin',
		'addUser',
		'saveUser',
		'editUser',
		'updateUser'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		adminUsersAdmin();
	}

	/**
	 *  Show the admin users admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function adminUsersAdmin() {
		$controller = new adminUsersController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Users');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adminUsersAdmin.htm');	
	} // function adminUsersAdmin

	/**
	 *  Add user section
	 *  Args: none
	 *  Return: none
	 */
	function addUser() {
		$adminUser = new adminUser;
		$controller = new adminUsersController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Users');
		$template->assignClean('adminUser', $adminUser->fetchArray());
		$template->assignClean('accessSections', adminUser::$accessSections);
		$template->assignClean('adminGroups', adminGroupsController::getAllGroups());
		$template->assignClean('userAccess', $adminUser->getAccess());
		$template->assignClean('userGroups', $adminUser->getGroups());
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adminUserEdit.htm');
	} // function addUser

	/**
	 *  Save a new user record
	 *  Args: none
	 *  Return: none
	 */
	function saveUser() {
		$adminUser = new adminUser;
		$controller = new adminUsersController;
		$adminUser->set('login', getPost('login'));
		$adminUser->set('password', getPost('password'));
		$password = $adminUser->get('password');
		$adminUser->set('name', getPost('name'));
		$adminUser->set('email', getPost('email'));
		$adminUser->set('status', getPost('status'));
		if ($adminUser->save()) {
			addSuccess('User saved successfully');
			$access = getPost('access');
			$adminUser->setAccess($access);
			$groups = getPost('groups');
			$adminUser->setGroups($groups);
			if (haveErrors() || getRequest('submit') == 'Add and Edit') {
				editUser($adminUser->get('adminUserID'));
			} else {
				addUser();
			}
			exit;
		} else {
			addError('An error occurred while saving the user');
			$adminUser->updateSystemMessages();
		}
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Users');
		$template->assignClean('adminUser', $adminUser->fetchArray());
		$template->assignClean('accessSections', adminUser::$accessSections);
		$template->assignClean('adminGroups', adminGroupsController::getAllGroups());
		$template->assignClean('userAccess', $adminUser->getAccess());
		$template->assignClean('userGroups', $adminUser->getGroups());
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adminUserEdit.htm');
	} // function saveUser

	/**
	 *  Edit admin user section
	 *  Args: (int) admin user id
	 *  Return: none
	 */
	function editUser($adminUserID = false) {
		if (!$adminUserID) {
			$adminUserID = getRequest('adminUserID', 'integer');
		}
		$adminUser = new adminUser($adminUserID);
		if ($adminUser->exists()) {
			$controller = new adminUsersController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Users');
			$template->assignClean('adminUser', $adminUser->fetchArray());
			$template->assignClean('accessSections', adminUser::$accessSections);
			$template->assignClean('adminGroups', adminGroupsController::getAllGroups());
			$template->assignClean('userAccess', $adminUser->getAccess());
			$template->assignClean('userGroups', $adminUser->getGroups());
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/adminUserEdit.htm');
		} else {
			addError('That user does not exist');
			adminUsersAdmin();
		}
	} // function editUser

	/**
	 *  Update an existing user record
	 *  Args: none
	 *  Return: none
	 */
	function updateUser() {
		$adminUser = new adminUser(getRequest('adminUserID', 'integer'));
		if ($adminUser->exists()) {
			$adminUser->set('name', getPost('name'));
			$adminUser->set('login', getPost('login'));
			$adminUser->set('email', getPost('email'));
			$adminUser->set('status', getPost('status'));
			$password = getPost('password');
			if ($password) {
				$adminUser->set('password', $password);
			}
			if ($adminUser->update()) {
				addSuccess('User updated successfully');
				$access = getPost('access');
				$adminUser->setAccess($access);
				$groups = getPost('groups');
				$adminUser->setGroups($groups);
				editUser($adminUser->get('adminUserID'));
			} else {
				addError('An error occurred while updating the user');
				$adminUser->updateSystemMessages();
				$controller = new adminUsersController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Users');
				$template->assignClean('adminUser', $adminUser->fetchArray());
				$template->assignClean('accessSections', adminUser::$accessSections);
				$template->assignClean('adminGroups', adminGroupsController::getAllGroups());
				$template->assignClean('userAccess', $adminUser->getAccess());
				$template->assignClean('userGroups', $adminUser->getGroups());
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/adminUserEdit.htm');
			}
		} else {
			addError('Admin user does not exist');
			adminUsersAdmin();
		}
	} // function updateUser

?>
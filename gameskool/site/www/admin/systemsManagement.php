<?

	adminCore::checkAccess('ADS');

	$actions = array(
		'systemsAdmin',
		'addSystem',
		'saveSystem',
		'editSystem',
		'updateSystem'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		systemsAdmin();
	}

	/**
	 *  Show the systems available for repair
	 *  Args: none
	 *  Return: none
	 */
	function systemsAdmin() {
		$controller = new systemsController;
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Systems Admin');
		$template->assignClean('records', $controller->performSearch());
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/systemsAdmin.htm');	
	} // function systemsAdmin

	/**
	 *  Add repair system
	 *  Args: none
	 *  Return: none
	 */
	function addSystem() {
		$system = new system;
		$controller = new systemsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Systems Admin');
		$template->assignClean('system', $system->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/systemEdit.htm');
	} // function addSystem

	/**
	 *  Save a new repair system record
	 *  Args: none
	 *  Return: none
	 */
	function saveSystem() {
		$system = new system;
		$system->set('name', getPost('name'));
		$system->set('status', getPost('status'));
		if ($system->save()) {
			addSuccess('Repair system saved successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editSystem($system->get('systemID'));
			} else {
				addSystem();
			}
			exit;
		}
		addError('An error occurred while saving the repair system');
		$system->updateSystemMessages();
		$controller = new systemsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Systems Admin');
		$template->assignClean('system', $system->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/systemEdit.htm');
	} // function saveSystem

	/**
	 *  Edit repair system
	 *  Args: (int) repair system id
	 *  Return: none
	 */
	function editSystem($systemID = false) {
		if (!$systemID) {
			$systemID = getRequest('systemID', 'integer');
		}
		$system = new system($systemID);
		if ($system->exists()) {
			$controller = new systemsController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Systems Admin');
			$template->assignClean('system', $system->fetchArray());
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/systemEdit.htm');
		} else {
			addError('Repair System does not exist');
			systemsAdmin();
		}
	} // function editSystem

	/**
	 *  Update an existing repair system record
	 *  Args: none
	 *  Return: none
	 */
	function updateSystem() {
		$system = new system(getRequest('systemID', 'integer'));
		if ($system->exists()) {
			$system->set('name', getPost('name'));
			$system->set('status', getPost('status'));
			if ($system->update()) {
				addSuccess('Repair system updated successfully');
				editSystem($system->get('systemID'));
			} else {
				addError('An error occurred while updating the repair system');
				$system->updateSystemMessages();
				$controller = new systemsController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Systems Admin');
				$template->assignClean('system', $system->fetchArray());
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/systemEdit.htm');
			}
		} else {
			addError('Repair system does not exist');
			systemsAdmin();
		}
	} // function updateSystem

?>
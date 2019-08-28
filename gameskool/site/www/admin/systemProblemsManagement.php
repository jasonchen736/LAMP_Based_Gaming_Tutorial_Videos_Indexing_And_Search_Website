<?

	adminCore::checkAccess('ADS');

	$actions = array(
		'systemProblemsAdmin',
		'addSystemProblem',
		'saveSystemProblem',
		'editSystemProblem',
		'updateSystemProblem'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		systemProblemsAdmin();
	}

	/**
	 *  Show the system problems
	 *  Args: none
	 *  Return: none
	 */
	function systemProblemsAdmin() {
		$controller = new systemProblemsController;
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' System Problems Admin');
		$template->assignClean('systems', systemsController::getSystems());
		$template->assignClean('records', $controller->performSearch());
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/systemProblemsAdmin.htm');	
	} // function systemProblemsAdmin

	/**
	 *  Add system problem
	 *  Args: none
	 *  Return: none
	 */
	function addSystemProblem() {
		$systemProblem = new systemProblem;
		$controller = new systemProblemsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' System Problems Admin');
		$template->assignClean('systems', systemsController::getSystems());
		$template->assignClean('systemProblem', $systemProblem->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/systemProblemEdit.htm');
	} // function addSystemProblem

	/**
	 *  Save a new system problem
	 *  Args: none
	 *  Return: none
	 */
	function saveSystemProblem() {
		$systemProblem = new systemProblem;
		$systemProblem->set('systemID', getPost('systemID'));
		$systemProblem->set('name', getPost('name'));
		$systemProblem->set('cost', getPost('cost'));
		$systemProblem->set('status', getPost('status'));
		if ($systemProblem->save()) {
			addSuccess('System Problem saved successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editSystemProblem($systemProblem->get('systemProblemID'));
			} else {
				addSystemProblem();
			}
			exit;
		}
		addError('An error occurred while saving the system problem');
		$systemProblem->updateSystemMessages();
		$controller = new systemProblemsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' System Problems Admin');
		$template->assignClean('systems', systemsController::getSystems());
		$template->assignClean('systemProblem', $systemProblem->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/systemProblemEdit.htm');
	} // function saveSystemProblem

	/**
	 *  Edit system problem
	 *  Args: (int) system problem id
	 *  Return: none
	 */
	function editSystemProblem($systemProblemID = false) {
		if (!$systemProblemID) {
			$systemProblemID = getRequest('systemProblemID', 'integer');
		}
		$systemProblem = new systemProblem($systemProblemID);
		if ($systemProblem->exists()) {
			$controller = new systemProblemsController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' System Problems Admin');
			$template->assignClean('systems', systemsController::getSystems());
			$template->assignClean('systemProblem', $systemProblem->fetchArray());
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/systemProblemEdit.htm');
		} else {
			addError('System Problem does not exist');
			systemProblemsAdmin();
		}
	} // function editSystemProblem

	/**
	 *  Update an existing system problem record
	 *  Args: none
	 *  Return: none
	 */
	function updateSystemProblem() {
		$systemProblem = new system(getRequest('systemProblemID', 'integer'));
		if ($systemProblem->exists()) {
			$systemProblem->set('systemID', getPost('systemID'));
			$systemProblem->set('name', getPost('name'));
			$systemProblem->set('cost', getPost('cost'));
			$systemProblem->set('status', getPost('status'));
			if ($systemProblem->update()) {
				addSuccess('System Problem updated successfully');
				editSystemProblem($systemProblem->get('systemProblemID'));
			} else {
				addError('An error occurred while updating the system problem');
				$systemProblem->updateSystemMessages();
				$controller = new systemProblemsController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' System Problems Admin');
				$template->assignClean('systems', systemsController::getSystems());
				$template->assignClean('systemProblem', $systemProblem->fetchArray());
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/systemProblemEdit.htm');
			}
		} else {
			addError('System Problem does not exist');
			systemProblemsAdmin();
		}
	} // function updateSystemProblem

?>
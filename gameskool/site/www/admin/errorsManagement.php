<?

	adminCore::checkAccess('DEVELOPER');

	$actions = array(
		'errorsAdmin',
		'editError',
		'updateError'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		errorsAdmin();
	}

	/**
	 *  Show the errors admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function errorsAdmin() {
		$controller = new errorsController;
		$controller->setDefaultSearch('date', array(date('Y-m-d'), date('Y-m-d', strtotime('tomorrow'))));
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Errors');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/errorsAdmin.htm');	
	} // function errorsAdmin

	/**
	 *  Edit error section
	 *  Args: (int) error id
	 *  Return: none
	 */
	function editError($errorID = false) {
		if (!$errorID) {
			$errorID = getRequest('errorID', 'integer');
		}
		$error = new error($errorID);
		if ($error->exists()) {
			$controller = new errorsController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Errors');
			$template->assignClean('error', $error->fetchArray());
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/errorEdit.htm');
		} else {
			addError('Error record not found');
			errorsAdmin();
		}
	} // function editError

	/**
	 *  Update an existing error record
	 *  Args: none
	 *  Return: none
	 */
	function updateError() {
		$error = new error(getRequest('errorID', 'integer'));
		if ($error->exists()) {
			$error->set('status', getPost('status'));
			$error->set('comments', getPost('comments'));
			if ($error->update()) {
				addSuccess('Error record updated');
				editError($error->get('errorID'));
			} else {
				addError('An error occurred while updating the error record');
				$error->updateSystemMessages();
				$controller = new errorController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Errors');
				$template->assignClean('error', $error->fetchArray());
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/errorEdit.htm');
			}
		} else {
			addError('Error record not found');
			errorsAdmin();
		}
	} // function updateError

?>
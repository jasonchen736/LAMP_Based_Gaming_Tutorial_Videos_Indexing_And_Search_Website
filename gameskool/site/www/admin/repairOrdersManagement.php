<?

	adminCore::checkAccess('REPAIRS');

	$actions = array(
		'repairOrdersAdmin',
		'addRepairOrder',
		'saveRepairOrder',
		'editRepairOrder',
		'updateRepairOrder'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		repairOrdersAdmin();
	}

	/**
	 *  Show the repair orders admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function repairOrdersAdmin() {
		$controller = new repairOrdersController;
		$controller->setDefaultSearch('orderDate', array(date('Y-m-d', strtotime('-1 month')), date('Y-m-d', strtotime('tomorrow'))));
		$userTypes = postsController::$userTypes;
		$userTypeOptions = array(
			'' => ''
		);
		foreach ($userTypes as $userType => $typeValue) {
			$userTypeOptions[strtoupper($userType)] = ucfirst($userType);
		}
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Orders Admin');
		$template->assignClean('userTypes', $userTypeOptions);
		$template->assignClean('records', $controller->performSearch());
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('consoles', systemsController::getSystems());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/repairOrdersAdmin.htm');	
	} // function repairOrdersAdmin

	/**
	 *  Add repair order section
	 *  Args: none
	 *  Return: none
	 */
	function addRepairOrder() {
		$repairOrder = new repairOrder;
		$controller = new repairOrdersController;
		$template = new template;
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/admin/repairOrder.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Orders Admin');
		$template->assignClean('repairOrder', $repairOrder->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('contactOptions', $controller->getOptions('contact'));
		$template->assignClean('receiveMethods', $controller->getOptions('receiveMethod'));
		$template->assignClean('returnMethods', $controller->getOptions('returnMethod'));
		$template->assignClean('consoles', systemsController::getSystems());
		$template->assignClean('states', statesController::getStates());
		$template->assignClean('countries', countriesController::getCountries());
		$template->assignClean('systemProblems', systemProblemsController::getSystemProblems());
		$template->assignClean('comments', array());
		$template->assignClean('comment', '');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->assignClean('fullAccess', true);
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/repairOrderEdit.htm');
	} // function addRepairOrder

	/**
	 *  Save a new repair order record
	 *  Args: none
	 *  Return: none
	 */
	function saveRepairOrder() {
		$repairOrder = new repairOrder;
		$repairOrder->set('console', getPost('console'));
		$repairOrder->set('serial', getPost('serial'));
		$repairOrder->set('systemProblemID', getPost('systemProblemID'));
		$repairOrder->set('description', getPost('description'));
		$repairOrder->set('status', getPost('status'));
		$repairOrder->set('receiveMethod', getPost('receiveMethod'));
		$repairOrder->set('returnMethod', getPost('returnMethod'));
		$repairOrder->set('first', getPost('first'));
		$repairOrder->set('last', getPost('last'));
		$repairOrder->set('email', getPost('email'));
		$repairOrder->set('phone', getPost('phone'));
		$repairOrder->set('address1', getPost('address1'));
		$repairOrder->set('address2', getPost('address2'));
		$repairOrder->set('city', getPost('city'));
		$repairOrder->set('postal', getPost('postal'));
		$country = getPost('country');
		if ($country == 'USA') {
			$repairOrder->set('state', getPost('state'));
		} else {
			$repairOrder->set('state', getPost('province'));
		}
		$repairOrder->set('country', $country);
		$repairOrder->set('cost', getPost('cost'));
		$repairOrder->set('contact', getPost('contact'));
		if ($repairOrder->save()) {
			addSuccess('Repair order saved successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editRepairOrder($repairOrder->get('repairOrderID'));
			} else {
				addRepairOrder();
			}
			exit;
		}
		addError('An error occurred while saving the repair order');
		$repairOrder->updateSystemMessages();
		$controller = new repairOrdersController;
		$template = new template;
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/admin/repairOrder.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Orders Admin');
		$template->assignClean('repairOrder', $repairOrder->fetchArray());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('contactOptions', $controller->getOptions('contact'));
		$template->assignClean('receiveMethods', $controller->getOptions('receiveMethod'));
		$template->assignClean('returnMethods', $controller->getOptions('returnMethod'));
		$template->assignClean('consoles', systemsController::getSystems());
		$template->assignClean('states', statesController::getStates());
		$template->assignClean('countries', countriesController::getCountries());
		$template->assignClean('systemProblems', systemProblemsController::getSystemProblems());
		$template->assignClean('comments', array());
		$template->assignClean('comment', '');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->assignClean('fullAccess', true);
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/repairOrderEdit.htm');
	} // function saveRepairOrder

	/**
	 *  Edit repair order section
	 *  Args: (int) repair order id
	 *  Return: none
	 */
	function editRepairOrder($repairOrderID = false) {
		if (!$repairOrderID) {
			$repairOrderID = getRequest('repairOrderID', 'integer');
		}
		$repairOrder = new repairOrder($repairOrderID);
		if ($repairOrder->exists()) {
			switch ($repairOrder->get('user')) {
				case 'USER':
					$user = new user($repairOrder->get('userID'));
					$userName = $user->get('userName');
					break;
				case 'ADMIN':
					$user = new adminUser($repairOrder->get('userID'));
					$userName = $user->get('login');
					break;
				default:
					$userName = 'not found';
					break;
			}
			$controller = new repairOrdersController;
			$template = new template;
			$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/admin/repairOrder.js');
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Orders Admin');
			$template->assignClean('repairOrder', $repairOrder->fetchArray());
			$template->assignClean('userName', $userName);
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('statusOptions', getStatusOptions($repairOrder));
			$template->assignClean('contactOptions', $controller->getOptions('contact'));
			$template->assignClean('receiveMethods', $controller->getOptions('receiveMethod'));
			$template->assignClean('returnMethods', $controller->getOptions('returnMethod'));
			$template->assignClean('consoles', systemsController::getSystems());
			$template->assignClean('states', statesController::getStates());
			$template->assignClean('countries', countriesController::getCountries());
			$template->assignClean('systemProblems', systemProblemsController::getSystemProblems());
			$template->assignClean('comments', $repairOrder->getComments());
			$template->assignClean('comment', '');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->assignClean('fullAccess', hasFullAccess($repairOrder));
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/repairOrderEdit.htm');
		} else {
			addError('Repair Order does not exist');
			repairOrdersAdmin();
		}
	} // function editRepairOrder

	/**
	 *  Update an existing repair order record
	 *  Args: none
	 *  Return: none
	 */
	function updateRepairOrder() {
		$repairOrder = new repairOrder(getRequest('repairOrderID', 'integer'));
		if ($repairOrder->exists()) {
			$fullAccess = hasFullAccess($repairOrder);
			$repairOrder->set('console', getPost('console'));
			$repairOrder->set('serial', getPost('serial'));
			$repairOrder->set('systemProblemID', getPost('systemProblemID'));
			$repairOrder->set('description', getPost('description'));
			$repairOrder->set('status', getPost('status'));
			$repairOrder->set('receiveMethod', getPost('receiveMethod'));
			$repairOrder->set('first', getPost('first'));
			$repairOrder->set('last', getPost('last'));
			$repairOrder->set('email', getPost('email'));
			$repairOrder->set('phone', getPost('phone'));
			$repairOrder->set('cost', getPost('cost'));
			$repairOrder->set('contact', getPost('contact'));
			if ($fullAccess) {
				$repairOrder->set('returnMethod', getPost('returnMethod'));
				$repairOrder->set('address1', getPost('address1'));
				$repairOrder->set('address2', getPost('address2'));
				$repairOrder->set('city', getPost('city'));
				$repairOrder->set('postal', getPost('postal'));
				$country = getPost('country');
				if ($country == 'USA') {
					$repairOrder->set('state', getPost('state'));
				} else {
					$repairOrder->set('state', getPost('province'));
				}
				$repairOrder->set('country', $country);
			}
			if ($comment = getPost('comment')) {
				$repairOrderComment = new repairOrderComment;
				$repairOrderComment->set('comment', $comment);
				$repairOrderComment->set('repairOrderID', $repairOrder->get('repairOrderID'));
			} else {
				$repairOrderComment = false;
			}
			if ($repairOrder->update()) {
				addSuccess('Repair order updated successfully');
				if ($repairOrderComment !== false && !$repairOrderComment->save()) {
					addError('An error occurred while saving a comment');
					$repairOrderComment->updateSystemMessages();
				}
				redirect('/admin/repairOrdersManagement/repairOrderID/'.$repairOrder->get('repairOrderID').'/action/editRepairOrder/propertyMenuItem/'.getRequest('propertyMenuItem'));
			} else {
				addError('An error occurred while updating the repair order');
				$repairOrder->updateSystemMessages();
				switch ($repairOrder->get('user')) {
					case 'USER':
						$user = new user($repairOrder->get('userID'));
						$userName = $user->get('userName');
						break;
					case 'ADMIN':
						$user = new adminUser($repairOrder->get('userID'));
						$userName = $user->get('login');
						break;
					default:
						$userName = 'not found';
						break;
				}
				$controller = new repairOrdersController;
				$template = new template;
				$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/admin/repairOrder.js');
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Repair Orders Admin');
				$template->assignClean('repairOrder', $repairOrder->fetchArray());
				$template->assignClean('userName', $userName);
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('statusOptions', getStatusOptions($repairOrder));
				$template->assignClean('contactOptions', $controller->getOptions('contact'));
				$template->assignClean('receiveMethods', $controller->getOptions('receiveMethod'));
				$template->assignClean('returnMethods', $controller->getOptions('returnMethod'));
				$template->assignClean('consoles', systemsController::getSystems());
				$template->assignClean('states', statesController::getStates());
				$template->assignClean('countries', countriesController::getCountries());
				$template->assignClean('systemProblems', systemProblemsController::getSystemProblems());
				$template->assignClean('comments', $repairOrder->getComments());
				$template->assignClean('comment', $comment);
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->assignClean('fullAccess', hasFullAccess($repairOrder));
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/repairOrderEdit.htm');
			}
		} else {
			addError('Repair order does not exist');
			repairOrdersAdmin();
		}
	} // function updateRepairOrder

	/**
	 *  Check whether user has full access to order data
	 *  Args: (object) repair order
	 *  Return: (boolean) has access
	 */
	function hasFullAccess($repairOrder) {
		return adminCore::hasAccess('SUPERADMIN') || !isset(repairOrdersController::$statuses['open'][$repairOrder->get('status')]);
	} // function hasFullAccess

	/**
	 *  Get available status options
	 *  Args: (object) repair order
	 *  Return: (array) status options
	 */
	function getStatusOptions($repairOrder) {
		$controller = new repairOrdersController;
		$statuses = $controller->getOptions('status');
		$status = $repairOrder->get('status');
		if (adminCore::hasAccess('SUPERADMIN')) {
			return $statuses;
		} else {
			if (isset(repairOrdersController::$statuses['open'][$status])) {
				return $statuses;
			} elseif (isset(repairOrdersController::$statuses['completed'][$status])) {
				$available = repairOrdersController::$statuses['completed'];
			} else {
				$available = repairOrdersController::$statuses['cancelled'];
			}
			foreach ($statuses as $currentStatus) {
				if (!isset($available[$currentStatus])) {
					unset($statuses[$currentStatus]);
				}
			}
			return $statuses;
		}
	} // function getStatusOptions

?>

<?

	adminCore::checkAccess('ADS');

	$actions = array(
		'adsAdmin',
		'addAd',
		'saveAd',
		'editAd',
		'updateAd'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		adsAdmin();
	}

	/**
	 *  Show the ads admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function adsAdmin() {
		$controller = new adsController;
		$userTypes = postsController::$userTypes;
		$userTypeOptions = array(
			'' => ''
		);
		foreach ($userTypes as $userType => $typeValue) {
			$userTypeOptions[strtoupper($userType)] = ucfirst($userType);
		}
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Ads Admin');
		$template->assignClean('userTypes', $userTypeOptions);
		$template->assignClean('records', $controller->performSearch());
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adsAdmin.htm');	
	} // function adsAdmin

	/**
	 *  Add ad section
	 *  Args: none
	 *  Return: none
	 */
	function addAd() {
		$ad = new ad;
		$controller = new adsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Ads Admin');
		$template->assignClean('ad', $ad->fetchArray());
		$template->assignClean('locationOptions', $controller->getOptions('location'));
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adEdit.htm');
	} // function addAd

	/**
	 *  Save a new ad record
	 *  Args: none
	 *  Return: none
	 */
	function saveAd() {
		$ad = new ad;
		$ad->set('name', getPost('name'));
		$ad->set('location', getPost('location'));
		$ad->set('url', getPost('url'));
		$ad->set('content', getPost('content'));
		$ad->set('status', getPost('status'));
		if ($ad->save()) {
			addSuccess('Ad saved successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editAd($ad->get('adID'));
			} else {
				addAd();
			}
			exit;
		}
		addError('An error occurred while saving the ad');
		$ad->updateSystemMessages();
		$controller = new adsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Ads Admin');
		$template->assignClean('ad', $ad->fetchArray());
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adEdit.htm');
	} // function saveAd

	/**
	 *  Edit ad section
	 *  Args: (int) ad id
	 *  Return: none
	 */
	function editAd($adID = false) {
		if (!$adID) {
			$adID = getRequest('adID', 'integer');
		}
		$ad = new ad($adID);
		if ($ad->exists()) {
			switch ($ad->get('poster')) {
				case 'USER':
					$user = new user($ad->get('posterID'));
					$userName = $user->get('userName');
					break;
				case 'ADMIN':
					$user = new adminUser($ad->get('posterID'));
					$userName = $user->get('login');
					break;
				default:
					$userName = 'not found';
					break;
			}
			$controller = new adsController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Ads Admin');
			$template->assignClean('ad', $ad->fetchArray());
			$template->assignClean('userName', $userName);
			$template->assignClean('locationOptions', $controller->getOptions('location'));
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/adEdit.htm');
		} else {
			addError('Ad does not exist');
			adsAdmin();
		}
	} // function editAd

	/**
	 *  Update an existing ad record
	 *  Args: none
	 *  Return: none
	 */
	function updateAd() {
		$ad = new ad(getRequest('adID', 'integer'));
		if ($ad->exists()) {
			$ad->set('name', getPost('name'));
			$ad->set('location', getPost('location'));
			$ad->set('url', getPost('url'));
			$ad->set('content', getPost('content'));
			$ad->set('status', getPost('status'));
			if ($ad->update()) {
				addSuccess('Ad updated successfully');
				editAd($ad->get('adID'));
			} else {
				addError('An error occurred while updating the ad');
				$ad->updateSystemMessages();
				switch ($ad->get('poster')) {
					case 'USER':
						$user = new user($ad->get('posterID'));
						$userName = $user->get('userName');
						break;
					case 'ADMIN':
						$user = new adminUser($ad->get('posterID'));
						$userName = $user->get('login');
						break;
					default:
						$userName = 'not found';
						break;
				}
				$controller = new adsController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Ads Admin');
				$template->assignClean('ad', $ad->fetchArray());
				$template->assignClean('userName', $userName);
				$template->assignClean('locationOptions', $controller->getOptions('location'));
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/adEdit.htm');
			}
		} else {
			addError('Ad does not exist');
			adsAdmin();
		}
	} // function updateAd

?>

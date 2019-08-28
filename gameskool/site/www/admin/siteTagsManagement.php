<?

	adminCore::checkAccess('STATS');

	$actions = array(
		'siteTagsAdmin',
		'addSiteTag',
		'saveSiteTag',
		'editSiteTag',
		'updateSiteTag'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		siteTagsAdmin();
	}

	/**
	 *  Show the site tags admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function siteTagsAdmin() {
		$controller = new siteTagsController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Tags Admin');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/siteTagsAdmin.htm');	
	} // function siteTagsAdmin

	/**
	 *  Add site tag section
	 *  Args: none
	 *  Return: none
	 */
	function addSiteTag() {
		$siteTag = new siteTag;
		$controller = new siteTagsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Tags Admin');
		$template->assignClean('siteTag', $siteTag->fetchArray());
		$template->assignClean('placementOptions', $controller->getOptions('placement'));
		$template->assignClean('matchTypeOptions', $controller->getOptions('matchType'));
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/siteTagEdit.htm');
	} // function addSiteTag

	/**
	 *  Save a new site tag record
	 *  Args: none
	 *  Return: none
	 */
	function saveSiteTag() {
		$siteTag = new siteTag;
		$siteTag->set('referrer', getPost('referrer'));
		$siteTag->set('description', getPost('description'));
		$siteTag->set('matchType', getPost('matchType'));
		$siteTag->set('matchValue', getPost('matchValue'));
		$siteTag->set('placement', getPost('placement'));
		$siteTag->set('weight', getPost('weight'));
		$siteTag->set('HTTP', getPost('HTTP'));
		$siteTag->set('HTTPS', getPost('HTTPS'));
		$siteTag->set('status', getPost('status'));
		if ($siteTag->save()) {
			addSuccess('Site tag added successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editSiteTag($siteTag->get('siteTagID'));
			} else {
				addSiteTag();
			}
			exit;
		} else {
			addError('An error occurred while saving the site tag');
			$siteTag->updateSystemMessages();
		}
		$controller = new siteTagsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Tags Admin');
		$template->assignClean('siteTag', $siteTag->fetchArray());
		$template->assignClean('placementOptions', $controller->getOptions('placement'));
		$template->assignClean('matchTypeOptions', $controller->getOptions('matchType'));
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/siteTagEdit.htm');
	} // function saveSiteTag

	/**
	 *  Edit site tag section
	 *  Args: (int) site tag id
	 *  Return: none
	 */
	function editSiteTag($siteTagID = false) {
		if (!$siteTagID) {
			$siteTagID = getRequest('siteTagID', 'integer');
		}
		$siteTag = new siteTag($siteTagID);
		if ($siteTag->exists()) {
			$controller = new siteTagsController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Tags Admin');
			$template->assignClean('siteTag', $siteTag->fetchArray());
			$template->assignClean('placementOptions', $controller->getOptions('placement'));
			$template->assignClean('matchTypeOptions', $controller->getOptions('matchType'));
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/siteTagEdit.htm');
		} else {
			addError('The site tag does not exist');
			siteTagsAdmin();
		}
	} // function editSiteTag

	/**
	 *  Update an existing site tag record
	 *  Args: none
	 *  Return: none
	 */
	function updateSiteTag() {
		$siteTag = new siteTag(getRequest('siteTagID', 'integer'));
		if ($siteTag->exists()) {
			$siteTag->set('referrer', getPost('referrer'));
			$siteTag->set('description', getPost('description'));
			$siteTag->set('matchType', getPost('matchType'));
			$siteTag->set('matchValue', getPost('matchValue'));
			$siteTag->set('placement', getPost('placement'));
			$siteTag->set('weight', getPost('weight'));
			$siteTag->set('HTTP', getPost('HTTP'));
			$siteTag->set('HTTPS', getPost('HTTPS'));
			$siteTag->set('status', getPost('status'));
			if ($siteTag->update()) {
				addSuccess('Site tag updated successfully');
				editSiteTag($siteTag->get('siteTagID'));
			} else {
				addError('An error occurred while updating the site tag');
				$siteTag->updateSystemMessages();
				$controller = new siteTagsController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Site Tags Admin');
				$template->assignClean('siteTag', $siteTag->fetchArray());
				$template->assignClean('placementOptions', $controller->getOptions('placement'));
				$template->assignClean('matchTypeOptions', $controller->getOptions('matchType'));
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/siteTagEdit.htm');
			}
		} else {
			addError('Site tag does not exist');
			siteTagsAdmin();
		}
	} // function updateSiteTag

?>
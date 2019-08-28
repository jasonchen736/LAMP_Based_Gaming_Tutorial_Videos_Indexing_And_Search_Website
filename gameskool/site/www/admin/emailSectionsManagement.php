<?

	adminCore::checkAccess('EMAIL');

	$actions = array(
		'emailSectionsAdmin',
		'addEmailSection',
		'saveEmailSection',
		'editEmailSection',
		'updateEmailSection'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		emailSectionsAdmin();
	}

	/**
	 *  Show the email sections admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function emailSectionsAdmin() {
		$controller = new emailSectionsController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Email Sections Admin');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/emailSectionsAdmin.htm');	
	} // function emailSectionsAdmin

	/**
	 *  Add email section
	 *  Args: none
	 *  Return: none
	 */
	function addEmailSection() {
		$section = new emailSection;
		$controller = new emailSectionsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Email Sections Admin');
		$template->assignClean('section', $section->fetchArray());
		$template->assignClean('typeOptions', $controller->getOptions('type'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/emailSectionEdit.htm');
	} // function addEmailSection

	/**
	 *  Save a new email section record
	 *  Args: none
	 *  Return: none
	 */
	function saveEmailSection() {
		$section = new emailSection;
		$section->set('type', getPost('type'));
		$section->set('name', getPost('name'));
		$section->set('html', getPost('html'));
		$section->set('text', getPost('text'));
		if ($section->save()) {
			addSuccess('Email section added successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editEmailSection($section->get('emailSectionID'));
			} else {
				addEmailSection();
			}
			exit;
		} else {
			addError('An error occurred while saving the email section');
			$section->updateSystemMessages();
		}
		$controller = new emailSectionsController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Email Sections Admin');
		$template->assignClean('section', $section->fetchArray());
		$template->assignClean('typeOptions', $controller->getOptions('type'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/emailSectionEdit.htm');
	} // function saveEmailSection

	/**
	 *  Edit email section
	 *  Args: (int) email section id
	 *  Return: none
	 */
	function editEmailSection($emailSectionID = false) {
		if (!$emailSectionID) {
			$emailSectionID = getRequest('emailSectionID', 'integer');
		}
		$section = new emailSection($emailSectionID);
		if ($section->exists()) {
			$controller = new emailSectionsController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Email Sections Admin');
			$template->assignClean('section', $section->fetchArray());
			$template->assignClean('typeOptions', $controller->getOptions('type'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/emailSectionEdit.htm');
		} else {
			addError('The email section does not exist');
			emailSectionsAdmin();
		}
	} // function editEmailSection

	/**
	 *  Update an existing email section record
	 *  Args: none
	 *  Return: none
	 */
	function updateEmailSection() {
		$section = new emailSection(getRequest('emailSectionID', 'integer'));
		if ($section->exists()) {
			$section->set('type', getPost('type'));
			$section->set('name', getPost('name'));
			$section->set('html', getPost('html'));
			$section->set('text', getPost('text'));
			if ($section->update()) {
				addSuccess('Email updated successfully');
				editEmailSection($section->get('emailSectionID'));
			} else {
				addError('An error occurred while updating the email section');
				$section->updateSystemMessages();
				$controller = new emailSectionsController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Email Sections Admin');
				$template->assignClean('section', $section->fetchArray());
				$template->assignClean('typeOptions', $controller->getOptions('type'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/emailSectionEdit.htm');
			}
		} else {
			addError('Email section does not exist');
			emailSectionsAdmin();
		}
	} // function updateEmailSection

?>
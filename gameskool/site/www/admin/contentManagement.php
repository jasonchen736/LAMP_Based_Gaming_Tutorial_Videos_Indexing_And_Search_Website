<?

	adminCore::checkAccess('CONTENT');

	$actions = array(
		'contentAdmin',
		'addContent',
		'saveContent',
		'editContent',
		'updateContent'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		contentAdmin();
	}

	/**
	 *  Show the content admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function contentAdmin() {
		$controller = new contentController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Admin');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/contentAdmin.htm');	
	} // function contentAdmin

	/**
	 *  Add content section
	 *  Args: none
	 *  Return: none
	 */
	function addContent() {
		$content = new content;
		$controller = new contentController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Admin');
		$template->assignClean('content', $content->fetchArray());
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/contentEdit.htm');
	} // function addContent

	/**
	 *  Save a new content record
	 *  Args: none
	 *  Return: none
	 */
	function saveContent() {
		$content = new content;
		$content->set('name', getPost('name'));
		$content->set('content', getPost('content'));
		$content->set('metaDescription', getPost('metaDescription'));
		$content->set('metaKeywords', getPost('metaKeywords'));
		$content->set('status', getPost('status'));
		if ($content->save()) {
			addSuccess('Content article saved successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editContent($content->get('contentID'));
			} else {
				addContent();
			}
			exit;
		} else {
			addError('An error occurred while saving the content article');
			$content->updateSystemMessages();
		}
		$controller = new contentController;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Admin');
		$template->assignClean('content', $content->fetchArray());
		$template->assignClean('statusOptions', $controller->getOptions('status'));
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/contentEdit.htm');
	} // function saveContent

	/**
	 *  Edit content section
	 *  Args: (int) content id
	 *  Return: none
	 */
	function editContent($contentID = false) {
		if (!$contentID) {
			$contentID = getRequest('contentID', 'integer');
		}
		$content = new content($contentID);
		if ($content->exists()) {
			$controller = new contentController;
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Admin');
			$template->assignClean('content', $content->fetchArray());
			$template->assignClean('statusOptions', $controller->getOptions('status'));
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/contentEdit.htm');
		} else {
			addError('Content article does not exist');
			contentAdmin();
		}
	} // function editContent

	/**
	 *  Update an existing content record
	 *  Args: none
	 *  Return: none
	 */
	function updateContent() {
		$content = new content(getRequest('contentID', 'integer'));
		if ($content->exists()) {
			$content->set('name', getPost('name'));
			$content->set('content', getPost('content'));
			$content->set('metaDescription', getPost('metaDescription'));
			$content->set('metaKeywords', getPost('metaKeywords'));
			$content->set('status', getPost('status'));
			if ($content->update()) {
				addSuccess('Content article updated successfully');
				editContent($content->get('contentID'));
			} else {
				addError('An error occurred while updating the content article');
				$content->updateSystemMessages();
				$controller = new contentController;
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Admin');
				$template->assignClean('content', $content->fetchArray());
				$template->assignClean('statusOptions', $controller->getOptions('status'));
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/contentEdit.htm');
			}
		} else {
			addError('Content article does not exist');
			contentAdmin();
		}
	} // function updateContent

?>
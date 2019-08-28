<?

	adminCore::checkAccess('SUPERADMIN');

	$actions = array(
		'adminGroupsAdmin',
		'addGroup',
		'saveGroup',
		'editGroup',
		'updateGroup',
		'removeGroup'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		adminGroupsAdmin();
	}

	/**
	 *  Show the admin groups admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function adminGroupsAdmin() {
		$controller = new adminGroupsController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Groups');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adminGroupsAdmin.htm');	
	} // function adminGroupsAdmin

	/**
	 *  Add group section
	 *  Args: none
	 *  Return: none
	 */
	function addGroup() {
		$adminGroup = new adminGroup;
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Groups');
		$template->assignClean('adminGroup', $adminGroup->fetchArray());
		$template->assignClean('accessSections', adminUser::$accessSections);
		$template->assignClean('groupAccess', $adminGroup->getAccess());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adminGroupEdit.htm');
	} // function addGroup

	/**
	 *  Save a new group record
	 *  Args: none
	 *  Return: none
	 */
	function saveGroup() {
		$adminGroup = new adminGroup;
		$adminGroup->set('name', getPost('name'));
		if ($adminGroup->save()) {
			addSuccess('Group saved successfully');
			$access = getPost('access');
			$adminGroup->setAccess($access);
			if (haveErrors() || getRequest('submit') == 'Add and Edit') {
				editGroup($adminGroup->get('adminGroupID'));
			} else {
				addGroup();
			}
			exit;
		} else {
			addError('An error occurred while saving the group');
			$adminGroup->updateSystemMessages();
		}
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Groups');
		$template->assignClean('adminGroup', $adminGroup->fetchArray());
		$template->assignClean('accessSections', adminUser::$accessSections);
		$template->assignClean('groupAccess', $adminGroup->getAccess());
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/adminGroupEdit.htm');
	} // function saveGroup

	/**
	 *  Edit admin group section
	 *  Args: (int) admin group id
	 *  Return: none
	 */
	function editGroup($adminGroupID = false) {
		if (!$adminGroupID) {
			$adminGroupID = getRequest('adminGroupID', 'integer');
		}
		$adminGroup = new adminGroup($adminGroupID);
		if ($adminGroup->exists()) {
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Groups');
			$template->assignClean('adminGroup', $adminGroup->fetchArray());
			$template->assignClean('accessSections', adminUser::$accessSections);
			$template->assignClean('groupAccess', $adminGroup->getAccess());
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/adminGroupEdit.htm');
		} else {
			addError('That group does not exist');
			adminGroupsAdmin();
		}
	} // function editGroup

	/**
	 *  Update an existing group record
	 *  Args: none
	 *  Return: none
	 */
	function updateGroup() {
		$adminGroup = new adminGroup(getRequest('adminGroupID', 'integer'));
		if ($adminGroup->exists()) {
			$adminGroup->set('name', getPost('name'));
			if ($adminGroup->update()) {
				addSuccess('Group updated successfully');
				$access = getPost('access');
				$adminGroup->setAccess($access);
				editGroup($adminGroup->get('adminGroupID'));
			} else {
				addError('An error occurred while updating the group');
				$adminGroup->updateSystemMessages();
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Groups');
				$template->assignClean('adminGroup', $adminGroup->fetchArray());
				$template->assignClean('accessSections', adminUser::$accessSections);
				$template->assignClean('groupAccess', $adminGroup->getAccess());
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/adminGroupEdit.htm');
			}
		} else {
			addError('That group does not exist');
			adminGroupsAdmin();
		}
	} // function updateGroup

	/**
	 *  Remove an existing group
	 *  Args: none
	 *  Return: none
	 */
	function removeGroup() {
		$adminGroupID = getRequest('adminGroupID', 'integer');
		if ($adminGroupID) {
			$adminGroup = new adminGroup($adminGroupID);
			if ($adminGroup->exists()) {
				$sql = "DELETE FROM `adminUserGroupMap` WHERE `adminGroupID` = '".$adminGroupID."'";
				$result = query($sql);
				if (!empty($result->sqlError)) {
					addError('There was a problem removing the group');
				} else {
					$sql = "DELETE FROM `adminGroupAccess` WHERE `adminGroupID` = '".$adminGroupID."'";
					$result = query($sql);
					if (!empty($result->sqlError)) {
						addError('There was a problem removing the group');
					} else {
						$sql = "DELETE FROM `adminGroups` WHERE `adminGroupID` = '".$adminGroupID."'";
						$result = query($sql);
						if (!empty($result->sqlError)) {
							addError('There was a problem removing the group');
						} else {
							addSuccess('Group removed successfully');
						}
					}
				}
				redirect('/admin/adminGroups');
			} else {
				addError('That group does not exist');
			}
		} else {
			addError('That group does not exist');
		}
		adminGroupsAdmin();
	} // function removeGroup

?>
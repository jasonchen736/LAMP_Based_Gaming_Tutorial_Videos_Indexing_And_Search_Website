<?

	adminCore::checkAccess('GENERAL');

	$admin = adminCore::getAdminUser();

	if ($admin['access']['STATS']) {
		require_once 'reports.php';
	} else {
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/home.htm');	
	}

?>
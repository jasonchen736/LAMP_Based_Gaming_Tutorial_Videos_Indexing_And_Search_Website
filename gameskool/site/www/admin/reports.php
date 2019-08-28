<?

	adminCore::checkAccess('STATS');

	$actions = array(
		'dashboard',
		'trafficSourceReport',
		'postStatistics'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		dashboard();
	}

	/**
	 *  Display overview of website sales
	 *  Args: none
	 *  Return: none
	 */
	function dashboard() {
		$startDate = getRequest('startDate');
		$startDate = $startDate ? dateToSql(urldecode($startDate)) : date('Y-m-d', strtotime('last month'));
		$endDate = getRequest('endDate');
		$endDate = $endDate ? dateToSql(urldecode($endDate)) : date('Y-m-d');
		websiteStatistics::initialize($startDate, $endDate);
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Admin Dashboard');
		$template->assignClean('trafficSources', websiteStatistics::getBestTrafficSources(10));
		$template->assignClean('topPosts', websiteStatistics::getTopPosts(10));
		$template->assignClean('startDate', $startDate);
		$template->assignClean('endDate', $endDate);
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/dashBoard.htm');
	} // function dashboard

	/**
	 *  Report of website traffic source hits
	 *  Args: none
	 *  Return: none
	 */
	function trafficSourceReport() {
		$controller = new trafficSourceHitsController;
		$controller->setDefaultSearch('date', array(date('Y-m-d'), date('Y-m-d', strtotime('tomorrow'))));
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Traffic Report');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/trafficSourceReport.htm');	
	} // function trafficSourceReport

	/**
	 *  Report of post staticstics
	 *  Args: none
	 *  Return: none
	 */
	function postStatistics() {
		$controller = new postStatisticsController;
		$controller->setDefaultSearch('date', array(date('Y-m-d'), date('Y-m-d', strtotime('tomorrow'))));
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Post Statistics Report');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents($recordsFound));
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/postStatistics.htm');	
	} // function postStatistics

?>
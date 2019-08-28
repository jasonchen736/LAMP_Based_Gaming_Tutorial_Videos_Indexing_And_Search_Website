<?

	// track landing
	tracker::trackLanding();

	switch(getRequest('code')) {
		// forbidden
		case '403':
			$errorMessage = 'access to this page is forbidden';
			break;
		// unauthorized access
		case '401':
			$errorMessage = 'unauthorized page access';
			break;
		// internal server error
		case '500':
			$errorMessage = 'the server encountered an unexpected condition which prevented it from fulfilling the request';
			break;
		// file not found
		case '404':
		default:
			$errorMessage = 'the page you are looking for does not exist';
			break;
	}
	addError($errorMessage);

	// initialize template
	$template = new template;
	$template->addMeta('<meta name="robots" content="noindex, nofollow" />', 'robots');
	$template->addMeta('<meta name="googlebot" content="noindex, nofollow" />', 'googlebot');
	$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
	$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
	$template->assignClean('_TITLE', systemSettings::get('SITENAME').' - Error');
	$template->assignClean('loggedin', userCore::isLoggedIn());
	$template->assignClean('user', userCore::getUser());
	$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
	$template->setSystemDataGateway();
	$template->getSystemMessages();
	$template->display('site/error.htm');

?>

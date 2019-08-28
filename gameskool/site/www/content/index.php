<?

	$article = getRequest('name', 'contentName');
	// track landing, if no additional reference, set to indicate content article landing
	if (!getRequest('eref')) {
		$_REQUEST['eref'] = 'content '.$article;
	}
	tracker::trackLanding();

	// check for valid content
	if ($article) {
		$result = query("SELECT `contentID`, `status` FROM `content` WHERE `name` = '".prep($article)."'");
		if ($result->rowCount > 0) {
			$row = $result->fetchRow();
			if ($row['status'] == 'disabled') {
				if (adminCore::isLoggedIn()) {
					$row['status'] = 'visible';
				}
			}
			if ($row['status'] != 'disabled') {
				$content = new content($row['contentID']);
				$metaDescription = $content->get('metaDescription');
				$metaKeywords = $content->get('metaKeywords');
				$template = new template;
				$template->registerContentResource();
				$contentBody = $template->fetch('content:'.$article);
				$template->assign('content', $contentBody);
				if ($metaDescription) {
					$template->addMeta('<meta name="description" content="'.$metaDescription.' | '.systemSettings::get('METADESCRIPTION').'" />', 'description');
				}
				if ($metaKeywords) {
					$template->addMeta('<meta name="keywords" content="'.$metaKeywords.', '.systemSettings::get('METAKEYWORDS').'" />', 'keywords');
				}
				$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
				$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
				$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
				$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' '.$article);
				$template->assign('ads', adsController::getAds('right column'));
				$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
				$template->assignClean('loggedin', userCore::isLoggedIn());
				$template->assignClean('user', userCore::getUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('site/content.htm');
			} else {
				redirect('/error/status/code/404');
			}
		} else {
			redirect('/error/status/code/404');
		}
	} else {
		redirect('/error/status/code/404');
	}

?>

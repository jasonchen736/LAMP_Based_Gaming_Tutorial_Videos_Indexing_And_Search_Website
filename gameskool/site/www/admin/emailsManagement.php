<?

	adminCore::checkAccess('EMAIL');

	$actions = array(
		'emailsAdmin',
		'addEmail',
		'saveEmail',
		'editEmail',
		'updateEmail',
		'preview',
		'sendEmail',
		'send'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		emailsAdmin();
	}

	/**
	 *  Show the emails admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function emailsAdmin() {
		$controller = new emailsController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Emails Admin');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/emailsAdmin.htm');	
	} // function emailsAdmin

	/**
	 *  Add email section
	 *  Args: none
	 *  Return: none
	 */
	function addEmail() {
		$email = new email;
		$controller = new emailsController;
		$headers = emailSectionsController::getSections('header');
		$headers[0] = 'None';
		ksort($headers);
		$footers = emailSectionsController::getSections('footer');
		$footers[0] = 'None';
		ksort($footers);
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Emails Admin');
		$template->assignClean('email', $email->fetchArray());
		$template->assignClean('headers', $headers);
		$template->assignClean('footers', $footers);
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/emailEdit.htm');
	} // function addEmail

	/**
	 *  Save a new email record
	 *  Args: none
	 *  Return: none
	 */
	function saveEmail() {
		$email = new email;
		$email->set('name', getPost('name'));
		$email->set('subject', getPost('subject'));
		$email->set('html', getPost('html'));
		$email->set('text', getPost('text'));
		$email->set('fromEmail', getPost('fromEmail'));
		$email->set('headerID', getPost('headerID'));
		$email->set('footerID', getPost('footerID'));
		if ($email->save()) {
			addSuccess('Email added successfully');
			if (getRequest('submit') == 'Add and Edit') {
				editEmail($email->get('emailID'));
			} else {
				addEmail();
			}
			exit;
		} else {
			addError('An error occurred while saving the email');
			$email->updateSystemMessages();
		}
		$controller = new emailsController;
		$headers = emailSectionsController::getSections('header');
		$headers[0] = 'None';
		ksort($headers);
		$footers = emailSectionsController::getSections('footer');
		$footers[0] = 'None';
		ksort($footers);
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Emails Admin');
		$template->assignClean('email', $email->fetchArray());
		$template->assignClean('headers', $headers);
		$template->assignClean('footers', $footers);
		$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
		$template->assignClean('mode', 'add');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/emailEdit.htm');
	} // function saveEmail

	/**
	 *  Edit email section
	 *  Args: (int) email id
	 *  Return: none
	 */
	function editEmail($emailID = false) {
		if (!$emailID) {
			$emailID = getRequest('emailID', 'integer');
		}
		$email = new email($emailID);
		if ($email->exists()) {
			$controller = new emailsController;
			$headers = emailSectionsController::getSections('header');
			$headers[0] = 'None';
			ksort($headers);
			$footers = emailSectionsController::getSections('footer');
			$footers[0] = 'None';
			ksort($footers);
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Emails Admin');
			$template->assignClean('email', $email->fetchArray());
			$template->assignClean('headers', $headers);
			$template->assignClean('footers', $footers);
			$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
			$template->assignClean('mode', 'edit');
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/emailEdit.htm');
		} else {
			addError('The email does not exist');
			emailsAdmin();
		}
	} // function editEmail

	/**
	 *  Update an existing email record
	 *  Args: none
	 *  Return: none
	 */
	function updateEmail() {
		$email = new email(getRequest('emailID', 'integer'));
		if ($email->exists()) {
			$email->set('name', getPost('name'));
			$email->set('subject', getPost('subject'));
			$email->set('html', getPost('html'));
			$email->set('text', getPost('text'));
			$email->set('fromEmail', getPost('fromEmail'));
			$email->set('headerID', getPost('headerID'));
			$email->set('footerID', getPost('footerID'));
			if ($email->update()) {
				addSuccess('Email updated successfully');
				editEmail($email->get('emailID'));
			} else {
				addError('An error occurred while updating the email');
				$email->updateSystemMessages();
				$controller = new emailsController;
				$headers = emailSectionsController::getSections('header');
				$headers[0] = 'None';
				ksort($headers);
				$footers = emailSectionsController::getSections('footer');
				$footers[0] = 'None';
				ksort($footers);
				$template = new template;
				$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Emails Admin');
				$template->assignClean('email', $email->fetchArray());
				$template->assignClean('headers', $headers);
				$template->assignClean('footers', $footers);
				$template->assignClean('propertyMenuItem', getRequest('propertyMenuItem'));
				$template->assignClean('mode', 'edit');
				$template->assignClean('admin', adminCore::getAdminUser());
				$template->setSystemDataGateway();
				$template->getSystemMessages();
				$template->display('admin/emailEdit.htm');
			}
		} else {
			addError('Email does not exist');
			emailsAdmin();
		}
	} // function updateEmail

	/**
	 *  Display an email preview
	 *  Args: none
	 *  Return: none
	 */
	function preview() {
		$id = getRequest('emailID', 'integer');
		$email = new email($id);
		headers::sendNoCacheHeaders();
		if ($email->exists()) {
			$template = new template;
			$mailer = $template->getMailer($email->get('name'));
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Preview Email: '.$email->get('name'));
			$template->assign('html', $mailer->get('html'));
			$template->assign('text', $mailer->get('text'));
			$template->setSystemDataGateway();
			$template->display('admin/emailPreview.htm');
		} else {
			echo 'Email not found';
		}
	} // function preview

	/**
	 *  Send email form and action
	 *  Args: none
	 *  Return: none
	 */
	function sendEmail() {
		$id = getRequest('emailID', 'integer');
		$email = new email($id);
		if ($email->exists()) {
			// retrieve smarty tags
			$subject = $email->get('subject');
			$html = $email->get('html');
			$text = $email->get('text');
			preg_match_all('/{\$([\w\d_\-\.]+)}/', $subject, $subjectTagsA);
			preg_match_all('/{\$([\w\d_\-\.]+)}/', $html, $htmlTagsA);
			preg_match_all('/{\$([\w\d_\-\.]+)}/', $text, $textTagsA);
			$headerID = $email->get('headerID');
			if ($headerID) {
				$emailHeader = new emailSection($headerID);
				$headerhtml = $emailHeader->get('html');
				$headertext = $emailHeader->get('text');
				preg_match_all('/{\$([\w\d_\-\.]+)}/', $headerhtml, $htmlHeaderTags);
				preg_match_all('/{\$([\w\d_\-\.]+)}/', $headertext, $textHeaderTags);
			} else {
				$htmlHeaderTags = array(1 => array());
				$textHeaderTags = array(1 => array());
			}
			$footerID = $email->get('footerID');
			if ($footerID) {
				$emailFooter = new emailSection($footerID);
				$footerhtml = $emailFooter->get('html');
				$footertext = $emailFooter->get('text');
				preg_match_all('/{\$([\w\d_\-\.]+)}/', $footerhtml, $htmlFooterTags);
				preg_match_all('/{\$([\w\d_\-\.]+)}/', $footertext, $textFooterTags);
			} else {
				$htmlFooterTags = array(1 => array());
				$textFooterTags = array(1 => array());
			}
			$template = new template;
			// retrieve mailer tags
			$mailer = $template->getMailer($email->get('name'));
			$subject = $mailer->get('subject');
			$html = $mailer->get('html');
			$text = $mailer->get('text');
			preg_match_all('/{([\w\d_\-\.]+)}/', $subject, $subjectTagsB);
			preg_match_all('/{([\w\d_\-\.]+)}/', $html, $htmlTagsB);
			preg_match_all('/{([\w\d_\-\.]+)}/', $text, $textTagsB);
			$emailTags = array_unique(array_merge($subjectTagsA[1], $htmlTagsA[1], $textTagsA[1], $subjectTagsB[1], $htmlTagsB[1], $textTagsB[1], $htmlHeaderTags[1], $textHeaderTags[1], $htmlFooterTags[1], $textFooterTags[1]));
			sort($emailTags);
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Send Email: '.$email->get('name'));
			$template->assignClean('email', $email->fetchArray());
			$template->assignClean('emailTags', $emailTags);
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/emailSend.htm');
		} else {
			addError('Email not found');
			emailsAdmin();
		}
	} // function sendEmail

	/**
	 *  Send email
	 *  Args: none
	 *  Return: none
	 */
	function send() {
		$id = getRequest('emailID', 'integer');
		$email = new email($id);
		if ($email->exists()) {
			$address = getPost('email', 'email');
			if (validEmail($address)) {
				// retrieve smarty tags
				$subject = $email->get('subject');
				$html = $email->get('html');
				$text = $email->get('text');
				preg_match_all('/{\$([\w\d_\-\.]+)}/', $subject, $subjectTagsA);
				preg_match_all('/{\$([\w\d_\-\.]+)}/', $html, $htmlTagsA);
				preg_match_all('/{\$([\w\d_\-\.]+)}/', $text, $textTagsA);
				$headerID = $email->get('headerID');
				if ($headerID) {
					$emailHeader = new emailSection($headerID);
					$headerhtml = $emailHeader->get('html');
					$headertext = $emailHeader->get('text');
					preg_match_all('/{\$([\w\d_\-\.]+)}/', $headerhtml, $htmlHeaderTags);
					preg_match_all('/{\$([\w\d_\-\.]+)}/', $headertext, $textHeaderTags);
				} else {
					$htmlHeaderTags = array(1 => array());
					$textHeaderTags = array(1 => array());
				}
				$footerID = $email->get('footerID');
				if ($footerID) {
					$emailFooter = new emailSection($footerID);
					$footerhtml = $emailFooter->get('html');
					$footertext = $emailFooter->get('text');
					preg_match_all('/{\$([\w\d_\-\.]+)}/', $footerhtml, $htmlFooterTags);
					preg_match_all('/{\$([\w\d_\-\.]+)}/', $footertext, $textFooterTags);
				} else {
					$htmlFooterTags = array(1 => array());
					$textFooterTags = array(1 => array());
				}
				$smartyTags = array_unique(array_merge($subjectTagsA[1], $htmlTagsA[1], $textTagsA[1], $htmlHeaderTags[1], $textHeaderTags[1], $htmlFooterTags[1], $textFooterTags[1]));
				$template = new template;
				// retrieve mailer tags
				$mailer = $template->getMailer($email->get('name'));
				$subject = $mailer->get('subject');
				$html = $mailer->get('html');
				$text = $mailer->get('text');
				preg_match_all('/{([\w\d_\-\.]+)}/', $subject, $subjectTagsB);
				preg_match_all('/{([\w\d_\-\.]+)}/', $html, $htmlTagsB);
				preg_match_all('/{([\w\d_\-\.]+)}/', $text, $textTagsB);
				$mailerTags = array_unique(array_merge($subjectTagsB[1], $htmlTagsB[1], $textTagsB[1]));
				// assign smarty tags
				$arrays = array();
				foreach ($smartyTags as $tag) {
					if (!preg_match('/\./', $tag)) {
						$template->assign($tag, getPost('#'.$tag));
					} else {
						$vars = explode('.', $tag);
						if (!isset($arrays[$vars[0]])) {
							$arrays[$vars[0]] = array();
						}
						$arrays[$vars[0]][$vars[1]] = getPost('#'.preg_replace('/\./', '#', $tag));
					}
				}
				if (!empty($arrays)) {
					foreach ($arrays as $key => $val) {
						$template->assign($key, $val);
					}
				}
				$mailer = $template->getMailer($email->get('name'));
				if ($mailer->composeMessage()) {
					$mailer->addRecipient($address);
					// assign mailer tags
					foreach ($mailerTags as $tag) {
						$mailer->assignTag($address, $tag, getPost('#'.preg_replace('/\./', '#', $tag)));
					}
					if ($mailer->send()) {
						addSuccess('Email sent');
					} else {
						addError('An error occurred while sending the email');
					}
				} else {
					addError('An error occurred while sending the email');
				}
			} else {
				addError('Invalid email address');
				addErrorField('email');
			}
			redirect('/admin/emailsManagement/action/sendEmail/emailID/'.$email->get('emailID'));
		} else {
			addError('Email not found');
			emailsAdmin();
		}
	} // function send

?>
<?

	adminCore::checkAccess('CONTENT');

	$actions = array(
		'contentImagesAdmin',
		'addContentImages',
		'uploadContentImages',
		'editContentImage',
		'renameContentImage',
		'replaceContentImage',
		'removeContentImage',
		'preview'
	);

	$action = getRequest('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		contentImagesAdmin();
	}

	/**
	 *  Show the content images admin, default action
	 *  Args: none
	 *  Return: none
	 */
	function contentImagesAdmin() {
		$controller = new contentImageIndexController;
		$records = $controller->performSearch();
		$recordsFound = $controller->countRecordsFound();
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Image Admin');
		$template->assignClean('records', $records);
		$template->assignClean('recordsFound', $recordsFound);
		$template->assignClean('search', $controller->getSearchValues());
		$template->assignClean('query', $controller->getQueryComponents());
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/contentImagesAdmin.htm');
	} // function contentImagesAdmin

	/**
	 *  Add content images section
	 *  Args: none
	 *  Return: none
	 */
	function addContentImages() {
		$template = new template;
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Image Admin');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/admin/imageEdit.js');
		$template->assignClean('admin', adminCore::getAdminUser());
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('admin/contentImagesAdd.htm');
	} // function addContentImages

	/**
	 *  Upload content images
	 *  Args: none
	 *  Return: none
	 */
	function uploadContentImages() {
		$images = getRequest('uploadName');
		foreach ($images as $key => $val) {
			$image = new contentImageIndex();
			$name = NULL;
			if ($val) {
				$name = $val;
			} elseif (isset($_FILES['uploadImage'.$key]['name'])) {
				$name = $_FILES['uploadImage'.$key]['name'];
			}
			// make filename jpg extension
			if (!preg_match('/\.jpg$/', $name)) {
				$name = preg_replace('/\.[^\.]*$/', '', $name);
				$name = $name.'.jpg';
			}
			$image->set('image', $name);
			if ($image->save('uploadImage'.$key)) {
				addSuccess($image->get('image').' has been uploaded successfully');
			} else {
				$image->updateSystemMessages();
			}
		}
		redirect('/admin/contentImageManagement');
	} // function uploadContentImages

	/**
	 *  Edit content section
	 *  Args: (int) image id
	 *  Return: none
	 */
	function editContentImage($imageID = false) {
		if (!$imageID) {
			$imageID = getRequest('imageID', 'integer');
		}
		$image = new contentImageIndex($imageID);
		if ($image->exists()) {
			$template = new template;
			$template->assignClean('_TITLE', systemSettings::get('SITENAME').' Content Image Admin');
			$template->assignClean('image', $image->fetchArray());
			$template->assignClean('admin', adminCore::getAdminUser());
			$template->setSystemDataGateway();
			$template->getSystemMessages();
			$template->display('admin/contentImageEdit.htm');
		} else {
			addError('Image does not exist');
			contentImagesAdmin();
		}
	} // function editContentImage

	/**
	 *  Rename a content image
	 *  Args: none
	 *  Return: none
	 */
	function renameContentImage() {
		$imageID = getRequest('imageID', 'integer');
		$image = new contentImageIndex($imageID);
		if ($image->exists()) {
			$name = getRequest('name', 'file');
			if ($name) {
				// make filename a jpg extension
				if (!preg_match('/\.jpg$/', $name)) {
					$name = preg_replace('/\.[^\.]*$/', '', $name);
					$name = $name.'.jpg';
				}
				$original = $image->get('image');
				if ($image->rename($name)) {
					addSuccess($original.' has been successfully renamed to '.$name);
				} else {
					$image->updateSystemMessages();
				}
			} else {
				addError('Invalid file name');
			}
			editContentImage($image->getID());
		} else {
			addError('Image not found');
			redirect('/admin/contentImageManagement');
		}
	} // function renameContentImage

	/**
	 *  Replace a content image file
	 *  Args: none
	 *  Return: none
	 */
	function replaceContentImage() {
		$imageID = getRequest('imageID', 'integer');
		$image = new contentImageIndex($imageID);
		if ($image->exists()) {
			if ($image->replace('image')) {
				addSuccess('Image replaced');
			} else {
				$image->updateSystemMessages();
			}
			editContentImage($image->getID());
		} else {
			addError('Image not found');
			redirect('/admin/contentImageManagement');
		}
	} // function replaceContentImage

	/**
	 *  Remove a content image record and file
	 *  Args: none
	 *  Return: none
	 */
	function removeContentImage() {
		$imageID = getRequest('imageID', 'integer');
		$image = new contentImageIndex($imageID);
		if ($image->exists()) {
			$name = $image->get('image');
			if ($image->delete()) {
				addSuccess($name.' has been successfully removed');
			} else {
				addError('An error occurred while attempting to remove '.$name);
			}
		} else {
			addError('Image not found');
		}
		redirect('/admin/contentImageManagement');
	} // function removeContentImage

	/**
	 *  Display a content image file
	 *  Args: none
	 *  Return: none
	 */
	function preview() {
		$imageID = getRequest('imageID', 'integer');
		$image = new contentImageIndex($imageID);
		headers::sendNoCacheHeaders();
		if ($image->exists()) {
			echo '<img src="/images/'.time().'/content/'.$image->get('image').'" />';
		} else {
			echo 'Image not found';
		}
	} // function preview

?>

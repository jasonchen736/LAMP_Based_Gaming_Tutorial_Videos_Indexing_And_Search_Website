<?

	headers::sendNoCacheHeaders();
	$postID = getPost('postID', 'integer');
	if (!empty($postID)) {
		postsController::addPageView($postID);
	}

?>

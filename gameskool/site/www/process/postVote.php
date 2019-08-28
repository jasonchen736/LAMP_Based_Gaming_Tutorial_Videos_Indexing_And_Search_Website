<?

	headers::sendNoCacheHeaders();
	$postID = getRequest('post', 'integer');
	if (!empty($postID)) {
		$vote = getRequest('vote', 'alpha');
		postsController::postVote($postID, $vote);
	}
	if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
		redirect(appendSearchParams($_SERVER['HTTP_REFERER']));
	} else {
		redirect(appendSearchParams('http://'.$_SERVER['HTTP_HOST']));
	}

?>

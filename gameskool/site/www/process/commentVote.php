<?

	headers::sendNoCacheHeaders();
	$postID = getRequest('post', 'integer');
	$commentID = getRequest('comment', 'integer');
	if (!empty($postID) && !empty($commentID)) {
		$vote = getRequest('vote', 'alpha');
		postsController::postCommentVote($postID, $commentID, $vote);
	}
	if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
		redirect(appendSearchParams($_SERVER['HTTP_REFERER']));
	} else {
		redirect(appendSearchParams('http://'.$_SERVER['HTTP_HOST']));
	}

?>

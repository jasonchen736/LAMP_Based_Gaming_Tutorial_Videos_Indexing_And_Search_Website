<?

	$actions = array(
		'showComments',
		'comment',
		'reply',
		'edit'
	);

	$action = getPost('action');
	if (in_array($action, $actions)) {
		$action();
	} else {
		showComments();
	}

	/**
	 *  Show post comments
	 *  Args: (array) post info
	 *  Return: none
	 */
	function showComments($post = false) {
		$game = getRequest('gameTitle', 'gameTitle');
		$title = getRequest('postTitle', 'postTitle');
		// track landing, if no additional reference, set to indicate post page landing
		if (!getRequest('eref')) {
			$_REQUEST['eref'] = 'comments for game '.$game.', post '.$title;
		}
		tracker::trackLanding();

		$comments = false;
		$commentOrder = false;
		$visibleComments = false;
		$totalComments = false;
		$start = false;
		$limit = false;
		$prev = false;
		$next = false;
		$embedSource = false;
		$content = false;
		if ($post === false) {
			$post = postsController::retrievePostData($game, $title);
			if (!empty($post)) {
				if ($post['status'] != 'active') {
					if (adminCore::isLoggedIn()) {
						$post['status'] = 'active';
					} else {
						$post = false;
					}
				}
			}
		}
		if (!empty($post)) {
			$content = $post['content'];
			unset($post['content']);
			$embedSource = postsController::getEmbedSource($post['source'], $post['identifier']);
			// check comment cache
			$sql = "SELECT `data`, `expired` FROM `postCommentsCache` WHERE `postID` = '".$post['postID']."'";
			$result = query($sql);
			$row = $result->fetchRow();
			if ($row['expired'] == 0) {
				$commentOrder = unserialize($row['data']);
				$sql = "SELECT `a`.*, IFNULL(`b`.`userName`, IFNULL(`c`.`name`, 'anonymous')) AS `posterName` 
						FROM `postComments".substr($post['postID'], -1, 1)."` `a` 
						LEFT JOIN `users` `b` ON (`a`.`poster` = 'USER' AND `a`.`posterID` = `b`.`userID`) 
						LEFT JOIN `adminUsers` `c` ON (`a`.`poster` = 'ADMIN' AND `a`.`posterID` = `c`.`adminUserID`) 
						WHERE `a`.`postID` = '".$post['postID']."'";
				$result = query($sql);
				if ($result->rowCount > 0) {
					$totalComments = $result->rowCount;
					// place comments into array, use comment id as key
					while ($row = $result->fetchRow()) {
						$row['indent'] = 0;
						$row['poster'] = strtolower($row['poster']);
						$comments[$row['postCommentID']] = $row;
					}
					// calculate indent value for each nesting layer
					foreach ($comments as $k => $v) {
						if ($v['parentCommentID'] > 0) {
							$comments[$k]['indent'] = $comments[$v['parentCommentID']]['indent'] + 1;
						}
					}
				} else {
					$totalComments = 0;
				}
			} else {
				// order clause used in conjunction with nesting order algorithm to obtain proper comment nesting order 
				$sql = "SELECT `a`.*, IFNULL(`b`.`userName`, IFNULL(`c`.`name`, 'anonymous')) AS `posterName` 
						FROM `postComments".substr($post['postID'], -1, 1)."` `a` 
						LEFT JOIN `users` `b` ON (`a`.`poster` = 'USER' AND `a`.`posterID` = `b`.`userID`) 
						LEFT JOIN `adminUsers` `c` ON (`a`.`poster` = 'ADMIN' AND `a`.`posterID` = `c`.`adminUserID`) 
						WHERE `a`.`postID` = '".$post['postID']."' 
						ORDER BY `a`.`parentCommentID` ASC, `a`.`totalVotes` ASC, `a`.`postCommentID` DESC";
				$result = query($sql);
				if ($result->rowCount > 0) {
					$totalComments = $result->rowCount;
					// place comments into array, use comment id as key
					while ($row = $result->fetchRow()) {
						$row['indent'] = 0;
						$row['poster'] = strtolower($row['poster']);
						$comments[$row['postCommentID']] = $row;
					}
					// calculate indent value for each nesting layer
					foreach ($comments as $k => $v) {
						if ($v['parentCommentID'] > 0) {
							$comments[$k]['indent'] = $comments[$v['parentCommentID']]['indent'] + 1;
						}
					}
					// calculate proper comment nesting order, this relies on the query order clause
					$commentOrder = '';
					foreach ($comments as $k => $v) {
						if ($commentOrder == '') {
							$commentOrder = $k.',';
						} elseif ($v['parentCommentID'] > 0) {
							$commentOrder = preg_replace('/((^|,)'.$v['parentCommentID'].'),/', '$1,'.$k.',', $commentOrder);
						} else {
							$commentOrder = $k.','.$commentOrder;
						}
					}
					$commentOrder = explode(',', substr($commentOrder, 0, -1));
				}
				// save organized comments to cache
				$sql = "UPDATE `postCommentsCache` SET `data` = '".prep(serialize($commentOrder))."', `expired` = 0 WHERE `postID` = '".$post['postID']."'";
				query($sql);
			}
			// pagination
			$start = getRequest('from');
			if (empty($start)) {
				if ($startComment = getRequest('comment')) {
					$start = array_search($startComment, $commentOrder) + 1;
				}
			}
			$absoluteLimit = 200;
			$limit = $absoluteLimit;
			if ($start < 1 || $start > $totalComments) {
				$start = 0;
			} else {
				$start = $start - 1;
			}
			if ($start + $limit - 1 > $totalComments) {
				$limit = $totalComments - $start;
			}
			if ($start > 0) {
				$prev = $start - $absoluteLimit + 1;
				if ($prev < 1) {
					$prev = 1;
				}
			} else {
				$prev = 0;
			}
			$next = $start + $limit + 1;
			// comment trees with parent comments in previous pages have indent values adjusted so they wont start off heavily indented
			$visibleComments = array();
			if (!empty($comments)) {
				$indentOffset = $comments[$commentOrder[$start]]['indent'];
				for ($i = $start; $i < $start + $limit; $i++) {
					if ($comments[$commentOrder[$i]]['indent'] < $indentOffset) {
						if ($comments[$commentOrder[$i]]['indent'] > 0) {
							$indentOffset = $comments[$commentOrder[$i]]['indent'];
						} else {
							$indentOffset = 0;
						}
					}
					$comments[$commentOrder[$i]]['postCommentKey'] = $comments[$commentOrder[$i]]['postID'].'_'.$comments[$commentOrder[$i]]['postCommentID'];
					$comments[$commentOrder[$i]]['indent'] = $comments[$commentOrder[$i]]['indent'] - $indentOffset;
					$comments[$commentOrder[$i]]['timePosted'] = time() - strtotime($comments[$commentOrder[$i]]['posted']);
					if ($comments[$commentOrder[$i]]['timePosted'] < 60) {
						$period = 'second';
					} elseif ($comments[$commentOrder[$i]]['timePosted'] < 3600) {
						$comments[$commentOrder[$i]]['timePosted'] = round($comments[$commentOrder[$i]]['timePosted'] / 60);
						$period = 'minute';
					} elseif ($comments[$commentOrder[$i]]['timePosted'] < 86400) {
						$comments[$commentOrder[$i]]['timePosted'] = round($comments[$commentOrder[$i]]['timePosted'] / 3600);
						$period = 'hour';
					} else {
						$comments[$commentOrder[$i]]['timePosted'] = round($comments[$commentOrder[$i]]['timePosted'] / 86400);
						$period = 'day';
					}
					if ($comments[$commentOrder[$i]]['timePosted'] > 1) {
						$comments[$commentOrder[$i]]['timePosted'] = $comments[$commentOrder[$i]]['timePosted'].' '.$period.'s';
					} else {
						$comments[$commentOrder[$i]]['timePosted'] = $comments[$commentOrder[$i]]['timePosted'].' '.$period;
					}
					$visibleComments[] = $comments[$commentOrder[$i]];
				}
			}
		}

		$topUser = array(
			'userType' => false,
			'userID' => false
		);
		$user = userCore::getUser();
		$adminUser = adminCore::getAdminUser();
		$loggedin = userCore::isLoggedIn();
		$adminLoggedin = adminCore::isLoggedIn();
		if (!empty($reply) && !$loggedin && !$adminLoggedin) {
			userCore::setTimedLoginPage($_SERVER['REQUEST_URI']);
			addMessage('oops, you have to log in to do that');
			redirect('/user/login');
		} else {
			$selfURL = htmlentities(strip_tags(preg_replace('/\/(reply|edit)\/\d+/', '', $_SERVER['REQUEST_URI'])));
			if ($adminLoggedin) {
				$topUser['userType'] = 'admin';
				$topUser['userID'] = $adminUser['adminUserID'];
			} elseif ($loggedin) {
				$topUser['userType'] = 'user';
				$topUser['userID'] = $user['userID'];
			}
		}

		$reply = getRequest('reply', 'integer');
		$edit = getRequest('edit', 'integer');
		if ($edit) {
			$editComment = new postComment($edit, $post['postID']);
			if (!$editComment->exists() || strtolower($editComment->get('poster')) != $topUser['userType'] || $editComment->get('posterID') != $topUser['userID']) {
				$edit = false;
			}
		}
		if (($reply || $edit) && $topUser['userType'] === false) {
			addMessage('oops, you have to log in to do that');
		}

		$template = new template;
		$template->addMeta('<meta name="description" content="'.$post['description'].' | '.systemSettings::get('METADESCRIPTION').'" />', 'description');
		$template->addMeta('<meta name="keywords" content="comments, '.$post['gameTitle'].', '.systemSettings::get('METAKEYWORDS').'" />', 'keywords');
		$template->addMeta('<meta name="robots" content="index, follow" />', 'robots');
		$template->addMeta('<meta name="googlebot" content="index, follow" />', 'googlebot');
		$template->addStyle('/css/'.systemSettings::get('SOURCEDIR').'/site/main.css');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/main.js');
		$template->addScript('/js/'.systemSettings::get('SOURCEDIR').'/site/postComments.js');
		$template->assignClean('_TITLE', systemSettings::get('SITENAME').' '.$post['gameTitle'].' - '.$post['postTitle']);
		$template->assignClean('post', $post);
		$template->assign('embedSource', $embedSource);
		$template->assign('content', $content);
		$template->assign('comments', $visibleComments);
		$template->assignClean('totalComments', $totalComments);
		$template->assignClean('prev', $prev);
		$template->assignClean('next', $next);
		$template->assignClean('selfURL', $selfURL);
		$template->assignClean('reply', $reply);
		$template->assignClean('edit', $edit);
		$template->assignClean('hotTitles', gameTitlesController::getTitles('weight'));
		$template->assignClean('loggedin', $loggedin);
		$template->assignClean('topUser', $topUser);
		$template->assignClean('user', $user);
		$template->assignClean('postVotes', usersController::getPostVotes(false, array($post['postID'])));
		$template->assignClean('commentVotes', usersController::getCommentVotes(false, $post['postID']));
		$template->assignClean('location', 'postComments');
		$template->setSystemDataGateway();
		$template->getSystemMessages();
		$template->display('site/postComments.htm');
	} // function showComments

	/**
	 *  Save new comment
	 *  Args: none
	 *  Return: none
	 */
	function comment() {
		$game = getRequest('gameTitle', 'gameTitle');
		$title = getRequest('postTitle', 'postTitle');
		$post = postsController::retrievePostData($game, $title);
		if (!empty($post) && (userCore::isLoggedIn() || adminCore::isLoggedIn())) {
			$commentText = getRequest('commentText', 'postComment');
			if (!empty($commentText)) {
				$comment = new postComment;
				$comment->set('postID', $post['postID']);
				$comment->set('parentCommentID', 0);
				$comment->set('comment', $commentText);
				if (!adminCore::isLoggedIn() && !userCore::isLoggedIn()) {
					$comment->setRecordEditor('ANONYMOUS', 0);
				}
				if ($comment->save()) {
					addSuccess('your comment has been added');
					redirect($_SERVER['REQUEST_URI']);
				} else {
					addError('oops, there was a problem saving your comment, try it again');
				}
			} else {
				addError('hey! are you trying to add an empty comment?');
			}
		} else {
			userCore::setLoginPage($_SERVER['REQUEST_URI']);
			addMessage('oops, you have to log in to do that');
			redirect('/user/login');
		}
		showComments($post);
	} // function comment

	/**
	 *  Reply to a comment
	 *  Args: none
	 *  Return: none
	 */
	function reply() {
		$game = getRequest('gameTitle', 'gameTitle');
		$title = getRequest('postTitle', 'postTitle');
		$post = postsController::retrievePostData($game, $title);
		if (!empty($post) && (userCore::isLoggedIn() || adminCore::isLoggedIn())) {
			if (getRequest('submit') == 'save') {
				$commentText = getRequest('commentText', 'postComment');
				$replyTo = getRequest('replyTo', 'integer');
				$parentComment = new postComment($replyTo, $post['postID']);
				if ($parentComment->exists()) {
					if (!empty($commentText)) {
						$comment = new postComment;
						$comment->set('postID', $post['postID']);
						$comment->set('parentCommentID', $replyTo);
						$comment->set('comment', $commentText);
						if (!adminCore::isLoggedIn() && !userCore::isLoggedIn()) {
							$comment->setRecordEditor('ANONYMOUS', 0);
						}
						if ($comment->save()) {
							addSuccess('your reply has been saved, the day is yours!');
							redirect($_SERVER['REQUEST_URI']);
						} else {
							addError('oops, there was a problem saving your reply, try it again');
						}
					} else {
						addError('hey! are you trying to add an empty comment?');
						redirect($_SERVER['REQUEST_URI'].'/reply/'.$replyTo);
					}
				} else {
					addError('the comment you\'re trying to reply to doesn\'t exist');
					addError('what happened there?');
				}
			}
		} else {
			userCore::setTimedLoginPage($_SERVER['REQUEST_URI']);
			addMessage('oops, you have to log in to do that');
			redirect('/user/login');
		}
		showComments($post);
	} // function reply

	/**
	 *  Edit a comment
	 *  Args: none
	 *  Return: none
	 */
	function edit() {
		$game = getRequest('gameTitle', 'gameTitle');
		$title = getRequest('postTitle', 'postTitle');
		$post = postsController::retrievePostData($game, $title);
		if (!empty($post) && (userCore::isLoggedIn() || adminCore::isLoggedIn())) {
			$commentID = getRequest('edit', 'integer');
			$commentText = getRequest('commentText', 'postComment');
			if (!empty($commentText)) {
				$comment = new postComment($commentID, $post['postID']);
				$comment->set('comment', $commentText);
				if ($comment->update()) {
					addSuccess('your comment has been updated');
					redirect($_SERVER['REQUEST_URI']);
				} else {
					addError('oops, there was a problem updating your comment, try it again');
				}
			} else {
				addError('hey! are you trying to save an empty comment?');
			}
		} else {
			userCore::setLoginPage($_SERVER['REQUEST_URI']);
			addMessage('oops, you have to log in to do that');
			redirect('/user/login');
		}
		showComments($post);
	} // function edit

?>

<?

	$segment = getRequest('q', 'alphanumspace');
	$returnLimit = getRequest('limit', 'integer') ? getRequest('limit', 'integer') : 10;
	$type = getRequest('type');
	$access = false;

	switch($type) {
		case 'gameTitle':
			$access = 'POST';
			break;
		case 'userSearch':
			$access = 'USER';
			break;
		default:
			exit;
			break;
	}

	adminCore::checkAccess($access);

	switch($type) {
		case 'gameTitle':
			$returnVals = array();
			$result = query("SELECT `gameTitle` FROM `gameTitles` WHERE `gameTitleKey` LIKE '".prep(clean($segment, 'alphanum'))."%' ORDER BY `gameTitleKey` ASC LIMIT ".$returnLimit);
			if ($result->rowCount > 0) {
				while ($row = $result->fetchRow()) {
					$returnVals[] = $row['gameTitle'];
				}
			}
			break;
		case 'userSearch':
			$returnVals = array();
			$result = query("SELECT `userID`, `userName`, `email` FROM `users` WHERE `userName` LIKE '".$segment."%' OR `email` LIKE '".$segment."%' OR `userID` LIKE '".$segment."%' LIMIT ".$returnLimit);
			if ($result->rowCount > 0) {
				$returnVals = $result->fetchAll();
			}
			break;
		default:
			exit;
			break;
	}

	if (isset($returnVals) && is_array($returnVals)) {
		$returnCount = 0;
		foreach ($returnVals as $val) {
			switch($type) {
				case 'userSearch':
					echo 'format-1|';
					break;
				default:
					break;
			}
			if (!is_array($val)) {
				echo $val."\n";
			} else {
				foreach ($val as $item) {
					echo $item."|";
				}
				echo "\n";
			}
			++$returnCount;
			if ($returnCount >= $returnLimit) {
				break;
			}
		}
	}

?>
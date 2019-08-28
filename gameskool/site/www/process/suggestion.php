<?

	$segment = getRequest('q', 'alphanumspace');
	$returnLimit = 10;

	$type = getRequest('type');

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
		default:
			exit;
			break;
	}

	if (isset($returnVals) && is_array($returnVals)) {
		$returnCount = 0;
		foreach ($returnVals as $val) {
			echo $val."\n";
			++$returnCount;
			if ($returnCount >= $returnLimit) {
				break;
			}
		}
	}

?>
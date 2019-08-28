<?

# this needs to be run after and in conjunction with the friendly urls sql script

require_once '../site/cron/cron_conf';

$results = query("SELECT * FROM `gameTitles`");
while ($row = $results->fetchRow()) {
	query("UPDATE `gameTitles` SET `gameTitleURL` = '".prep(friendlyURL($row['gameTitleURL']))."', `gameTitleKey` = '".prep(strtolower($row['gameTitleKey']))."' WHERE `gameTitleID` = '".$row['gameTitleID']."'");
}

$results = query("SELECT * FROM `posts`");
while ($row = $results->fetchRow()) {
	query("UPDATE `posts` SET `postTitleURL` = '".prep(friendlyURL($row['postTitleURL']))."' WHERE `postID` = '".$row['postID']."'");
}

$results = query("SELECT * FROM `postsHistory`");
while ($row = $results->fetchRow()) {
	query("UPDATE `postsHistory` SET `postTitleURL` = '".prep(friendlyURL($row['postTitleURL']))."' WHERE `postsHistoryID` = '".$row['postsHistoryID']."'");
}

?>

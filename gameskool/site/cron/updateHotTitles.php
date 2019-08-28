<?

require_once 'cron_conf';

$sql = "INSERT INTO `gameTitleStatistics` 
		SELECT `a`.`gameTitleID`, COUNT(DISTINCT `b`.`postID`), IFNULL(SUM(`c`.`views`), 0), COUNT(DISTINCT `b`.`postID`) + IFNULL(SUM(`c`.`views`), 0), 'new' 
		FROM `gameTitles` `a` 
		LEFT JOIN `posts` `b` ON (`a`.`gameTitleID` = `b`.`gameTitleID` AND `b`.`status` = 'active') 
		LEFT JOIN `postStatistics` `c` ON (`b`.`postID` = `c`.`postID`) GROUP BY `a`.`gameTitleID`";
query($sql);
$sql = "DELETE FROM `gameTitleStatistics` 
		WHERE `status` = 'current'";
query($sql);
$sql = "UPDATE `gameTitleStatistics` 
		SET `status` = 'current'";
query($sql);

?>

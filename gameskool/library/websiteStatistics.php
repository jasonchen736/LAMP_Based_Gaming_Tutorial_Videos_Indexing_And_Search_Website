<?

	class websiteStatistics {
		public static $dbh;
		public static $startDate;
		public static $endDate;

		/**
		 *  Initialize resources and references
		 *  Args: (str) start date, (str) end date
		 *  Returns: none
		 */
		public static function initialize($startDate, $endDate) {
			self::$dbh = database::getInstance();
			self::$startDate = $startDate;
			self::$endDate = $endDate;
		} // function initialize

		/**
		 *  Retrieve top traffic sources
		 *  Args: (int) number of top sources displayed
		 *  Returns: (array) top traffic sources
		 */
		public static function getBestTrafficSources($limit = 10) {
			$sql = "SELECT `ID`, `subID`, `extra`, SUM(`hits`) AS `hits`, SUM(`uniqueHits`) AS `uniques` 
					FROM `trafficSourceHits` 
					WHERE `date` BETWEEN '".dateToSql(self::$startDate)."' AND '".dateToSql(self::$endDate)."' 
					GROUP BY `ID`, `subID`, `extra` 
					ORDER BY `hits` DESC, `uniques` DESC 
					LIMIT ".$limit;
			$result = self::$dbh->query($sql);
			$trafficSources = $result->fetchAll();
			return $trafficSources;
		} // function getBestTrafficSources

		/**
		 *  Retrieve top viewed posts
		 *  Args: (int) number of top posts displayed
		 *  Returns: (array) top viewed posts
		 */
		public static function getTopPosts($limit = 10) {
			$sql = "SELECT `a`.`postID`, SUM(`a`.`views`) AS `views`, `b`.`postTitle` 
					FROM `postViews` `a` 
					JOIN `posts` `b` USING (`postID`) 
					WHERE `a`.`date` BETWEEN '".dateToSql(self::$startDate)."' AND '".dateToSql(self::$endDate)."' 
					GROUP BY `a`.`postID` 
					ORDER BY `views` DESC 
					LIMIT ".$limit;
			$result = self::$dbh->query($sql);
			$topPosts = $result->fetchAll();
			return $topPosts;
		} // function getTopPosts
	} // class websiteStatistics

?>
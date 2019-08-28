ALTER TABLE `videos` RENAME TO `posts`,
 CHANGE COLUMN `videoID` `postID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `source` `source` VARCHAR(45) DEFAULT NULL,
 CHANGE COLUMN `identifier` `identifier` VARCHAR(45) DEFAULT NULL,
 CHANGE COLUMN `videoTitle` `postTitle` VARCHAR(255) NOT NULL,
 CHANGE COLUMN `image` `image` TEXT DEFAULT NULL,
 CHANGE COLUMN `description` `description` TEXT DEFAULT NULL,
 CHANGE COLUMN `content` `content` TEXT DEFAULT NULL,
 ADD COLUMN `type` ENUM('video', 'link', 'wiki', 'blog') NOT NULL AFTER `postID`,
 ADD COLUMN `url` TEXT DEFAULT NULL AFTER `postTitle`,
 DROP PRIMARY KEY,
 DROP INDEX `gameTitleID_videoTitle`,
 DROP INDEX `source_identifier`,
 ADD PRIMARY KEY (`postID`),
 ADD UNIQUE INDEX `gameTitleID_postTitle`(`gameTitleID`, `postTitle`),
 ADD INDEX `type_source_identifier`(`type`, `source`, `identifier`),
 ADD INDEX `source`(`source`),
 ADD INDEX `postTitle`(`postTitle`),
 ADD INDEX `status`(`status`),
 ADD INDEX `poster`(`poster`),
 ADD INDEX `posted`(`posted`),
 ADD INDEX `url`(`url` (255));

ALTER TABLE `videosHistory` RENAME TO `postsHistory`,
 CHANGE COLUMN `videosHistoryID` `postsHistoryID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` INTEGER UNSIGNED NOT NULL,
 CHANGE COLUMN `source` `source` VARCHAR(45) DEFAULT NULL,
 CHANGE COLUMN `identifier` `identifier` VARCHAR(45) DEFAULT NULL,
 CHANGE COLUMN `videoTitle` `postTitle` VARCHAR(255) NOT NULL,
 CHANGE COLUMN `image` `image` TEXT DEFAULT NULL,
 CHANGE COLUMN `description` `description` TEXT DEFAULT NULL,
 CHANGE COLUMN `content` `content` TEXT DEFAULT NULL,
 ADD COLUMN `type` ENUM('video', 'link', 'wiki', 'blog') NOT NULL AFTER `postID`,
 ADD COLUMN `url` TEXT DEFAULT NULL AFTER `postTitle`,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`postsHistoryID`),
 ADD INDEX `postID`(`postID`);

ALTER TABLE `videoViews` RENAME TO `postViews`,
 CHANGE COLUMN `videoID` `postID` INTEGER UNSIGNED NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `date_videoID`,
 ADD PRIMARY KEY (`postID`, `date`),
 ADD INDEX `date_postID`(`date`, `postID`);

ALTER TABLE `videoStatistics` RENAME TO `postStatistics`,
 CHANGE COLUMN `videoID` `postID` INTEGER UNSIGNED NOT NULL,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY (`postID`);

ALTER TABLE `videoContentRevisions` RENAME TO `postContentRevisions`,
 CHANGE COLUMN `videoContentRevisionID` `postContentRevisionID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` INTEGER UNSIGNED NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_posted`,
 ADD PRIMARY KEY (`postContentRevisionID`),
 ADD INDEX `postID_posted`(`postID`, `posted`);

ALTER TABLE `videoIndexDelta` RENAME TO `postIndexDelta`,
 CHANGE COLUMN `videoID` `postID` INTEGER UNSIGNED NOT NULL,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY (`postID`);

ALTER TABLE `videoCommentsHistory0` RENAME TO `postCommentsHistory0`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory1` RENAME TO `postCommentsHistory1`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory2` RENAME TO `postCommentsHistory2`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory3` RENAME TO `postCommentsHistory3`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory4` RENAME TO `postCommentsHistory4`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory5` RENAME TO `postCommentsHistory5`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory6` RENAME TO `postCommentsHistory6`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory7` RENAME TO `postCommentsHistory7`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory8` RENAME TO `postCommentsHistory8`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsHistory9` RENAME TO `postCommentsHistory9`,
 CHANGE COLUMN `videoCommentHistoryID` `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoCommentID`,
 ADD PRIMARY KEY (`postCommentHistoryID`),
 ADD INDEX `postCommentID` (`postCommentID`);

ALTER TABLE `videoCommentsCache` RENAME TO `postCommentsCache`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY (`postID`);

ALTER TABLE `videoComments0` RENAME TO `postComments0`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments1` RENAME TO `postComments1`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments2` RENAME TO `postComments2`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments3` RENAME TO `postComments3`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments4` RENAME TO `postComments4`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments5` RENAME TO `postComments5`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments6` RENAME TO `postComments6`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments7` RENAME TO `postComments7`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments8` RENAME TO `postComments8`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `videoComments9` RENAME TO `postComments9`,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID_parentCommentID`,
 ADD PRIMARY KEY (`postCommentID`),
 ADD INDEX `postID_parentCommentID` (`postID`,`parentCommentID`);

ALTER TABLE `userVideoVotes0` RENAME TO `userPostVotes0`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes1` RENAME TO `userPostVotes1`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes2` RENAME TO `userPostVotes2`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes3` RENAME TO `userPostVotes3`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes4` RENAME TO `userPostVotes4`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes5` RENAME TO `userPostVotes5`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes6` RENAME TO `userPostVotes6`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes7` RENAME TO `userPostVotes7`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes8` RENAME TO `userPostVotes8`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userVideoVotes9` RENAME TO `userPostVotes9`,
 CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `videoID`,
 ADD PRIMARY KEY (`userID`, `postID`),
 ADD INDEX `postID` (`postID`);

ALTER TABLE `userCommentVotes0` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes1` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes2` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes3` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes4` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes5` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes6` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes7` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes8` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `userCommentVotes9` CHANGE COLUMN `videoID` `postID` int(10) unsigned NOT NULL,
 CHANGE COLUMN `videoCommentID` `postCommentID` int(10) unsigned NOT NULL,
 DROP PRIMARY KEY,
 DROP INDEX `userID_videoCommentID`,
 ADD PRIMARY KEY (`userID`,`postID`,`postCommentID`),
 ADD INDEX `userID_postCommentID` (`userID`,`postCommentID`);

ALTER TABLE `gameTitleStatistics` CHANGE COLUMN `videos` `posts` INTEGER UNSIGNED NOT NULL,
 CHANGE COLUMN `videoViews` `postViews` BIGINT UNSIGNED NOT NULL,
 DROP INDEX `videos`,
 DROP INDEX `videoViews`,
 ADD INDEX `posts` (`posts`),
 ADD INDEX `postViews` (`postViews`);

UPDATE `adminUserAccess` SET `access` = 'POST' WHERE `access` = 'VIDEO';
UPDATE `adminGroupAccess` SET `access` = 'POST' WHERE `access` = 'VIDEO';

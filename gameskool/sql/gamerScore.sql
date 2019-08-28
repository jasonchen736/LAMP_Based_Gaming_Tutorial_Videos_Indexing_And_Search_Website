CREATE TABLE  `userGameStatistics` (
  `userID` int(10) unsigned NOT NULL,
  `gameTitleID` INTEGER NOT NULL,
  `knowledge` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`userID`, `gameTitleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `userGameStatistics` 
SELECT `a`.`posterID`, `a`.`gameTitleID`, SUM(`a`.`postKnowledge`) AS `postKnowledge` FROM (
SELECT `a`.`posterID`, `a`.`gameTitleID`, IFNULL(LOG2(IF(`b`.`upVotes` - `b`.`downVotes` < 0, 0, `b`.`upVotes` - `b`.`downVotes`)), 0) + 2 AS `postKnowledge` FROM `posts` `a` JOIN `postStatistics` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
) `a` GROUP BY `a`.`posterID`, `a`.`gameTitleID`;

INSERT INTO `userGameStatistics` 
SELECT `a`.`posterID`, `a`.`gameTitleID`, SUM(`a`.`commentKnowledge`) AS `commentKnowledge` FROM (
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments0` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments1` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments2` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments3` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments4` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments5` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments6` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments7` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments8` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `b`.`gameTitleID`, IFNULL(LOG2(IF(`a`.`upVotes` - `a`.`downVotes` < 0, 0, `a`.`upVotes` - `a`.`downVotes`)), 0) + 1 AS `commentKnowledge` FROM `postComments9` `a` JOIN `posts` `b` ON (`a`.`postID` = `b`.`postID`) WHERE `a`.`poster` = 'USER'
) `a` GROUP BY `a`.`posterID`, `a`.`gameTitleID` 
ON DUPLICATE KEY UPDATE `knowledge` = `knowledge` + VALUES(`knowledge`);

CREATE TABLE  `userStatistics` (
  `userID` int(10) unsigned NOT NULL,
  `reputation` int(10) NOT NULL,
  `knowledge` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `userStatistics` SELECT `userID`, 0, SUM(`knowledge`) FROM `userGameStatistics` GROUP BY `userID`;

INSERT INTO `userStatistics`
SELECT `a`.`posterID`, SUM(`a`.`totalVotes`) AS `totalVotes`, 0 FROM (
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments0` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments1` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments2` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments3` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments4` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments5` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments6` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments7` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments8` `a` WHERE `a`.`poster` = 'USER'
UNION ALL
SELECT `a`.`posterID`, `a`.`totalVotes` FROM `postComments9` `a` WHERE `a`.`poster` = 'USER'
) `a` GROUP BY `a`.`posterID`
ON DUPLICATE KEY UPDATE `reputation` = VALUES(`reputation`);

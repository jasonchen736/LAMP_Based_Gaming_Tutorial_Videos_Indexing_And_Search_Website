# this sql script needs to be run before and in conjunction with the friendly urls php utility script

ALTER TABLE `gameTitles` ADD COLUMN `gameTitleURL` VARCHAR(255) NOT NULL AFTER `gameTitle`, DROP KEY `gameTitle`;
UPDATE `gameTitles` SET `gameTitleURL` = `gameTitle`;
ALTER TABLE `gameTitles` ADD UNIQUE INDEX `gameTitleURL` (`gameTitleURL`);

ALTER TABLE `posts` ADD COLUMN `postTitleURL` VARCHAR(255) NOT NULL AFTER `postTitle`, DROP KEY `gameTitleID_postTitle`, DROP KEY `postTitle`;
ALTER TABLE `postsHistory` ADD COLUMN `postTitleURL` VARCHAR(255) NOT NULL AFTER `postTitle`;
UPDATE `posts` SET `postTitle` = REPLACE(`postTitle`, '_', ' ');
UPDATE `posts` SET `postTitleURL` = `postTitle`;
UPDATE `postsHistory` SET `postTitle` = REPLACE(`postTitle`, '_', ' ');
UPDATE `postsHistory` SET `postTitleURL` = `postTitle`;
ALTER TABLE `posts` ADD UNIQUE INDEX `gameTitleID_postTitleURL` (`gameTitleID`, `postTitleURL`), ADD INDEX `postTitleURL` (`postTitleURL`);

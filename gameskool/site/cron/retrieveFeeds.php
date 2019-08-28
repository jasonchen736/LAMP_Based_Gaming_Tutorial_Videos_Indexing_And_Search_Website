<?

require_once 'cron_conf';

$feeds = feedAPI::retrieveFeeds();
feedAPI::runFeeds($feeds);

?>

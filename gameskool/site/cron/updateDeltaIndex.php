<?

/**
 * Reindex sphinx delta post index
 */

require_once 'cron_conf';

if (postsController::hasDeltaUpdates()) {
	$handles = array();
	$descSpec = array(
			0 => array('pipe', 'r'), // stdin
			1 => array('pipe', 'w'), // stdout
			2 => array('pipe', 'w')  // stderr
	);
	$cmd = systemSettings::get('SPHINXPATHINDEXER').' -c '.systemSettings::get('SPHINXPATHCONFIG').' '.systemSettings::get('POSTDELTAINDEX').' --rotate';
	$process = proc_open($cmd, $descSpec, $handles);
	$output = stream_get_contents($handles[1]);
	$errors = stream_get_contents($handles[2]);
	if (preg_match('/rotating indices: succesfully sent SIGHUP to searchd/', $output)) {
			postsController::removeIndexedDeltas();
	}
}

?>

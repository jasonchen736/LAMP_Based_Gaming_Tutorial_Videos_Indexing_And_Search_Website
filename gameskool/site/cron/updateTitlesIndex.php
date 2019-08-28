<?

/**
 * Reindex sphinx game titles index
 */

require_once 'cron_conf';

$handles = array();
$descSpec = array(
        0 => array('pipe', 'r'), // stdin
        1 => array('pipe', 'w'), // stdout
        2 => array('pipe', 'w')  // stderr
);
$cmd = systemSettings::get('SPHINXPATHINDEXER').' -c '.systemSettings::get('SPHINXPATHCONFIG').' '.systemSettings::get('GAMETITLEINDEX').' --rotate';
$process = proc_open($cmd, $descSpec, $handles);
$output = stream_get_contents($handles[1]);
$errors = stream_get_contents($handles[2]);

?>

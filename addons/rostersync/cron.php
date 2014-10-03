<?php
///var/www/clients/client2/web19/web/wowroster/addons/ApiSync/guild
echo 'This is a cron charavter update file for apisync<br>';
define('IN_ROSTER', true);
require_once ('settings.php');
if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}
$addon = getaddon('ApiSync');
require_once ($addon['dir'] . 'inc/ApiSyncjob.class.php');

//$roster->output['show_header'] = false;
$roster->output['show_footer'] = false;
$roster->output['show_menu'] = false;
$job = new ApiSyncJob();
$job->is_cron = true;
$job->_startSyncing();
_run();
//$this->done, $this->total
function _run()
{
	global $roster, $job;
	echo '<br>'.$job->active_member['name'].' - '.$job->done.' - '.$job->total.'<br>'."\n";
	if ($job->done != $job->total)
	{
		$job->_startSyncing();
		_loop();
	}
	else
	{
		return true;
	}
}
function _loop()
{
	global $roster, $job;
	$job->_updateStatus( $job->jobid );
	_run();
}
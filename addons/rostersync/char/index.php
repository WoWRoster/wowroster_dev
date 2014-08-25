<?php

if($roster->pages[2] == 'profile')
{	
	require_once ($addon['dir'] . 'inc/rsync_core.class.php');
	$job = new rsync();
	if ($addon['config']['rsync_skip_start'] == 0 && !isset($_POST['action']) && !( isset($_GET['job_id']) || isset($_POST['job_id']) ))
	{
		$job->_showStartPage('profile');
	}
	if ($addon['config']['rsync_skip_start'] == 0 && isset($_POST['action']) && $_POST['action'] == 'start')
	{
		$job->_start_profile();
	}
	
	if ($addon['config']['rsync_skip_start'] == 1 && ( isset($_GET['job_id']) || isset($_POST['job_id']) ))
	{
		$job->_start_profile();
	}
}


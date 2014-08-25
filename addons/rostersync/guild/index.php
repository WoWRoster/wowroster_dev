<?php

echo '<pre>';
print_r($_POST);

echo '</pre>';
if($roster->pages[2] == 'memberlist')
{

	require_once ($addon['dir'] . 'inc/rsync_core.class.php');
	$job = new rsync();
	if ($addon['config']['rsync_skip_start'] == 0 && !isset($_POST['action']))
	{
		$job->_showStartPage('memberlist');
	}
	if ($addon['config']['rsync_skip_start'] == 0 && isset($_POST['action']) && $_POST['action'] == 'start')
	{
		$job->_start_memberlist();
	}
	
	if ($addon['config']['rsync_skip_start'] == 1)
	{
		$job->_start_memberlist();
	}
	

}

if($roster->pages[2] == 'gprofile')
{	
	require_once ($addon['dir'] . 'inc/rsync_core.class.php');
	$job = new rsync();
	if ($addon['config']['rsync_skip_start'] == 0 && !isset($_POST['action']) && !( isset($_GET['job_id']) || isset($_POST['job_id']) ))
	{
		$job->_showStartPage('gprofile');
	}
	if ($addon['config']['rsync_skip_start'] == 0 && isset($_POST['action']) && $_POST['action'] == 'start')
	{
		$job->_start_gprofile();
	}
	
	if ($addon['config']['rsync_skip_start'] == 1 && ( isset($_GET['job_id']) || isset($_POST['job_id']) ))
	{
		$job->_start_gprofile();
	}
	if ( ( isset($_GET['job_id']) || isset($_POST['job_id']) ))
	{
		$job->_start_gprofile();
	}
}
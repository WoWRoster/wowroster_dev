<?php

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

if($roster->pages[2] == 'addguild')
{

	require_once ($addon['dir'] . 'inc/rsync_core.class.php');
	$job = new rsync();
	if ($addon['config']['rsync_skip_start'] == 0 && !isset($_POST['action']))
	{
		$job->_showStartPage('addguild');
	}
	if ($addon['config']['rsync_skip_start'] == 0 && isset($_POST['action']) && $_POST['action'] == 'start')
	{
		$job->_showAddScreen();
	}
	if (isset($_POST['action']) && $_POST['action'] == 'addguild' && isset($_POST['process']) && $_POST['process'] == 'addguild_data')
	{
		$job->_start_addguild();
	}
	
	if ($addon['config']['rsync_skip_start'] == 1)
	{
		$job->_showAddScreen();
	}

}

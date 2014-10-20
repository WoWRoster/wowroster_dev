<?php


	if( !defined('IN_ROSTER') || !defined('IN_ROSTER_ADMIN') )
	{
		exit('Detected invalid access to this file!');
	}
	
	$type = (isset($_GET['type']) ? $_GET['type'] : 0);
	$id = (isset($_GET['id']) ? $_GET['id'] : 0);
	
	if( isset($_POST['process']))
	{
		$table = $field_id = $field_col = '';
		$a=$_POST;
		switch( $_POST['action'] )
		{
			case 'userman':
				$table = 'user_members';
				$field_id = 'id';
				$field_col = 'user_permissions';
				break;

			case 'usergroups':
				$table = 'user_groups';
				$field_id = 'group_id';
				$field_col = 'group_permissions';
				break;
		}
		unset($a['action']);
		unset($a['process']);
		$b = json_encode($a,true);
		
		$up_query = "UPDATE `" . $roster->db->table($table) . "` SET `".$field_col."` = '".$b."' WHERE `".$field_id."` = '".$_POST['id']."';";
		$up_result = $roster->db->query($up_query);
		if ($up_result)
		{
			$roster->set_message('Permissions Saved', 'Permissions');
		}
		$type = (isset($_POST['action']) ? $_POST['action'] : 0);
		$id = (isset($_POST['id']) ? $_POST['id'] : 0);
	
	}
	/*
	*
	*	We have to load All addon locals for this page ...
	*/
	$addons = '';
	$output = '';

	if( $handle = @opendir(ROSTER_ADDONS) )
	{
		while( false !== ($file = readdir($handle)) )
		{
			if( $file != '.' && $file != '..' && $file != '.svn' && substr($file, strrpos($file, '.')+1) != 'txt')
			{
				$addons[] = $file;
			}
		}
	}

	usort($addons, 'strnatcasecmp');

	if( is_array($addons) )
	{
		foreach( $addons as $addon )
		{
			// Save current locale array
			// Since we add all locales for localization, we save the current locale array
			// This is in case one addon has the same locale strings as another, and keeps them from overwritting one another
			$localetemp = $roster->locale->wordings;

			foreach( $roster->multilanguages as $lang )
			{
				$roster->locale->add_locale_file(ROSTER_ADDONS . $addon . DIR_SEP . 'locale' . DIR_SEP . $lang . '.php',$lang);
			}
			unset($localetemp);
		}
	}

	$roster->tpl->assign_vars(array(
			'TYPE'		=>  $type,
			'ID'		=>  $id,
			'RETURN'	=> makelink('rostercp-' . $type),
		)
	);
	
	// Pull the user or group data from the database
	$table = $field_id = $field_col = '';
	switch( $type )
	{
		case 'userman':
			$table = 'user_members';
			$field_id = 'id';
			$field_col = 'user_permissions';
			break;

		case 'usergroups':
			$table = 'user_groups';
			$field_id = 'group_id';
			$field_col = 'group_permissions';
			break;
	}
		
	$up_query = "SELECT `".$field_col."` FROM `" . $roster->db->table($table) . "` WHERE `".$field_id."` = '".$id."'; ";
	$up_result = $roster->db->query($up_query);
	$data = $roster->db->fetch($up_result);
	$values = json_decode($data[$field_col],true);
	
	$roster->output['title'] .= $roster->locale->act['pagebar_userman'];

	$dm_query = "SELECT * FROM `" . $roster->db->table('permissions') . "` ORDER BY `type_id` ASC";
	$dm_result = $roster->db->query($dm_query);

	if( !$dm_result )
	{
		die_quietly($roster->db->error(), 'Database error', __FILE__, __LINE__, $dm_query);
	}

	$s=array();
	while( $row = $roster->db->fetch($dm_result) )
	{
		$s[$row['type']][$row['catagory']][] = $row;
	}
	$roster->db->free_result($dm_result);
	
	foreach ($s as $addon => $cat)
	{
		$roster->tpl->assign_block_vars('type', array(
			'TYPE'	=> $addon,
			)
		);
		foreach ($cat as $r => $info)
		{
			$roster->tpl->assign_block_vars('type.catagory', array(
				'NAME'	=> $roster->locale->act['admin'][$r],
				)
			);
			foreach ($info as $e => $i)
			{
				$roster->tpl->assign_block_vars('type.catagory.info', array(
					'NAME'		=> (isset($roster->locale->act['admin'][$i['name']]) ? $roster->locale->act['admin'][$i['name']] : $i['name'] ),
					'INFO'		=> $roster->locale->act['admin'][$i['info']],
					'CFG'		=> $i['cfg_name'],
					'CFGVALUE'	=> ( isset($values[$i['cfg_name']]) ? $values[$i['cfg_name']] : 0),
					)
				);
			}
		}
	}	
	
$roster->tpl->set_filenames(array('body' => 'admin/permissions.html'));
$body = $roster->tpl->fetch('body');
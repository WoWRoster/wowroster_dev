<?php

if( !isset($user) )
{
include_once ($addon['inc_dir'] . 'conf.php');
}
	
if (isset($_POST['stage']))
{
	$stage = $_POST['stage'];
}
else if (isset($_GET['stage']))
{
	$stage = $_GET['stage'];
}
else
{
	$stage = 1;
}
if(isset($_POST['op']) && $_POST['op']=='start')
{

	// If the Register form has been submitted
	$err = array();
	if ($stage == 1)
	{
		if (!isset($_GET['code']))
		{
			$auth_url = $roster->api2->getAuthenticationUrl($roster->api2->baseurl[$roster->api2->region]['AUTHORIZATION_ENDPOINT'], $roster->api2->redirect_uri);
			header('Location: ' . $auth_url);
			exit();
		}
		else
		{

		}
	}
	
	// blah blah blah for sakes perpous the code worked and 
	$stage++;
	
}
if (isset($_GET['stage']) && $_GET['stage'] == 2)
{
		
	$params = array('code' => $_GET['code'], 'auth_flow' => 'auth_code', 'redirect_uri' => $roster->api2->redirect_uri);
	$response = $roster->api2->getAccessToken($roster->api2->baseurl[$roster->api2->region]['TOKEN_ENDPOINT'], 'authorization_code', $params);
	$roster->api2->setAccessToken($response['result']['access_token']);
	$chars = $roster->api2->fetch('wowprofile');
	$update_sql = array();
	if (is_array($chars['result']['characters']))
	{
		$query1 = 'DELETE FROM `' . $roster->db->table('user_link', 'user') . '` WHERE `uid` = '.$roster->auth->user['id'].'';
		$result1 = $roster->db->query($query1);
		foreach ($chars['result']['characters'] as $id => $char)
		{
			$data = array(
				'uid'					=> $roster->auth->user['id'],
				'member_id'				=> '',
				'guild_id'				=> '',
				'group_id'				=> '',
				'is_main'				=> '0',
				'realm'					=> $char['realm'],
				'region'				=> $roster->api2->region,
				'name'					=> $char['name'],
				'battlegroup'			=> $char['battlegroup'],
				'class'					=> $char['class'],
				'race'					=> $char['race'],
				'gender'				=> $char['gender'],
				'level'					=> $char['level'],
				'achievementPoints'		=> $char['achievementPoints'],
				'thumbnail'				=> $char['thumbnail'],
				'guild'					=> (isset($char['guild']) ? $char['guild'] : ''),
				'guildRealm'			=> (isset($char['guildRealm']) ? $char['guildRealm'] : ''),
			);
			$query = 'INSERT INTO `' . $roster->db->table('user_link', 'user') . '` ' . $roster->db->build_query('INSERT', $data);
			$result = $roster->db->query($query);
			$update_sql[] = "UPDATE `" . $roster->db->table('members') . "`"
								  . " SET `account_id` = '" . $roster->auth->user['id'] . "'"
								  . " WHERE `name` = '".$roster->db->escape($char['name'])."' AND `server` = '".$roster->db->escape($char['realm'])."';";
			
		}
		foreach( $update_sql as $sql )
		{
			$result = $roster->db->query($sql);
		}
	}
}

$form = 'userApp';
//$user->form->newForm('post', makelink('util-accounts-application'), $form, 'formClass', 4);
$user->form->newForm('post', makelink('user-user-alt'), $form, 'formClass', 4);

if ($stage == 1)
{
	$roster->tpl->assign_vars(array(
			'STAGE'		=> $stage,
			'TEXT'		=> 'stage 1',
		)
	);
/*
$r = $roster->api2->fetch('character',array('name'=>'zenlee','server'=>'zangarmarsh'));
echo '<pre>';
print_r($r);
echo '</pre>';
*/
}
if ($stage == 2)
{
	$roster->tpl->assign_vars(array(
			'STAGE'		=> $stage,
			'TEXT'		=> 'Select the character to be your main char',
		)
	);

	$query = "SELECT * FROM `". $roster->db->table('user_link', 'user') ."` WHERE `uid` = '". $roster->auth->user['id'] ."';";
	$result = $roster->db->query($query);

	if( !$result )
	{
		die_quietly($roster->db->error, 'claim alt', __FILE__,__LINE__,$query);
	}
	while( $row = $roster->db->fetch($result) )
	{

		$roster->tpl->assign_block_vars('chars', array(
				'THUMB'		=> 'http://us.battle.net/static-render/us/'.$row['thumbnail'],
				'NAME'		=> $row['name'],
				'ID'		=> $row['link_id'],
				'LEVEL'		=> $row['level'],
				'RACE'		=> $row['race'],
				'GENDER'	=> $row['gender'],
				'SERVER'	=> $row['realm'],
				'GUILD'		=> $row['guild'],
				'IS_MAIN'	=> (bool)$row['is_main'],
				'CLASS'		=> $roster->locale->act['id_to_class'][$row['class']],
			)
		);
	}

}

$roster->tpl->set_filenames(array('alt' => $addon['basename'] . '/alt.html'));
$roster->tpl->display('alt');
			
?>
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
$auth_url = $roster->api2->getAuthenticationUrl($roster->api2->baseurl[$roster->api2->region]['AUTHORIZATION_ENDPOINT'], $roster->api2->redirect_uri);
$js1 = "
var oAuth2AuthWindow;
	function popupClosing() {
		alert('About to refresh');
		window.location.href = '".makelink('user-user-alt&amp;stage=3')."';
	}
	
	function closepopup()
	{
		self.close();
		opener.location.href = '".makelink('user-user-alt&stage=3')."';
	}
	function openWin()
	{
		oAuth2AuthWindow = window.open('".$auth_url."', 'masheryOAuth2AuthWindow', 'width=430,height=660');
	}
	jQuery(document).ready( function($){

		jQuery('#charclaim').click(function(e)
		{
			e.preventDefault();
			//alert('boo');
			oAuth2AuthWindow = window.open('".$auth_url."', 'masheryOAuth2AuthWindow', 'width=430,height=660');
		});

	});
";
roster_add_js($js1, 'inline', 'header', false, false);
if (isset($_GET['stage']) && $_GET['stage'] == 2)
{

	$params = array('code' => $_GET['code'], 'auth_flow' => 'auth_code', 'redirect_uri' => $roster->api2->redirect_uri);
	$response = $roster->api2->getAccessToken($roster->api2->baseurl[$roster->api2->region]['TOKEN_ENDPOINT'], 'authorization_code', $params);

	$roster->api2->setAccessToken($response['access_token']);
	$chars = $roster->api2->fetch('wowprofile');
	echo '<pre>';
print_r($chars);
echo '</pre>';
	$update_sql = array();
	if (is_array($chars['characters']))
	{
		$query1 = 'DELETE FROM `' . $roster->db->table('user_link', 'user') . '` WHERE `uid` = '.$roster->auth->user['id'].'';
		$result1 = $roster->db->query($query1);
		foreach ($chars['characters'] as $id => $char)
		{
			$idww = array();
			$idww = getcharid($char['name'],$char['realm']);
			$data = array(
				'uid'					=> $roster->auth->user['id'],
				'member_id'				=> (isset($idww['member_id']) ? $idww['member_id'] : '0'),
				'guild_id'				=> (isset($idww['guild_id']) ? $idww['guild_id'] : '0'),
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
		$jscript = '

		//closepopup();

		';
		roster_add_js($jscript, 'inline', 'header', false, false);
	}
}



if ($stage == 1)
{
	$roster->tpl->assign_vars(array(
			'STAGE'		=> $stage,
			'TEXT'		=> 'stage 1',
		)
	);

	
	$js = "
	
		
	";
	roster_add_js($js, 'inline', 'header', false, false);
	
}
if ($stage == 3)
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
				'PROFILE'	=> (( active_addon('info') && $row['server'] && $row['member_id'] != 0 ) ? makelink('char-info&amp;a=c:' . $row['member_id']) : '' )
			)
		);
	}

}

$roster->tpl->set_filenames(array('alt' => $addon['basename'] . '/alt.html'));
$roster->tpl->display('alt');


	function getcharid($name,$server)
	{
		global $roster, $addon;
		$mid = array();
		$sql = 'SELECT `member_id`,`name`,`server`,`guild_id` FROM `' . $roster->db->table('members') . '` WHERE `name` = "' . $name . '" AND `server` = "'.$server.'"';
		$query = $roster->db->query($sql);
		while( $row = $roster->db->fetch($query) )
		{
			$mid = array(
			'member_id'	=> $row['member_id'],
			'name'		=> $row['name'],
			'server'	=> $row['server'],
			'guild_id'	=> $row['guild_id']
			);
		}
		return $mid;
	}			
?>
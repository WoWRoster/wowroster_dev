<?php

if( !isset($user) )
{
include_once ($addon['inc_dir'] . 'conf.php');
}

if(isset($_POST['op']) && $_POST['op']=='register')
{
	// If the Register form has been submitted
	$err = array();
	
	if(strlen($_POST['username'])<4 || strlen($_POST['username'])>64)
	{
		$err[]='Your username must be between 3 and 64 characters!';
	}

	if (isset($_POST['password1']) && isset($_POST['password2']) && $_POST['password1'] == $_POST['password2'])
	{
		$pass = md5($_POST['password1']);
	}
	
	//"SELECT COUNT(*) AS `check` FROM %s WHERE `email` = '%s' AND `active` = '1'";
	//
	$email = mysql_real_escape_string($_POST['email']);
	if ( $user->user->checkEMail($email) )
	{
		// ok the email passed the form check now see if its used anywhere else...
		$em = "SELECT COUNT(`email`) AS `check` FROM `".$roster->db->table('user_members')."` WHERE `email` = '".$email."'";
		$ema = $roster->db->query($em);
		$rowem = $roster->db->fetch($ema);
		if ($rowem['check'] == '0')
		{
		
			if(!count($err))
			{
			
				$_POST['username'] = mysql_real_escape_string($_POST['username']);
				
				$querya = "SELECT `name`,`guild_rank` FROM `".$roster->db->table('members')."` WHERE `name` = '".$_POST['username']."';";
				$resulta = $roster->db->query($querya);
				if( $resulta )
				{
					$row = $roster->db->fetch($resulta);
					$rank = $row['guild_rank'];
				}
				else
				{
					$rank = '';
				}

				$data = array(
					'usr'		=> $_POST['username'],
					'pass'		=> $pass,
					'email'		=> $email,
					'regIP'		=> $_SERVER['REMOTE_ADDR'],
					'dt'		=> $roster->db->escape(gmdate('Y-m-d H:i:s')),
					'access'	=> '0:'.$rank,
					'active'	=> '1'
				);
				$query = 'INSERT INTO `' . $roster->db->table('user_members') . '` ' . $roster->db->build_query('INSERT', $data);

				// user link table i was hoping to NOT use this....
				
				if( $roster->db->query($query) )
				{
					$uuid = $roster->db->insert_id();
					$roster->set_message('You are registered and can now login','User Register:','notice');
					
					$querya = "SELECT `name`,`guild_id`,`server`,`region`,`member_id` FROM `".$roster->db->table('members')."` WHERE `name` = '".$_POST['username']."';";
					$resulta = $roster->db->query($querya);
					$a = "INSERT INTO `".$roster->db->table('profile','user')."` (`uid`, `signature`, `avatar`, `avsig_src`, `show_fname`, `show_lname`, `show_email`, `show_city`, `show_country`, `show_homepage`, `show_notes`, `show_joined`, `show_lastlogin`, `show_chars`, `show_guilds`, `show_realms`) VALUES ('$uuid', '', '', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');";
					$aa = $roster->db->query($a);

					if( !$resulta )
					{
						die_quietly($roster->db->error, 'user Profile', __FILE__,__LINE__,$querya);
					}
					
					echo $roster->auth->getLoginForm();
					return;

				}
				else
				{
					$roster->set_message('There was a DB error while creating your user.', '', 'error');
					$roster->set_message('<pre>' . $roster->db->error() . '</pre>', 'MySQL Said', 'error');
				}
			}
		}
		else
		{
			$roster->set_message($roster->locale->act['user_user']['msg31'],$roster->locale->act['user_page']['register'],'error');
		}

	}
	else
	{
		//echo $roster->locale->act['user_user']['msg16']
		$roster->set_message($roster->locale->act['user_user']['msg16'],$roster->locale->act['user_page']['register'],'warning');
	}
	if(count($err))
	{
		$e = implode('<br />',$err);
	}

}

if ($addon['config']['char_auth'] == '1')
{
	$roster->tpl->assign_vars(array(
		'CHAR_AUTH'		=> $addon['config']['char_auth'],
		'CNAMETT' 		=> makeOverlib($roster->locale->act['cname_tt'],$roster->locale->act['cname'],'',1,'',',WRAP'),
		'CNAME' 		=> $roster->locale->act['cname'],
		'EMAILTT' 		=> makeOverlib($roster->locale->act['cemail_tt'],$roster->locale->act['cemail'],'',1,'',',WRAP'),
		'EMAIL' 		=> $roster->locale->act['cemail'],
		)
	);

}
else
{
	$roster->tpl->assign_vars(array(
		'CHAR_AUTH'		=> $addon['config']['char_auth'],
		'CNAMETT' 		=> makeOverlib($roster->locale->act['cname_tt'],$roster->locale->act['cname'],'',1,'',',WRAP'),
		'CNAME' 		=> $roster->locale->act['cname'],
		'EMAILTT' 		=> makeOverlib($roster->locale->act['cemail_tt'],$roster->locale->act['cemail'],'',1,'',',WRAP'),
		'EMAIL' 		=> $roster->locale->act['cemail'],
		)
	);
}

$roster->tpl->set_filenames(array(
	'register' => 'register.html'
	)
);
$roster->tpl->display('register');	

?>
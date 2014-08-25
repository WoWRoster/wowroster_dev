<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: index.php 1686 2008-02-17 14:47:45Z pleegwat $
 * @link       http://www.wowroster.net
 * @package    GuildHistory
 */

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}
if( !$roster->auth->getAuthorized($addon['config']['guildhistory_access']) )
{
	print
	'<span class="title_text">'.$roster->locale->act['login'].'</span><br />'.
	$roster->auth->getLoginForm();
 
	return; //Kicks out of the AddOn and back to the Roster framework
}
else {
  	if ( (isset($_REQUEST['order'])) && ($_REQUEST['order'] != ''))
  	{
	    $order = $_REQUEST['order'];
	}
	else 
	{
	  	$order = "ASC";
	}
	$query = "SELECT * FROM `".$roster->db->table('guildhistory', 'guildhistory')."` 
			 WHERE `guild_id` = '".$roster->data['guild_id']."' ORDER BY `logtime` $order";
	if ($roster->db->query($query)) 
	{
  		$result = $roster->db->query($query);
	  	if ($roster->db->num_rows($result) > 0)
  		{
			if ($addon['config']['guildhistory_format'] == 0)
			{
				$content = border('sgreen', 'start', $roster->locale->act['guildhistory'], '40%');
				$content .= '<table border="0" cellpadding="0" cellspacing="0" align="center" 
							width="100%" style="padding:5px;">'."\n";
				$content .= '	<tr>'."\n";
				if ($order == 'ASC')
				{
					$content .= '		<th><a href="'.makelink('guild-guildhistory').'&amp;order=DESC" 
										target="_self">'.$roster->locale->act['datetime'].'</a></th>'
										."\n";
				}
				else 
				{
					$content .= '		<th><a href="'.makelink('guild-guildhistory').'&amp;
										order=ASC" target="_self">'.$roster->locale->act['datetime'].
										'</a></th>'."\n";			  
				}
				$content .= '		<th>'.$roster->locale->act['action'].'</th>'."\n";
				$content .= '	</tr>'."\n";
				$content .= '	<tr><td colspan="2"><hr /></td></tr>'."\n";
				$line = 0;
			    while ($data = $roster->db->fetch($result)) 
		    	{
		    	 	$temp = explode(' ', $data['logtime']);
					if ($roster->config['locale'] == 'deDE') 
					{
			     		$temp_date = explode('-', $temp[0]);
			    	 	$temp_time = explode(':', $temp[1]);
			     		$year = $temp_date[0];
				     	$month = $temp_date[1];
				     	$day = $temp_date[2];
			    	 	$logtime = $day.'.'.$month.'.'.$year.'&nbsp;'.substr($temp[1], 0, 5);
		    		}
				    else 
					{
			    	  	$logtime = $data['logtime'];			  	
			    	 	$logtime = $temp[0].'&nbsp;'.substr($temp[1], 0, 5);
					}
					if ($data['type'] != '')
					{
						if ($line == 0)
						{
							$content .= '<tr class="membersRow1"><td>'.$logtime.'</td><td style="text-align:left;">';
							$line++;
						}
						else {
							$content .= '<tr class="membersRow2"><td>'.$logtime.'</td><td style="text-align:left;">';
							$line = 0;						  	
						}
						if ($data['type'] == 'join') 
						{
						  	$content .= $data['player1'].'&nbsp;'.
							  			$roster->locale->act[$data['type']];
						}
						elseif ($data['type'] == 'remove')
						{
						  	$content .= $data['player1'].'&nbsp;'.
							  			$roster->locale->act[$data['type']].
							  			'&nbsp;'.$data['player2'];			  
						}
						elseif ($data['type'] == 'invite') 
						{
							$content .= $data['player1'].'&nbsp;'.$roster->locale->act['had'].'&nbsp;'.
										$data['player2'].'&nbsp;'.$roster->locale->act[$data['type']];  
						}
						elseif ($data['type'] == 'quit')
						{
							$content .= $data['player1'].'&nbsp;'.$roster->locale->act[$data['type']];
						}
						else 
						{
							$content .= $data['player1'].'&nbsp;'.$roster->locale->act[$data['type']].
										'&nbsp;'.$data['player2'].'&nbsp;'.$roster->locale->act['to'].
										'&nbsp;'.$data['rank'];
						}
						$content .= '</td></tr>'."\n";
					}
				}
				$content .= '</table>'."\n";
				$content .= border('sgreen', 'end','');
			}
			else {
			  	if ($addon['config']['guildhistory_line_format'] == 0)
			  	{
				  	$content = border('sgreen', 'start', $roster->locale->act['guildhistory'], '60%');
					$content .= '<table border="0" cellpadding="2" cellspacing="0" align="left" width="100%" style="text-align:left;">'."\n";
				  	$content .= '	<tr><td class="membersRow1" style="text-align:center;">'.
					  			str_replace('%3', $roster->data['guild_num_accounts'], 
					  			str_replace('%2', $roster->data['guild_num_members'], 
								str_replace('%1', $roster->data['guild_name'], 
				  				$roster->locale->act['blog_header_text']))).'<br />';
					if ($order == 'ASC') {
					  	$content .= '<a href="'.
					  				makelink('guild-guildhistory').
									'&amp;order=DESC" target="_self">'.
									$roster->locale->act['sorting_asc'].
									'</a>';
					}
					else 
					{
					  	$content .= '<a href="'.
					  				makelink('guild-guildhistory').
									'&amp;order=ASC" target="_self">'.
									$roster->locale->act['sorting_desc'].
									'</a>';				  
					}
					$content .= '</td></tr>'."\n";
					$content .= '	<tr><td class="membersRow2"><hr /></td></tr>'."\n";
				    while ($data = $roster->db->fetch($result)) 
		    		{
		    	  		$rnd = rand(1,5);
		    		  	if ($line == 0) {
				    	  	$content .= '<tr><td class="membersRow1">';
				    	  	$line++;
			    		}
				    	else 
						{
			    		  	$content .= '<tr><td class="membersRow2">';
							$line = 0;  
						}
				  	 	$temp = explode(' ', $data['logtime']);
						if ($roster->config['locale'] == 'deDE') 
						{
			   	 			$temp_date = explode('-', $temp[0]);
			   		 		$temp_time = explode(':', $temp[1]);
			   				$year = $temp_date[0];
			    	 		$month = $temp_date[1];
					     	$day = $temp_date[2];
				    	 	$logtime = $temp[1];
			    	 		$logdate = $day.'.'.$month.'.'.$year;
			   			}
			   			else {
			    		 	$logtime = $temp[1];
		    	 			$logdate = $temp[0];				  	
						}
						$length = strlen($logtime);
						$logtime = substr($logtime, -$length, 5);
						if ($data['type'] == 'join') {
						  	$content .= str_replace('%4', $logtime, 
							  			str_replace('%3', $logdate, 
										str_replace('%2', $data['player2'], 
										str_replace('%1', $data['player1'], 
										$roster->locale->act['blog_line_on_join_'.$rnd]))));
						}
						elseif ($data['type'] == 'remove') {
					  		$content .= str_replace('%4', $logtime, 
							  			str_replace('%3', $logdate, 
										str_replace('%2', $data['player2'], 
										str_replace('%1', $data['player1'], 
										$roster->locale->act['blog_line_on_remove_'.$rnd]))));
						}	
						elseif ($data['type'] == 'quit') {
						  	$content .= str_replace('%4', $logtime, 
							  			str_replace('%3', $logdate, 
										str_replace('%2', $data['player2'], 
										str_replace('%1', $data['player1'], 
										$roster->locale->act['blog_line_on_quit_'.$rnd]))));
						}
						elseif ($data['type'] == 'invite') {
						  	$content .= str_replace('%4', $logtime, 
						  				str_replace('%3', $logdate, 
										str_replace('%2', $data['player2'], 
										str_replace('%1', $data['player1'], 
										$roster->locale->act['blog_line_on_invite_'.$rnd]))));
						}
						elseif ($data['type'] == 'demote') {
						  	$content .= str_replace('%5', $data['rank'], 
						  				str_replace('%4', $logtime, 
										str_replace('%3', $logdate, 
										str_replace('%2', $data['player2'], 
										str_replace('%1', $data['player1'], 
										$roster->locale->act['blog_line_on_demote_'.$rnd])))));
						}
						elseif ($data['type'] == 'promote') {
						  	$content .= str_replace('%5', $data['rank'], 
							  			str_replace('%4', $logtime, 
										str_replace('%3', $logdate, 
										str_replace('%2', $data['player2'], 
										str_replace('%1', $data['player1'], 
										$roster->locale->act['blog_line_on_promote_'.$rnd])))));
						}
						else {
					  		$content .= str_replace('%5', $data['rank'], 
							  			str_replace('%4', $logtime, 
										str_replace('%3', $logdate, 
										str_replace('%2', $data['player2'], 
										str_replace('%1', $data['player1'], 
										$roster->locale->act['blog_line_on_else_'.$rnd])))));
						}
						$content .= '</td></tr>';
					}
					$content .= '</table>'."\n";
					$content .= border('sgreen', 'end', '');			  			
				}	
				else
				{
				  	$prev_logdate = '';
			    	while ($data = $roster->db->fetch($result)) 
		    		{
				  	 	$temp = explode(' ', $data['logtime']);
						if ($roster->config['locale'] == 'deDE') 
						{
			   	 			$temp_date = explode('-', $temp[0]);
			   		 		$temp_time = explode(':', $temp[1]);
			   				$year = $temp_date[0];
			    	 		$month = $temp_date[1];
					     	$day = $temp_date[2];
				    	 	$logtime = $temp[1];
			    	 		$logdate = $day.'.'.$month.'.'.$year;
			   			}
			   			else {
			    		 	$logtime = $temp[1];
		    	 			$logdate = $temp[0];				  	
						}
						if ($prev_logdate != $logdate)
						{
						  	$index = 0;
							$prev_logdate = $logdate;
						}
						else {
						  	$index++;
						}
		    			$historie[$logdate][$index]['player1'] = $data['player1'];
		    			$historie[$logdate][$index]['player2'] = $data['player2'];
		    			$historie[$logdate][$index]['type'] = $data['type'];
		    			$historie[$logdate][$index]['rank'] = $data['rank'];
		    			$historie[$logdate][$index]['date'] = $logdate;
		    			$historie[$logdate][$index]['time'] = $logtime;
					}
					$datekeys = array_keys($historie);
				  	$content = border('sgreen', 'start', $roster->locale->act['guildhistory'], '60%');
					$content .= '<table border="0" cellpadding="2" cellspacing="0" align="left" width="100%" style="text-align:left;">'."\n";
				  	$content .= '	<tr><td class="membersRow1" style="text-align:center;">'.
					  			str_replace('%3', $roster->data['guild_num_accounts'], 
					  			str_replace('%2', $roster->data['guild_num_members'], 
								str_replace('%1', $roster->data['guild_name'], 
				  				$roster->locale->act['blog_header_text']))).'<br />';
					if ($order == 'ASC') {
					  	$content .= '<a href="'.
					  				makelink('guild-guildhistory').
									'&amp;order=DESC" target="_self">'.
									$roster->locale->act['sorting_asc'].
									'</a>';
					}
					else 
					{
					  	$content .= '<a href="'.
					  				makelink('guild-guildhistory').
									'&amp;order=ASC" target="_self">'.
									$roster->locale->act['sorting_desc'].
									'</a>';				  
					}
					$content .= '</td></tr>'."\n";
					$content .= '	<tr><td class="membersRow1"><hr /></td></tr>'."\n";
					foreach ($datekeys as $key) {
					  	$content .= '	<tr><td class="membersRow1"><u>'.$key.'</u></td></tr>'."\n";
					  	$content .= '	<tr><td class="membersRow2">'."\n";
					  	foreach ($historie[$key] as $data) {
			    	  		$rnd = rand(1,5);
							if ($data['type'] == 'join') {
							  	$content .= str_replace('%4', $data['time'], 
								  			str_replace('%3', $data['date'], 
											str_replace('%2', $data['player2'], 
											str_replace('%1', $data['player1'], 
											$roster->locale->act['blog_text_on_join_'.$rnd]))));
							}
							elseif ($data['type'] == 'remove') {
							  	$content .= str_replace('%4', $data['time'], 
								  			str_replace('%3', $data['date'], 
											str_replace('%2', $data['player2'], 
											str_replace('%1', $data['player1'], 
											$roster->locale->act['blog_text_on_remove_'.$rnd]))));
							}	
							elseif ($data['type'] == 'quit') {
							  	$content .= str_replace('%4', $data['time'], 
								  			str_replace('%3', $data['date'], 
											str_replace('%2', $data['player2'], 
											str_replace('%1', $data['player1'], 
											$roster->locale->act['blog_text_on_quit_'.$rnd]))));
							}
							elseif ($data['type'] == 'invite') {
							  	$content .= str_replace('%4', $data['time'], 
								  			str_replace('%3', $data['date'], 
											str_replace('%2', $data['player2'], 
											str_replace('%1', $data['player1'], 
											$roster->locale->act['blog_text_on_invite_'.$rnd]))));
							}
							elseif ($data['type'] == 'demote') {
							  	$content .= str_replace('%5', $data['rank'], 
							  				str_replace('%4', $data['time'], 
								  			str_replace('%3', $data['date'], 
											str_replace('%2', $data['player2'], 
											str_replace('%1', $data['player1'], 
											$roster->locale->act['blog_text_on_demote_'.$rnd])))));
							}
							elseif ($data['type'] == 'promote') {
							  	$content .= str_replace('%5', $data['rank'], 
							  				str_replace('%4', $data['time'], 
								  			str_replace('%3', $data['date'], 
											str_replace('%2', $data['player2'], 
											str_replace('%1', $data['player1'], 
											$roster->locale->act['blog_text_on_promote_'.$rnd])))));
							}
							else {
						  		$content .= str_replace('%5', $data['rank'], 
							  				str_replace('%4', $data['time'], 
								  			str_replace('%3', $data['date'], 
											str_replace('%2', $data['player2'], 
											str_replace('%1', $data['player1'], 
											$roster->locale->act['blog_text_on_else_'.$rnd])))));
							}
						}
					$content .= '	</td></tr>';
					$content .= '	<tr><td><hr /></td></tr>'."\n";
					}
					$content .= '</table>'."\n";
					$content .= border('sgreen', 'end', '');
				}
#				$content .= print_r($roster->data, true);
			}
		}
		else 
		{
	  		$content .= border('sred', 'start', $roster->locale->act['guildhistory'], '40%');
		  	$content .= $roster->locale->act['no_data'];
	  		$content .= border('sred', 'end', '');
		}
	}
	else 
	{
  		$content .= border('sred', 'start', $roster->locale->act['guildhistory'], '40%');
	  	$content .= $roster->locale->act['no_data'];
  		$content .= border('sred', 'end', '');
	}
}

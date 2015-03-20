<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5" * @package    InstanceKeys
 * @subpackage UpdateHook
*/

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

/**
 * InstanceKeys Update Hook
 *
 * @package    InstanceKeys
 * @subpackage UpdateHook
 */
class guildhistoryUpdate
{
	// Update messages
	var $messages = '';

	// Addon data object, recieved in constructor
	var $data;

	// LUA upload files accepted. We don't use any.
	var $files = array();
	var $timestamp = '';

	/**
	 * Constructor
	 *
	 * @param array $data
	 *		Addon data object
	 */
	function guildhistoryUpdate($data)
	{
		$this->data = $data;
	}

	/**
	 * Resets addon messages
	 */
	function reset_messages()
	{
		/**
		 * We display the addon name at the beginning of the output line. If
		 * the hook doesn't exist on this side, nothing is output. If we don't
		 * produce any output (update method off) we empty this before returning.
		 */

		$this->messages = '<li>GuildHistory<ul>';
	}

	/**
	 * Guild post trigger
	 *
	 * @param array $guild
	 *		CP.lua guild data
	 */
	function guild_post($guild)
	{
		global $roster;
		$history_data = $guild['EventLog'];
		$guild_id = $guild['guild_id'];
		for ($id = 1; $id <= 100; $id++) 
		{
		  	$this->messages .= '<li>Processing Line '.$id.' / 100 - Status: ';
		  	$this->timestamp = $history_data[$id]['Time'];
		  	$time = $this->make_time($history_data[$id]['LogTime']);
			$query = "SELECT * FROM `".$roster->db->table('guildhistory', $this->data['basename'])."` WHERE `id` = '$id' AND `guild_id` = '$guild_id'";
			if ($roster->db->query($query)) 
			{
			  	$result = $roster->db->query($query);
			  	if ($roster->db->num_rows($result) > 0) 
				{
				  	$query = "UPDATE `".$roster->db->table('guildhistory', $this->data['basename'])."` SET `player1` = '".$history_data[$id]['Player1']."', `type` = '".$history_data[$id]['Type']."', `player2` = '".$history_data[$id]['Player2']."', `time` = '".date('Y-m-d H:i', $history_data[$id]['Time'])."', `logtime` = '".date('Y-m-d H:i:s', $time[0])."', `rank` = '".$history_data[$id]['Rank']."' WHERE `id` = '$id'  AND `guild_id` = '$guild_id'";
				}
				else 
				{
				  	$query = "INSERT INTO `".$roster->db->table('guildhistory', $this->data['basename'])."` (`guild_id`, `id`, `player1`, `type`, `player2`, `time`, `logtime`, `rank`) VALUES ('$guild_id', '$id', '".$history_data[$id]['Player1']."', '".$history_data[$id]['Type']."', '".$history_data[$id]['Player2']."','".date('Y-m-d H:i', $history_data[$id]['Time'])."', '".date('Y-m-d H:i', $time[0])."', '".$history_data[$id]['Rank']."')";
				} 
				if ($roster->db->query($query)) 
				{
				  	$this->messages .= '<font color="#00FF00">Ok</font></li>'."\n";
				}
				else {
				  	$this->messages .= '<font color="#FF0000">Failed</font></li>'."\n";
				}
			}
			else 
			{
			  	$query = "INSERT INTO `".$roster->db->table('guildhistory', $this->data['basename'])."` (`guild_id`, `id`, `player1`, `type`, `player2`, `time`, `logtime`, `rank`) VALUES ('$guild_id', '$id', '".$history_data[$id]['Player1']."', '".$history_data[$id]['Type']."', '".$history_data[$id]['Player2']."','".date('Y-m-d H:i:s', $history_data[$id]['Time'])."', '".date('Y-m-d H:i:s', $time[0])."', '".$history_data[$id]['Rank']."')";
				if ($roster->db->query($query)) 
				{
				  	$this->messages .= '<font color="#00FF00">Ok</font></li>'."\n";
				}
				else 
				{
				  	$this->messages .= '<font color="#FF0000">Failed</font></li>'."\n";
				}
			}
		}
		$this->messages .= '</ul></li>';
		return true;
	}

	function make_time( $time )
	{
		global $update;

		list($lastOnlineYears,$lastOnlineMonths,$lastOnlineDays,$lastOnlineHours) = explode(':',$time);

		$timeString = '-';
		if ($lastOnlineYears > 0)
		{
			$timeString .= $lastOnlineYears . ' Years ';
		}
		if ($lastOnlineMonths > 0)
		{
			$timeString .= $lastOnlineMonths . ' Months ';
		}
		if ($lastOnlineDays > 0)
		{
			$timeString .= $lastOnlineDays . ' Days ';
		}
		$timeString .= max($lastOnlineHours,1) . ' Hours';

		$lastOnlineTime = strtotime($timeString,$this->timestamp);
		return getDate($lastOnlineTime);
	}
}
<?php
/**
 * WoWRoster.net WoWRoster
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: update_hook.php 2460 2012-05-14 16:53:38Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @package    GuildInfo
 * @subpackage UpdateHook
 */

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

/**
 * MembersList Update Hook
 *
 * @package    MembersList
 * @subpackage UpdateHook
 */
class feedsUpdate
{
	// Update messages
	var $messages = '';

	// Addon data object, recieved in constructor
	var $data;

	// LUA upload files accepted. We don't use any.
	var $files = array();

	// Character data cache
	var $chars = array();

	// Officer note check. Default true, because manual update bypasses the check.
	var $passedCheck=true;
	var $assignstr = array();
	var $guild_id = '';

	/**
	 * Constructor
	 *
	 * @param array $data
	 *		Addon data object
	 */
	function feedsUpdate($data)
	{
		$this->data = $data;

		include_once($this->data['conf_file']);
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

		$this->messages = 'Activity Feeds: ';
	}

	/**
	 * Resets the SQL insert/update string holder
	 */
	function reset_values()
	{
		$this->assignstr = '';
	}

	/**
	 * Guild_pre trigger, set out guild id here
	 *
	 * @param array $guild
	 * 		CP.lua guild data
	 */
	function guild_pre($guild)
	{
		global $roster, $update;

		$this->guild_id = $guild['guild_id'];

		require_once (ROSTER_LIB . 'update.lib.php');
		$update = new update();
		$feed = $roster->api2->fetch('guild',array('name'=>$guild['GuildName'],'server'=>$guild['Server'],'fields'=>'news'));
		//$roster->api->Guild->getGuildInfo($guild['Server'],$guild['GuildName'],'3');
		$tooltip_text = '';
		foreach ($feed['news'] as $e => $a)
		{
			//print_r($this->data);
			if ($a['type'] == 'playerAchievement' OR $a['type'] == 'guildAchievement')
			{
				$tooltip_text = '<div style="width:100%;style="color:#FFB100""><span style="float:right;">' . $a['achievement']['points'] . ' Points</span>' . $a['achievement']['title'] . '</div><br>' . $a['achievement']['description'] . '';
				$crit='';
				if (isset($a['featOfStrength']) && $a['featOfStrength'] != 1)
				{
					$crit .= '<br><div class="meta-achievements"><ul>';
					foreach ($a['achievement']['criteria'] as $r => $d)
					{
						$crit .= '<li>'.$d['description'].'</li>';
					}
					$crit .= '</ul></div>';
				}
				$tooltip_text .= $crit;
			}
			else
			{
				$tooltip_text = '';
			}
			$title = '';
			$update_sql = null;
			$update->reset_values();
			$update->add_value('guild_id', 					$this->guild_id);
			$update->add_value('type' ,							$a['type']);
			
			if (isset($a['character']))
			{
				$update->add_value('Member',					$a['character']);
			}
			if ($a['type'] == 'playerAchievement' OR $a['type'] == 'guildAchievement')
			{
				$update->add_value('Achievement' ,				$tooltip_text);
				$update->add_value('achievement_icon' ,			$a['achievement']['icon']);
				$update->add_value('achievement_title' ,		$a['achievement']['title']);
				$update->add_value('achievement_points' ,		$a['achievement']['points']);
				$update->add_value('achievement_id' ,			$a['achievement']['id']);
				if (isset($a['criteria']) && is_array($a['criteria']))
				{
					if (count($a['criteria']) == 1)
					{
						$update->add_value('criteria_description' ,		$a['criteria']['description']);
					}
				}
				$title = $a['achievement']['title'];
			}
			if ($a['type'] == 'BOSSKILL')
			{
				$update->add_value('achievement_points' ,		$a['quantity']);
			}
			if (isset($a['itemId']))
			{
				$update->add_value('item_id' ,					$a['itemId']);
			}
			$update->add_value('timestamp' ,					$a['timestamp']);
			
			if ($a['type'] == 'playerAchievement' OR $a['type'] == 'guildAchievement')
			{
			$queryx = "SELECT * FROM `".$roster->db->table('guild_feed',$this->data['basename'])."` WHERE `guild_id`='" . $this->guild_id . "' and `achievement_title` = '".$roster->db->escape($title)."' AND `timestamp` = '".$a['timestamp']."'";
			}
			if ($a['type'] == 'itemLoot' OR  $a['type'] == 'itemPurchase')
			{
			$queryx = "SELECT * FROM `".$roster->db->table('guild_feed',$this->data['basename'])."` WHERE `guild_id`='" . $this->guild_id . "' and `item_id` = '".$a['itemId']."'";
			}
			//$queryx = "SELECT * FROM `".$roster->db->table('char_feed',$this->data['basename'])."` WHERE `member_id`='" . $member_id . "' and `timestamp`='".$a['timestamp']."'";
			$resultx = $roster->db->query( $queryx );
			$update_sql = $roster->db->num_rows( $resultx );

			$rowg = $roster->db->fetch($resultx);
			if (!isset($a['itemId']) && $update_sql == 1)
			{
				if ($rowg['achievement_title'] != $title)
				{
					$update_sql = 0;
				}
			}
			if (isset($a['itemId']) && $update_sql == 1)
			{
				if ($a['itemId'] != $rowg['item_id'])
				{
					$update_sql = 0;
				}
			}

			if( $update_sql >= '1' )
			{

			}	
			else
			{
				$querystr = "INSERT INTO `".$roster->db->table('guild_feed',$this->data['basename'])."` SET " .  $update->assignstr . ";";
				$result = $roster->db->query($querystr);
				$this->messages .= '.';
			}

			
		}
		
		return true;
	}

	/**
	 * Guild trigger:
	 *
	 * @param array $char
	 *		CP.lua guild member data
	 * @param int $member_id
	 * 		Member ID
	 */
	function char($char, $member_id)
	{
		global $roster, $update;
		//echo '<pre>';
		//print_r($char);
		if (isset($char['API']))
		{
			$feed = $char['API'];
		}
		else
		{
			$feed = $roster->api2->fetch('character',array('name'=>$char['Name'],'server'=>$char['Server'],'fields'=>'feed'));
			//$roster->api->Char->getCharInfo($char['Server'],$char['Name'],'16');
		}
		
		foreach ($feed['feed'] as $e => $a)
		{
			//print_r($this->data);
			if ($a['type'] != 'LOOT')
			{
				$tooltip_text = '<div style="width:100%;style="color:#FFB100""><span style="float:right;">' . $a['achievement']['points'] . ' Points</span>' . $a['achievement']['title'] . '</div><br>' . $a['achievement']['description'] . '';
				$crit='';
				if ($a['featOfStrength'] != 1)
				{
					$crit .= '<br><div class="meta-achievements"><ul>';
					foreach ($a['achievement']['criteria'] as $r => $d)
					{
						$crit .= '<li>'.$d['description'].'</li>';
					}
					$crit .= '</ul></div>';
				}
				$tooltip_text .= $crit;
			}
			else
			{
				$tooltip_text = '';
			}
			$title = '';
			$update_sql = null;
			$update->reset_values();
			$update->add_value('member_id', 					$member_id);
			$update->add_value('type' ,							$a['type']);
			//$update->add_ifvalue('Member' ,					$a['']);
			if ($a['type'] != 'LOOT')
			{
				$update->add_value('Achievement' ,				$tooltip_text);
				$update->add_value('achievement_icon' ,			$a['achievement']['icon']);
				$update->add_value('achievement_title' ,		$a['achievement']['title']);
				$title = $a['achievement']['title'];
				if ($a['type'] != 'BOSSKILL')
				{
					$update->add_value('achievement_points' ,		$a['achievement']['points']);
				}
				$update->add_value('achievement_id' ,			$a['achievement']['id']);
			}
			if ($a['type'] == 'CRITERIA')
			{
				$update->add_value('criteria_description' ,		$a['criteria']['description']);
			}
			if ($a['type'] == 'BOSSKILL')
			{
				$update->add_value('achievement_points' ,		$a['quantity']);
			}
			if (isset($a['itemId']))
			{
				$update->add_value('item_id' ,					$a['itemId']);
			}
			$update->add_value('timestamp' ,					$a['timestamp']);
			
			
			if ($a['type'] != 'LOOT')
			{
			$queryx = "SELECT * FROM `".$roster->db->table('char_feed',$this->data['basename'])."` WHERE `member_id`='" . $member_id . "' and `achievement_title` = '".$roster->db->escape($title)."' AND `timestamp` = '".$a['timestamp']."'";
			}
			if ($a['type'] == 'LOOT')
			{
			$queryx = "SELECT * FROM `".$roster->db->table('char_feed',$this->data['basename'])."` WHERE `member_id`='" . $member_id . "' and `item_id` = '".$a['itemId']."'";
			}
			//$queryx = "SELECT * FROM `".$roster->db->table('char_feed',$this->data['basename'])."` WHERE `member_id`='" . $member_id . "' and `timestamp`='".$a['timestamp']."'";
			$resultx = $roster->db->query( $queryx );
			$update_sql = $roster->db->num_rows( $resultx );

			$rowg = $roster->db->fetch($resultx);
			if ($a['type'] != 'LOOT' && $update_sql == 1)
			{
				if ($rowg['achievement_title'] != $title)
				{
					$update_sql = 0;
				}
			}
			if ($a['type'] == 'LOOT' && $update_sql == 1)
			{
				if ($a['itemId'] != $rowg['item_id'])
				{
					$update_sql = 0;
				}
			}

			if( $update_sql >= '1' )
			{

			}	
			else
			{
				$querystr = "INSERT INTO `".$roster->db->table('char_feed',$this->data['basename'])."` SET " .  $update->assignstr . ";";
				$result = $roster->db->query($querystr);
				if ( $result )
				{
					$this->messages .= '.';
				}
			}

			
		}
		return true;
		
	}


}

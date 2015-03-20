<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LUA updating library
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license	http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @package	WoWRoster
 * @subpackage LuaUpdate
 */

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

/**
 * Lua Update handler
 *
 * @package	WoWRoster
 * @subpackage LuaUpdate
 */
class update
{
	var $textmode = false;
	var $uploadData;
	var $addons = array();
	var $files = array();
	var $locale;
	var $blinds = array();

	var $processTime;			// time() starting timestamp for enforceRules

	var $messages = array();
	var $errors = array();
	var $assignstr = '';
	var $assigngem = '';		// 2nd tracking property since we build a gem list while building an items list

	var $membersadded = 0;
	var $membersupdated = 0;
	var $membersremoved = 0;

	var $current_region = '';
	var $current_realm = '';
	var $current_guild = '';
	var $current_member = '';
	var $talent_build_urls = array();


	/**
	 * Delete Members in database not matching the upload rules
	 */
	function enforceRules( $timestamp )
	{
		global $roster;

		$messages = '';
		// Select and delete all non-matching guilds
		$query = "SELECT *"
			. " FROM `" . $roster->db->table('guild') . "` guild"
			. " WHERE `guild_name` NOT LIKE 'guildless-_';";
		$result = $roster->db->query($query);
		while( $row = $roster->db->fetch($result) )
		{
			$query = "SELECT `type`, COUNT(`rule_id`)"
				   . " FROM `" . $roster->db->table('upload') . "`"
				   . " WHERE (`type` = 0 OR `type` = 1)"
				   . " AND '" . $roster->db->escape($row['guild_name']) . "' LIKE `name` "
				   . " AND '" . $roster->db->escape($row['server']) . "' LIKE `server` "
				   . " AND '" . $roster->db->escape($row['region']) . "' LIKE `region` "
				   . " GROUP BY `type` "
				   . " ORDER BY `type` DESC;";
			if( $roster->db->query_first($query) !== '0' )
			{
				$messages .= '<ul><li>Deleting guild "' . $row['guild_name'] . '" and setting its members guildless.</li>';
				// Does not match rules
				$this->deleteGuild($row['guild_id'], $timestamp);
				$messages .= '</ul>';
			}
		}

		// Select and delete all non-matching guildless members
		$messages .= '<ul>';
		$inClause=array();

		$query = "SELECT *"
			. " FROM `" . $roster->db->table('members') . "` members"
			. " INNER JOIN `" . $roster->db->table('guild') . "` guild"
				. " USING (`guild_id`)"
			. " WHERE `guild_name` LIKE 'guildless-_';";
		$result = $roster->db->query($query);

		while( $row = $roster->db->fetch($result) )
		{
			$query = "SELECT `type`, COUNT(`rule_id`)"
				   . " FROM `" . $roster->db->table('upload') . "`"
				   . " WHERE (`type` = 2 OR `type` = 3)"
				   . " AND '" . $roster->db->escape($row['name']) . "' LIKE `name` "
				   . " AND '" . $roster->db->escape($row['server']) . "' LIKE `server` "
				   . " AND '" . $roster->db->escape($row['region']) . "' LIKE `region` "
				   . " GROUP BY `type` "
				   . " ORDER BY `type` DESC;";
			if( $roster->db->query_first($query) !== '2' )
			{
				$messages .= '<li>Deleting member "' . $row['name'] . '".</li>';
				// Does not match rules
				$inClause[] = $row['member_id'];
			}
		}

		if( count($inClause) == 0 )
		{
			$messages .= '<li>No members deleted.</li>';
		}
		else
		{
			$this->deleteMembers(implode(',', $inClause));
		}
		$this->setMessage($messages . '</ul>');
	}


	/**
	 * Update Memberlog function
	 *
	 */
	function updateMemberlog( $data , $type , $timestamp )
	{
		global $roster;

		$this->reset_values();
		$this->add_ifvalue($data, 'member_id');
		$this->add_ifvalue($data, 'name');
		$this->add_ifvalue($data, 'server');
		$this->add_ifvalue($data, 'region');
		$this->add_ifvalue($data, 'guild_id');
		$this->add_ifvalue($data, 'class');
		$this->add_ifvalue($data, 'classid');
		$this->add_ifvalue($data, 'level');
		$this->add_ifvalue($data, 'note');
		$this->add_ifvalue($data, 'guild_rank');
		$this->add_ifvalue($data, 'guild_title');
		$this->add_ifvalue($data, 'officer_note');
		$this->add_time('update_time', getDate($timestamp));
		$this->add_value('type', $type);

		$querystr = "INSERT INTO `" . $roster->db->table('memberlog') . "` SET " . $this->assignstr . ";";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Member Log [' . $data['name'] . '] could not be inserted',$roster->db->error());
		}
	}


	/**
	 * Delete Guild from database. Doesn't directly delete members, because some of them may have individual upload permission (char based)
	 *
	 * @param int $guild_id
	 * @param string $timestamp
	 */
	function deleteGuild( $guild_id , $timestamp )
	{
		global $roster;

		$query = "SELECT (`guild_name` LIKE 'Guildless-%') FROM `" . $roster->db->table('guild') . "` WHERE `guild_id` = '" . $guild_id . "';";

		if( $roster->db->query_first($query) )
		{
			$this->setError('Guildless- guilds have a special meaning internally. You cannot explicitly delete them, they will be deleted automatically once the last member is deleted. To delete the guildless guild, delete all its members');
		}

		// Set all members as left
		$query = "UPDATE `" . $roster->db->table('members') . "` SET `active` = 0 WHERE `guild_id` = '" . $guild_id . "';";
		$roster->db->query($query);

		// Set those members guildless. After that the guild will be empty, and remove_guild_members will call deleteEmptyGuilds to clean that up.
		$this->remove_guild_members($guild_id, $timestamp);
	}

	/**
	 * Clean up empty guilds.
	 */
	function deleteEmptyGuilds()
	{
		global $roster;

		$query = "DELETE FROM `" . $roster->db->table('guild') . "` WHERE `guild_id` NOT IN (SELECT DISTINCT `guild_id` FROM `" . $roster->db->table('members') . "`);";
		$roster->db->query($query);

	}

	/**
	 * Delete Members in database using inClause
	 * (comma separated list of member_id's to delete)
	 *
	 * @param string $inClause
	 */
	function deleteMembers( $inClause )
	{
		global $roster;

		$messages = '<li>';

		$messages .= 'Character Data..';

		$messages .= 'Skills..';
		$querystr = "DELETE FROM `" . $roster->db->table('skills') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Skill Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Inventory..';
		$querystr = "DELETE FROM `" . $roster->db->table('items') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Inventory Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Quests..';
		$querystr = "DELETE FROM `" . $roster->db->table('quests') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Quest Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Professions..';
		$querystr = "DELETE FROM `" . $roster->db->table('recipes') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Recipe Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Talents..';
		$querystr = "DELETE FROM `" . $roster->db->table('talents') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Talent Data could not be deleted',$roster->db->error());
		}

		$querystr = "DELETE FROM `" . $roster->db->table('talenttree') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Talent Tree Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Glyphs..';
		$querystr = "DELETE FROM `" . $roster->db->table('glyphs') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Talent Tree Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Spellbook..';
		$querystr = "DELETE FROM `" . $roster->db->table('spellbook') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Spell Data could not be deleted',$roster->db->error());
		}

		$querystr = "DELETE FROM `" . $roster->db->table('spellbooktree') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Spell Tree Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Pets..';
		$querystr = "DELETE FROM `" . $roster->db->table('pets') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Pet Data could not be deleted',$roster->db->error());
		}

		$querystr = "DELETE FROM `" . $roster->db->table('companions') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Companion Data could not be deleted',$roster->db->error());
		}

		$messages .= 'Pet Spells..';
		$querystr = "DELETE FROM `" . $roster->db->table('pet_spellbook') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Pet Spell Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Pet Talents..';
		$querystr = "DELETE FROM `" . $roster->db->table('pet_talents') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Pet Talent Data could not be deleted',$roster->db->error());
		}

		$querystr = "DELETE FROM `" . $roster->db->table('pet_talenttree') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Pet Talent Tree Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Reputation..';
		$querystr = "DELETE FROM `" . $roster->db->table('reputation') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Reputation Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Currency..';
		$querystr = "DELETE FROM `" . $roster->db->table('currency') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Currency Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Mail..';
		$querystr = "DELETE FROM `" . $roster->db->table('mailbox') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Mail Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Membership..';
		$querystr = "DELETE FROM `" . $roster->db->table('members') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Member Data could not be deleted',$roster->db->error());
		}


		$messages .= 'Final Character Cleanup..';
		$querystr = "DELETE FROM `" . $roster->db->table('players') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Player Data could not be deleted',$roster->db->error());
		}

		$querystr = "DELETE FROM `" . $roster->db->table('buffs') . "` WHERE `member_id` IN ($inClause)";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Player Buff Data could not be deleted',$roster->db->error());
		}
		if( $roster->config['use_update_triggers'] )
		{
			$messages .= $this->addon_hook('char_delete', $inClause);
		}

		$this->deleteEmptyGuilds();

		$this->setMessage($messages . '</li>');
	}

	/**
	 * Removes guild members with `active` = 0
	 *
	 * @param int $guild_id
	 * @param string $timestamp
	 */
	function remove_guild_members( $guild_id , $timestamp )
	{
		global $roster;

		$querystr = "SELECT * FROM `" . $roster->db->table('members') . "` WHERE `guild_id` = '$guild_id' AND `active` = '0';";

		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Members could not be selected for deletion',$roster->db->error());
			return;
		}

		$num = $roster->db->num_rows($result);
		if( $num > 0 )
		{
			// Get guildless guild for this realm
			$query = "SELECT * FROM `" . $roster->db->table('guild') . "` WHERE `guild_id` = '$guild_id';";
			$result2 = $roster->db->query($query);
			$row = $roster->db->fetch($result2);
			$roster->db->free_result($result2);

			$query = "SELECT `guild_id` FROM `" . $roster->db->table('guild') . "` WHERE `server` = '" . $roster->db->escape($row['server']) . "' AND `region` = '" . $roster->db->escape($row['region']) . "' AND `factionEn` = '" . $roster->db->escape($row['factionEn']) . "' AND `guild_name` LIKE 'guildless-%';";
			$guild_id = $roster->db->query_first($query);

			if( !$guild_id )
			{
				$guilddata['Faction'] = $row['factionEn'];
				$guilddata['FactionEn'] = $row['factionEn'];
				$guilddata['Locale'] = $row['Locale'];
				$guilddata['Info'] = '';
				$guild_id = $this->update_guild($row['server'],'GuildLess-' . substr($row['factionEn'],0,1),strtotime($timestamp),$guilddata,$row['region']);
				unset($guilddata);
			}

			$inClause = array();
			while( $row = $roster->db->fetch($result) )
			{
				$this->setMessage('<li><span class="red">[</span> ' . $row[1] . ' <span class="red">] - Removed</span></li>');
				$this->setMemberLog($row,0,$timestamp);

				$inClause[] = $row[0];
			}
			$inClause = implode(',',$inClause);

			// now that we have our inclause, set them guildless
			$this->setMessage('<li><span class="red">Setting ' . $num . ' member' . ($num > 1 ? 's' : '') . ' to guildless</span></li>');

			$roster->db->free_result($result);

			$this->reset_values();
			$this->add_value('guild_id',$guild_id);
			$this->add_value('note','');
			$this->add_value('guild_rank',0);
			$this->add_value('guild_title','');
			$this->add_value('officer_note','');

			$querystr = "UPDATE `" . $roster->db->table('members') . "` SET " . $this->assignstr . " WHERE `member_id` IN ($inClause);";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Guild members could not be set guildless',$roster->db->error());
			}

			$this->reset_values();
			$this->add_value('guild_id',$guild_id);

			$querystr = "UPDATE `" . $roster->db->table('players') . "` SET " . $this->assignstr . " WHERE `member_id` IN ($inClause);";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Guild members could not be set guildless',$roster->db->error());
			}
		}

		$this->deleteEmptyGuilds();
	}

	/**
	 * Gets guild info from database
	 * Returns info as an array
	 *
	 * @param string $realmName
	 * @param string $guildName
	 * @return array
	 */
	function get_guild_info( $realmName , $guildName , $region='' )
	{
		global $roster;

		$guild_name_escape = $roster->db->escape($guildName);
		$server_escape = $roster->db->escape($realmName);

		if( !empty($region) )
		{
			$region = " AND `region` = '" . $roster->db->escape($region) . "'";
		}

		$querystr = "SELECT * FROM `" . $roster->db->table('guild') . "` WHERE `guild_name` = '$guild_name_escape' AND `server` = '$server_escape'$region;";
		$result = $roster->db->query($querystr) or die_quietly($roster->db->error(),'WowDB Error',__FILE__ . '<br />Function: ' . (__FUNCTION__),__LINE__,$querystr);

		if( $roster->db->num_rows() > 0 )
		{
			$retval = $roster->db->fetch($result);
			$roster->db->free_result($result);

			return $retval;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Function to prepare the memberlog data
	 *
	 * @param array $data | Member info array
	 * @param multiple $type | Action to update ( 'rem','del,0 | 'add','new',1 )
	 * @param string $timestamp | Time
	 */
	function setMemberLog( $data , $type , $timestamp )
	{
		if ( is_array($data) )
		{
			switch ($type)
			{
				case 'del':
				case 'rem':
				case 0:
					$this->membersremoved++;
					$this->updateMemberlog($data,0,$timestamp);
					break;

				case 'add':
				case 'new':
				case 1:
					$this->membersadded++;
					$this->updateMemberlog($data,1,$timestamp);
					break;
			}
		}
	}


	/**
	 * Updates or creates an entry in the guild table in the database
	 * Then returns the guild ID
	 *
	 * @param string $realmName
	 * @param string $guildName
	 * @param array $currentTime
	 * @param array $guild
	 * @return string
	 */
	function update_guild( $realmName , $guildName , $currentTime , $guild , $region )
	{
		global $roster;
		$guildInfo = $this->get_guild_info($realmName,$guildName,$region);

		$this->locale = $guild['Locale'];

		$this->reset_values();

		$this->add_value('guild_name', $guildName);

		$this->add_value('server', $realmName);
		$this->add_value('region', $region);
		$this->add_ifvalue($guild, 'Faction', 'faction');
		$this->add_ifvalue($guild, 'FactionEn', 'factionEn');
		$this->add_ifvalue($guild, 'Motd', 'guild_motd');

		$this->add_ifvalue($guild, 'NumMembers', 'guild_num_members');
		$this->add_ifvalue($guild, 'NumAccounts', 'guild_num_accounts');

		$this->add_ifvalue($guild, 'GuildXP', 'guild_xp');
		$this->add_ifvalue($guild, 'GuildXPCap', 'guild_xpcap');
		$this->add_ifvalue($guild, 'GuildLevel', 'guild_level');

		$this->add_timestamp('update_time', $currentTime);

		$this->add_ifvalue($guild, 'DBversion');
		$this->add_ifvalue($guild, 'GPversion');

		$this->add_value('guild_info_text', str_replace('\n',"<br />",$guild['Info']));

		if( is_array($guildInfo) )
		{
			$querystra = "UPDATE `" . $roster->db->table('guild') . "` SET " . $this->assignstr . " WHERE `guild_id` = '" . $guildInfo['guild_id'] . "';";
			$output = $guildInfo['guild_id'];
		}
		else
		{
			$querystra = "INSERT INTO `" . $roster->db->table('guild') . "` SET " . $this->assignstr;
		}

		$roster->db->query($querystra) or die_quietly($roster->db->error(),'WowDB Error',__FILE__ . '<br />Function: ' . (__FUNCTION__),__LINE__,$querystra);

		if( is_array($guildInfo) )
		{
			$querystr = "UPDATE `" . $roster->db->table('members') . "` SET `active` = '0' WHERE `guild_id` = '" . $guildInfo['guild_id'] . "';";
			$roster->db->query($querystr) or die_quietly($roster->db->error(),'WowDB Error',__FILE__ . '<br />Function: ' . (__FUNCTION__),__LINE__,$querystr);
		}

		if( !is_array($guildInfo) )
		{
			$guildInfo = $this->get_guild_info($realmName,$guildName);
			$output = $guildInfo['guild_id'];
		}

		return $output;
	}


	/**
	 * Updates or adds guild members
	 *
	 * @param int $guildId	| Character's guild id
	 * @param string $name	| Character's name
	 * @param array $char	| LUA data
	 * @param array $currentTimestamp
	 * @return mixed		| False on error, memberid on success
	 */
	function update_guild_member( $guildId , $name , $server , $region , $char , $currentTimestamp , $guilddata )
	{
		global $roster;

		$name_escape = $roster->db->escape($name);
		$server_escape = $roster->db->escape($server);
		$region_escape = $roster->db->escape($region);

		$querystr = "SELECT `member_id` "
			. "FROM `" . $roster->db->table('members') . "` "
			. "WHERE `name` = '$name_escape' "
			. "AND `server` = '$server_escape' "
			. "AND `region` = '$region_escape';";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Member could not be selected for update',$roster->db->error());
			return false;
		}

		$memberInfo = $roster->db->fetch( $result );
		if( $memberInfo )
		{
			$memberId = $memberInfo['member_id'];
		}

		$roster->db->free_result($result);

		$this->reset_values();

		$this->add_value('name', $name);
		$this->add_value('server', $server);
		$this->add_value('region', $region);
		$this->add_value('guild_id', $guildId);
		$this->add_ifvalue($char, 'Class', 'class');
		$this->add_ifvalue($char, 'ClassId', 'classid');
		$this->add_ifvalue($char, 'Level', 'level');
		$this->add_ifvalue($char, 'Note', 'note', '');
		$this->add_ifvalue($char, 'Rank', 'guild_rank');

		if( isset($char['Rank']) && isset($guilddata['Ranks'][$char['Rank']]['Title']) )
		{
			$this->add_value('guild_title', $guilddata['Ranks'][$char['Rank']]['Title']);
		}

		if( isset($guilddata['ScanInfo']) && $guilddata['ScanInfo']['HasOfficerNote'] )
		{
			$this->add_ifvalue($char, 'OfficerNote', 'officer_note', '');
		}

		$this->add_ifvalue($char, 'Zone', 'zone', '');
		$this->add_ifvalue($char, 'Status', 'status', '');
		$this->add_value('active', '1');

		if( isset($char['Online']) && $char['Online'] == '1' )
		{
			$this->add_value('online', 1);
			$this->add_time('last_online', getDate($currentTimestamp));
		}
		else
		{
			$this->add_value('online', 0);
			list($lastOnlineYears,$lastOnlineMonths,$lastOnlineDays,$lastOnlineHours) = explode(':',$char['LastOnline']);

			# use strtotime instead
			#	  $lastOnlineTime = $currentTimestamp - 365 * 24* 60 * 60 * $lastOnlineYears
			#						- 30 * 24 * 60 * 60 * $lastOnlineMonths
			#						- 24 * 60 * 60 * $lastOnlineDays
			#						- 60 * 60 * $lastOnlineHours;
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

			$lastOnlineTime = strtotime($timeString,$currentTimestamp);
			$this->add_time('last_online', getDate($lastOnlineTime));
		}

		if( isset($memberId) )
		{
			$querystr = "UPDATE `" . $roster->db->table('members') . "` SET " . $this->assignstr . " WHERE `member_id` = '$memberId';";
			$this->setMessage('<li>[ ' . $name . ' ]<ul>');
			$this->membersupdated++;

			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError($name . ' could not be inserted',$roster->db->error());
				return false;
			}
		}
		else
		{
			$querystr = "INSERT INTO `" . $roster->db->table('members') . "` SET " . $this->assignstr . ';';
			//$this->setMessage('<li><span class="green">[</span> ' . $name . ' <span class="green">] - Added</span></li>');
			$this->setMessage('<li><span class="green">[</span> ' . $name . ' <span class="green">] - Added</span><ul>');

			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError($name . ' could not be inserted',$roster->db->error());
				return false;
			}

			$memberId = $roster->db->insert_id();

			$querystr = "SELECT * FROM `" . $roster->db->table('members') . "` WHERE `member_id` = '$memberId';";
			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError('Member could not be selected for MemberLog',$roster->db->error());
			}
			else
			{
				$row = $roster->db->fetch($result);
				$this->setMemberLog($row,1,$currentTimestamp);
			}
		}

		// We may have added the last member of the guildless guild to a real guild, so check for empty guilds
		$this->deleteEmptyGuilds();

		return $memberId;
	}

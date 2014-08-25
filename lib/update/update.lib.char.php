<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LUA updating library
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license	http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version	SVN: $Id: update.lib.php 2617 2012-11-29 20:01:49Z ulminia@gmail.com $
 * @link	   http://www.wowroster.net
 * @since	  File available since Release 1.8.0
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
	 * Add a rating (base, buff, debuff, total)
	 *
	 * @param string $row_name will be appended _d, _b, _c for debuff, buff, total
	 * @param string $data colon-separated data
	 */
	function add_rating( $row_name , $data )
	{
		$data = explode(':',$data);
		$data[0] = ( isset($data[0]) && $data[0] != '' ? $data[0] : 0 );
		$data[1] = ( isset($data[1]) && $data[1] != '' ? $data[1] : 0 );
		$data[2] = ( isset($data[2]) && $data[2] != '' ? $data[2] : 0 );
		$this->add_value($row_name, round($data[0]));
		$this->add_value($row_name . '_c', round($data[0]+$data[1]+$data[2]));
		$this->add_value($row_name . '_b', round($data[1]));
		$this->add_value($row_name . '_d', round($data[2]));
	}

	/**
	 * Inserts mail into the Database
	 *
	 * @param array $mail
	 */
	function insert_mail( $mail )
	{
		global $roster;

		$this->reset_values();
		$this->add_ifvalue($mail, 'member_id');
		$this->add_ifvalue($mail, 'mail_slot', 'mailbox_slot');
		$this->add_ifvalue($mail, 'mail_icon', 'mailbox_icon');
		$this->add_ifvalue($mail, 'mail_coin', 'mailbox_coin');
		$this->add_ifvalue($mail, 'mail_coin_icon', 'mailbox_coin_icon');
		$this->add_ifvalue($mail, 'mail_days', 'mailbox_days');
		$this->add_ifvalue($mail, 'mail_sender', 'mailbox_sender');
		$this->add_ifvalue($mail, 'mail_subject', 'mailbox_subject');

		$querystr = "INSERT INTO `" . $roster->db->table('mailbox') . "` SET " . $this->assignstr . ";";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Mail [' . $mail['mail_subject'] . '] could not be inserted',$roster->db->error());
		}
	}


	/**
	 * Formats quest data and inserts into the DB
	 *
	 * @param array $quest
	 * @param int $member_id
	 * @param string $zone
	 * @param array $data
	 */
	function insert_quest( $quest , $member_id , $zone , $slot , $data )
	{
		global $roster;

		// Fix quest name since many 'quest' addons cause the level number to be added to title
		while( substr($quest['Title'],0,1) == '[' )
		{
			$quest['Title'] = ltrim(substr($quest['Title'],strpos($quest['Title'],']')+1));
		}

		// Insert this quest into the quest data table, db normalization is great huh?
		$this->reset_values();
		$this->add_ifvalue($quest, 'QuestId', 'quest_id');
		$this->add_value('quest_name', $quest['Title']);
		$this->add_ifvalue($quest, 'Level', 'quest_level');
		$this->add_ifvalue($quest, 'Tag', 'quest_tag');
		$this->add_ifvalue($quest, 'Group', 'group');
		$this->add_ifvalue($quest, 'Daily', 'daily');
		$this->add_ifvalue($quest, 'RewardMoney', 'reward_money');

		if( isset($quest['Description']) )
		{
			$description = str_replace('\n',"\n",$quest['Description']);
			$description = str_replace($data['Class'],'<class>',$description);
			$description = str_replace($data['Name'],'<name>',$description);

			$this->add_value('description', $description);

			unset($description);
		}

		if( isset($quest['Objective']) )
		{
			$objective = str_replace('\n',"\n",$quest['Objective']);
			$objective = str_replace($data['Class'],'<class>',$objective);
			$objective = str_replace($data['Name'],'<name>',$objective);

			$this->add_value('objective', $objective);

			unset($objective);
		}

		$this->add_value('zone', $zone);
		$this->add_value('locale', $data['Locale']);

		$querystr = "REPLACE INTO `" . $roster->db->table('quest_data') . "` SET " . $this->assignstr . ";";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Quest Data [' . $quest['QuestId'] . ' : ' . $quest['Title'] . '] could not be inserted',$roster->db->error());
		}
/*
		// Now process tasks
		   NOT PROCESSING, BUT CODE AND TABLE LAYOUT IS HERE FOR LATER
		   The reason is that the task number is in the name
		   and this is not good for a normalized table

# --------------------------------------------------------
### Quest Tasks

DROP TABLE IF EXISTS `renprefix_quest_task_data`;
CREATE TABLE `renprefix_quest_task_data` (
  `quest_id` int(11) NOT NULL default '0',
  `task_id` int(11) NOT NULL default '0',
  `note` varchar(128) NOT NULL default '',
  `type` varchar(32) NOT NULL default '',
  `locale` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`quest_id`,`task_id`,`locale`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

		if( isset($quest['Tasks']) && !empty($quest['Tasks']) && is_array($quest['Tasks']) )
		{
			$tasks = $quest['Tasks'];

			foreach( array_keys($tasks) as $task )
			{
				$taskInfo = $tasks[$task];

				$this->reset_values();
				$this->add_ifvalue($quest, 'QuestId', 'quest_id');
				$this->add_value('task_id', $task);

				if( isset($taskInfo['Note']) )
				{
					$note = explode(':',$taskInfo['Note']);
					$this->add_value('note', $note[0]);
					unset($note);
				}
				$this->add_ifvalue($taskInfo, 'Type', 'type');
				$this->add_value('locale', $data['Locale']);

				$querystr = "REPLACE INTO `" . $roster->db->table('quest_task_data') . "` SET " . $this->assignstr . ";";
				$result = $roster->db->query($querystr);
				if( !$result )
				{
					$this->setError('Quest Task [' . $taskInfo['Note'] . '] for Quest Data [' . $quest['QuestId'] . ' : ' . $quest['Title'] . '] could not be inserted',$roster->db->error());
				}
			}
		}
*/

		// Insert this quest id for the character
		$this->reset_values();
		$this->add_value('member_id', $member_id);
		$this->add_ifvalue($quest, 'QuestId', 'quest_id');
		$this->add_value('quest_index', $slot);
		$this->add_ifvalue($quest, 'Difficulty', 'difficulty');
		$this->add_ifvalue($quest, 'Complete', 'is_complete');

		$querystr = "INSERT INTO `" . $roster->db->table('quests') . "` SET " . $this->assignstr . ";";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Quest [' . $quest['Title'] . '] could not be inserted',$roster->db->error());
		}
	}


	/**
	 * Formats mail data to be inserted to the db
	 *
	 * @param array $mail_data
	 * @param int $memberId
	 * @param string $slot_num
	 * @return array
	 */
	function make_mail( $mail_data , $memberId , $slot_num )
	{
		$mail = array();
		$mail['member_id'] = $memberId;
		$mail['mail_slot'] = $slot_num;
		$mail['mail_icon'] = $this->fix_icon($mail_data['MailIcon']);
		$mail['mail_coin'] = ( isset($mail_data['Coin']) ? $mail_data['Coin'] : 0 );
		$mail['mail_coin_icon'] = ( isset($mail_data['CoinIcon']) ? $this->fix_icon($mail_data['CoinIcon']) : '' );
		$mail['mail_days'] = $mail_data['Days'];
		$mail['mail_sender'] = $mail_data['Sender'];
		$mail['mail_subject'] = $mail_data['Subject'];

		return $mail;
	}


	/**
	 * Handles formating and insertion of buff data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_buffs( $data , $memberId )
	{
		global $roster;

		// Delete the stale data
		$querystr = "DELETE FROM `" . $roster->db->table('buffs') . "` WHERE `member_id` = '$memberId';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Buffs could not be deleted',$roster->db->error());
			return;
		}

		if( isset($data['Attributes']['Buffs']) )
		{
			$buffs = $data['Attributes']['Buffs'];
		}

		if( !empty($buffs) && is_array($buffs) )
		{
			// Then process buffs
			$buffsnum = 0;
			foreach( $buffs as $buff )
			{
				if( is_null($buff) || !is_array($buff) || empty($buff) )
				{
					continue;
				}
				$this->reset_values();

				$this->add_value('member_id', $memberId);
				$this->add_ifvalue($buff, 'Name', 'name');

				if( isset($buff['Icon']) )
				{
					$this->add_value('icon', $this->fix_icon($buff['Icon']));
				}

				$this->add_ifvalue($buff, 'Rank', 'rank');
				$this->add_ifvalue($buff, 'Count', 'count');

				if( !empty($buff['Tooltip']) )
				{
					$this->add_value('tooltip', $this->tooltip($buff['Tooltip']));
				}
				else
				{
					$this->add_ifvalue($buff, 'Name', 'tooltip');
				}

				$querystr = "INSERT INTO `" . $roster->db->table('buffs') . "` SET " . $this->assignstr . ";";
				$result = $roster->db->query($querystr);
				if( !$result )
				{
					$this->setError('Buff [' . $buff['Name'] . '] could not be inserted',$roster->db->error());
				}

				$buffsnum++;
			}
			$this->setMessage('<li>Updating Buffs: ' . $buffsnum . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Buffs</li>');
		}
	}


	/**
	 * Handles formating and insertion of quest data
	 *
	 * @param array $data
	 * @param int $member_id
	 */
	function do_quests( $data , $member_id )
	{
		global $roster;

		if( isset($data['Quests']) && !empty($data['Quests']) && is_array($data['Quests']) )
		{
			$quests = $data['Quests'];
		}
		else
		{
			$this->setMessage('<li>No Quest Data</li>');
			return;
		}

		// Delete the stale data
		$querystr = "DELETE FROM `" . $roster->db->table('quests') . "` WHERE `member_id` = '$member_id';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Quests could not be deleted',$roster->db->error());
			return;
		}

		// Then process quests
		$questnum = 0;
		foreach( array_keys($quests) as $zone )
		{
			$zoneInfo = $quests[$zone];
			foreach( array_keys($zoneInfo) as $slot)
			{
				$slotInfo = $zoneInfo[$slot];
				if( is_null($slotInfo) || !is_array($slotInfo) || empty($slotInfo) )
				{
					continue;
				}
				$this->insert_quest($slotInfo, $member_id, $zone, $slot, $data);
				$questnum++;
			}
		}
		$this->setMessage('<li>Updating Quests: ' . $questnum . '</li>');
	}

	/**
	 * Handles formating and insertion of mailbox data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_mailbox( $data , $memberId )
	{
		global $roster;

		if(isset($data['MailBox']))
		{
			$mailbox = $data['MailBox'];
		}

		// If maildate is newer than the db value, wipe all mail from the db...someday
		//if(  )
		//{
		$querystr = "DELETE FROM `" . $roster->db->table('mailbox') . "` WHERE `member_id` = '$memberId';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Mail could not be deleted',$roster->db->error());
			return;
		}
		//}

		// Delete any attachments too
		$querystr = "DELETE FROM `" . $roster->db->table('items') . "` WHERE `member_id` = '$memberId' AND UPPER(`item_parent`) LIKE 'MAIL%';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Mail could not be deleted',$roster->db->error());
			return;
		}

		if( !empty($mailbox) && is_array($mailbox) )
		{
			foreach( $mailbox as $mail_num => $mail )
			{
				if( is_null($mail) || !is_array($mail) || empty($mail) )
				{
					continue;
				}
				$dbmail = $this->make_mail($mail, $memberId, $mail_num);
				$this->insert_mail($dbmail);

				if( isset($mail['Contents']) && is_array($mail['Contents']) )
				{
					foreach( $mail['Contents'] as $attach_num => $attach )
					{
						if( is_null($attach) || !is_array($attach) || empty($attach) )
						{
							continue;
						}
						$item = $this->make_item($attach, $memberId, 'Mail ' . $mail_num, $attach_num);
						$this->insert_item($item,$data['Locale']);
					}
				}
			}
			$this->setMessage('<li>Updating Mailbox: ' . count($mailbox) . '</li>');
		}
		else
		{
			$this->setMessage('<li>No New Mail</li>');
		}
	}


	/**
	 * Handles formating and insertion of currency data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_currency( $data , $memberId )
	{
		global $roster;

		if( !empty($data['Currency']) && is_array($data['Currency']) )
		{
			$currencyData = $data['Currency'];

			$messages = '<li>Updating Currency ';

			// delete the stale data
			$querystr = "DELETE FROM `" . $roster->db->table('currency') . "` WHERE `member_id` = '$memberId';";

			if( !$roster->db->query($querystr) )
			{
				$this->setError('Currency could not be deleted', $roster->db->error());
				return;
			}

			$order = 0;
			foreach( array_keys($currencyData) as $category ) // eg. 'Miscellaneous, Player vs. Player, Dungeon and Raid
			{
				$categoryData = $currencyData[$category];

				foreach( array_keys($categoryData) as $currency ) // eg. Arena Points, Badge of Justice, Emblem of Valor
				{
					$itemData = $categoryData[$currency];
					$this->reset_values();

					$this->add_value('member_id', $memberId);
					$this->add_value('order', $order);
					$this->add_value('category', $category);
					$this->add_ifvalue($itemData, 'Name', 'name');
					$this->add_ifvalue($itemData, 'Count', 'count');
					$this->add_ifvalue($itemData, 'Type', 'type');

					if( !empty($itemData['Tooltip']) )
					{
						$this->add_value('tooltip', $this->tooltip($itemData['Tooltip']));
					}
					if( !empty($itemData['Icon']) )
					{
						$this->add_value('icon', $this->fix_icon($itemData['Icon']));
					}

					$messages .= '.';

					$querystr = "INSERT INTO `" . $roster->db->table('currency') . "` SET " . $this->assignstr . ';';

					$result = $roster->db->query($querystr);
					if( !$result )
					{
						$this->setError('Currency for ' . $currency . ' could not be inserted', $roster->db->error());
					}
					$order++;
				}
			}
			$this->setMessage($messages . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Currency Data</li>');
		}
	}


	/**
	 * Handles formating and insertion of skills data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_skills( $data , $memberId )
	{
		global $roster;

		if( isset($data['Skills']) )
		{
			$skillData = $data['Skills'];
		}

		if( !empty($skillData) && is_array($skillData) )
		{
			$messages = '<li>Updating Skills ';

			//first delete the stale data
			$querystr = "DELETE FROM `" . $roster->db->table('skills') . "` WHERE `member_id` = '$memberId';";

			if( !$roster->db->query($querystr) )
			{
				$this->setError('Skills could not be deleted',$roster->db->error());
				return;
			}

			foreach( array_keys($skillData) as $skill_type )
			{
				$sub_skill = $skillData[$skill_type];
				$order = $sub_skill['Order'];
				foreach( array_keys($sub_skill) as $skill_name )
				{
					if( $skill_name != 'Order' )
					{
						$this->reset_values();
						$this->add_value('member_id', $memberId);
						$this->add_value('skill_type', $skill_type);
						$this->add_value('skill_name', $skill_name);
						$this->add_value('skill_order', $order);
						$this->add_ifvalue($sub_skill, $skill_name, 'skill_level');

						$messages .= '.';

						$querystr = "INSERT INTO `" . $roster->db->table('skills') . "` SET " . $this->assignstr . ";";

						$result = $roster->db->query($querystr);
						if( !$result )
						{
							$this->setError('Skill [' . $skill_name . '] could not be inserted',$roster->db->error());
						}
					}
				}
			}
			$this->setMessage($messages . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Skill Data</li>');
		}
	}


	/**
	 * Handles formating and insertion of spellbook data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_spellbook( $data , $memberId )
	{
		global $roster;

		$spellBuildData = array();

		if( isset($data['SpellBook']) && !empty($data['SpellBook']) && is_array($data['SpellBook']) )
		{
			$spellBuildData[0] = $data['SpellBook'];
		}
		else
		{
			$this->setMessage('<li>No Spellbook Data</li>');
			return;
		}

		$messages = '<li>Updating Spellbook';

		// then process spellbook
		foreach( $spellBuildData as $build => $spellbook )
		{
			// Delete the stale data
			$querystr = "DELETE FROM `" . $roster->db->table('spellbook') . "` WHERE `member_id` = '$memberId' AND `spell_build` = " . $build . ";";
			if( !$roster->db->query($querystr) )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Spells could not be deleted',$roster->db->error());
				return;
			}

			// then process Spellbook Tree
			$querystr = "DELETE FROM `" . $roster->db->table('spellbooktree') . "` WHERE `member_id` = '$memberId' AND `spell_build` = " . $build . ";";
			if( !$roster->db->query($querystr) )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Spell Trees could not be deleted',$roster->db->error());
				return;
			}

			foreach( array_keys($spellbook) as $spell_type )
			{
				$messages .= " : $spell_type";

				$data_spell_type = $spellbook[$spell_type];
				foreach( array_keys($data_spell_type) as $spell )
				{
					$data_spell = $data_spell_type[$spell];

					if( is_array($data_spell) )
					{
						foreach( array_keys($data_spell) as $spell_name )
						{
							$data_spell_name = $data_spell[$spell_name];

							$this->reset_values();
							$this->add_value('member_id', $memberId);
							$this->add_value('spell_build', $build);
							$this->add_value('spell_type', $spell_type);
							$this->add_value('spell_name', $spell_name);

							if( !empty($data_spell_name['Icon']) )
							{
								$this->add_value('spell_texture', $this->fix_icon($data_spell_name['Icon']));
							}
							if( isset($data_spell_name['Rank']) )
							{
								$this->add_value('spell_rank', $data_spell_name['Rank']);
							}

							if( !empty($data_spell_name['Tooltip']) )
							{
								$this->add_value('spell_tooltip', $this->tooltip($data_spell_name['Tooltip']));
							}
							else
							{
								$this->add_value('spell_tooltip', $spell_name . ( isset($data_spell_name['Rank']) ? "\n" . $data_spell_name['Rank'] : '' ));
							}

							$querystr = "INSERT INTO `" . $roster->db->table('spellbook') . "` SET " . $this->assignstr;
							$result = $roster->db->query($querystr);
							if( !$result )
							{
								$this->setError($roster->locale->act['talent_build_' . $build] . ' Spell [' . $spell_name . '] could not be inserted',$roster->db->error());
							}
						}
					}
				}
				$this->reset_values();
				$this->add_value('member_id', $memberId);
				$this->add_value('spell_build', $data_spell_type['OffSpec']);
				$this->add_value('spell_type', $spell_type);
				$this->add_value('spell_texture', $this->fix_icon($data_spell_type['Icon']));

				$querystr = "INSERT INTO `" . $roster->db->table('spellbooktree') . "` SET " . $this->assignstr;
				$result = $roster->db->query($querystr);
				if( !$result )
				{
					$this->setError($roster->locale->act['talent_build_' . $build] . ' Spell Tree [' . $spell_type . '] could not be inserted',$roster->db->error());
				}
			}
		}
		$this->setMessage($messages . '</li>');
	}


	/**
	 * Handles formating and insertion of pet spellbook data
	 *
	 * @param array $data
	 * @param int $memberId
	 * @param int $petID
	 */
	function do_pet_spellbook( $data , $memberId , $petID )
	{
		global $roster;

		if( isset($data['SpellBook']['Spells']) &&  !empty($data['SpellBook']['Spells']) && is_array($data['SpellBook']['Spells']) )
		{
			$spellbook = $data['SpellBook']['Spells'];
		}
		else
		{
			$this->setMessage('<li>No Spellbook Data</li>');
			return;
		}

		$messages = '<li>Updating Spellbook';

		// first delete the stale data
		$querystr = "DELETE FROM `" . $roster->db->table('pet_spellbook') . "` WHERE `pet_id` = '$petID';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Spells could not be deleted',$roster->db->error());
			return;
		}

		// then process spellbook

		foreach( array_keys($spellbook) as $spell )
		{
			$messages .= '.';
			$data_spell = $spellbook[$spell];

			if( is_array($data_spell) )
			{
				$this->reset_values();
				$this->add_value('member_id', $memberId);
				$this->add_value('pet_id', $petID);
				$this->add_value('spell_name', $spell);
				$this->add_value('spell_texture', $this->fix_icon($data_spell['Icon']));
				$this->add_ifvalue($data_spell, 'Rank', 'spell_rank');

				if( !empty($data_spell['Tooltip']) )
				{
					$this->add_value('spell_tooltip', $this->tooltip($data_spell['Tooltip']));
				}
				elseif( !empty($spell) || !empty($data_spell['Rank']) )
				{
					$this->add_value('spell_tooltip', $spell . "\n" . $data_spell['Rank']);
				}

				$querystr = "INSERT INTO `" . $roster->db->table('pet_spellbook') . "` SET " . $this->assignstr;
				$result = $roster->db->query($querystr);
				if( !$result )
				{
					$this->setError('Pet Spell [' . $spell . '] could not be inserted',$roster->db->error());
				}
			}
		}

		$this->setMessage($messages . '</li>');
	}


	/**
	 * Handles formating and insertion of companions
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_companions( $data , $memberId )
	{
		global $roster;

		if( !empty( $data['Companions'] ) && is_array($data['Companions']) )
		{
			$companiondata = $data['Companions'];
		}
		else
		{
			$this->setMessage('<li>No Companions</li>');
			return;
		}

		$messages = '<li>Updating Companions<ul>';

		// delete the stale data
		$querystr = "DELETE FROM `" . $roster->db->table('companions') . "` WHERE `member_id` = '$memberId';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Companions could not be deleted',$roster->db->error());
			return;
		}

		foreach( $companiondata as $type => $companion )
		{
			$messages .= '<li>' . $type;

			foreach( $companion as $id => $data )
			{
				$messages .= '.';

				$this->reset_values();

				$this->add_value('member_id', $memberId);
				$this->add_value('name', $data['Name']);
				$this->add_value('type', $type);
				$this->add_value('slot', $id);
				$this->add_value('spellid', $data['SpellId']);
				$this->add_value('tooltip', $data['Tooltip']);
				$this->add_value('creatureid', $data['CreatureID']);

				if( !empty($data['Icon']) )
				{
					$this->add_value('icon', $this->fix_icon($data['Icon']) );
				}

				$querystr = "INSERT INTO `" . $roster->db->table('companions') . "` SET " . $this->assignstr . ";";
				$result = $roster->db->query($querystr);

				if( !$result )
				{
					$this->setError('Companion [' . $data['Name'] . '] could not be inserted',$roster->db->error());
				}
			}
			$messages .= '</li>';
		}

		$this->setMessage($messages . '</ul></li>');
	}


	/**
	 * Updates/Inserts pets into the db
	 *
	 * @param int $memberId
	 * @param array $data
	 */
	function update_pet( $memberId , $data )
	{
		global $roster;

		if (!empty($data['Name']))
		{
			$querystr = "SELECT `pet_id`
				FROM `" . $roster->db->table('pets') . "`
				WHERE `member_id` = '$memberId' AND `name` LIKE '" . $roster->db->escape($data['Name']) . "'";

			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError('Cannot select Pet Data',$roster->db->error());
				return;
			}

			if( $roster->db->num_rows($result) == 1 )
			{
				$update = true;
				$petID = $roster->db->fetch($result);
				$petID = $petID['pet_id'];
			}
			else
			{
				$update = false;
			}
			$roster->db->free_result($result);

			$this->reset_values();

			$this->add_value('member_id', $memberId);

			$this->add_ifvalue($data, 'Name', 'name');
			$this->add_ifvalue($data, 'Slot', 'slot', '0');

			// BEGIN STATS
			if( !empty( $data['Attributes']['Stats'] ) )
			{
				$main_stats = $data['Attributes']['Stats'];

				$this->add_rating('stat_int', $main_stats['Intellect']);
				$this->add_rating('stat_agl', $main_stats['Agility']);
				$this->add_rating('stat_sta', $main_stats['Stamina']);
				$this->add_rating('stat_str', $main_stats['Strength']);
				$this->add_rating('stat_spr', $main_stats['Spirit']);

				unset($main_stats);
			}
			// END STATS

			// BEGIN DEFENSE
			if( !empty($data['Attributes']['Defense']) )
			{
				$main_stats = $data['Attributes']['Defense'];

				$this->add_ifvalue($main_stats, 'DodgeChance', 'dodge');
				$this->add_ifvalue($main_stats, 'ParryChance', 'parry');
				$this->add_ifvalue($main_stats, 'BlockChance', 'block');
				$this->add_ifvalue($main_stats, 'ArmorReduction', 'mitigation');

				$this->add_rating('stat_armor', $main_stats['Armor']);
				$this->add_rating('stat_def', $main_stats['Defense']);
				$this->add_rating('stat_block', $main_stats['BlockRating']);
				$this->add_rating('stat_parry', $main_stats['ParryRating']);
				$this->add_rating('stat_defr', $main_stats['DefenseRating']);
				$this->add_rating('stat_dodge', $main_stats['DodgeRating']);

				$this->add_ifvalue($main_stats['Resilience'], 'Ranged', 'stat_res_ranged');
				$this->add_ifvalue($main_stats['Resilience'], 'Spell', 'stat_res_spell');
				$this->add_ifvalue($main_stats['Resilience'], 'Melee', 'stat_res_melee');
			}
			// END DEFENSE

			// BEGIN RESISTS
			if( !empty($data['Attributes']['Resists']) )
			{
				$main_res = $data['Attributes']['Resists'];

				$this->add_rating('res_holy', $main_res['Holy']);
				$this->add_rating('res_frost', $main_res['Frost']);
				$this->add_rating('res_arcane', $main_res['Arcane']);
				$this->add_rating('res_fire', $main_res['Fire']);
				$this->add_rating('res_shadow', $main_res['Shadow']);
				$this->add_rating('res_nature', $main_res['Nature']);

				unset($main_res);
			}
			// END RESISTS

			// BEGIN MELEE
			if( !empty($data['Attributes']['Melee']) )
			{
				$attack = $data['Attributes']['Melee'];

				if( isset($attack['AttackPower']) )
				{
					$this->add_rating('melee_power', $attack['AttackPower']);
				}
				if( isset($attack['HitRating']) )
				{
					$this->add_rating('melee_hit', $attack['HitRating']);
				}
				if( isset($attack['CritRating']) )
				{
					$this->add_rating('melee_crit', $attack['CritRating']);
				}
				if( isset($attack['HasteRating']) )
				{
					$this->add_rating('melee_haste', $attack['HasteRating']);
				}

				$this->add_ifvalue($attack, 'CritChance', 'melee_crit_chance');
				$this->add_ifvalue($attack, 'AttackPowerDPS', 'melee_power_dps');

				if( is_array($attack['MainHand']) )
				{
					$hand = $attack['MainHand'];

					$this->add_ifvalue($hand, 'AttackSpeed', 'melee_mhand_speed');
					$this->add_ifvalue($hand, 'AttackDPS', 'melee_mhand_dps');
					$this->add_ifvalue($hand, 'AttackSkill', 'melee_mhand_skill');

					list($mindam, $maxdam) = explode(':',$hand['DamageRange']);
					$this->add_value('melee_mhand_mindam', $mindam);
					$this->add_value('melee_mhand_maxdam', $maxdam);
					unset($mindam, $maxdam);

					$this->add_rating( 'melee_mhand_rating', $hand['AttackRating']);
				}

				if( isset($attack['DamageRangeTooltip']) )
				{
					$this->add_value( 'melee_range_tooltip', $this->tooltip($attack['DamageRangeTooltip']) );
				}
				if( isset($attack['AttackPowerTooltip']) )
				{
					$this->add_value( 'melee_power_tooltip', $this->tooltip($attack['AttackPowerTooltip']) );
				}

				unset($hand, $attack);
			}
			// END MELEE

			$this->add_ifvalue($data, 'Level', 'level', 0);
			$this->add_ifvalue($data, 'Health', 'health', 0);
			$this->add_ifvalue($data, 'Mana', 'mana', 0);
			$this->add_ifvalue($data, 'Power', 'power', 0);

			$this->add_ifvalue($data, 'Experience', 'xp', 0);
			$this->add_ifvalue($data, 'TalentPoints', 'totaltp', 0);
			$this->add_ifvalue($data, 'Type', 'type', '');
			if( !empty($data['Icon']) )
			{
				$this->add_value('icon', $this->fix_icon($data['Icon']));
			}

			if( $update )
			{
				$this->setMessage('<li>Updating pet [' . $data['Name'] . ']<ul>');
				$querystr = "UPDATE `" . $roster->db->table('pets') . "` SET " . $this->assignstr . " WHERE `pet_id` = '$petID'";
				$result = $roster->db->query($querystr);
			}
			else
			{
				$this->setMessage('<li>New pet [' . $data['Name'] . ']<ul>');
				$querystr = "INSERT INTO `" . $roster->db->table('pets') . "` SET " . $this->assignstr;
				$result = $roster->db->query($querystr);
				$petID = $roster->db->insert_id();
			}

			if( !$result )
			{
				$this->setError('Cannot update Pet Data',$roster->db->error());
				return;
			}
			$this->do_pet_spellbook($data,$memberId,$petID);
			$this->do_pet_talents($data,$memberId,$petID);

			$this->setMessage('</ul></li>');
		}
	}


	/**
	 * Handles formatting an insertion of Character Data
	 *
	 * @param int $guildId
	 * @param string $region
	 * @param string $name
	 * @param array $data
	 * @return mixed False on failure | member_id on success
	 */
	function update_char( $guildId , $region , $server , $name , $data )
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
			$this->setError('Cannot select member_id for Character Data',$roster->db->error());
			return false;
		}

		$memberInfo = $roster->db->fetch($result);
		$roster->db->free_result($result);

		if (isset($memberInfo) && is_array($memberInfo))
		{
			$memberId = $memberInfo['member_id'];
		}
		else
		{
			$this->setMessage('<li>Missing member id for ' . $name . '</li>');
			return false;
		}

		// update level in members table
		$querystr = "UPDATE `" . $roster->db->table('members') . "` SET `level` = '" . $data['Level'] . "' WHERE `member_id` = '$memberId' LIMIT 1;";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Cannot update Level in Members Table',$roster->db->error());
		}


		$querystr = "SELECT `member_id` FROM `" . $roster->db->table('players') . "` WHERE `member_id` = '$memberId';";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Cannot select member_id for Character Data',$roster->db->error());
			return false;
		}

		$update = $roster->db->num_rows($result) == 1;
		$roster->db->free_result($result);

		$this->reset_values();

		$this->add_value('name', $name);
		$this->add_value('guild_id', $guildId);
		$this->add_value('server', $server);
		$this->add_value('region', $region);

		$this->add_ifvalue($data, 'Level', 'level');

		// BEGIN HONOR VALUES
		if( isset($data['Honor']) && is_array($data['Honor']) && count($data['Honor']) > 0 )
		{
			$honor = $data['Honor'];

			$this->add_ifvalue($honor['Session'], 'HK', 'sessionHK', 0);
			$this->add_ifvalue($honor['Session'], 'CP', 'sessionCP', 0);
			$this->add_ifvalue($honor['Yesterday'], 'HK', 'yesterdayHK', 0);
			$this->add_ifvalue($honor['Yesterday'], 'CP', 'yesterdayContribution', 0);
			$this->add_ifvalue($honor['Lifetime'], 'HK', 'lifetimeHK', 0);
			$this->add_ifvalue($honor['Lifetime'], 'Rank', 'lifetimeHighestRank', 0);
			$this->add_ifvalue($honor['Lifetime'], 'Name', 'lifetimeRankname', '');
			$this->add_ifvalue($honor['Current'], 'HonorPoints', 'honorpoints', 0);
			$this->add_ifvalue($honor['Current'], 'ArenaPoints', 'arenapoints', 0);

			unset($honor);
		}
		// END HONOR VALUES
		if ( isset($data['Attributes']['Melee']) && is_array($data['Attributes']['Melee']) )
		{
			$this->add_ifvalue($data['Attributes']['Melee'], 'CritChance', 'crit', 0);
		}
		// BEGIN STATS
		if( isset($data['Attributes']['Stats']) && is_array($data['Attributes']['Stats']) )
		{
			$main_stats = $data['Attributes']['Stats'];

			$this->add_rating('stat_int', $main_stats['Intellect']);
			$this->add_rating('stat_agl', $main_stats['Agility']);
			$this->add_rating('stat_sta', $main_stats['Stamina']);
			$this->add_rating('stat_str', $main_stats['Strength']);
			$this->add_rating('stat_spr', $main_stats['Spirit']);

			unset($main_stats);
		}
		// END STATS

		// BEGIN DEFENSE
		if( isset($data['Attributes']['Defense']) && is_array($data['Attributes']['Defense']) )
		{
			$main_stats = $data['Attributes']['Defense'];

			$this->add_ifvalue($main_stats, 'DodgeChance', 'dodge');
			$this->add_ifvalue($main_stats, 'ParryChance', 'parry');
			$this->add_ifvalue($main_stats, 'BlockChance', 'block');
			$this->add_ifvalue($main_stats, 'ArmorReduction', 'mitigation');
			$this->add_ifvalue($main_stats, 'PvPPower', 'pvppower');
			$this->add_ifvalue($main_stats, 'PvPPowerBonus', 'pvppower_bonus');

			$this->add_rating('stat_armor', $main_stats['Armor']);
			$this->add_rating('stat_def', $main_stats['Defense']);
			$this->add_rating('stat_block', $main_stats['BlockRating']);
			$this->add_rating('stat_parry', $main_stats['ParryRating']);
			$this->add_rating('stat_defr', $main_stats['DefenseRating']);
			$this->add_rating('stat_dodge', $main_stats['DodgeRating']);

			$this->add_ifvalue($main_stats['Resilience'], 'Ranged', 'stat_res_ranged');
			$this->add_ifvalue($main_stats['Resilience'], 'Spell', 'stat_res_spell');
			$this->add_ifvalue($main_stats['Resilience'], 'Melee', 'stat_res_melee');
		}
		// END DEFENSE

		// BEGIN RESISTS
		if( isset($data['Attributes']['Resists']) && is_array($data['Attributes']['Resists']) )
		{
			$main_res = $data['Attributes']['Resists'];

			$this->add_rating('res_holy', $main_res['Holy']);
			$this->add_rating('res_frost', $main_res['Frost']);
			$this->add_rating('res_arcane', $main_res['Arcane']);
			$this->add_rating('res_fire', $main_res['Fire']);
			$this->add_rating('res_shadow', $main_res['Shadow']);
			$this->add_rating('res_nature', $main_res['Nature']);

			unset($main_res);
		}
		// END RESISTS

		// BEGIN MELEE
		if( isset($data['Attributes']['Melee']) && is_array($data['Attributes']['Melee']) )
		{
			$attack = $data['Attributes']['Melee'];

			$this->add_rating('melee_power', $attack['AttackPower']);
			$this->add_rating('melee_hit', $attack['HitRating']);
			$this->add_rating('melee_crit', $attack['CritRating']);
			$this->add_rating('melee_haste', $attack['HasteRating']);

			if (isset($attack['Expertise']))
			{
				$this->add_rating('melee_expertise', $attack['Expertise']);
			}

			$this->add_ifvalue($attack, 'CritChance', 'melee_crit_chance');
			$this->add_ifvalue($attack, 'AttackPowerDPS', 'melee_power_dps');

			if( isset($attack['MainHand']) && is_array($attack['MainHand']) )
			{
				$hand = $attack['MainHand'];

				$this->add_ifvalue($hand, 'AttackSpeed', 'melee_mhand_speed');
				$this->add_ifvalue($hand, 'AttackDPS', 'melee_mhand_dps');
				$this->add_ifvalue($hand, 'AttackSkill', 'melee_mhand_skill');

				list($mindam, $maxdam) = explode(':',$hand['DamageRangeBase']);
				$this->add_value('melee_mhand_mindam', $mindam);
				$this->add_value('melee_mhand_maxdam', $maxdam);
				unset($mindam, $maxdam);

				$this->add_rating('melee_mhand_rating', $hand['AttackRating']);
			}

			if( isset($attack['OffHand']) && is_array($attack['OffHand']) )
			{
				$hand = $attack['OffHand'];

				$this->add_ifvalue($hand, 'AttackSpeed', 'melee_ohand_speed');
				$this->add_ifvalue($hand, 'AttackDPS', 'melee_ohand_dps');
				$this->add_ifvalue($hand, 'AttackSkill', 'melee_ohand_skill');

				list($mindam, $maxdam) = explode(':',$hand['DamageRangeBase']);
				$this->add_value('melee_ohand_mindam', $mindam);
				$this->add_value('melee_ohand_maxdam', $maxdam);
				unset($mindam, $maxdam);

				$this->add_rating('melee_ohand_rating', $hand['AttackRating']);
			}
			else
			{
				$this->add_value('melee_ohand_speed', 0);
				$this->add_value('melee_ohand_dps', 0);
				$this->add_value('melee_ohand_skill', 0);

				$this->add_value('melee_ohand_mindam', 0);
				$this->add_value('melee_ohand_maxdam', 0);

				$this->add_rating('melee_ohand_rating', 0);
			}

			if( isset($attack['DamageRangeTooltip']) )
			{
				$this->add_value('melee_range_tooltip', $this->tooltip($attack['DamageRangeTooltip']));
			}
			if( isset($attack['AttackPowerTooltip']) )
			{
				$this->add_value('melee_power_tooltip', $this->tooltip($attack['AttackPowerTooltip']));
			}

			unset($hand, $attack);
		}
		// END MELEE

		// BEGIN RANGED
		if( isset($data['Attributes']['Ranged']) && is_array($data['Attributes']['Ranged']) )
		{
			$attack = $data['Attributes']['Ranged'];

			$this->add_rating('ranged_power', ( isset($attack['AttackPower']) ? $attack['AttackPower'] : '0' ));
			$this->add_rating('ranged_hit', $attack['HitRating']);
			$this->add_rating('ranged_crit', $attack['CritRating']);
			$this->add_rating('ranged_haste', $attack['HasteRating']);

			$this->add_ifvalue($attack, 'CritChance', 'ranged_crit_chance');
			$this->add_ifvalue($attack, 'AttackPowerDPS', 'ranged_power_dps', 0);

			$this->add_ifvalue($attack, 'AttackSpeed', 'ranged_speed');
			$this->add_ifvalue($attack, 'AttackDPS', 'ranged_dps');
			$this->add_ifvalue($attack, 'AttackSkill', 'ranged_skill');

			list($mindam, $maxdam) = explode(':',$attack['DamageRangeBase']);
			$this->add_value('ranged_mindam', $mindam);
			$this->add_value('ranged_maxdam', $maxdam);
			unset($mindam, $maxdam);

			$this->add_rating( 'ranged_rating', $attack['AttackRating']);

			if( isset($attack['DamageRangeTooltip']) )
			{
				$this->add_value('ranged_range_tooltip', $this->tooltip($attack['DamageRangeTooltip']));
			}
			if( isset($attack['AttackPowerTooltip']) )
			{
				$this->add_value('ranged_power_tooltip', $this->tooltip($attack['AttackPowerTooltip']));
			}
			unset($attack);
		}
		// END RANGED

		if( isset($data['Attributes']['ITEMLEVEL']))
		{
			$this->add_value('ilevel', $data['Attributes']['ITEMLEVEL']);
		}
		// BEGIN mastery
		if( isset($data['Attributes']['Mastery']) && is_array($data['Attributes']['Mastery']) )
		{
			$attack = $data['Attributes']['Mastery'];

			$this->add_ifvalue($attack, 'Percent', 'mastery');
			//$this->add_ifvalue($attack, 'Tooltip', 'mastery_tooltip');
			$this->add_value('mastery_tooltip', $this->tooltip($data['Attributes']['Mastery']['Tooltip']));

			unset($attack);
		}
		// END Mastery

		// BEGIN SPELL
		if( isset($data['Attributes']['Spell']) && is_array($data['Attributes']['Spell']) )
		{
			$spell = $data['Attributes']['Spell'];

			$this->add_rating('spell_hit', $spell['HitRating']);
			$this->add_rating('spell_crit', $spell['CritRating']);
			$this->add_rating('spell_haste', $spell['HasteRating']);

			$this->add_ifvalue($spell, 'CritChance', 'spell_crit_chance');

			list($not_cast, $cast) = explode(':',$spell['ManaRegen']);
			$this->add_value('mana_regen', $not_cast);
			$this->add_value('mana_regen_cast', $cast);
			unset($not_cast, $cast);

			$this->add_ifvalue($spell, 'Penetration', 'spell_penetration');
			$this->add_ifvalue($spell, 'BonusDamage', 'spell_damage');
			$this->add_ifvalue($spell, 'BonusHealing', 'spell_healing');

			if( isset($spell['SchoolCrit']) && is_array($spell['SchoolCrit']) )
			{
				$schoolcrit = $spell['SchoolCrit'];

				$this->add_ifvalue($schoolcrit, 'Holy', 'spell_crit_chance_holy');
				$this->add_ifvalue($schoolcrit, 'Frost', 'spell_crit_chance_frost');
				$this->add_ifvalue($schoolcrit, 'Arcane', 'spell_crit_chance_arcane');
				$this->add_ifvalue($schoolcrit, 'Fire', 'spell_crit_chance_fire');
				$this->add_ifvalue($schoolcrit, 'Shadow', 'spell_crit_chance_shadow');
				$this->add_ifvalue($schoolcrit, 'Nature', 'spell_crit_chance_nature');

				unset($schoolcrit);
			}

			if( isset($spell['School']) && is_array($spell['School']) )
			{
				$school = $spell['School'];

				$this->add_ifvalue($school, 'Holy', 'spell_damage_holy');
				$this->add_ifvalue($school, 'Frost', 'spell_damage_frost');
				$this->add_ifvalue($school, 'Arcane', 'spell_damage_arcane');
				$this->add_ifvalue($school, 'Fire', 'spell_damage_fire');
				$this->add_ifvalue($school, 'Shadow', 'spell_damage_shadow');
				$this->add_ifvalue($school, 'Nature', 'spell_damage_nature');

				unset($school);
			}

			unset($spell);
		}
		// END SPELL

		$this->add_ifvalue($data, 'TalentPoints', 'talent_points');

		//$this->add_ifvalue('money_c', $data['Money']['Copper']);
		//$this->add_ifvalue('money_s', $data['Money']['Silver']);
		//$this->add_ifvalue('money_g', $data['Money']['Gold']);
		if (isset($data['Money']))
		{
		$this->add_ifvalue($data['Money'], 'Silver', 'money_s');
		$this->add_ifvalue($data['Money'], 'Copper', 'money_c');
		$this->add_ifvalue($data['Money'], 'Gold', 'money_g');
		}

		$this->add_ifvalue($data, 'Experience', 'exp');
		$this->add_ifvalue($data, 'Race', 'race');
		$this->add_ifvalue($data, 'RaceId', 'raceid');
		$this->add_ifvalue($data, 'RaceEn', 'raceEn');
		$this->add_ifvalue($data, 'Class', 'class');
		$this->add_ifvalue($data, 'ClassId', 'classid');
		$this->add_ifvalue($data, 'ClassEn', 'classEn');
		$this->add_ifvalue($data, 'Health', 'health');
		$this->add_ifvalue($data, 'Mana', 'mana');
		$this->add_ifvalue($data, 'Power', 'power');
		$this->add_ifvalue($data, 'Sex', 'sex');
		$this->add_ifvalue($data, 'SexId', 'sexid');
		$this->add_ifvalue($data, 'Hearth', 'hearth');

		$this->add_ifvalue($data['timestamp']['init'], 'DateUTC','dateupdatedutc');

		$this->add_ifvalue($data, 'DBversion');
		$this->add_ifvalue($data, 'CPversion');

		$this->add_ifvalue($data,'TimePlayed','timeplayed',0);
		$this->add_ifvalue($data,'TimeLevelPlayed','timelevelplayed',0);

		// Capture mailbox update time/date
		if( isset($data['timestamp']['MailBox']) )
		{
			$this->add_timestamp('maildateutc',$data['timestamp']['MailBox']);
		}

		// Capture client language
		$this->add_ifvalue($data, 'Locale', 'clientLocale');

		$this->setMessage('<li>About to update player</li>');

		if( $update )
		{
			$querystr = "UPDATE `" . $roster->db->table('players') . "` SET " . $this->assignstr . " WHERE `member_id` = '$memberId';";
		}
		else
		{
			$this->add_value('member_id', $memberId);
			$querystr = "INSERT INTO `" . $roster->db->table('players') . "` SET " . $this->assignstr . ";";
		}

		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Cannot update Character Data',$roster->db->error());
			return false;
		}

		$this->locale = $data['Locale'];

		if ( isset($data['Equipment']) && is_array($data['Equipment']) )
		{
			$this->do_equip($data, $memberId);
		}
		if ( isset($data['Inventory']) && is_array($data['Inventory']) )
		{
			$this->do_inventory($data, $memberId);
		}
		$this->do_bank($data, $memberId);
		$this->do_mailbox($data, $memberId);
		$this->do_skills($data, $memberId);
		$this->do_recipes($data, $memberId);
		$this->do_spellbook($data, $memberId);
		$this->do_glyphs($data, $memberId);
		$this->do_talents($data, $memberId);
		$this->do_reputation($data, $memberId);
		$this->do_currency($data, $memberId);
		$this->do_quests($data, $memberId);
		$this->do_buffs($data, $memberId);
		$this->do_companions($data, $memberId);

		// Adding pet info
		// Quick fix for DK multiple pet error, we only scan the pets section for hunters and warlocks
		if( (strtoupper($data['ClassEn']) == 'HUNTER' || strtoupper($data['ClassEn']) == 'WARLOCK') && isset($data['Pets']) && !empty($data['Pets']) && is_array($data['Pets']) )
		{
			$petsdata = $data['Pets'];
			foreach( $petsdata as $pet )
			{
				$this->update_pet($memberId, $pet);
			}
		}
		else
		{
			$querystr = "DELETE FROM `" . $roster->db->table('pets') . "` WHERE `member_id` = '$memberId';";
			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError('Cannot delete Pet Data',$roster->db->error());
			}

			$querystr = "DELETE FROM `" . $roster->db->table('pet_spellbook') . "` WHERE `member_id` = '$memberId';";
			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError('Cannot delete Pet Spell Data',$roster->db->error());
			}
		}

		return $memberId;

	} //-END function update_char()
}

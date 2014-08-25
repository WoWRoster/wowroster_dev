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
class rep
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
	 * Handles formating and insertion of rep data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_reputation( $data , $memberId )
	{
		global $roster;

		if( isset($data['Reputation']) )
		{
			$repData = $data['Reputation'];
		}

		if( !empty($repData) && is_array($repData) )
		{
			$messages = '<li>Updating Reputation ';

			//first delete the stale data
			$querystr = "DELETE FROM `" . $roster->db->table('reputation') . "` WHERE `member_id` = '$memberId';";

			if( !$roster->db->query($querystr) )
			{
				$this->setError('Reputation could not be deleted',$roster->db->error());
				return;
			}

			$count = $repData['Count'];
			$key = '';

			foreach ($repData as $cat => $factions)
			{
				if ($cat != 'Count')
				{
					foreach ($factions as $faction => $data)
					{
						if ($faction != 'AtWar' & $faction != 'Standing' & $faction != 'Value' & $faction != 'Description' )
						{
							if (is_array($data))
							{
								$sub_x = $faction;
								foreach ($data as $name => $v)
								{
									if ($name != 'AtWar' & $name != 'Standing' & $name != 'Value' & $name != 'Description' )
									{
										$this->reset_values();
										if( !empty($memberId) )
										{
											$this->add_value('member_id', $memberId );
										}
										if( !empty($cat) )
										{
											$this->add_value('faction', $cat );
										}
										if( !empty($faction) )
										{
											$this->add_value('parent', $faction );
										}
										if( !empty($name) )
										{
											$this->add_value('name', $name );
										}

										if( !empty($v['Value']) )
										{
											list($level, $max) = explode(':',$v['Value']);
											$this->add_value('curr_rep', $level );
											$this->add_value('max_rep', $max );
										}

										$this->add_ifvalue( $v, 'AtWar' );
										$this->add_ifvalue( $v, 'Standing' );
										$this->add_ifvalue( $v, 'Description' );

										$messages .= '.';

										$querystr = "INSERT INTO `" . $roster->db->table('reputation') . "` SET " . $this->assignstr . ";";

										$result = $roster->db->query($querystr);
										if( !$result )
										{
											$this->setError('Reputation for ' . $name . ' could not be inserted',$roster->db->error());
										}
										if (isset($v['Value']))
										{
											$key = $faction;
										}
									}
								}
							}

							$this->reset_values();
							if( !empty($memberId) )
							{
								$this->add_value('member_id', $memberId );
							}
							if( !empty($cat) )
							{
								$this->add_value('faction', $cat );
							}
							if( !empty($key) )
							{
								$this->add_value('parent', $key );
							}
							if( !empty($faction) )
							{
								$this->add_value('name', $faction );
							}

							if( !empty($data['Value']) )
							{
								list($level, $max) = explode(':',$data['Value']);
								$this->add_value('curr_rep', $level );
								$this->add_value('max_rep', $max );
							}

							$this->add_ifvalue( $data, 'AtWar' );
							$this->add_ifvalue( $data, 'Standing' );
							$this->add_ifvalue( $data, 'Description' );

							$messages .= '.';

							$querystr = "INSERT INTO `" . $roster->db->table('reputation') . "` SET " . $this->assignstr . ";";
							$result = $roster->db->query($querystr);
							if( !$result )
							{
								$this->setError('Reputation for ' . $faction . ' could not be inserted',$roster->db->error());
							}
							$key = '';
						}
					}
				}
			}
			$this->setMessage($messages . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Reputation Data</li>');
		}
	}


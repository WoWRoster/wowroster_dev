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
	 * Handles formating and insertion of glyphs
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_glyphs( $data , $memberId )
	{
		global $roster;

		$glyphBuildData = array();

		$messages = '<li>Updating Glyphs: ';
		foreach( $data['Talents'] as $build => $talentData )
		{
			if( isset($talentData['Glyphs']) && !empty($talentData['Glyphs']) && is_array($talentData['Glyphs']) )
			{
				$querystr = "DELETE FROM `" . $roster->db->table('glyphs') . "` WHERE `member_id` = '$memberId' AND `glyph_build` = " . $build . ";";

				if( !$roster->db->query($querystr) )
				{
					$this->setError($roster->locale->act['talent_build_' . $build] . ' Glyphs could not be deleted',$roster->db->error());
					return;
				}
				$messages .= ':'.$talentData['Name'].' - ';
			}
			else
			{
				$messages .= ':'.$talentData['Name'].' - No Glyph Data ';
			}
			
			foreach ($talentData['Glyphs'] as $idx => $glyph )
			{
				$this->reset_values();
				$this->add_value('member_id', $memberId);
				$this->add_ifvalue($glyph, 'Name', 'glyph_name');
				$this->add_ifvalue($glyph, 'Type', 'glyph_type');
				$this->add_value('glyph_build', $build);

				if( isset($glyph['Icon']) )
				{
					$this->add_value('glyph_icon', $this->fix_icon($glyph['Icon']));
				}
				if( isset($glyph['Tooltip']) )
				{
					$this->add_value('glyph_tooltip', $this->tooltip($glyph['Tooltip']));
				}

				//$this->add_value('glyph_order', $glyphOrder);

				$messages .= '.';

				$querystr = "INSERT INTO `" . $roster->db->table('glyphs') . "` SET " . $this->assignstr . ";";

				$result = $roster->db->query($querystr);
				if( !$result )
				{
					$this->setError($roster->locale->act['talent_build_' . $build] . ' Glyph [' . $glyph['glyph_name'] . '] could not be inserted', $roster->db->error());
				}
			}
			$messages .= '';
		}
		$this->setMessage($messages . '</li>');
	}


	/**
	 * Handles formating and insertion of talent data
	 * Also handles dual build talent data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_talents( $data , $memberId )
	{
		global $roster;

		$talentBuildData = array();

		if( isset($data['Talents']) && !empty($data['Talents']) && is_array($data['Talents']) )
		{
			$talentBuildData = $data['Talents'];
		}
		else
		{
			$this->setMessage('<li>No Talent Data</li>');
			return;
		}
		//echo'<pre>';print_r($talentBuildData);echo'</pre>';
		// Check for dual talent build
		// removed for MOp auti scanning now used...

		$messages = '<li>Updating Talents';

		// first delete the stale data
			$querystr = "DELETE FROM `" . $roster->db->table('talents') . "` WHERE `member_id` = '$memberId';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Talents could not be deleted',$roster->db->error());
				return;
			}

			$querystr = "DELETE FROM `" . $roster->db->table('talenttree') . "` WHERE `member_id` = '$memberId';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Talent Trees could not be deleted',$roster->db->error());
				return;
			}
			$querystr = "DELETE FROM `" . $roster->db->table('talent_builds') . "` WHERE `member_id` = '$memberId';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Talent build could not be deleted',$roster->db->error());
				return;
			}
		// Update Talents
		foreach( $talentBuildData as $build => $talentData )
		{


			
			//"Role" "Name" "Active" "Talents" "Background" "Icon" "Desc" 
			$messages .= " : ".$build."-".$talentData["Name"]." ";
			$tree_pointsspent = 0;
			$burl = array();
			$burl2 = '';

				$tid = $data['ClassId'].'0';

				$tx = 0;
			foreach ($talentData['Talents'] as $t_name => $info )
			{
				//$rank = (int)$info['Selected'];//'0';
				//echo $rank;
				$location = explode(':', $info['Location']);
				
				if (!$info['Selected'])
				{
					$rank = '0';
				}
				else
				{
					$rank = '1';
				}

				$this->reset_values();
				$this->add_value('member_id', $memberId);
				$this->add_value('name', $info["Name"]);
				$this->add_value('tree', $talentData["Name"]);
				$this->add_value('build', $build);

				if( !empty($info['Tooltip']) )
				{
					$this->add_value('tooltip', $this->tooltip($info['Tooltip']));
				}
				else
				{
					$this->add_value('tooltip', $info["Name"]);
				}

				if( !empty($info['Texture']) )
				{
					$this->add_value('texture', $this->fix_icon($info['Texture']));
				}

				if ($info["Selected"])
				{
					$tree_pointsspent++;
					$burl[] = $location[0].'-'.$location[1];
					$burl2 .= $rank;
					
				}
				$this->add_value('row', $location[0]);
				$this->add_value('column', $location[1]);
				$this->add_value('rank', $rank);
				$this->add_value('maxrank', '1');

				unset($location);

				$querystr = "INSERT INTO `" . $roster->db->table('talents') . "` SET " . $this->assignstr;
				$result = $roster->db->query($querystr);
				if( !$result )
				{
					$this->setError($roster->locale->act['talent_build_' . $build] . ' Talent [' . $talent_skill . '] could not be inserted',$roster->db->error());
				}
			}

			$values = array(
				'tree'       => $talentData["Name"],
				'order'      => '1',
				'class_id'   => $data['ClassId'],
				'background' => strtolower($this->fix_icon($talentData["Background"])),
				'icon'       => $this->fix_icon($talentData["Icon"]),
				'roles'		 => $talentData["Role"],
				'desc'		 => $talentData['Desc'],
				'tree_num'   => '1'
			);
			
			$querystr = "DELETE FROM `" . $roster->db->table('talenttree_data') . "` WHERE `class_id` = '" . $data['ClassId'] . "' and `tree` = '".$talentData["Name"]."';";
			if (!$roster->db->query($querystr))
			{
				$roster->set_message('Talent Tree Data Table could not be emptied.', '', 'error');
				$roster->set_message('<pre>' . $roster->db->error() . '</pre>', 'MySQL Said', 'error');
				return;
			}
	
			$querystr = "INSERT INTO `" . $roster->db->table('talenttree_data') . "` "
				. $roster->db->build_query('INSERT', $values) . "
				;";
			$result = $roster->db->query($querystr);
			
			$this->reset_values();
			$this->add_value('member_id', $memberId);
			$this->add_value('tree', $talentData["Name"]);
			$this->add_value('background', $this->fix_icon($talentData["Background"]));
			$this->add_value('pointsspent', $tree_pointsspent);
			$this->add_value('order', ($talentData["Active"] ? 1 : 2));
			$this->add_value('build', $build);

			$querystr = "INSERT INTO `" . $roster->db->table('talenttree') . "` SET " . $this->assignstr;
			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Talent Tree [' . $talentData["Name"] . '] could not be inserted',$roster->db->error());
			}
		

			$build_url = $this->_talent_layer_url( $memberId, $build);
			$this->reset_values();
			$messages .= " - ".$build_url." ";
			$this->reset_values();
			$this->add_value('build', $build);
			$this->add_value('member_id', $memberId);
			$this->add_value('tree', $build_url);
			$this->add_value('spec', $talentData["Name"]);
			$querystr = "INSERT INTO `" . $roster->db->table('talent_builds') . "` SET " . $this->assignstr;

			$result = $roster->db->query($querystr);

			if( !$result )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Talent Tree [' . $talent_tree . '] could not be inserted',$roster->db->error());
			}
/*
			$querystr = "DELETE FROM `" . $roster->db->table('talents') . "` WHERE `member_id` = '$memberId' AND `build` = " . $build . ";";

			if( !$roster->db->query($querystr) )
			{
				$this->setError($roster->locale->act['talent_build_' . $build] . ' Talents could not be deleted',$roster->db->error());
				return;
			}
*/ 
		}
		$this->setMessage($messages . '</li>');
	}

	function _talent_layer_url( $memberId , $build )
	{
		global $roster;

			$sqlquery = "SELECT * FROM `" . $roster->db->table('talents') . "` WHERE `member_id` = '" . $memberId . "' AND `build` = '" . $build . "' ORDER BY `row` ASC , `column` ASC";
			$result = $roster->db->query($sqlquery);

			$returndataa = '';

			while( $talentdata = $roster->db->fetch($result) )
			{
				$returndataa .= $talentdata['rank'];
			}
		return $returndataa;
		//return true;
	}

	/**
	 * Handles formating and insertion of pet talent data
	 *
	 * @param array $data
	 * @param int $memberId
	 * @param int $petID
	 */
	function do_pet_talents( $data , $memberId , $petID )
	{
		global $roster;

		if( isset($data['Talents']) && !empty($data['Talents']) && is_array($data['Talents']))
		{
			$talentData = $data['Talents'];
		}
		else
		{
			$this->setMessage('<li>No Talents Data</li>');
			return;
		}

		$messages = '<li>Updating Talents';

		// first delete the stale data
		$querystr = "DELETE FROM `" . $roster->db->table('pet_talents') . "` WHERE `member_id` = '$memberId' AND `pet_id` = '$petID';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Pet Talents could not be deleted',$roster->db->error());
			return;
		}

		// then process Talents
		$querystr = "DELETE FROM `" . $roster->db->table('pet_talenttree') . "` WHERE `member_id` = '$memberId' AND `pet_id` = '$petID';";
		if( !$roster->db->query($querystr) )
		{
			$this->setError('Pet Talent Trees could not be deleted',$roster->db->error());
			return;
		}

		// Update Talents
		foreach( array_keys($talentData) as $talent_tree )
		{
			$messages .= " : $talent_tree";

			$data_talent_tree = $talentData[$talent_tree];
			foreach( array_keys($data_talent_tree) as $talent_skill )
			{
				$data_talent_skill = $data_talent_tree[$talent_skill];
				if( $talent_skill == 'Order' )
				{
					$tree_order = $data_talent_skill;
				}
				elseif( $talent_skill == 'PointsSpent' )
				{
					$tree_pointsspent = $data_talent_skill;
				}
				elseif( $talent_skill == 'Background' )
				{
					$tree_background = $data_talent_skill;
				}
				else
				{
					$this->reset_values();
					$this->add_value('member_id', $memberId);
					$this->add_value('pet_id', $petID);
					$this->add_value('name', $talent_skill);
					$this->add_value('tree', $talent_tree);

					if( !empty($data_talent_skill['Tooltip']) )
					{
						$this->add_value('tooltip', $this->tooltip($data_talent_skill['Tooltip']));
					}
					else
					{
						$this->add_value('tooltip', $talent_skill);
					}

					if( !empty($data_talent_skill['Icon']) )
					{
						$this->add_value('icon', $this->fix_icon($data_talent_skill['Icon']));
					}

					$location = explode(':', $data_talent_skill['Location']);
					$rank = explode(':', $data_talent_skill['Rank']);

					$this->add_value('row', $location[0]);
					$this->add_value('column', $location[1]);
					$this->add_value('rank', $rank[0]);
					$this->add_value('maxrank', $rank[1]);

					unset($location,$rank);

					$querystr = "INSERT INTO `" . $roster->db->table('pet_talents') . "` SET " . $this->assignstr;
					$result = $roster->db->query($querystr);
					if( !$result )
					{
						$this->setError('Pet Talent [' . $talent_skill . '] could not be inserted',$roster->db->error());
					}
				}
			}
			$this->reset_values();

			$this->add_value('member_id', $memberId);
			$this->add_value('pet_id', $petID);
			$this->add_value('tree', $talent_tree);
			$this->add_value('background', $this->fix_icon($tree_background));
			$this->add_value('pointsspent', $tree_pointsspent);
			$this->add_value('order', $tree_order);

			$querystr = "INSERT INTO `" . $roster->db->table('pet_talenttree') . "` SET " . $this->assignstr;
			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError('Pet Talent Tree [' . $talent_tree . '] could not be inserted',$roster->db->error());
			}
		}
		$this->setMessage($messages . '</li>');
	}

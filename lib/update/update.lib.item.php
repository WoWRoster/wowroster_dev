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
	 * Inserts an reagent into the database
	 *
	 * @param string $item
	 * @return bool
	 */
	function insert_reagent( $memberId , $reagents , $locale )
	{
		global $roster;
		//echo'<pre>';
		//print_r($reagents);

		foreach ($reagents as $ind => $reagent)
		{
			$this->reset_values();
			$this->add_value('member_id', $memberId);
			$this->add_value('reagent_id', $reagent['Item']);
			$this->add_ifvalue($reagent, 'Name', 'reagent_name');
			$this->add_ifvalue($reagent, 'Count', 'reagent_count');
			$this->add_ifvalue($reagent, 'Color', 'reagent_color');

			// Fix icon
			if( !empty($reagent['Icon']) )
			{
				$reagent['Icon'] = $this->fix_icon($reagent['Icon']);
			}
			else
			{
				$reagent['Icon'] = 'inv_misc_questionmark';
			}

			// Fix tooltip
			if( !empty($reagent['Tooltip']) )
			{
				$reagent['item_tooltip'] = $this->tooltip($reagent['Tooltip']);
			}
			else
			{
				$reagent['item_tooltip'] = $reagent['Name'];
			}

			$this->add_value('reagent_texture', $reagent['Icon']);
			$this->add_value('reagent_tooltip', $reagent['Tooltip']);

			$this->add_value('locale', $locale);

/*			$level = array();
			if( isset($reagent_data['reqLevel']) && !is_null($reagent_data['reqLevel']) )
			{
				$this->add_value('level', $reagent_data['reqLevel']);
			}
			else if( preg_match($roster->locale->wordings[$locale]['requires_level'],$reagent['item_tooltip'],$level))
			{
				$this->add_value('level', $level[1]);
			}

			// gotta see of the reagent is in the db already....
*/
			$querystra = "SELECT * FROM `" . $roster->db->table('recipes_reagents') . "` WHERE `reagent_id` = " . $reagent['Item'] . ";";
			$resulta = $roster->db->query($querystra);
			$num = $roster->db->num_rows($resulta);

			if ($num < '1')
			{
			$querystr = "INSERT INTO `" . $roster->db->table('recipes_reagents') . "` SET " . $this->assignstr . ";";
			$result = $roster->db->query($querystr);
			if( !$result )
			{
				$this->setError('Item [' . $reagent['Name'] . '] could not be inserted',$roster->db->error());
			}
			}

		}
	}



	/**
	 * Inserts an item into the database
	 *
	 * @param string $item
	 * @return bool
	 */
	function insert_item( $item , $locale )
	{
		global $roster;
		// echo '<pre>';
		//print_r($item);

		$this->reset_values();
		$this->add_ifvalue($item, 'member_id');
		$this->add_ifvalue($item, 'item_name');
		$this->add_ifvalue($item, 'item_parent');
		$this->add_ifvalue($item, 'item_slot');
		$this->add_ifvalue($item, 'item_color');
		$this->add_ifvalue($item, 'item_id');
		$this->add_ifvalue($item, 'item_texture');
		$this->add_ifvalue($item, 'item_quantity');
		$this->add_ifvalue($item, 'item_tooltip');
		$this->add_ifvalue($item, 'item_level');
		$this->add_ifvalue($item, 'item_type');
		$this->add_ifvalue($item, 'item_subtype');
		$this->add_ifvalue($item, 'item_rarity');
		$this->add_value('locale', $locale);

/*
		$level = array();
		if( isset($item_data['reqLevel']) && !is_null($item_data['reqLevel']) )
		{
			$this->add_value('level', $item_data['reqLevel']);
		}
		else if( preg_match($roster->locale->wordings[$locale]['requires_level'],$item['item_tooltip'],$level))
		{
			$this->add_value('level', $level[1]);
		}
 */
		$querystr = "INSERT INTO `" . $roster->db->table('items') . "` SET " . $this->assignstr . ";";
		$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Item [' . $item['item_name'] . '] could not be inserted',$roster->db->error());
		}
	}

	/**
	 * Inserts a recipe into the Database
	 *
	 * @param array $recipe
	 * @param string $locale
	 */
	function insert_recipe( $recipe , $locale )
	{
		global $roster;

		$this->reset_values();
		$this->add_ifvalue($recipe, 'member_id');
		$this->add_ifvalue($recipe, 'recipe_id');
		$this->add_ifvalue($recipe, 'item_id');
		$this->add_ifvalue($recipe, 'recipe_name');
		$this->add_ifvalue($recipe, 'recipe_type');
		$this->add_ifvalue($recipe, 'recipe_sub_type');
		$this->add_ifvalue($recipe, 'skill_name');
		$this->add_ifvalue($recipe, 'difficulty');
		$this->add_ifvalue($recipe, 'item_color');
		$this->add_ifvalue($recipe, 'reagent_list','reagents');
		$this->add_ifvalue($recipe, 'recipe_texture');
		$this->add_ifvalue($recipe, 'recipe_tooltip');

		$level = array();
		if( preg_match($roster->locale->wordings[$locale]['requires_level'],$recipe['recipe_tooltip'],$level))
		{
			$this->add_value('level',$level[1]);
		}

		$querystra = "SELECT * FROM `" . $roster->db->table('recipes') . "` WHERE `member_id` = '" . $recipe['member_id'] . "' and `recipe_name` = '".addslashes($recipe['recipe_name'])."' and `skill_name` = '".addslashes($recipe['skill_name'])."';";
		$resulta = $roster->db->query($querystra);
		$num = $roster->db->num_rows($resulta);

		if ($num <=0)
		{
			$querystr = "INSERT INTO `" . $roster->db->table('recipes') . "` SET " . $this->assignstr . ";";
			$result = $roster->db->query($querystr);
		if( !$result )
		{
			$this->setError('Recipe [' . $recipe['recipe_name'] . '] could not be inserted',$roster->db->error());
		}
		}
	}

	/**
	 * Formats item data to be inserted into the db
	 *
	 * @param array $item_data
	 * @param int $memberId
	 * @param string $parent
	 * @param string $slot_name
	 * @return array
	 */
	function make_item( $item_data , $memberId , $parent , $slot_name )
	{
		$item = array();
		$item['member_id'] = $memberId;
		$item['item_name'] = $item_data['Name'];
		$item['item_parent'] = $parent;
		$item['item_slot'] = $slot_name;
		$item['item_color'] = ( isset($item_data['Color']) ? $item_data['Color'] : 'ffffff' );
		$item['item_id'] = ( isset($item_data['Item']) ? $item_data['Item'] : '0:0:0:0:0:0:0:0' );
		$item['item_texture'] = ( isset($item_data['Icon']) ? $this->fix_icon($item_data['Icon']) : 'inv_misc_questionmark' );
		$item['item_quantity'] = ( isset($item_data['Quantity']) ? $item_data['Quantity'] : 1 );
		$item['level'] = ( isset($item_data['reqLevel']) ? $item_data['reqLevel'] : null );
		$item['item_level'] = ( isset($item_data['iLevel']) ? $item_data['iLevel'] : '' );
		$item['item_type'] = ( isset($item_data['Type']) ? $item_data['Type'] : '' );
		$item['item_subtype'] = ( isset($item_data['SubType']) ? $item_data['SubType'] : '' );
		$item['item_rarity'] = ( isset($item_data['Rarity']) ? $item_data['Rarity'] : '' );

		if( !empty($item_data['Tooltip']) )
		{
			$item['item_tooltip'] = $this->tooltip($item_data['Tooltip']);
		}
		else
		{
			$item['item_tooltip'] = $item_data['Name'];
		}

		if( !empty($item_data['Gem']))
		{
			$this->do_gems($item_data['Gem'], $item_data['Item']);
		}

		return $item;
	}

	/**
	 * Formats gem data to be inserted into the database
	 *
	 * @param array $gem_data
	 * @param int $socket_id
	 * @return array $gem if successful else returns false
	 */
	function make_gem( $gem_data , $socket_id )
	{
		global $roster;

		$gemtt = explode( '<br>', $gem_data['Tooltip'] );

		if( $gemtt[0] !== '' )
		{
			foreach( $gemtt as $line )
			{
				$colors = array();
				$line = preg_replace('/\|c[a-f0-9]{8}(.+?)\|r/i','$1',$line); // CP error? strip out color
				// -- start the parsing
				if( preg_match('/'.$roster->locale->wordings[$this->locale]['tooltip_boss'] . '|' . $roster->locale->wordings[$this->locale]['tooltip_source'] . '|' . $roster->locale->wordings[$this->locale]['tooltip_droprate'].'/', $line) )
				{
					continue;
				}
				elseif( preg_match('/%|\+|'.$roster->locale->wordings[$this->locale]['tooltip_chance'].'/', $line) )  // if the line has a + or % or the word Chance assume it's bonus line.
				{
					$gem_bonus = $line;
				}
				elseif( preg_match($roster->locale->wordings[$this->locale]['gem_preg_meta'], $line) )
				{
					$gem_color = 'meta';
				}
				elseif( preg_match($roster->locale->wordings[$this->locale]['gem_preg_multicolor'], $line, $colors) )
				{
					if( $colors[1] == $roster->locale->wordings[$this->locale]['gem_colors']['red'] && $colors[2] == $roster->locale->wordings[$this->locale]['gem_colors']['blue'] || $colors[1] == $roster->locale->wordings[$this->locale]['gem_colors']['blue'] && $colors[2] == $roster->locale->wordings[$this->locale]['gem_colors']['red'] )
					{
						$gem_color = 'purple';
					}
					elseif( $colors[1] == $roster->locale->wordings[$this->locale]['gem_colors']['yellow'] && $colors[2] == $roster->locale->wordings[$this->locale]['gem_colors']['red'] || $colors[1] == $roster->locale->wordings[$this->locale]['gem_colors']['red'] && $colors[2] == $roster->locale->wordings[$this->locale]['gem_colors']['yellow'] )
					{
						$gem_color = 'orange';
					}
					elseif( $colors[1] == $roster->locale->wordings[$this->locale]['gem_colors']['yellow'] && $colors[2] == $roster->locale->wordings[$this->locale]['gem_colors']['blue'] || $colors[1] == $roster->locale->wordings[$this->locale]['gem_colors']['blue'] && $colors[2] == $roster->locale->wordings[$this->locale]['gem_colors']['yellow'] )
					{
						$gem_color = 'green';
					}
				}
				elseif( preg_match($roster->locale->wordings[$this->locale]['gem_preg_singlecolor'], $line, $colors) )
				{
					$tmp = array_flip($roster->locale->wordings[$this->locale]['gem_colors']);
					$gem_color = $tmp[$colors[1]];
				}
				elseif( preg_match($roster->locale->wordings[$this->locale]['gem_preg_prismatic'], $line) )
				{
					$gem_color = 'prismatic';
				}
			}
			//get gemid and remove the junk
			list($gemid) = explode(':', $gem_data['Item']);

			$gem = array();
			$gem['gem_name'] 	= $gem_data['Name'];
			$gem['gem_tooltip'] = $this->tooltip($gem_data['Tooltip']);
			$gem['gem_bonus'] 	= $gem_bonus;
			$gem['gem_socketid']= $gem_data['gemID'];//$socket_id;  // the ID the gem holds when socketed in an item.
			$gem['gem_id'] 		= $gemid; // the ID of gem when not socketed.
			$gem['gem_texture'] = $this->fix_icon($gem_data['Icon']);
			$gem['gem_color'] 	= $gem_color;  //meta, prismatic, red, blue, yellow, purple, green, orange.

			return $gem;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Formats each gem found in each slot of item and inserts into database.
	 *
	 * @param array $gems
	 * @param string $itemid_data
	 */
	function do_gems( $gems , $itemid_data )
	{
		$itemid = explode(':', $itemid_data);
		foreach($gems as $key => $val)
		{
			$socketid = $itemid[(int)$key+1];
			$gem = $this->make_gem($val, $socketid);
			if( $gem )
			{
				$this->insert_gem($gem);
			}
		}
	}
	/**
	 * Inserts a gem into the database
	 *
	 * @param array $gem
	 * @return bool | true on success, false if error
	 */
	function insert_gem( $gem )
	{
		global $roster;

		$this->assigngem='';
		$this->add_gem('gem_id', $gem['gem_socketid']);//$gem['gem_id']);
		$this->add_gem('gem_name', $gem['gem_name']);
		$this->add_gem('gem_color', $gem['gem_color']);
		$this->add_gem('gem_tooltip', $gem['gem_tooltip']);
		$this->add_gem('gem_bonus', $gem['gem_bonus']);
		$this->add_gem('gem_socketid', $gem['gem_id']);//$gem['gem_socketid']);
		$this->add_gem('gem_texture', $gem['gem_texture']);
		$this->add_gem('locale', $this->locale);

		$querystr = "REPLACE INTO `" . $roster->db->table('gems') . "` SET ".$this->assigngem . "  ";//WHERE `gem_socketid` = '".$gem['gem_socketid']."'";
		$result = $roster->db->query($querystr);
		if ( !$result )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	

	/**
	 * Formats recipe data to be inserted into the db
	 *
	 * @param array $recipe_data
	 * @param int $memberId
	 * @param string $parent
	 * @param string $recipe_type
	 * @param string $recipe_name
	 * @return array
	 */
	function make_recipe( $recipe_data , $memberId , $parent , $recipe_type , $recipe_sub_type , $recipe_name )
	{
		$recipe = array();
		$recipe['member_id'] = $memberId;
		$recipe['recipe_name'] = $recipe_name;
		$recipe['recipe_type'] = $recipe_type;
		$recipe['recipe_sub_type'] = $recipe_sub_type;
		$recipe['skill_name'] = $parent;

		// Fix Difficulty since it's now a string field
		if( !is_numeric($recipe_data['Difficulty']) )
		{
			switch($recipe_data['Difficulty'])
			{
				case 'difficult':
					$recipe['difficulty'] = 5;
					break;

				case 'optimal':
					$recipe['difficulty'] = 4;
					break;

				case 'medium':
					$recipe['difficulty'] = 3;
					break;

				case 'easy':
					$recipe['difficulty'] = 2;
					break;

				case 'trivial':
				default:
					$recipe['difficulty'] = 1;
					break;
			}
		}
		else
		{
			$recipe['difficulty'] = $recipe_data['Difficulty'];
		}

		$recipe['item_color'] = isset($recipe_data['Color']) ? $recipe_data['Color'] : '';
		$recipe['item_id'] = isset($recipe_data['Item']) ? $recipe_data['Item'] : '';
		$recipe['recipe_id'] = isset($recipe_data['RecipeID']) ? $recipe_data['RecipeID'] : '';

		$recipe['reagent_data'] = $recipe_data['Reagents'];
		$recipe['reagent_list'] = array();

		foreach( $recipe_data['Reagents'] as $d => $reagent )
		{
			//aprint($reagent);
			$id = explode(':', $reagent['Item']);
			if(isset($reagent['Quantity']))
			{
				$count = $reagent['Quantity'];
			}
			elseif (isset($reagent['Count']))
			{
				$count = $reagent['Count'];
			}
			else
			{
				$count = '1';
			}
			$recipe['reagent_list'][] = $id[0] . ':' . $count;
		}
		$recipe['reagent_list'] = implode('|',$recipe['reagent_list']);

		$recipe['recipe_texture'] = $this->fix_icon($recipe_data['Icon']);

		if( !empty($recipe_data['Tooltip']) )
		{
			$recipe['recipe_tooltip'] = $this->tooltip( $recipe_data['Tooltip'] );
		}
		else
		{
			$recipe['recipe_tooltip'] = $recipe_name;
		}

		return $recipe;
	}


	/**
	 * Handles formating and insertion of recipe data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_recipes( $data , $memberId )
	{
		global $roster;

		if(isset($data['Professions']))
		{
			$prof = $data['Professions'];
		}

		if( !empty($prof) && is_array($prof) )
		{
			$messages = '<li>Updating Professions';

			// Delete the stale data
			$querystr = "DELETE FROM `" . $roster->db->table('recipes') . "` WHERE `member_id` = '$memberId';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Professions could not be deleted',$roster->error());
				return;
			}

			// Then process Professions
			foreach( array_keys($prof) as $skill_name )
			{
				$messages .= " : $skill_name";

				$skill = $prof[$skill_name];
				foreach( array_keys($skill) as $recipe_type )
				{
					$item = $skill[$recipe_type];
					foreach(array_keys($item) as $recipe_name)
					{
						$recipeDetails = $item[$recipe_name];
						if (!isset($item[$recipe_name]["RecipeID"]))
						{
							$subitem = $item[$recipe_name];
							foreach(array_keys($subitem) as $recipe_name2)
							{
								$recipeDetail = $subitem[$recipe_name2];
								if( is_null($recipeDetails) || !is_array($recipeDetails) || empty($recipeDetails) )
								{
									continue;
								}
								$recipe = $this->make_recipe($recipeDetail, $memberId, $skill_name, $recipe_type,$recipe_name, $recipe_name2);
								$this->insert_recipe($recipe,$data['Locale']);
								$this->insert_reagent($memberId,$recipe['reagent_data'],$data['Locale']);
							}
						}
						else
						{
							if( is_null($recipeDetails) || !is_array($recipeDetails) || empty($recipeDetails) )
							{
								continue;
							}
							$recipe = $this->make_recipe($recipeDetails, $memberId, $skill_name, $recipe_type, '', $recipe_name);
							$this->insert_recipe($recipe,$data['Locale']);
							$this->insert_reagent($memberId,$recipe['reagent_data'],$data['Locale']);
						}
					}
				}
				/*
				foreach( array_keys($skill) as $recipe_type )
				{
					$item = $skill[$recipe_type];
					foreach(array_keys($item) as $recipe_name)
					{
						$recipeDetails = $item[$recipe_name];
						if( is_null($recipeDetails) || !is_array($recipeDetails) || empty($recipeDetails) )
						{
							continue;
						}
						$recipe = $this->make_recipe($recipeDetails, $memberId, $skill_name, $recipe_type, $recipe_name);
						$this->insert_recipe($recipe,$data['Locale']);
						$this->insert_reagent($memberId,$recipe['reagent_data'],$data['Locale']);
					}
				}
				*/
			}
			$this->setMessage($messages . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Recipe Data</li>');
		}
	}


	/**
	 * Handles formating and insertion of equipment data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_equip( $data , $memberId )
	{
		global $roster;

		// Update Equipment Inventory
		$equip = $data['Equipment'];
		if( !empty($equip) && is_array($equip) )
		{
			$messages = '<li>Updating Equipment ';

			$querystr = "DELETE FROM `" . $roster->db->table('items') . "` WHERE `member_id` = '$memberId' AND `item_parent` = 'equip';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Equipment could not be deleted',$roster->db->error());
				return;
			}
			foreach( array_keys($equip) as $slot_name )
			{
				$messages .= '.';

				$slot = $equip[$slot_name];
				if( is_null($slot) || !is_array($slot) || empty($slot) )
				{
					continue;
				}
				$item = $this->make_item($slot, $memberId, 'equip', $slot_name);
				$this->insert_item($item,$data['Locale']);
			}
			$this->setMessage($messages . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Equipment Data</li>');
		}
	}


	/**
	 * Handles formating and insertion of inventory data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_inventory( $data , $memberId )
	{
		global $roster;

		// Update Bag Inventory
		$inv = $data['Inventory'];
		if( !empty($inv) && is_array($inv) )
		{
			$messages = '<li>Updating Inventory';

			$querystr = "DELETE FROM `" . $roster->db->table('items') . "` WHERE `member_id` = '$memberId' AND UPPER(`item_parent`) LIKE 'BAG%' AND `item_parent` != 'bags';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Inventory could not be deleted',$roster->db->error());
				return;
			}

			$querystr = "DELETE FROM `" . $roster->db->table('items') . "` WHERE `member_id` = '$memberId' AND `item_parent` = 'bags' AND UPPER(`item_slot`) LIKE 'BAG%';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Inventory could not be deleted',$roster->db->error());
				return;
			}

			foreach( array_keys($inv) as $bag_name )
			{
				$messages .= " : $bag_name";

				$bag = $inv[$bag_name];
				if( is_null($bag) || !is_array($bag) || empty($bag) )
				{
					continue;
				}
				$item = $this->make_item($bag, $memberId, 'bags', $bag_name);

				// quantity for a bag means number of slots it has
				$item['item_quantity'] = $bag['Slots'];
				$this->insert_item($item,$data['Locale']);

				if (isset($bag['Contents']) && is_array($bag['Contents']))
				{
					foreach( array_keys($bag['Contents']) as $slot_name )
					{
						$slot = $bag['Contents'][$slot_name];
						if( is_null($slot) || !is_array($slot) || empty($slot) )
						{
							continue;
						}
						$item = $this->make_item($slot, $memberId, $bag_name, $slot_name);
						$this->insert_item($item,$data['Locale']);
					}
				}
			}
			$this->setMessage($messages . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Inventory Data</li>');
		}
	}


	/**
	 * Handles formating and insertion of bank data
	 *
	 * @param array $data
	 * @param int $memberId
	 */
	function do_bank( $data , $memberId )
	{
		global $roster;

		// Update Bank Inventory
		if(isset($data['Bank']))
		{
			$inv = $data['Bank'];
		}

		if( !empty($inv) && is_array($inv) )
		{
			$messages = '<li>Updating Bank';

			// Clearing out old items
			$querystr = "DELETE FROM `" . $roster->db->table('items') . "` WHERE `member_id` = '$memberId' AND UPPER(`item_parent`) LIKE 'BANK%';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Bank could not be deleted',$roster->db->error());
				return;
			}

			$querystr = "DELETE FROM `" . $roster->db->table('items') . "` WHERE `member_id` = '$memberId' AND `item_parent` = 'bags' AND UPPER(`item_slot`) LIKE 'BANK%';";
			if( !$roster->db->query($querystr) )
			{
				$this->setError('Bank could not be deleted',$roster->db->error());
				return;
			}

			foreach( array_keys($inv) as $bag_name )
			{
				$messages .= " : $bag_name";

				$bag = $inv[$bag_name];
				if( is_null($bag) || !is_array($bag) || empty($bag) )
				{
					continue;
				}

				$dbname = 'Bank ' . $bag_name;
				$item = $this->make_item($bag, $memberId, 'bags', $dbname);

				// Fix bank bag icon
				if( $bag_name == 'Bag0' )
				{
					$item['item_texture'] = 'inv_misc_bag_15';
				}

				// quantity for a bag means number of slots it has
				$item['item_quantity'] = $bag['Slots'];
				$this->insert_item($item,$data['Locale']);

				if (isset($bag['Contents']) && is_array($bag['Contents']))
				{
					foreach( array_keys($bag['Contents']) as $slot_name )
					{
						$slot = $bag['Contents'][$slot_name];
						if( is_null($slot) || !is_array($slot) || empty($slot) )
						{
							continue;
						}
						$item = $this->make_item($slot, $memberId, $dbname, $slot_name);
						$this->insert_item($item,$data['Locale']);
					}
				}
			}
			$this->setMessage($messages . '</li>');
		}
		else
		{
			$this->setMessage('<li>No Bank Data</li>');
		}
	}

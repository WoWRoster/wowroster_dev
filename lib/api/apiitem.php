<?php
/**
 * WoWRoster.net WoWRoster
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: apiitem.php 2631 2014-08-21 17:54:35Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 2.2.0
 * @package    WoWRoster
 */
 
 
/*
this file will process the json data for items and make a tooltip for us ..

		EXPERAMENTAL!!! use at own risk!!

*/

class ApiItem {

	var $member_id, $item_id, $name, $level, $icon, $color;
	var $slot, $parent, $tooltip, $quantity, $locale;

	// 0=none, 1=poor, 2=common, 3=uncommon, 4=rare, 5=epic, 6=legendary, 7=heirloom
	var $quality_id; //holds numerical value of item quality
	var $quality; // holds string value of item quality

	// parsing flags
	var $isBag = false, $isSetPiece = false, $isSocketable = false, $isEnchant = false, $isArmor = false;
	var $isWeapon = false, $isParseError = false, $isParseMode = false, $isSocketBonus = false;

	// parsing counters
	var $setItemEquiped = 0;
	var $setItemOwned = 0;
	var $setItemTotal = 0;

	/**
	 * Armory Lookup Object
	 *
	 * @var RosterArmory
	 */
	var $armory_db;

	// parsed arrays/strings
	var $parsed_item = array();  // fully parsed item array
	var $attributes = array(); // holds all parsed item attributes
	var $effects = array(); // holds passive bonus effects of the item
	var $enchantment;
	var $sockets = array('red' => 0, 'yellow' => 0, 'blue' => 0, 'meta' => 0, 'Hydraulic' => 0); //socket colors
	var $hasMetaGem = false;
	var $gemColors = array( 'red' => 0, 'yellow' => 0, 'blue' => 0 );
	var $html_tooltip;

	// item debugging. debug level 0, 1, 2
	var $DEBUG = 1; // 0 (never show debug), 1 (show debug on parse error), 2 (always show debug)
	var $DEBUG_junk = '';
	var $user = array();
	var $dapi = array();
	var $dgems = array();
	
	function sortByOneKey(array $array, $key, $asc = true)
	{
		$result = array();
			
		$values = array();
		foreach ($array as $id => $value) {
			$values[$id] = isset($value[$key]) ? $value[$key] : '';
		}
			
		if ($asc) {
			asort($values);
		}
		else {
			arsort($values);
		}
			
		foreach ($values as $key => $value) {
			$result[$key] = $array[$key];
		}
			
		return $result;
	}
	
	function item ($data,$userData=null,$gems=false)
	{
		global $api, $roster;
		$this->user = $userData;
		$this->dapi = $data;
		$this->dgems = $gems;
		
		if (isset($data['tooltip_html']))
		{
			$a = str_replace("<br /><br />", '<br />', $data['tooltip_html']);//$data['tooltip_html'];
		}
		else
		{
			$this->build_Attributes($data);
			$a = $this->_makeTooltip();
		}
		return $a;
	}
	
	function build_Attributes($data)
	{
		global $api, $roster;
		//duhharharhar copy and paste....
		$tt=array();
			//$tt['Attributes']['TempEnchantment'][] = $matches[1];
			if (isset($data['socketInfo']['socketBonus']))
			{
				$tt['Attributes']['SocketBonus'] = $data['socketInfo']['socketBonus'];
			}
//			$tt['Attributes']['Enchantment'] = $matches[1];
			$tt['General']['Name'] = $data['name'];
			$tt['General']['ItemId'] = $data['id'];
			$tt['General']['ItemColor'] = $this->_getItemColor($data['quality']);
			$tt['General']['Icon'] = $data['icon'];
			$tt['Attributes']['Icon'] = $data['icon'];
			$tt['General']['Slot'] = $data['inventoryType'];
			$tt['General']['Parent'] = 'Equip';
			$tt['General']['Tooltip'] = (isset($data['tooltip_html']) ? $data['tooltip_html'] : '');// i wish...str_replace("<br />", '<br />', $data['tooltip']);
			$tt['General']['Locale']=$roster->config['locale'];

			if (isset($this->user['stats']))
			{
				$bonus = $this->sortByOneKey($this->user['stats'], 'stat');
				foreach( $bonus as $id => $stat )
				{
					$tt['Attributes']['BaseStats'][$roster->locale->act['apiitem']['statlocal'][$stat['stat']]] = sprintf( $roster->locale->act['apiitem']['itemstat'][$stat['stat']], $stat['amount']);
				}
			}
			else
			{
				$bonus = $this->sortByOneKey($data['bonusStats'], 'stat');
				foreach( $bonus as $id => $stat )
				{
					$tt['Attributes']['BaseStats'][$roster->locale->act['apiitem']['statlocal'][$stat['stat']]] = sprintf( $roster->locale->act['apiitem']['itemstat'][$stat['stat']], $stat['amount']);
				}
			}

			if (isset($data['itemSpells']))
			{
				foreach($data['itemSpells'] as $id => $spell)
				{
					if ($spell['spell']['description'] != '')
					{
						if ($spell['trigger'] == 'ON_EQUIP' )
						{
							$tt['Effects']['Equip'][] = $roster->locale->act['tooltip_equip'].' '.$spell['spell']['description'];
						}
						else
						{
							$tt['Effects']['Use'][] = $roster->locale->act['tooltip_equip'].' '.$spell['spell']['description'];
						}
					}
				}
			}
			if (isset($this->user['stats']))
			{
				$tt['Attributes']['ItemLevel'] = $this->user['itemLevel'];
			}
			else
			{
				$tt['Attributes']['ItemLevel'] = $data['itemLevel'];
			}
			if ($data['requiredLevel'] > 0)
			{
				$tt['Attributes']['Requires'] = $data['requiredLevel'];
			}
			//$tt['Effects']['ChanceToProc'][] = $line;
			$tt['Attributes']['BindType'] = $roster->locale->act['apiitem']['bind'][$data['itemBind']];
/*
			if (isset($this->user['tooltipParams']['set']))
			{
				$this->isSetPiece = true;
				$this->setItemEquiped = count($this->user['tooltipParams']['set']);
				foreach ($data['itemSet']['setBonuses'] as $r => $e)
				{
					if ($this->setItemEquiped >= $e['threshold'])
					{
						$tt['Attributes']['Set']['SetBonus'][] = "(".$e['threshold'].") Set: ".$e['description']."";
					}
					else
					{
						$tt['Attributes']['Set']['InactiveSet'][] = "(".$e['threshold'].") Set: ".$e['description']."";
					}
				}
				//$tt['Attributes']['Set']['InactiveSet'][] = '';//$line;
				$tt['Attributes']['Set']['ArmorSet']['Name'] = $data['itemSet']['name'];
				
				$this->isSetPiece = true;
				$setpiece = 0;
				$set_data = $this->_getSetData($data['itemSet']['id']);
				//echo '<pre>'; print_r($set_data); echo '</pre>';
				foreach ($set_data as $e => $s_data)
				{
					$tt['Attributes']['Set']['ArmorSet']['Piece'][$setpiece]['Name'] = "  ".$s_data['Name'];//trim($line);
					$setpiece++;
				}
			}
			
			
			
			if (isset($data['itemSet']) && !isset($this->user['tooltipParams']['set']))
			{
				$this->isSetPiece = true;
				$this->setItemEquiped = 0;//count($this->user['tooltipParams']['set']);
				foreach ($data['itemSet']['setBonuses'] as $r => $e)
				{
					if ($this->setItemEquiped <= $e['threshold'])
					{
						$tt['Attributes']['Set']['SetBonus'][] = "(".$e['threshold'].") Set: ".$e['description']."";
					}
					else
					{
						$tt['Attributes']['Set']['InactiveSet'][] = "(".$e['threshold'].") Set: ".$e['description']."";
					}
				}
				//$tt['Attributes']['Set']['InactiveSet'][] = '';//$line;
				$tt['Attributes']['Set']['ArmorSet']['Name'] = $data['itemSet']['name'];
				
				//$this->isSetPiece = true;
				$setpiece = 0;
				//$set_data = $this->_getSetDataa($data['itemSet']['items'],$data['itemSet']['id']);
				/*echo '<pre>'; print_r($set_data); echo '</pre>';
				foreach ($set_data as $e => $s_data)
				{
					$tt['Attributes']['Set']['ArmorSet']['Piece'][$setpiece]['Name'] = "  ".$s_data['Name'];//trim($line);
					$setpiece++;
				}
				*
			}
			*/
			
			
			
			if ($data['maxDurability'] > 0)
			{
				$tt['Attributes']['Durability']['Line']= $roster->locale->act['tooltip_durability'];
				$tt['Attributes']['Durability']['Current'] = ( isset($data['curDurability']) ? $data['curDurability'] : $data['maxDurability']);
				$tt['Attributes']['Durability']['Max'] = $data['maxDurability'];
			}
			if (isset($data['allowableClasses']))
			{
				$tt['Attributes']['Class'] = $roster->locale->act['tooltip_classes'];
				foreach($data['allowableClasses'] as $id => $classes)
				{
					$tt['Attributes']['ClassText'][] = $roster->locale->act['id_to_class'][$classes];
				}
			}
			if (isset($data['allowableRaces']))
			{
				$tt['Attributes']['Race'] = $roster->locale->act['tooltip_races'];
				foreach($data['allowableRaces'] as $id => $classes)
				{
					$tt['Attributes']['RaceText'][] = $roster->locale->act['id_to_race'][$classes];
				}
			}
			//socketInfo][sockets
			if (isset($data['socketInfo']['sockets']))
			{
				foreach($data['socketInfo']['sockets'] as $id => $sc)
				{
					$sk =  mb_strtolower($sc['type'], 'UTF-8');
					$sk_name = ucfirst($sk);
					if ($sk_name == 'Hydraulic')
					{
						$sk_name = 'Sha-Touched Socket';
					}
					else
					{
						$sk_name = $sk_name.' Socket';
					}
					$tt['Attributes']['Sockets'][$id]['color'] = ucfirst($sk);
					$tt['Attributes']['Sockets'][$id]['line'] = $sk_name;
				}
			}
			if(isset($data['gemInfo']))
			{
				$tt['Attributes']['GemBonus'] = $data['gemInfo']['bonus']['name'];
				if (isset($data['gemInfo']['bonus']['requiredSkillId']) && $data['gemInfo']['bonus']['requiredSkillId'] > 0)
				{
					$tt['Attributes']['SkillRequired'] = $roster->locale->act['apiitem']['skills'][$data['gemInfo']['bonus']['requiredSkillId']].' ('.$data['gemInfo']['bonus']['requiredSkillRank'].')';
				}
			}
			$this->isSocketable = $data['hasSockets'];
			
			$tt['Attributes']['ItemNote'] = $data['description'];
			//$tt['Attributes']['Unique'] = $line;
			if ($data['itemClass'] == '4')
			{
				if ($data['baseArmor'] > 0)
				{
					$tt['Attributes']['ArmorClass']['Line'] = $data['baseArmor'] .' Armor';
					$tt['Attributes']['ArmorClass']['Rating'] = $data['baseArmor'];
				}
				$tt['Attributes']['ArmorType'] = $roster->locale->act['apiitem']['itemSubClass'][$data['itemClass']][$data['itemSubClass']];
				$tt['Attributes']['ArmorSlot'] = ''.$roster->locale->act['apiitem']['slotType'][$data['inventoryType']].'';
				$this->isArmor = true;
			}
			if ($data['itemClass'] == '2' )
			{
				if(isset($this->user['weaponInfo']))
				{
					$tt['Attributes']['WeaponType'] = $roster->locale->act['apiitem']['itemSubClass'][$data['itemClass']][$data['itemSubClass']];
					$tt['Attributes']['WeaponSlot'] = ''.$roster->locale->act['apiitem']['slotType'][$data['inventoryType']].'';
					$tt['Attributes']['WeaponSpeed'] = $data['weaponInfo']['weaponSpeed'];
					$tt['Attributes']['WeaponDamage'] = $this->user['weaponInfo']['damage']['min'].' - '.$this->user['weaponInfo']['damage']['max'];
					$tt['Attributes']['WeaponDPS'] = number_format($this->user['weaponInfo']['dps'], 1, '.', '');//$data['weaponInfo']['dps'];
				}
				else
				{
					$tt['Attributes']['WeaponType'] = $roster->locale->act['apiitem']['itemSubClass'][$data['itemClass']][$data['itemSubClass']];
					$tt['Attributes']['WeaponSlot'] = ''.$roster->locale->act['apiitem']['slotType'][$data['inventoryType']].'';
					$tt['Attributes']['WeaponSpeed'] = $data['weaponInfo']['weaponSpeed'];
					$tt['Attributes']['WeaponDamage'] = $data['weaponInfo']['damage']['min'].' - '.$data['weaponInfo']['damage']['max'];
					$tt['Attributes']['WeaponDPS'] = number_format($data['weaponInfo']['dps'], 1, '.', '');//$data['weaponInfo']['dps'];
				}
				$this->isWeapon = true;
				
			}
			if ($data['itemClass'] == '1' )
			{
				$tt['Attributes']['BagSomething'] = $roster->locale->act['apiitem']['itemSubClass'][$data['itemClass']][$data['itemSubClass']];
				$tt['Attributes']['BagType'] = ''.$roster->locale->act['apiitem']['slotType'][$data['inventoryType']].'';
				$tt['Attributes']['BagSize'] = $data['containerSlots'];
				$tt['Attributes']['BagDesc'] = $data['containerSlots'].' Slot '.$roster->locale->act['apiitem']['itemSubClass'][$data['itemClass']][$data['itemSubClass']].'';
				$this->isBag = true;
			}
			if ($data['upgradable'])
			{
				if (isset($this->user['tooltipParams']['upgrade']['current']))
				{
					$tt['Attributes']['Upgrade']['Base'] = $this->user['tooltipParams']['upgrade']['current'];
					$tt['Attributes']['Upgrade']['Max'] = $this->user['tooltipParams']['upgrade']['total'];
				}
				else
				{
					$tt['Attributes']['Upgrade']['Base'] = 0;
					$tt['Attributes']['Upgrade']['Max'] = 2;
				}
				
			}
			//$this->isWeapon = true;
			//$tt['Attributes']['MadeBy']['Name'] = $matches[1];
			//$tt['Attributes']['MadeBy']['Line'] = $matches[0];
			if ( $data['heroicTooltip'] )
			{
				$tt['Attributes']['Heroic'] = $roster->locale->act['tooltip_heroic'];
			}
			if ( isset($data['nameDescription']) )
			{
				$tt['Attributes']['NameDesc']['Text'] = $data['nameDescription'];
				$tt['Attributes']['NameDesc']['color'] = $data['nameDescriptionColor'];
			}
			//$tt['Attributes']['Charges'] = $line;
			//$tt['Poison']['Effect'][] = $line;
			//$this->isPoison = true;
			//$tt['Attributes']['Conjured'][] = $line;
			//$this->parsed_item = $tt;
			//$this->attributes = ( isset($tt['Attributes']) ? $tt['Attributes'] : null );
			//$this->effects = ( isset($tt['Effects']) ? $tt['Effects'] : null );
			//echo '<pre>'; print_r($tt); echo '</pre>';
		$this->parsed_item = $tt;
		$this->attributes = ( isset($tt['Attributes']) ? $tt['Attributes'] : null );
		$this->effects = ( isset($tt['Effects']) ? $tt['Effects'] : null );
		
	}
	
	function _getSetData($set_id)
	{
		global $roster;
		//$item_api = $roster->api->Data->getItemSet($set_id);
		$set = array();
		foreach($this->dapi['itemSet']['items'] as $item)
		{
			$item_a = $roster->api->Data->getItemInfo($item);
			$set[]['Name'] = $item_a['name'];
		}
		return $set;
	}
	
	function _getSetDataa($set_ids,$set_id)
	{
		global $roster;
		$item_api = $roster->api->Data->getItemSet($set_id);
		echo '<pre>';print_r($item_api);echo '</pre>';
		$set = array();
		foreach($set_ids as $a => $item)
		{
			echo $item.'<br>';
			$item_a = $roster->api->Data->getItemInfo($item);
			$set[]['Name'] = $item_a['name'];
		}
		return $set;
	}
	function _makeTooltip()
	{
		$html_tt = $this->_getCaption();
		if( isset($this->attributes['Conjured']) )
		{
			$html_tt .= $this->_getConjures();
		}
		if( isset($this->attributes['Heroic']) )
		{
			$html_tt .= $this->_getHeroic();
		}
		if( isset($this->attributes['NameDesc']['Text']) )
		{
			$html_tt .= $this->_getNameDesc();
		}
		if( isset($this->attributes['BindType']) )
		{
			$html_tt .= $this->_getBindType();
		}
		if( isset($this->attributes['Unique']) )
		{
			$html_tt .= $this->_getUnique();
		}
		if( isset($this->attributes['Upgrade']) )
		{
			$html_tt .= $this->_getUpgrade();
		}
		if( isset($this->attributes['ItemLevel']) )
		{
			$html_tt .= $this->_getItemLevel();
		}
		if( $this->isArmor )
		{
			$html_tt .= $this->_getArmor();
		}
		if( $this->isWeapon )
		{
			$html_tt .= $this->_getWeapon();
		}
		if( $this->isBag )
		{
			$html_tt .= $this->_getBag();
		}

		if( isset($this->attributes['ArmorClass']) )
		{
			$html_tt .= $this->_getArmorClass();
		}
		if( isset($this->attributes['GemBonus']) )
		{
			$html_tt .= $this->_getGemBonus();
		}
		if( isset($this->attributes['SkillRequired']) )
		{
			$html_tt .= $this->_getSkillRequired();
		}
		if( isset($this->attributes['BaseStats']) )
		{
			$html_tt .= $this->_getBaseStats();
		}
		if( isset($this->attributes['Enchantment']) )
		{
			$html_tt .= $this->_getEnchantment();
		}
		if( isset($this->attributes['TempEnchantment']) )
		{
			$html_tt .= $this->_getTempEnchantment();
		}
		if( $this->isSocketable )
		{
			$html_tt .= $this->_getSockets();
			$html_tt .= $this->_getSocketBonus();
		}
		if( isset($this->attributes['Class']) )
		{
			$html_tt .= $this->_getRequiredClasses();
		}
		if( isset($this->attributes['Race']) )
		{
			$html_tt .= $this->_getRequiredRaces();
		}
		if( isset($this->attributes['Durability']) )
		{
			$html_tt .= $this->_getDurability();
		}
		if( isset($this->attributes['Requires']) )
		{
			$html_tt .= $this->_getRequired();
		}
		if( isset($this->effects) )
		{
			$html_tt .= $this->_getPassiveBonus();
		}
		if( isset($this->attributes['Charges']) )
		{
			$html_tt .= $this->_getItemCharges();
		}
		if( $this->isSetPiece )
		{
			$html_tt .= $this->_getSetPiece();
			$html_tt .= $this->_getSetBonus();
			$html_tt .= $this->_getInactiveSetBonus();
		}
		if( isset($this->attributes['MadeBy']['Line']) )
		{
			$html_tt .= $this->_getCrafter();
		}
		if( isset($this->attributes['Restrictions']) )
		{
			$html_tt .= $this->_getRestrictions();
		}
		if( isset($this->attributes['ItemNote']) )
		{
			$html_tt .= $this->_getItemNote();
		}
		if( isset($this->attributes['Source']) )
		{
			$html_tt .= $this->_getSource();
		}
		if( isset($this->attributes['Boss']) )
		{
			$html_tt .= $this->_getBoss();
		}
		if( isset($this->attributes['DropRate']) )
		{
			$html_tt .= $this->_getDropRate();
		}

		$html_tt = str_replace("<br />", '<br />', $html_tt);
		
		if( ($this->DEBUG && $this->isParseError) || $this->DEBUG == 2 )
		{
			trigger_error('<table class="border_frame" cellpadding="0" cellspacing="1" width="350"><tr><td>'
			. $html_tt
			. '<hr width="80%" /> ' . str_replace("<br />", '<br />', $this->tooltip)
			. '</td></tr></table><br />'
			. aprint($this->parsed_item,'',true));
		}
		//echo  $html_tt . ( $this->DEBUG ? '<br />Parsed Full' : '' );
		$tooltip = ''.$html_tt.'';
		return $tooltip;
	}
	
function _getCaption()
	{
		$html = '' . $this->parsed_item['General']['Name'] . "<br />";
		return $html;
	}

	// heroic shit wow i missed this 
	function _getHeroic()
	{
		global $roster;

		$heroic = $this->attributes['Heroic'];

		if( preg_match( $roster->locale->act['tooltip_preg_heroic'], $heroic) )
		{
			$color = '66DD33';
		}
		
		else
		{
			$color = 'ffffff';
		}

		$html = '' . $heroic . "<br />";

		return $html;
	}
	
	function _getNameDesc()
	{
		$heroic = $this->attributes['NameDesc']['Text'];

		$html = '' . $heroic . "<br />";

		return $html;
	}
	
	function _getBindType()
	{
		global $roster;

		$html = $this->attributes['BindType']. "<br />";

		return $html;
	}

	function _getConjures()
	{
		$html = '';
		foreach( $this->attributes['Conjured'] as $conjured )
		{
			$html .= $conjured . "<br />";
		}
		return $html;
	}

	function _getUnique()
	{
		$html = $this->attributes['Unique'] . "<br />";
		return $html;
	}
	
	function _getUpgrade()
	{
		$html = sprintf( $roster->locale->act['tooltip_upgrade'], $this->attributes['Upgrade']['Base'], $this->attributes['Upgrade']['Max']). "<br />";
		return $html;
	}

	function _getArmor()
	{
		if( isset($this->attributes['ArmorType']) && isset($this->attributes['ArmorSlot']) )
		{
			//$html = '<div style="width:100%;"><span style="float:right;">' . $this->attributes['ArmorType'] . '</span>' . $this->attributes['ArmorSlot'] . '</div>';
			$html = '' . $this->attributes['ArmorSlot'] . '	' . $this->attributes['ArmorType'] . "<br />";
		}
		elseif( isset($this->attributes['ArmorSlot'] ) )
		{
			$html = $this->attributes['ArmorSlot'] . "<br />";
		}
		elseif( isset($this->attributes['ArmorType']) )
		{
			$html = $this->attributes['ArmorType'] . "<br />";
		}
		else
		{
			return null;
		}
		return $html;
	}

	function _getWeapon()
	{
		global $roster;
		$html='';
		if( isset($this->attributes['WeaponType']) && isset($this->attributes['WeaponSlot']) )
		{
			/*$html = '<div style="width:100%;"><span style="float:right;">'
				  . $this->attributes['WeaponType'] . '</span>'
				  . $this->attributes['WeaponSlot'] . '</div>';
				  */
			$html .= '' . $this->attributes['WeaponSlot'] . '	' . $this->attributes['WeaponType'] . "<br />";
		}
		elseif( isset($this->attributes['WeaponType']) )
		{
			$html .= $this->attributes['WeaponType'] . "<br />";
		}
		elseif( isset($this->attributes['WeaponSlot']) )
		{
			$html .= $this->attributes['WeaponSlot'] . "<br />";
		}

		if( isset($this->attributes['WeaponDamage']) )
		{
		$html .= $this->attributes['WeaponDamage'].' Damage	Speed '.$this->attributes['WeaponSpeed'] . "<br />";
		}
		if( isset($this->attributes['WeaponDPS']) )
		{
			$html .= '('.$this->attributes['WeaponDPS'] . " dps)<br />";
		}

		return $html;
	}

	function _getBag()
	{
		if (isset( $this->attributes['BagDesc']))
		{
		//$html = $this->attributes['BagDesc'] . "<br />";
		$html = $this->attributes['BagDesc'] . "<br />";
		return $html;
		}
		else
		{
		return;
		}
		
	}
	
	function _getGemBonus()
	{
		$html = $this->attributes['GemBonus'] . "<br />";
		return $html;
	}

	function _getSkillRequired()
	{
		$html = $roster->locale->act['tooltip_requires'].' ' . $this->attributes['SkillRequired'] . "<br />";
		return $html;
	}
	
	function _getArmorClass()
	{
		$html = $this->attributes['ArmorClass']['Line'] . "<br />";
		return $html;
	}

	function _getBaseStats()
	{
		$html = '';
		$stats = array();
		$stats = $this->attributes['BaseStats'];

		foreach( $stats as $stat )
		{
			$html .= '' . $stat . "<br />";
		}
		return $html;
	}

	function _getEnchantment()
	{
		$html = '<span style="color:#00ff00;">' . $this->attributes['Enchantment'] . '</span><br />';
		return $html;
	}

	function _getTempEnchantment()
	{
		$html = '';

		foreach( $this->attributes['TempEnchantment'] as $bonus )
		{
			$html .= '' . $bonus . "<br />";
		}
		return $html;
	}

	function _getSockets()
	{
		global $roster;

		$html = '';

		$numSockets = count($this->dapi['socketInfo']['sockets']);
		$gem0 =  $gem1 =  $gem2 = $gem3 = null;
		$i =0;
		
		if (isset($this->dgems['gem0']['gemInfo']))
		{
			if (isset($this->user['tooltipParams']['gem0']))
			{
				$html .= $this->dgems['gem0']['gemInfo']['bonus']['name']."<br />";
				$i++;
			}
			if (isset($this->user['tooltipParams']['gem1']))
			{
				$html .= $this->dgems['gem1']['gemInfo']['bonus']['name']."<br />";
				$i++;
			}
			if (isset($this->user['tooltipParams']['gem2']))
			{
				$html .= $this->dgems['gem2']['gemInfo']['bonus']['name']."<br />";
				$i++;
			}
			if (isset($this->user['tooltipParams']['gem3']))
			{
				$html .= $this->dgems['gem3']['gemInfo']['bonus']['name']."<br />";
				$i++;
			}
		}
		if (!isset($this->dgems['gem0']['gemInfo']) && isset($this->dgems['gem0']['gem_bonus']))
		{
			foreach ($this->dgems as $id => $gem)
			{
				$html .= $gem['gem_bonus']."<br />";
				$i++;		
			}
		}

		for( $i; $i < $numSockets; $i++ )
		{
			$sk =  mb_strtolower($this->dapi['socketInfo']['sockets'][$i]['type'], 'UTF-8');
			if ($sk == 'hydraulic')
			{
				$sk_name = 'Sha-Touched '.$roster->locale->act['apiitem']['socket'];
			}
			else
			{
				$sk_name = ucfirst ($sk).' '.$roster->locale->act['apiitem']['socket'];
			}
			$html .= $sk_name."<br />";
		}
		//now lets do sockets with gems
		
		return $html;
	}

	function _getSocketBonus()
	{
		global $roster;
		if( isset($this->attributes['SocketBonus']) )
		{
			if( isset($this->isSocketBonus) == true )
			{
				$html = sprintf( $roster->locale->act['apiitem']['socketbonus'], $this->attributes['SocketBonus']). "<br />";
			}
			else
			{
				$html = sprintf( $roster->locale->act['apiitem']['socketbonus'], $this->attributes['SocketBonus']). "<br />";
			}

			return $html;
		}
		return null;
	}

	function _getDurability()
	{
		global $roster;

		$current = $this->attributes['Durability']['Current'];;
		$max = $this->attributes['Durability']['Max'];
		$percent = (($current / $max) * 100);
		$html = $this->attributes['Durability']['Line'] . ' ';

		$html .= $current . ' / ' . $max . " <br />";

		return $html;
	}

	function _getRequiredClasses()
	{
		global $roster;

		$html = $this->attributes['Class'] . ' ';
		$count = count($this->attributes['ClassText']);

		$i = 0;
		foreach( $this->attributes['ClassText'] as $class => $x)
		{
			$i++;
			$html .= $x;
			if( $count > $i )
			{
				$html .= ', ';
			}
		}
		$html .= " <br />";
		return $html;
	}

	function _getRequiredRaces()
	{
		$html = $this->attributes['Race'] . ' ';
		$count = count($this->attributes['RaceText']);

		$i = 0;
		foreach( $this->attributes['RaceText'] as $class => $x)
		{
			$i++;
			$html .= $x;
			if( $count > $i )
			{
				$html .= ', ';
			}
		}
		$html .= "<br />";
		return $html;
	}

	function _getRequired()
	{
		global $roster;

		$requires = array();
		$requires = $this->attributes['Requires'];
		$html = '';
		//$html .= 'Requires Level '.$this->attributes['Requires']."<br />";
		$html .= sprintf( $roster->locale->act['apiitem']['reqlevel'], $this->attributes['Requires'])."<br />";

		return $html;
	}

	
	function _getILevel()
	{
		global $roster;

		$this->attributes['ItemLevel'];
		$html = '';
		//$html .= 'Item Level '.$this->attributes['ItemLevel']."<br />";
		$html .= sprintf( $roster->locale->act['apiitem']['ilevel'], $this->attributes['ItemLevel'])."<br />";

		return $html;
	}
	
	
	function _getPassiveBonus()
	{
		$html = '';
		$effects = array();
		$effects = $this->effects;

		foreach( $effects as $type )
		{
			foreach( $type as $effect)
			{
				$html .= $effect . "<br />";
			}
		}
		return $html;
	}

	function _getItemCharges()
	{
		$html = $this->attributes['Charges'] . "<br />";
		return $html;
	}

	function _getSetPiece()
	{
		$html = '';
		if (!empty($this->attributes['Set']['ArmorSet']['Name']))
		{
			$html .= "<br /><br />" . $this->attributes['Set']['ArmorSet']['Name'] ." (".$this->setItemEquiped."/5)<br />";
		}
		$pices = $this->attributes['Set']['ArmorSet']['Piece'];
		foreach ($pices as $num => $p)
		{
			$html .= "" . $this->attributes['Set']['ArmorSet']['Piece'][$num]['Name'] ."<br />";
		}
		return $html;
	}

	function _getSetBonus()
	{
		if( isset($this->attributes['Set']['SetBonus']) )
		{
			$html = '';
			foreach( $this->attributes['Set']['SetBonus'] as $bonus )
			{
				$html .= $bonus . "<br />";
			}
		return $html;
		}
	return null;
	}

	function _getInactiveSetBonus()
	{
		if( !isset($this->attributes['Set']['InactiveSet']) )
		{
			return false;
		}
		$html = '';

		foreach( $this->attributes['Set']['InactiveSet'] as $piece )
		{
			$html .= $piece . "<br />";
		}
		return $html;
	}

	function _getCrafter()
	{
		$html = htmlentities($this->attributes['MadeBy']['Line']) . "<br />";
		return $html;
	}

	function _getRestrictions()
	{
		$html = '';

		foreach( $this->attributes['Restrictions'] as $val )
		{
			$html .= $val . "<br />";
		}
		return $html;
	}

	function _getItemNote()
	{
		$html = $this->attributes['ItemNote'] . "<br />";
		return $html;
	}

	function _getItemLevel()
	{
		$html = 'Item Level ' . $this->attributes['ItemLevel']. "<br />";
		return $html;
	}

	function _getBoss()
	{
		
		$html = '';
		return $html;
	}

	function _getSource()
	{

		$html = '';
		return $html;
	}

	function _getDropRate()
	{

		$html = '';
		return $html;
	}
	
		function _getItemColor($value) 
		{
		$ret = '';
		switch ($value) {
			case 5: $ret = "ff8000"; //Orange
				break;
			case 4: $ret = "a335ee"; //Purple
				break;
			case 3: $ret = "0070dd"; //Blue
				break;
			case 2: $ret = "1eff00"; //Green
				break;
			case 1: $ret = "ffffff"; //White
				break;
			default: $ret = "9d9d9d"; //Grey
				break;
		}
		
		return $ret;
	}

	
}

?>
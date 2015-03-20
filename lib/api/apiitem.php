<?php
/**
 * WoWRoster.net WoWRoster
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
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
	
	var $enchants = array();
	var $de = '{"5324":{"bonus":"+50 Critical Strike"},"5325":{"bonus":"+50 Haste"},"5326":{"bonus":"+50 Mastery"},"5327":{"bonus":"+50 Multistrike"},
	"5328":{"bonus":"+50 Versatility"},"5317":{"bonus":"+75 Critical Strike"},"5318":{"bonus":"+75 Haste"},"5319":{"bonus":"+75 Mastery"},"5320":{"bonus":"+75 Multistrike"},"5321":{"bonus":"+75 Versatility"},"5310":{"bonus":"+100 Critical Strike & +10% Speed"},"5311":{"bonus":"+100 Haste & +10% Speed"},"5312":{"bonus":"+100 Mastery & +10% Speed"},"5313":{"bonus":"+100 Multistrike & +10% Speed"},"5314":{"bonus":"+100 Versatility & +10% Speed"},"5330":{"bonus":"Mark of the Thunderlord"},"5331":{"bonus":"Mark of the Shattered Hand"},"5335":{"bonus":"Mark of Shadowmoon"},"5336":{"bonus":"Mark of Blackrock"},"5337":{"bonus":"Mark of Warsong"},"5334":{"bonus":"Mark of the Frostwolf"},"5384":{"bonus":"Mark of Bleeding Hollow"},"5284":{"bonus":"+30 Critical Strike"},"5297":{"bonus":"+30 Haste"},"5299":{"bonus":"+30 Mastery"},"5301":{"bonus":"+30 Multistrike"},"5303":{"bonus":"+30 Versatility"},"5285":{"bonus":"+40 Critical Strike"},"5292":{"bonus":"+40 Haste"},"5293":{"bonus":"+40 Mastery"},"5294":{"bonus":"+40 Multistrike"},"5295":{"bonus":"+40 Versatility"},"5281":{"bonus":"+100 Critical Strike"},"5298":{"bonus":"+100 Haste"},"5300":{"bonus":"+100 Mastery"},"5302":{"bonus":"+100 Multistrike"},"5304":{"bonus":"+100 Versatility"},"4441":{"bonus":"Windsong"},"4442":{"bonus":"Jade Spirit"},"4443":{"bonus":"Elemental Force"},"4444":{"bonus":"Dancing Steel"},"4445":{"bonus":"Colossus"},"4446":{"bonus":"River\'s Song"},"4411":{"bonus":"+170 Mastery"},"4412":{"bonus":"+170 Dodge"},"4414":{"bonus":"+180 Intellect"},"4415":{"bonus":"+180 Strength"},"4416":{"bonus":"+180 Agility"},"4417":{"bonus":"+200 PvP Resilience"},"4418":{"bonus":"+200 Spirit"},"4419":{"bonus":"+80 All Stats"},"4420":{"bonus":"+300 Stamina"},"4421":{"bonus":"+180 Critical Strike"},"4422":{"bonus":"+200 Stamina"},"4423":{"bonus":"+180 Intellect"},"4424":{"bonus":"+180 Critical Strike"},"4426":{"bonus":"+175 Haste"},"4427":{"bonus":"+175 Critical Strike"},"4428":{"bonus":"+140 Agility & Minor Speed Increase"},"4429":{"bonus":"+140 Mastery & Minor Speed Increase"},"4430":{"bonus":"+170 Haste"},"4431":{"bonus":"+170 Haste"},"4432":{"bonus":"+170 Strength"},"4433":{"bonus":"+170 Mastery"},"4434":{"bonus":"+165 Intellect"},"4993":{"bonus":"+170 Parry"},"4066":{"bonus":"Mending"},"4258":{"bonus":"+50 Agility"},"4256":{"bonus":"+50 Strength"},"4257":{"bonus":"+50 Intellect"},"4061":{"bonus":"+50 Mastery"},"4062":{"bonus":"+30 Stamina and Minor Movement Speed"},"4063":{"bonus":"+15 All Stats"},"4064":{"bonus":"+56 PvP Power"},"4065":{"bonus":"+50 Haste"},"4067":{"bonus":"Avalanche"},"4068":{"bonus":"+50 Haste"},"4069":{"bonus":"+50 Haste"},"4070":{"bonus":"+55 Stamina"},"4071":{"bonus":"+50 Critical Strike"},"4072":{"bonus":"+30 Intellect"},"4073":{"bonus":"+16 Stamina"},"4074":{"bonus":"Elemental Slayer"},"4075":{"bonus":"+35 Strength"},"4076":{"bonus":"+35 Agility"},"4077":{"bonus":"+40 PvP Resilience"},"4082":{"bonus":"+50 Haste"},"4083":{"bonus":"Hurricane"},"4084":{"bonus":"Heartsong"},"4085":{"bonus":"+50 Mastery"},"4086":{"bonus":"+50 Dodge"},"4087":{"bonus":"+50 Critical Strike"},"4088":{"bonus":"+40 Spirit"},"4089":{"bonus":"+50 Critical Strike"},"4090":{"bonus":"+30 Stamina"},"4091":{"bonus":"+40 Intellect"},"4092":{"bonus":"+50 Critical Strike"},"4093":{"bonus":"+50 Spirit"},"4094":{"bonus":"+50 Mastery"},"4095":{"bonus":"+50 Haste"},"4096":{"bonus":"+50 Intellect"},"4097":{"bonus":"Power Torrent"},"4098":{"bonus":"Windwalk"},"4099":{"bonus":"Landslide"},"4100":{"bonus":"+65 Critical Strike"},"4101":{"bonus":"+65 Critical Strike"},"4102":{"bonus":"+20 All Stats"},"4103":{"bonus":"+75 Stamina"},"4105":{"bonus":"+25 Agility and Minor Movement Speed"},"4104":{"bonus":"+35 Mastery and Minor Movement Speed"},"4106":{"bonus":"+50 Strength"},"4107":{"bonus":"+65 Mastery"},"4108":{"bonus":"+65 Haste"},"4227":{"bonus":"+130 Agility"},"3225":{"bonus":"Executioner"},"3844":{"bonus":"+45 Spirit"},"3239":{"bonus":"Icebreaker Weapon"},"3241":{"bonus":"Lifeward"},"3247":{"bonus":"+70 Attack Power versus Undead"},"3251":{"bonus":"Giantslaying"},"3830":{"bonus":"+50 Spell Power"},"3828":{"bonus":"+42 Attack Power"},"1103":{"bonus":"+26 Agility"},"3273":{"bonus":"Deathfrost"},"3790":{"bonus":"Black Magic"},"1606":{"bonus":"+25 Attack Power"},"3827":{"bonus":"+55 Attack Power"},"3833":{"bonus":"+32 Attack Power"},"3834":{"bonus":"+63 Spell Power"},"3789":{"bonus":"Berserking"},"3788":{"bonus":"+50 Critical Strike"},"3854":{"bonus":"+81 Spell Power"},"3855":{"bonus":"+69 Spell Power"},"3233":{"bonus":"+250 Mana"},"3231":{"bonus":"+15 Haste"},"3234":{"bonus":"+20 Critical Strike"},"1952":{"bonus":"+20 Dodge"},"3236":{"bonus":"+200 Health"},"4747":{"bonus":"+16 Agility"},"1147":{"bonus":"+18 Spirit"},"2381":{"bonus":"+20 Spirit"},"3829":{"bonus":"+17 Attack Power"},"1075":{"bonus":"+22 Stamina"},"5259":{"bonus":"+20 Agility"},"1119":{"bonus":"+16 Intellect"},"1600":{"bonus":"+19 Attack Power"},"3243":{"bonus":"+28 PvP Power"},"3244":{"bonus":"+14 Spirit and +14 Stamina"},"3245":{"bonus":"+20 PvP Resilience"},"4748":{"bonus":"+16 Agility"},"1951":{"bonus":"+18 Dodge"},"3246":{"bonus":"+28 Spell Power"},"3826":{"bonus":"+24 Critical Strike"},"2661":{"bonus":"+3 All Stats"},"3252":{"bonus":"+8 All Stats"},"3253":{"bonus":"+2% Threat and +10 Parry"},"3256":{"bonus":"+10 Agility and +40 Armor"},"2326":{"bonus":"+23 Spell Power"},"3294":{"bonus":"+25 Stamina"},"1953":{"bonus":"+22 Dodge"},"3831":{"bonus":"+23 Haste"},"3296":{"bonus":"+10 Spirit and 2% Reduced Threat"},"3297":{"bonus":"+275 Health"},"3232":{"bonus":"+15 Stamina and Minor Speed Increase"},"3824":{"bonus":"+12 Attack Power"},"1128":{"bonus":"+25 Intellect"},"3825":{"bonus":"+15 Haste"},"1099":{"bonus":"+22 Agility"},"1603":{"bonus":"+22 Attack Power"},"3832":{"bonus":"+10 All Stats"},"1597":{"bonus":"+16 Attack Power"},"2332":{"bonus":"+30 Spell Power"},"3845":{"bonus":"+25 Attack Power"},"3850":{"bonus":"+40 Stamina"},"4746":{"bonus":"+7 Weapon Damage"},"2666":{"bonus":"+30 Intellect"},"2667":{"bonus":"+35 Attack Power"},"2668":{"bonus":"+20 Strength"},"2669":{"bonus":"+40 Spell Power"},"2670":{"bonus":"+35 Agility"},"2671":{"bonus":"+50 Arcane and Fire Spell Power"},"2672":{"bonus":"+54 Shadow and Frost Spell Power"},"2673":{"bonus":"Mongoose"},"2674":{"bonus":"Spellsurge"},"2675":{"bonus":"Battlemaster"},"3846":{"bonus":"+40 Spell Power"},"3222":{"bonus":"+20 Agility"},"2657":{"bonus":"+12 Agility"},"2622":{"bonus":"+12 Dodge"},"2647":{"bonus":"+12 Strength"},"1891":{"bonus":"+4 All Stats"},"2648":{"bonus":"+14 Dodge"},"5183":{"bonus":"+15 Spell Power"},"2679":{"bonus":"+12 Spirit"},"2649":{"bonus":"+12 Stamina"},"5184":{"bonus":"+15 Spell Power"},"2653":{"bonus":"+12 Dodge"},"2654":{"bonus":"+12 Intellect"},"2655":{"bonus":"+15 Parry"},"2656":{"bonus":"+10 Spirit and +10 Stamina"},"2658":{"bonus":"+10 Haste and +10 Critical Strike"},"2659":{"bonus":"+150 Health"},"2662":{"bonus":"+120 Armor"},"5237":{"bonus":"+15 Spirit"},"3150":{"bonus":"+14 Spirit"},"2933":{"bonus":"+15 PvP Resilience"},"2934":{"bonus":"+10 Critical Strike"},"2935":{"bonus":"+15 Critical Strike"},"5250":{"bonus":"+15 Strength"},"5255":{"bonus":"+13 Attack Power"},"2937":{"bonus":"+20 Spell Power"},"2322":{"bonus":"+19 Spell Power"},"369":{"bonus":"+12 Intellect"},"5257":{"bonus":"+12 Attack Power"},"2938":{"bonus":"+16 PvP Power"},"5258":{"bonus":"+12 Agility"},"2939":{"bonus":"Minor Speed and +6 Agility"},"2940":{"bonus":"Minor Speed and +9 Stamina"},"1071":{"bonus":"+18 Stamina"},"3229":{"bonus":"+12 PvP Resilience"},"5260":{"bonus":"+18 Dodge"},"4723":{"bonus":"+2 Weapon Damage"},"249":{"bonus":"+2 Beastslaying"},"250":{"bonus":"+1  Weapon Damage"},"723":{"bonus":"+3 Intellect"},"255":{"bonus":"+3 Spirit"},"241":{"bonus":"+2 Weapon Damage"},"943":{"bonus":"+3 Weapon Damage"},"853":{"bonus":"+6 Beastslaying"},"854":{"bonus":"+6 Elemental Slayer"},"4745":{"bonus":"+3 Weapon Damage"},"1897":{"bonus":"+5 Weapon Damage"},"803":{"bonus":"Fiery Weapon"},"912":{"bonus":"Demonslaying"},"963":{"bonus":"+7 Weapon Damage"},"805":{"bonus":"+4 Weapon Damage"},"1894":{"bonus":"Icy Chill"},"1896":{"bonus":"+9 Weapon Damage"},"1898":{"bonus":"Lifestealing"},"1899":{"bonus":"Unholy Weapon"},"1900":{"bonus":"Crusader"},"1903":{"bonus":"+9 Spirit"},"1904":{"bonus":"+9 Intellect"},"2443":{"bonus":"+7 Frost Spell Damage"},"2504":{"bonus":"+30 Spell Power"},"2505":{"bonus":"+29 Spell Power"},"2563":{"bonus":"+15 Strength"},"2564":{"bonus":"+15 Agility"},"2567":{"bonus":"+20 Spirit"},"2568":{"bonus":"+22 Intellect"},"2646":{"bonus":"+25 Agility"},"3869":{"bonus":"Blade Ward"},"3870":{"bonus":"Blood Draining"},"4720":{"bonus":"+5 Health"},"41":{"bonus":"+5 Health"},"44":{"bonus":"Absorption (10)"},"924":{"bonus":"+2 Dodge"},"24":{"bonus":"+5 Mana"},"4721":{"bonus":"+1 Stamina"},"242":{"bonus":"+15 Health"},"243":{"bonus":"+1 Spirit"},"783":{"bonus":"+10 Armor"},"246":{"bonus":"+20 Mana"},"4725":{"bonus":"+1 Agility"},"248":{"bonus":"+1 Strength"},"254":{"bonus":"+25 Health"},"4727":{"bonus":"+3 Spirit"},"66":{"bonus":"+1 Stamina"},"247":{"bonus":"+1 Agility"},"4722":{"bonus":"+1 Stamina"},"4724":{"bonus":"+1 Agility"},"744":{"bonus":"+20 Armor"},"4733":{"bonus":"+30 Armor"},"4728":{"bonus":"+3 Spirit"},"4730":{"bonus":"+3 Stamina"},"823":{"bonus":"+3 Strength"},"63":{"bonus":"Absorption (25)"},"843":{"bonus":"+30 Mana"},"844":{"bonus":"+2 Mining"},"845":{"bonus":"+2 Herbalism"},"2603":{"bonus":"+2 Fishing"},"4729":{"bonus":"+3 Intellect"},"847":{"bonus":"+1 All Stats"},"4731":{"bonus":"+3 Stamina"},"848":{"bonus":"+30 Armor"},"849":{"bonus":"+3 Agility"},"850":{"bonus":"+35 Health"},"4735":{"bonus":"+5 Spirit"},"724":{"bonus":"+3 Stamina"},"925":{"bonus":"+3 Dodge"},"4737":{"bonus":"+5 Stamina"},"4736":{"bonus":"+5 Spirit"},"856":{"bonus":"+5 Strength"},"857":{"bonus":"+50 Mana"},"4726":{"bonus":"+3 Spirit"},"863":{"bonus":"+10 Parry"},"865":{"bonus":"+5 Skinning"},"866":{"bonus":"+2 All Stats"},"884":{"bonus":"+50 Armor"},"4740":{"bonus":"+5 Agility"},"4738":{"bonus":"+5 Stamina"},"905":{"bonus":"+5 Intellect"},"852":{"bonus":"+5 Stamina"},"906":{"bonus":"+5 Mining"},"907":{"bonus":"+7 Spirit"},"908":{"bonus":"+50 Health"},"909":{"bonus":"+5 Herbalism"},"4734":{"bonus":"+3 Agility"},"4739":{"bonus":"+5 Strength"},"911":{"bonus":"Minor Speed Increase"},"4741":{"bonus":"+7 Spirit"},"913":{"bonus":"+65 Mana"},"923":{"bonus":"+5 Dodge"},"904":{"bonus":"+5 Agility"},"927":{"bonus":"+7 Strength"},"928":{"bonus":"+3 All Stats"},"4743":{"bonus":"+7 Stamina"},"930":{"bonus":"+2% Mount Speed"},"931":{"bonus":"+10 Haste"},"1883":{"bonus":"+7 Intellect"},"1884":{"bonus":"+9 Spirit"},"1885":{"bonus":"+9 Strength"},"1886":{"bonus":"+9 Stamina"},"1887":{"bonus":"+7 Agility"},"4742":{"bonus":"+7 Strength"},"1889":{"bonus":"+70 Armor"},"1890":{"bonus":"+10 Spirit and +10 Stamina"},"4744":{"bonus":"+7 Stamina"},"929":{"bonus":"+7 Stamina"},"851":{"bonus":"+5 Spirit"},"1892":{"bonus":"+100 Health"},"1893":{"bonus":"+100 Mana"},"2565":{"bonus":"+9 Spirit"},"2650":{"bonus":"+15 Spell Power"},"2613":{"bonus":"+2% Threat"},"2614":{"bonus":"+20 Shadow Spell Power"},"2615":{"bonus":"+20 Frost Spell Power"},"2616":{"bonus":"+20 Fire Spell Power"},"2617":{"bonus":"+16 Spell Power"},"910":{"bonus":"+8 Agility and +8 Dodge"},"2621":{"bonus":"2% Reduced Threat"},"3238":{"bonus":"Gatherer"},"3858":{"bonus":"+10 Critical Strike"},"4732":{"bonus":"+5 Fishing"}}';
	
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
		$this->enchants = json_decode($this->de,true);
		
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
			
			if (isset($data['description']) && !empty($data['description']))
			{
				$tt['Attributes']['ItemNote'] = '"'.$data['description'].'"';
			}
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
				//else
				//{
				//	$tt['Attributes']['Upgrade']['Base'] = 0;
				//	$tt['Attributes']['Upgrade']['Max'] = 2;
				//}
				
			}
			//build new chchant magic...
			//$enchants
			if (isset($this->user['tooltipParams']['enchant']) && isset($this->enchants[$this->user['tooltipParams']['enchant']]['bonus']))
			{
				$tt['Attributes']['Enchantment'] = $this->enchants[$this->user['tooltipParams']['enchant']]['bonus'];
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
		global $roster;
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
		global $roster;
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
		global $roster;
		$html = '<span style="color:#00ff00;">' . sprintf( $roster->locale->act['tooltip_ienchant'], $this->attributes['Enchantment']) . '</span><br />';
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
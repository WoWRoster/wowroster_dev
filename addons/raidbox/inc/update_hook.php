<?php


if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}


class raidboxUpdate
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
	var $raids = array(
	'mv' => Array
		(
			'id' => "6668",
			'title' => "Mogu'shan Vaults Guild Run",
			'points' => "10",
			'description' => "Defeat the bosses in Mogu'shan Vaults while in a guild group.",
			'icon' => "achievement_raid_secondhalfmogu",
			'criteria' => Array
				(
					'0' => Array ('id' => "22384",'description' => "Stone Guard"),
					'1' => Array ('id' => "19485",'description' => "Feng the Accursed"),
					'2' => Array ('id' => "19486",'description' => "Gara'jal the Spiritbinder"),
					'3' => Array ('id' => "19487",'description' => "Four Kings"),
					'4' => Array ('id' => "19114",'description' => "Elegon"),
					'5' => Array ('id' => "19488",'description' => "Will of the Emperor"),
				),
		),

	'hof' => Array
		(
			'id' => "6669",
			'title' => "Heart of Fear Guild Run",
			'points' => "10",
			'description' => "Defeat the bosses in Heart of Fear while in a guild group.",
			'icon' => "achievement_raid_mantidraid01",
			'criteria' => Array
				(
					'0' => Array ('id' => "19489",'description' => "Imperial Vizier Zor'lok"),
					'1' => Array ('id' => "19490",'description' => "Blade Lord Ta'yak"),
					'2' => Array ('id' => "19491",'description' => "Garalon"),
					'3' => Array ('id' => "19630",'description' => "Wind Lord Mel'jarak"),
					'4' => Array ('id' => "19492",'description' => "Amber-Shaper Un'sok"),
					'5' => Array ('id' => "19493",'description' => "Grand Empress Shek'zeer"),
				),
		),

	'toes' => Array
		(
			'id' => "6670",
			'title' => "Terrace of Endless Spring Guild Run",
			'points' => "10",
			'description' => "Defeat the bosses in Terrace of Endless Spring while in a guild group.",
			'icon' => "achievement_raid_terraceofendlessspring01",
			'criteria' => Array
				(
					'0' => Array ('id' => "19651",'description' => "Protectors of the Endless"),
					'1' => Array ('id' => "19652",'description' => "Tsulong"),
					'2' => Array ('id' => "19494",'description' => "Lei Shi"),
					'3' => Array ('id' => "19495",'description' => "Sha of Fear"),
				),
		),
											   
	'tot' => Array
		(
			'id' => "8140",
			'title' => "Throne of Thunder Guild Run",
			'points' => "10",
			'description' => "Defeat the bosses in the Throne of Thunder while in a guild group.",
			'icon' => "archaeology_5_0_thunderkinginsignia",
			'criteria' => Array
				(
					'0' => Array ('id' => "23072",'description' => "Jin'rokh the Breaker"),
					'1' => Array ('id' => "23073",'description' => "Horridon"),
					'2' => Array ('id' => "23074",'description' => "Council of Elders"),
					'3' => Array ('id' => "23075",'description' => "Tortos"),
					'4' => Array ('id' => "23076",'description' => "Megaera"),
					'5' => Array ('id' => "23077",'description' => "Ji-Kun"),
					'6' => Array ('id' => "23078",'description' => "Durumu the Forgotten"),
					'7' => Array ('id' => "23079",'description' => "Primordius"),
					'8' => Array ('id' => "23080",'description' => "Dark Animus"),
					'9' => Array ('id' => "23081",'description' => "Iron Qon"),
					'10' => Array ('id' => "23082",'description' => "Twin Consorts"),
					'11' => Array ('id' => "23083",'description' => "Lei Shen"),
				),
		),
	'soo' => Array
	(
		"id" 			=> "8510",
		"title" 		=> "Siege of Orgrimmar Guild Run",
		"points" 		=> "10",
		"description" 	=> "Defeat the bosses in the Siege of Orgrimmar while in a guild group.",
		"icon" 			=> "ability_garrosh_touch_of_yshaarj",

		'criteria' => Array
		(
			'0' => Array ( "id" => '23692', "description" => "Immerseus"),
			'1' => Array ( "id" => '23693', "description" => "Fallen Protectors"),
			'2' => Array ( "id" => '23694', "description" => "Norushen"),
			'3' => Array ( "id" => '23695', "description" => "Sha of Pride"),
			'4' => Array ( "id" => '23696', "description" => "Galakras"),
			'5' => Array ( "id" => '23697', "description" => "Iron Juggernaut"),
			'6' => Array ( "id" => '23698', "description" => "Kor'kron Dark Shaman"),
			'7' => Array ( "id" => '23699', "description" => "General Nazgrim"),
			'8' => Array ( "id" => '23700', "description" => "Malkorok"),
			'9' => Array ( "id" => '23702', "description" => "Spoils of Pandaria"),
			'10' => Array ( "id" => '23703', "description" => "Thok the Bloodthirsty"),
			'11' => Array ( "id" => '23701', "description" => "Siegecrafter Blackfuse"),
			'12' => Array ( "id" => '23704', "description" => "Paragons of the Klaxxi"),
			'13' => Array ( "id" => '23705', "description" => "Garrosh Hellscream"),
		),
	),
	);
	/**
	 * Constructor
	 *
	 * @param array $data
	 *		Addon data object
	 */
	function raidboxUpdate($data)
	{
		$this->data = $data;
	}

function reset_messages()
	{
		/**
		 * We display the addon name at the beginning of the output line. If
		 * the hook doesn't exist on this side, nothing is output. If we don't
		 * produce any output (update method off) we empty this before returning.
		 */

		$this->messages = 'Raid Box: ';
	}
	/**
	 * Guild_pre trigger, set out guild id here
	 *
	 * @param array $guild
	 * 		CP.lua guild data
	 */
	function guild_post($guild)
	{
		
		global $roster, $update;
		$addon = getaddon('raidbox');
		
		//echo '<pre>';
		//print_r($addon);
		//echo '</pre>';
		
		//build critera data 
		$query = "SELECT * FROM `".$roster->db->table('g_criteria','achievements')."`";// WHERE `cid` = '15079'";
		$result = $roster->db->query($query) or die_quietly($roster->db->error(),'Database Error',basename(__FILE__),__LINE__,$query);
		$crit = array();
		
		while ($row = $roster->db->fetch($result))
		{
			$crit[$row['crit_id']] = array(
					'crit_id'		=> $row['crit_id'],
					'crit_date'		=> $row['crit_date'],
					'crit_value'	=> $row['crit_value'],
				);
		}
		//build achievement data
		$query1 = "SELECT * FROM `".$roster->db->table('g_achievements','achievements')."`";// WHERE `cid` = '15079'";
		$result1 = $roster->db->query($query1) or die_quietly($roster->db->error(),'Database Error',basename(__FILE__),__LINE__,$query);
		$achi = array();
		
		while ($row = $roster->db->fetch($result1))
		{
			$achi[$row['achie_id']] = array(
					'achie_id'		=> $row['achie_id'],
					'achie_date'		=> $row['achie_date'],
				);
		}

		// this loops each raid	
		
		foreach ($this->raids as $raid => $rinfo)
		{
			$overide = false;
			if (isset($achi[$rinfo['id']]['achie_date']))
			{
				$overide = true;
			}
			$this->messages .= '<br/>'.$rinfo['title'].'<ul>';	
			//echo $rinfo['title'].' <img src="'.$roster->config['img_url'].'interface/icons/'.$rinfo['icon'].'"></a><br>';
			foreach ($rinfo['criteria'] as $id => $boss)
			{
				if (isset($crit[$boss['id']]['crit_value']))
				{
					$down = $crit[$boss['id']]['crit_value'];
				}
				else
				{
					$down = '0';
				}
				if ($overide OR $down >= 1)
				{
					$down = 1;
				}
				//echo $boss['description'] . ' x' . $down . '<br>';
				$this->messages .= '<li>'.$boss['description'].' - '.$down.'</li>';
				
				$querystr = "UPDATE `".$roster->db->table('addon_config')."` SET `config_value` = '".$down."'  WHERE `config_name` = '".$raid.'_boss_'.($id+1)."'";
				//$this->messages .= $querystr.'<br>';
				$result = $roster->db->query($querystr);
				
			}
			$this->messages .= '</ul>';
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
		
		return true;
	}

}

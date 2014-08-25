<?php
	
class feed
{

	var $output;
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'feed';
	var $filename = 'main-guild-feed.php';
	var $parent = 'main';
	var $icon = 'inv_misc_note_05';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'Latest Activity Feeds';
	var $description = 'displays top X feeds from the database.';
	var $credits = array(
		array(	"name"=>	"Ulminia <Ulminia@gmail.com>",
				"info"=>	"Feeds (Alpha Release)"),
	);

	var $config = array();
	/*
	*	__construct
	*	this is there the veriables for the addons are 
	*	set in the plugin these are unique to each addon 
	*
	*	contact the addon author is you have a sugestion 
	*	as to where plugin code should occure or use there descression
	*/
	
	public function __construct($pdata)
	{
		global $roster;
		
		//$this->config = $pdata['config'];
		$this->display();
	}

	function display ()
	{
		global $roster;
		$output='';
		$sql = "SELECT 
			`feed`.`member_id`,
			MAX(`feed`.`timestamp`) as ts ,
			`feed`.`item_icon`,
			`feed`.`achievement_title`,
			`feed`.`item_id`,
			`feed`.`type`,
			`feed`.`criteria_description`,
			`members`.`member_id`,
			`members`.`name`
			FROM `".$roster->db->table('char_feed','feeds')."` feed
			LEFT JOIN `".$roster->db->table('members')."` members ON `members`.`member_id` = `feed`.`member_id`
			GROUP BY `feed`.`member_id` ORDER BY ts desc limit 15";
		$resultx = $roster->db->query( $sql );
		while($info = $roster->db->fetch($resultx))
		{
			if (isset($info['member_id']))
			{
				$output .= '<div style="font:10px arial,helvetica,sans-serif;">'.$this->$info['type']($info).'</div><hr>';
			}
		}
	
		$this->output = $output;
	}
	
	
	
	function convert_date($date)
	{
		global $roster;
		//phptimeformat
		$date = date($roster->locale->act['phptimeformat'],$date);
		return $date;
	}
	/*
			guild functions
	*/
	function playerAchievement($data)
	{
		global $roster, $tooltips;
				
		$tooltip_text = $data['Achievement'];
		//return 'Earned the achievement "'.$data['achievement']['title'].'"<br>';
		$tooltip = makeOverlib($tooltip_text, '', '' , 0, '', ', WIDTH, 325');
		$ts = ($data['ts'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-36" >
			<img src="http://www.wowroster.net/Interface/Icons/'.$data['achievement_icon'].'.png" />
			</span>
			<div class="text">
				'.$data['Member'].' Earned the achievement <span style="color:#FFB100" ' . $tooltip . '>'.$data['achievement_title'].'</span> for '.$data['achievement_points'].' points.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
		
	}
	function guildAchievement($data)
	{
		global $roster, $tooltips;
		$tooltip_text = $data['Achievement'];
		//return 'Earned the achievement "'.$data['achievement']['title'].'"<br>';
		$tooltip = makeOverlib($tooltip_text, '', '' , 0, '', ', WIDTH, 325');
		$ts = ($data['ts'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-36" >
			<img src="http://www.wowroster.net/Interface/Icons/'.$data['achievement_icon'].'.png" />
			</span>
			<div class="text">
				Earned the achievement <span style="color:#FFB100" ' . $tooltip . '>'.$data['achievement_title'].'</span> for '.$data['achievement_points'].' points.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}
	
	function guildLevel($data)
	{
		global $roster, $tooltips;
		
		return sprintf($roster->locale->act[$data['type']], $roster->data['guild_name'], $data['levelUp'] );
	}
	
	function guildCreated($data)
	{
		global $roster, $tooltips;

		return ''.$data['Member'].' <span style="color:#FFB100">Crafted '.$item['name'].'</span>';
	}

	function itemPurchase($data)
	{
		global $roster, $tooltips;
		
		$item = $roster->api->Data->getItemInfo($data['item_id']);
		$ts = ($data['ts'] / 1000);

		return ''.$data['Member'].' <span style="color:#FFB100">Purchase '.$item['name'].'</span>';

	}	
	
	function itemLoot( $data )
	{
		global $roster, $tooltips;
		
		$item = $roster->api->Data->getItemInfo($data['item_id']);
		$ts = ($data['ts'] / 1000);

		return ''.$data['Member'].' <span style="color:#FFB100">Obtained '.$item['name'].'</span>';
	}
	
	
	/*
		character functions
	*/
	function LOOT( $data )
	{
		global $roster, $tooltips;
		
		require_once (ROSTER_LIB . 'item.php');
		$item = $roster->api->Data->getItemInfo($data['item_id']);
		$item_color = $roster->api->Data->_setQualityc($item['quality']);
		$ts = ($data['ts'] / 1000);
		return ''.$data['name'].' <span style="color:#FFB100">Obtained <span style="color:#'.$item_color.'">'.$item['name'].'</span></span>';
	}
	
	
	function ACHIEVEMENT( $data )
	{
		$ts = ($data['ts'] / 1000);

		return ''.$data['name'].' <span style="color:#FFB100">'.$data['achievement_title'].'</span>';
		
	}
	function CRITERIA( $data )
	{
		global $addon;
		$ts = ($data['ts'] / 1000);
		//$addon['url_path'] .'images/
				return ''.$data['name'].' <span style="color:#FFB100">Completed step <strong>'.$data['criteria_description'].'</strong></span>';
	}
	
	
	function BOSSKILL( $data )
	{
		//return 'has killed '.$data['name'].' ('.$data['quantity'].')<br>';
		$ts = ($data['ts'] / 1000);

		return ''.$data['name'].' <span style="color:#FFB100">'.$data['achievement_title'].'</span> Kills.';
	}
	
	
	
}
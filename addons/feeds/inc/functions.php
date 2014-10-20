<?php

class feeds{
/*
	var $functions = array(
			'LOOT' => 'event_LOOT',
			'ACHIEVEMENT' => 'event_ACHIEVEMENT',
			'CRITERIA' => 'event_CRITERIA',
			'BOSSKILL' => 'event_BOSSKILL',
			
			'itemPurchase' => 'event_Purchase',
			'itemLoot' => 'gevent_LOOT',
			'guildCreated
			'guildLevel
			'guildAchievement
			'playerAchievement
			);

*/
	function convert_date($date)
	{
		global $roster, $addon;
		//phptimeformat
		$date = date($roster->locale->act['phptimeformat'],$date);
		return $date;
	}
	/*
			guild functions
	*/
	function playerAchievement($data)
	{
		global $roster, $addon;
				
		$tooltip_text = $data['Achievement'];
		//return 'Earned the achievement "'.$data['achievement']['title'].'"<br>';
		$tooltip = makeOverlib($tooltip_text, '', '' , 0, '', ', WIDTH, 325');
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
			<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$data['achievement_icon'].'.'.$roster->config['img_suffix'].'" />
			</span>
			<div class="text">
				'.$data['Member'].' Earned the achievement <span style="color:#FFB100" ' . $tooltip . '>'.$data['achievement_title'].'</span> for '.$data['achievement_points'].' points.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
		
	}
	function guildAchievement($data)
	{
		global $roster, $addon;
		$tooltip_text = $data['Achievement'];
		//return 'Earned the achievement "'.$data['achievement']['title'].'"<br>';
		$tooltip = makeOverlib($tooltip_text, '', '' , 0, '', ', WIDTH, 325');
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
			<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$data['achievement_icon'].'.'.$roster->config['img_suffix'].'" />
			</span>
			<div class="text">
				Earned the achievement <span style="color:#FFB100" ' . $tooltip . '>'.$data['achievement_title'].'</span> for '.$data['achievement_points'].' points.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}
	
	function guildLevel($data)
	{
		global $roster, $addon;
		
		return sprintf($roster->locale->act[$data['type']], $roster->data['guild_name'], $data['levelUp'] );
	}
	
	function guildCreated($data)
	{
		global $roster, $addon;

		return '<div class="line">
			<span  class="icon-frame frame-24" >
				<img src="'.$addon['url_path'] .'images/criteria.png" />
			</span>
			<div class="text">
				'.sprintf($roster->locale->act[$data['type']], $roster->data['guild_name'] ).'.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}

	function itemCraft($data)
	{
		global $roster, $addon;;
		
		$item =  $roster->api2->fetch('item',array('id'=>$data['item_id']));
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
			<a href="http://battle.net/wow/en/item/'.$item['id'].'" target"_blank">
				<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$item['icon'].'.'.$roster->config['img_suffix'].'" />
			</a>
			</span>
			<div class="text">
				'.sprintf($roster->locale->act[$data['type']],$data['Member'], $this->processItem($item)).'.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}
	function itemPurchase($data)
	{
		global $roster, $addon;
		
		$item =  $roster->api2->fetch('item',array('id'=>$data['item_id']));
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
			<a href="http://battle.net/wow/en/item/'.$item['id'].'" target"_blank">
				<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$item['icon'].'.'.$roster->config['img_suffix'].'" />
			</a>
			</span>
			<div class="text">
				'.sprintf($roster->locale->act[$data['type']],$data['Member'], $this->processItem($item)).'.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';

	}	
	
	function itemLoot( $data )
	{
		global $roster, $addon;
		
		$item =  $roster->api2->fetch('item',array('id'=>$data['item_id']));
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
			<a href="http://battle.net/wow/en/item/'.$item['id'].'" target"_blank">
				<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$item['icon'].'.'.$roster->config['img_suffix'].'" />
			</a>
			</span>
			<div class="text">
				'.sprintf($roster->locale->act[$data['type']],$data['Member'], $this->processItem($item)).'.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}
	
	
	/*
		character functions
	*/
	function LOOT( $data )
	{
		global $roster, $addon;
		
		require_once (ROSTER_LIB . 'item.php');
		//$x = new item();
		// lets be fancy now...
		$item =  $roster->api2->fetch('item',array('id'=>$data['item_id']));
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
				<a href="http://battle.net/wow/en/item/'.$item['id'].'" target"_blank">
				<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$item['icon'].'.'.$roster->config['img_suffix'].'" />
				</a>
			</span>
			<div class="text">
				Obtained '.$this->processItem($item).'</a>.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}
	
	
	function ACHIEVEMENT( $data )
	{
		global $roster, $addon;
		$tooltip_text = $data['Achievement'];
		
		//return 'Earned the achievement "'.$data['achievement']['title'].'"<br>';
		$tooltip = makeOverlib($tooltip_text, '', '' , 0, '', ', WIDTH, 325');
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
				<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$data['achievement_icon'].'.'.$roster->config['img_suffix'].'" />
			</span>
			<div class="text">
				Earned the achievement <span style="color:#FFB100" ' . $tooltip . '>'.$data['achievement_title'].'</span> for '.$data['achievement_points'].' points.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
		
	}
	function CRITERIA( $data )
	{
		global $roster, $addon;
		$tooltip_text = $data['Achievement'];
		$tooltip = makeOverlib($tooltip_text, '', '' , 0, '', ', WIDTH, 325');
		$ts = ($data['timestamp'] / 1000);
		//$addon['url_path'] .'images/

		return '<div class="line">
			<span  class="icon-frame frame-24" >
				<img src="'.$addon['url_path'] .'images/criteria.png" />
			</span>
			<div class="text">
				Completed step <strong>'.$data['criteria_description'].'</strong> of achievement <span style="color:#FFB100" ' . $tooltip . '>'.$data['achievement_title'].'</span>.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}
	
	
	function BOSSKILL( $data )
	{
		global $roster, $addon;
		//return 'has killed '.$data['name'].' ('.$data['quantity'].')<br>';
		$ts = ($data['timestamp'] / 1000);

		return '<div class="line">
			<span  class="icon-frame frame-24" >
				<img src="'.$roster->config['interface_url'] . 'Interface/Icons/'.$data['achievement_icon'].'.'.$roster->config['img_suffix'].'" />
			</span>
			<div class="text">
				'.$data['achievement_points'].' <span style="color:#FFB100">'.$data['achievement_title'].'</span> Kills.
			</div>
			<div class="date">'.$this->convert_date($ts).'</div>
		</div>';
	}

	function processItem($item)
	{
		global $roster, $tooltips;
		
		require_once (ROSTER_LIB . 'item.php');
		//$x = new item();
		// lets be fancy now...
		if (isset( $item['id']))
		{
			$item_color = $roster->api->Data->_setQualityc($item['quality']);

			$item_id = $item['id'];
			
			$tooltip = 'data-tooltip="item-'.$item_id.'"';//makeOverlib($it, '', '' , 2, '', ', WIDTH, 325');

			$num_of_tips = (count($tooltips)+1);
			$linktip = '';

			foreach( $roster->locale->wordings[$roster->config['locale']]['itemlinks'] as $key => $ilink )
			{
				$linktip .= '<a href="' . $ilink . $item_id . '" target="_blank">' . $key . '</a><br />';
			}
			setTooltip($num_of_tips, $linktip);
			setTooltip('itemlink', $roster->locale->wordings[$roster->config['locale']]['itemlink']);

			$linktip = ' onclick="return overlib(overlib_' . $num_of_tips . ',CAPTION,overlib_itemlink,STICKY,NOCLOSE,WRAP,OFFSETX,5,OFFSETY,5);"';
			

			return '<span style="color:#' . $item_color . ';font-weight:bold;text-align: center;" ' . $tooltip . $linktip . '>' . $item['name'] . '</span>';
		}
		return '';
	}


}


<?php
	
class rb_m
{

	var $output;
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'rb_m';
	var $filename = 'main-guild-feed.php';
	var $parent = 'main';
	var $icon = 'inv_misc_note_05';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'Raid Box modual';
	var $description = 'displays a raid box on the main guild cms page';
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
		$raids = array(
			"Mogu'shan Vaults" => array(
				'boss_count'		=> '6',
				'image' 		=> 'mv',
				'cfg'	 		=> 'mv',
			),
			'Heart of Fear' => array(
				'boss_count'		=> '6',
				'image' 		=> 'hf',
				'cfg'	 		=> 'hof',
			),
			'Terrace of Endless Spring' => array(
				'boss_count'		=> '4',
				'image' 		=> 'tes',
				'cfg'	 		=> 'toes',
			),
			'Throne of Thunder' => array(
				'boss_count'		=> '13',
				'image' 		=> 'tot',
				'cfg'	 		=> 'tot',
			),
			'Siege of Orgrimmar' => array(
				'boss_count'	=> '14',
				'image' 		=> 'soo',
				'cfg'	 		=> 'soo',
			)
		);

		$addon = getaddon('raidbox');

		$down = 0;
		$bosses = '';	
		foreach($raids as $name => $det)
		{
			$down = 0;
			$per=0;
			$bosses = $name.'<br />';
			for( $t=1; $t < ($det['boss_count']+1); $t++)
			{
				$down = ($down+$addon['config'][''.$det['cfg'].'_boss_'.$t.'']);
				$color ='ff0000';
				if ($addon['config'][''.$det['cfg'].'_boss_'.$t.''] == 1)
				{
					$color = '7eff00';
				}
				$e = ($addon['config'][''.$det['cfg'].'_boss_'.$t.''] == 1 ? 'Down' : 'Up');
				//$bosses .= ''.$roster->locale->act[''.$det['cfg'].'_boss_'.$t.''].' - '.$e.'</span><br />';
				$bosses .= '<span style="color:#'.$color.';font-size:12px;font-weight:bold;"><div style="width:250px;"><span style="float:right;">'.$e.'</span>'.$roster->locale->act[''.$det['cfg'].'_boss_'.$t.''].'</div></span>';
				//$bosses .= $roster->locale->act[''.$det['cfg'].'_boss_'.$t.''].' - '.($addon['config'][''.$det['cfg'].'_boss_'.$t.''] == 1 ? 'Up' : 'Down').'<br />';
			}
			$tooltip = makeOverlib($bosses, '', '#8000ff', 0, '', ', WIDTH, 275');

			$per = ($down/$det['boss_count'])*100;
			if ($per == 0)
			{
				$per = '1';
			}
			$roster->tpl->assign_block_vars('raidbox',array(
					'TITLE'		=> $name,
					'IMG' 		=> $addon['image_url'].$det['image'],
					'COUNT'		=> $det['boss_count'],
					'TOOLTIP'	=> $tooltip,
					'DOWN'		=> $down,
					'PER'		=> $per,
					'XPER'		=> 100-$per,
				)
			);

		}
		
		$roster->tpl->set_filenames(array(
			'rbpannle' => $addon['basename'] . '/raidbox.html',
		));

		$this->output = $roster->tpl->fetch('rbpannle');
	}
	
	
	
	
	
	
	
}
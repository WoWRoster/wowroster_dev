<?php

// loads the css for the addon
roster_add_css($addon['dir'] . 'style.css','module');

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
			),
			// begin dranor
			
			'Blackrock Foundry' => array(
				'boss_count'	=> '10',
				'image' 		=> 'brf',
				'cfg'	 		=> 'brf',
			),
			'Highmaul' => array(
				'boss_count'	=> '7',
				'image' 		=> 'hml',
				'cfg'	 		=> 'hml',
			),
		);
		
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
				$bosses .= '<span style="color:#'.$color.';font-size:12px;font-weight:bold;"><div style="width:250px;"><span style="float:right;">'.$e.'</span>'.$roster->locale->act[''.$det['cfg'].'_boss_'.$t.''].'</div></span>';
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

	$roster->tpl->display('rbpannle');
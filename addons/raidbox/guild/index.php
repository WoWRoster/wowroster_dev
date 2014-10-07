<?php

// loads the css for the addon
roster_add_css($addon['dir'] . 'style.css','module');

	
	$roster->tpl->assign_vars(array(
			'SCOPE' => $roster['scope'],
			)
		);
		
	for ($i=0;$i=<10;$i++)
	{
		
		$roster->tpl->assign_block_vars('x',array(
				'STR' => $i.'<br>';,
				)
			);

	}
	
	$roster->tpl->set_filenames(array(
		'addonSDK' => $addon['basename'] . '/sdk.html',
	));

	$roster->tpl->display('addonSDK');
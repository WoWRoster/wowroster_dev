<?php

	include_once ($addon['inc_dir'] . 'functions.php');

	$feeds = new feeds();

	$member_id = $roster->data['member_id'];
	$member_name = $roster->data['name'];
	$member_realm = $roster->data['server'];
	$member_str = $member_name . '@' . $roster->data['region'] . '-' . $member_realm;
	
	$pageanat = ($addon['config']['page_size'] > '0' ? true : false );
	
	$get_s = ( isset($_GET['s']) ? $_GET['s'] : '' );
	$get_st = ( isset($_GET['st']) ? $_GET['st'] : 0 );
		
	$queryx = "SELECT * FROM `".$roster->db->table('char_feed',$addon['basename'])."` WHERE `member_id`='" . $member_id . "' ORDER BY `timestamp` DESC";

	if( $pageanat )
	{
		$queryx .= ' LIMIT ' . $get_st . ',' . $addon['config']['page_size'];
	}

	$resultx = $roster->db->query( $queryx );
	
	if( $pageanat )
	{
		$q1 = "SELECT * FROM `".$roster->db->table('char_feed',$addon['basename'])."` WHERE `member_id`='" . $member_id . "'";
		$r1 = $roster->db->query( $q1 );
		$num_rows = $roster->db->num_rows( $r1 );
		
		$num_pages = ceil($num_rows/$addon['config']['page_size']);
	}
	// --[ Page list ]--
	if( $pageanat && $num_pages > 1)
	{
		$params = '';
		paginate2($params . '&amp;st=', $num_rows, $addon['config']['page_size'], $get_st,true,null);
	}
		
	$rowg = $roster->db->fetch($resultx);

	roster_add_css($addon['dir'] . 'style.css','module');
	
	$roster->tpl->assign_vars(array(
			'MBR_STR' => $member_str,
			'B_PAGINATION' => $pageanat,
			)
		);
		
	while($info = $roster->db->fetch($resultx))
	{
		
		$roster->tpl->assign_block_vars('feed',array(
				'STR' => $feeds->$info['type']($info),
				)
			);

	}
	
	$roster->tpl->set_filenames(array(
		'feeds' => $addon['basename'] . '/feeds.html',
	));

	$roster->tpl->display('feeds');


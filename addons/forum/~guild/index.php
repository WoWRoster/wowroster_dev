<?php

include_once (ROSTER_LIB . 'cache.php');
		$cache = new RosterCache();
		$cache->cleanCache();
		
include( $addon['dir'] . 'inc/function.lib.php' );
$functions = new forum;

	//$view->extend('forum/forum_template', 'content', array('title' => $view->lang->get('forum')->get('forums'))); 
	//
	$forums = $functions->getForums();
/*
	echo '<pre>';
	print_r($forums);
	echo '</pre>';
	*/
	foreach($forums as $parent_id => $forum)
	{
		if( $roster->auth->getAuthorized( $forum['access'] ) )
		{
			$roster->tpl->assign_block_vars('forums', array(
					'FORUM_ID' 	=> $forum['forumid'],
					'LOCKED' 	=> $forum['locked'],
					//'FORUM_URL'	=> makelink('guild-'.$addon['basename'].'-forum&amp;id=' . $forum['forumid']),
					'TITLE'		=> $forum['title']
					//'P_URL'		=> makelink('guild-'.$addon['basename'].'-topic&amp;tid=' . $forum['t_id'])
				));
		}
		foreach($forum['forums'] as $forum_id => $data)
		{
			if( $roster->auth->getAuthorized( $data['access'] ) )
			{
				$is_read = false;//$functions->get_topic_tracking($forum['forum_id'], $forum['topicid'], $forum['r_date']);
				$roster->tpl->assign_block_vars('forums.subforum', array(
						'FORUM_ID' 		=> $data['forumid'],
						'LOCKED' 		=> $data['locked'],
						'FORUM_URL'		=> makelink('guild-'.$addon['basename'].'-forum&amp;id=' . $data['forumid']),
						'TITLE'			=> $data['title'],
						'POSTS'			=> $data['posts'],
						'TOPICS'		=> $data['topics'],
						'L_POSTER'		=> $data['t_poster'],
						'L_POST_TIME'	=> $data['t_time'],
						'READ'		=> $is_read,
						'LOCKED'	=> ($forum['locked'] == 1 ? true : false),
						'IMAGEL'    => ($forum['locked'] == 1 ? '_locked' : ''),
						'DESC'			=> $data['desc']
					));
			}
		}
	}		
	
	$roster->tpl->set_filenames(array(
		'forum_main' => $addon['basename'] . '/index.html',
		));

	$roster->tpl->display('forum_main');

?>
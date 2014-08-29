<?php
require_once ($addon['dir'] . 'inc/constants.php');
require_once ($addon['dir'] . 'inc/rsync_base.class.php');
class rsync extends rsyncBase {

	var $title;
	var $time_started;
	var $debugmessages = array();
	var $errormessages = array();
	var $log;
	var $jobid;
	var $memberName = '';
	var $memberId = 0;
	var $guildId = 0;
	var $server = '';
	var $guild_name = '';
	var $region = '';
	var $data = array();
	var $factionID;
	var $factionEN = array(0 => 'Alliance',
	1 => 'Horde');
	var $id = 0;
	var $isMemberList = 0;
	var $isAuth = 0;
	var $link;
	var $dataNotAccepted = 0;
	var $util_type = '';
	var $api_data = array();
	var $done;
	var $total;
	var $is_listupdate;
	var $active_member = array();
	var $status = array(	'guildInfo' => 0,
							'characterInfo' => 0,
							'skillInfo' => 0,
							'reputationInfo' => 0,
							'equipmentInfo' => 0,
							'talentInfo' => 0,
						);
		
	
	//var $starttime = format_microtime();

	function rsync()
	{
		$this->time_started = gmdate('Y-m-d H:i:s');
	}
	
	function _showStartPage( $type )
	{
		global $roster, $addon;
		
		$message = '<br />';
		
		$message .= sprintf( $roster->locale->act['start_message'], $roster->locale->act['start_message_the_'.$type.''], $roster->locale->act['start_message_this_'.$type.'']);

		
		$message .= '<img src="' . $roster->config['img_url'] . 'blue-question-mark.gif" alt="?" />
		<br /><br />
		<form action="' . makelink() . '" method="post" id="allow">
		<input type="hidden" id="start" name="action" value="'.$type.'" />
		<input type="hidden" name="job_id" value="" />
		<button type="submit" class="input" onclick="setvalue(\'job_id\',\'0\');setvalue(\'start\',\'start\');">' . $roster->locale->act['start'] . '</button>
		</form>
		<br />';
		
		$out = messagebox( $message, $this->title,'sred', '500px');
		$this->_debug( 1, $out, 'Printed start page', $out ? 'OK' : 'Failed');
		print $out;
	}
	
	/**
	* Get guild members that match prerequesists from db for update
	*
	* @param string $starttimeutc
	* @return int $jobid
	*/
	function _insertJobID( $starttimeutc = '' )
	{
		global $roster, $addon;
		
		$query = "INSERT INTO ". $roster->db->table('jobs',$addon['basename']). " ".
		"SET starttimeutc=".'"'. $starttimeutc. '"'.";";
		
		$result = $roster->db->query($query);
		$ret = false;
		if ( $result )
		{
			$query = "SELECT LAST_INSERT_ID();";
			$jobid = $roster->db->query_first($query);
			if ( $jobid )
			{
				$ret = $jobid;
			}
		}
		$this->_debug( $ret ? 2 : 0, $ret, 'Fetched job id from DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	/**
		* Get job starttime from db
		*
		* @param int $jobid
		* @return string $starttime
	*/
	function _getJobStartTime( $jobid = 0 )
	{
		global $roster, $addon;
		
		$query =	"SELECT starttimeutc ".
		"FROM `". $roster->db->table('jobqueue',$addon['basename']). "` ".
		"WHERE job_id=". $jobid;
		$ret = $roster->db->query_first($query);
		$this->_debug( $ret ? 2 : 0, $ret, 'Fetched job start time from DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	/**
		* create footer
		*
		* @param int $jobid
	*/
	function _showFooter()
	{
		global $roster, $addon;
		
		//aprint($this->debugmessages[0]['ret']);
		
		$roster->tpl->assign_vars( array (
		'IMAGE_PATH' => $addon['image_path'],
		'RSYNC_VERSION' => $addon['version']. ' by Ulminia',
		'RSYNC_CREDITS' => $roster->locale->act['rsync_credits'],
		'ERROR' => count( $this->errormessages ) > 0,
		'DEBUG' => $addon['config']['rsync_debuglevel'],
		'DEBUG_DATA' => $addon['config']['rsync_debugdata'],
		'D_START_BORDER' => border( 'sblue', 'start', 'rsync Debugging '. ( $addon['config']['rsync_debugdata'] ? 'Infos & Data' : 'Infos'), '100%' ),
		'E_START_BORDER' => border( 'sred', 'start', 'rsync Error '. ( $addon['config']['rsync_debugdata'] ? 'Infos & Data' : 'Infos'), '100%' ),
		'RUNTIME' => round((format_microtime() - RSYNC_STARTTIME), 4),
		'S_SQL_WIN' => $addon['config']['rsync_sqldebug'],
		));
		
		$this->_debug( 3, null, 'Printed footer', 'OK');
		
		if ($roster->switch_row_class(false) != 1 )
		{
			$roster->switch_row_class();
		}
		
		foreach ( $this->errormessages as $message )
		{
			$roster->tpl->assign_block_vars('e_row', array(
			'FILE' => $message['file'],
			'LINE' => $message['line'],
			'TIME' => $message['time'],
			'CLASS' => $message['class'],
			'FUNC' => $message['function'],
			'INFO' => $message['info'],
			'STATUS' => $message['status'],
			'ARGS' => aprint($message['args'], '', 1),
			'RET'  => aprint($message['ret'], '' , 1),
			'ROW_CLASS1' => $addon['config']['rsync_debugdata'] ? 1 : $roster->switch_row_class(),
			'ROW_CLASS2' => 1,
			'ROW_CLASS3' => 1,
			));
		}
		
		$roster->tpl->assign_var( 'E_STOP_BORDER', border( 'sred', 'end', '', '' ) );
		
		if ($roster->switch_row_class(false) != 1 )
		{
			$roster->switch_row_class();
		}
		
		foreach ( $this->debugmessages as $message )
		{
			$roster->tpl->assign_block_vars('d_row', array(
			'FILE' => $message['file'],
			'LINE' => $message['line'],
			'TIME' => $message['time'],
			'CLASS' => $message['class'],
			'FUNC' => $message['function'],
			'INFO' => $message['info'],
			'STATUS' => $message['status'],
			'ARGS' => aprint($message['args'], '', 1),
			'RET'  => aprint($message['ret'], '' , 1),
			'ROW_CLASS1' => $addon['config']['rsync_debugdata'] ? 1 : $roster->switch_row_class(),
			'ROW_CLASS2' => 1,
			'ROW_CLASS3' => 1,
			));
		}
		
		$roster->tpl->assign_var( 'D_STOP_BORDER', border( 'sblue', 'end', '', '' ) );
		
		if( $addon['config']['rsync_sqldebug'] )
		{
			if( count($roster->db->queries) > 0 )
			{
				foreach( $roster->db->queries as $file => $queries )
				{
					if (!preg_match('#[\\\/]{1}addons[\\\/]{1}rsync[\\\/]{1}inc[\\\/]{1}[a-z_.]+.php$#', $file))
					{
						continue;
					}
					$roster->tpl->assign_block_vars('sql_debug', array(
					'FILE' => substr($file, strlen(ROSTER_BASE)),
					)
					);
					foreach( $queries as $query )
					{
						$roster->tpl->assign_block_vars('sql_debug.row', array(
						'ROW_CLASS' => $roster->switch_row_class(),
						'LINE'	  => $query['line'],
						'TIME'	  => $query['time'],
						'QUERY'	 => nl2br(htmlentities($query['query'])),
						)
						);
					}
				}
				
				$roster->tpl->assign_vars(array(
				'SQL_DEBUG_B_S' => border('sgreen','start',$roster->locale->act['sql_queries']),
				'SQL_DEBUG_B_E' => border('sgreen','end'),
				)
				);
			}
		}
		
		$roster->tpl->set_filenames( array (
		'footer' => $addon['basename'] . '/footer.html',
		));
		$roster->tpl->display('footer');
	}
		
	/**
 	* fetches guild info
 	*
 	*/
	function _getGuildInfo()
	{
		global $roster, $addon;

		$guild_name = (isset($roster->data['guild_name']) ? $roster->data['guild_name'] : $this->guild_name);
		$content = $this->datas;

		$this->data['Ranks'] = $this->_getGuildRanks( $this->guildId );
		$this->data['timestamp']['init']['datakey'] = $roster->data['region'];
		$this->data['timestamp']['init']['TimeStamp'] = time();
		$this->data['timestamp']['init']['Date'] = date('Y-m-d H:i:s');
		$this->data['timestamp']['init']['DateUTC'] = gmdate('Y-m-d H:i:s');
		$this->data['Locale'] = $roster->data['region'];
		$this->data['GPprovider'] = "rsyncGuild";
		$this->data['FactionEn'] = $this->factionEN[$content['side']];
		$this->data['Faction'] = $this->factionEN[$content['side']];
		$this->data["DBversion"] = '3.1';
		$this->data["GPversion"] = $roster->config['minGPver'];
		$this->data['name'] = $guild_name;
		$this->data['Server'] = $this->datas['realm'];
		$this->data['GuildName'] = $guild_name;
		$this->data['GuildLevel'] = $content['level'];
		$this->data['NumMembers'] = count($content['members']);
		$this->data['GuildLevel'] = $content['level'];
		$this->data['Info'] = ''; //$roster->data['guild_info_text'];

		$min = 60;
		$hour = 60 * $min;
		$day = 24 * $hour;
		$month = 30 * $day;
		$year = 365 * $day;
		foreach ( $content['members'] as $id => $member )
		{
			$cname = $member['character']['name'].'-'.$member['character']['realm'];;
			$player['AchRank'] = '';
			$player['Zone'] = "";
			$player['Class'] = $roster->locale->act['id_to_class'][$member['character']['class']];
			$player['ClassId'] = $member['character']['class'];
			$player['Name'] = $cname;
			$player['Realm'] = $member['character']['realm'];
			$player['Level'] = $member['character']['level'];
			$player['Mobile'] = false;
			$player['Title'] = $this->data['Ranks'][$member['rank']]['Title'];
			$player['AchPoints'] = $member['character']['achievementPoints'];
			$player['RankEn'] = $this->data['Ranks'][$member['rank']]['Title'];
			$player['LastOnline'] = "0:0:0:0";
			$player['Rank'] = $member['rank'];
			$player['Online'] ='0';
			$this->status['guildInfo'] += 1;
			$this->data['Members'][$cname] = $player;
		}

		$this->_debug( 1, $this->data, 'Parsed guild info',  'OK' );
		
		
	}
	
	/**
 	* db function to get member name by its id
 	*
 	* @param int $memberId
 	* @return string $memberName
 	*/
	function _getGuildRanks( $guild_id )
	{
		global $roster, $addon;

		$array = array();
		$ranks = array();
		if ( $addon['config']['rsync_rank_set_order'] >= 1 ) {
			$query =	"SELECT rank, title FROM ". $roster->db->table('guild_rank'). " WHERE guild_id=". $guild_id. " ORDER BY guild_rank;";
			$result = $roster->db->query($query);
			if( $roster->db->num_rows($result) > 0 ) {

				$tmp = $roster->db->fetch_all();
				foreach ( $tmp as $rank ) {
					$ranks[$rank['rank']] = $rank['title'];
				}
			}
		}

		if ( $addon['config']['rsync_rank_set_order'] == 3 ) 
			{
			for ( $i = 0; $i <= 9; $i++ ) 
  				{
				$array[$i]['Title'] = isset($ranks[$i]) && $ranks[$i] != '' ?
				$ranks[$i] :
				( $addon['config']['rsync_rank_'. $i] != '' ?
				$addon['config']['rsync_rank_'. $i] :
				( $i == 0 ?
				$roster->locale->act['guildleader'] :
				$roster->locale->act['rank']. $i ) );
			}
		}
		elseif ( $addon['config']['rsync_rank_set_order'] == 2 ) 
			{
			for ( $i = 0; $i <= 9; $i++ ) 
  				{
				$array[$i]['Title'] = $addon['config']['rsync_rank_'. $i] != '' ?
				$addon['config']['rsync_rank_'. $i] :
				( isset($ranks[$i]) && $ranks[$i] != '' ?
				$ranks[$i] :
				( $i == 0 ?
				$roster->locale->act['guildleader'] :
				$roster->locale->act['rank']. $i ) );
			}
		}
		elseif ( $addon['config']['rsync_rank_set_order'] == 1 ) 
			{
  				for ( $i = 0; $i <= 9; $i++ ) 
  				{
				$array[$i]['Title'] = isset($ranks[$i]) && $ranks[$i] != '' ?
						$ranks[$i] :
						( $i == 0 ?
						$roster->locale->act['guildleader'] :
						$roster->locale->act['rank']. $i );
			}
		}
		elseif ( $addon['config']['rsync_rank_set_order'] == 0 ) 
			{
			for ( $i = 0; $i <= 9; $i++ ) {
				$array[$i]['Title'] = $i == 0 ?
				$roster->locale->act['guildleader'] :
				$roster->locale->act['rank']. $i;
			}
		}
		$this->_debug( 3, $array, 'Fetched guild ranks from DB', 'OK' );
		return $array;
	}

	/**
 	* db function to get members guild_rank and guild_title by its id
 	*
 	* @param int $memberId
 	* @return string $memberName
 	*/
	function _getMemberRank( $member_id ) 
	{
		global $roster, $addon;

		$query =	"SELECT ".
						"guild_rank, guild_title ".
						"FROM ". $roster->db->table('members'). " AS members ".
						"WHERE ".
						"members.member_id=". $member_id. ";";
		$result = $roster->db->query($query);
		if( $roster->db->num_rows($result) > 0 ) {

			$ranks = $roster->db->fetch_all();
			$this->_debug( 3, $ranks[0], 'Fetched member rank from DB', 'OK' );
			return $ranks[0];
		} else {
			$array = array();
			$this->_debug( 3, $array, 'Fetched member rank from DB', 'Failed' );
			return $array;
		}
	}	

	
	/**
		* fetch insert jobid, fill jobqueue
		*
	*/
	function _prepareUpdateMemberlist( $id = 0, $name = false , $server = false , $region = false )
	{
		global $roster, $addon;
		
		if ( ! $id )
		{
			$id = $roster->data['guild_id'];
		}
		if ( ! $name )
		{
			$name = $roster->data['guild_name'];
		}
		if ( ! $server )
		{
			$server = $roster->data['server'];
		}
		if ( ! $region )
		{
			$region = $roster->data['region'];
		}
		/*
		$this->time_started = gmdate('Y-m-d H:i:s');
		$this->active_member['starttimeutc'] = gmdate('Y-m-d H:i:s');
		$this->_checkGuildExist( $name, $server, $region );
		$this->_getGuildInfo();
		$this->log = $this->synchGuildbob( $server, $memberId = 0, $name, $region, null);
		*/
		$this->members = $this->_getGuildMembersToUpdate();
			
		if ( array_keys( $this->members ) )
		{
			$this->jobid = $this->_insertJobID($this->time_started);
			$this->_insertMembersToJobqueue($this->jobid, $this->members);
			$this->_debug( 1, true, 'Prepared character update job', 'OK');
		}
			
		$this->is_listupdate = 1;
		return true;
	}
	
	/**
		* statusbox output
		*
		* @param int $jobid
	*/
	function _nothingToDo()
	{
		global $roster;
		
		$html = '<span class="title_text">&nbsp;&nbsp;'. $roster->locale->act['nothing_to_do']. '&nbsp;&nbsp;</span>';
		
		$out = messagebox( $html , $title=$this->title , $style='syellow' , $width='' );
		$this->_debug( 3, $out, 'Printed error message', 'OK');
		print $out;
	}
	
	function synchGuildbob( $server, $memberId = 0, $memberName = false, $region = false, $data)
	{
		global $addon, $roster, $update;

		$this->server = $server;
		$this->guildId = $memberId;
		$roster->data['region'] = $region;
		$this->memberName = $memberName;
		$this->active_member['starttimeutc'] = gmdate('Y-m-d H:i:s');

		include_once(ROSTER_LIB . 'update.lib.php');
		$update = new update;
		$update->fetchAddonData();
		$update->uploadData['wowrcp']['cpProfile'][$server]['Guild'][ $memberName] = $this->data;
		$x = $update->processGuildRoster();
		$x .= ''.$this->message.'<br>';
		$this->_debug( 1, true, 'Synced armory data '. $this->memberName. ' with roster',  'OK' );
		return $x;//true;

	}
	
	function _updateStatusMemberlist( $jobid = 0 )
	{
		global $roster;
		
		//$this->_init();	
		
		//$this->active_member = $this->_isPostSyncStatus( $this->jobid );
		$active_member = $this->active_member;

		if ( ! isset ($active_member['guild_name']) )
		{
			//$this->active_member = $this->_getNextGuildToUpdate( $this->jobid );
			$active_member = $this->active_member;
			//echo '<pre>';print_r($this->active_member);echo '</pre><br>';
			$cleanup = 0;
			if ( isset ($active_member['guild_name']) )
			{
				$this->active_member['starttimeutc'] = gmdate('Y-m-d H:i:s');
				if ( $this->_updateGuildJobStatus( $this->jobid, $this->active_member ) )
				{
					$ret = true;
				}
			}
			else
			{
				$cleanup = 1;
				$ret = false;
				
			}
			$this->active_member['guild_info'] = $this->status['guildInfo'];
			$this->active_member['stoptimeutc'] = gmdate('Y-m-d H:i:s');

			$this->_debug( 1, $ret, 'Updated memberlist job status', $ret ? 'OK': 'FINISHED');
			return $ret;
		}
		else
		{
			if ( ! $this->_synchGuildByID( $active_member['server'], $active_member['guild_id'], $active_member['guild_name'], $active_member['region']) )
			{
				$this->dataNotAccepted = 1;
			}
			//echo '<pre>';print_r($this->active_member);echo '</pre><br>';
			//$this->_updateGuild( $active_member['guild_name'], $active_member['server'], $active_member['region'] );
			
			$this->active_member['guild_info'] = $this->status['guildInfo'];
			$this->active_member['stoptimeutc'] = gmdate('Y-m-d H:i:s');
			$this->active_member['log'] = $this->message;
			if ( $this->dataNotAccepted != 1 )
			{
				$this->done = $this->total = '1';
				$this->_debug( 1, true, 'Updated memberlist job status', 'OK');
				//$this->_updateGuild( $active_member['guild_name'], $active_member['server'], $active_member['region'] );
				return true;
			}
			else
			{
				$this->_debug( 0, false, 'Updated memberlist job status', 'Failed');
				return false;
			}
		}
		
	}
	
	/**
		* statusbox Memberlist output
		*
		* @param int $jobid
	*/
	function _showStatusMemberlist( $jobid = 0 )
	{
		global $roster;
		
		$this->_showStatus( $jobid, 0 );
		$this->_debug( 1, null, 'Printed memberlist status', 'OK');
	}
	
	/**
		* statusbox output with templates
		*
		* @param int $jobid
	*/
	function _showStatus( $jobid = 0, $memberlist = false )
	{
		global $roster, $addon;
		
		$jscript = "<script type=\"text/javascript\" src=\"". $addon['url_path']. "js/rsync.js\"></script>\n";
		
		$jscript .= '
		<script type="text/javascript">
		var rsync_debuglevel = '. $addon['config']['rsync_debuglevel']. ';
		var rsync_debugdata = '. $addon['config']['rsync_debugdata']. ';
		</script>
		';
		//function rsync_debuglevel() { return '. $addon['config']['rsync_debuglevel']. '; }
		//function rsync_debugdata() { return '. $addon['config']['rsync_debugdata']. '; }
		
		$this->header .= $jscript;
		
		$members = $this->members;
		
		$status = isset($_POST['StatusHidden']) ? $_POST['StatusHidden'] :
		( $addon['config']['rsync_status_hide'] ? 'ON' : 'OFF' );
		$display = ( $status == 'ON' ) ? 'none' : '';
		$icon = ROSTER_PATH. ( $status == 'ON' ? $roster->config['theme_path'] . '/images/plus.gif' : $roster->config['theme_path'] . '/images/minus.gif' );
		$style = 'syellow';
		
		$roster->tpl->assign_vars(array(
			'IMAGE_PATH' => $addon['image_path'],
			
			'USE_EFFECTS' => null,
			
			'LINK' => ( $this->link ? $this->link : makelink() ),
			'DEBUG' => $addon['config']['rsync_xdebug_php'] ? "<input type=\"hidden\" name=\"XDEBUG_SESSION_START\" value=\"". $addon['config']['rsync_xdebug_idekey']. "\" />" : "",
			'STATUSHIDDEN' => $status,
			'JOB_ID' => $this->jobid,
			'MEMBERSLIST' => $this->is_listupdate,
			'DISPLAY' => $display,
			'ICON' => $icon,
			'START_BORDER' => border( $style, 'start', '', '848px' ),
			'STYLE' => $style,
			'TITLE' => $this->title,
			'PROGRESSBAR' => $this->_getProgressBar($this->done, $this->total),
		));
		
		if (isset($this->active_member['name']) || isset($this->active_member['guild_name']))
		{
			$roster->tpl->assign_var( 'NEXT', $roster->locale->act['next_to_update']. ( $memberlist ? $this->active_member['guild_name'] : $this->active_member['name'] ) );
		} 
		else
		{
			$roster->tpl->assign_var( 'NEXT', false );
		}
		
		if ( !$memberlist ) 
		{
			$roster->tpl->assign_block_vars('head_col', array(
					'HEAD_TITLE' => $roster->locale->act['name'], 
					'HEAD_WIDTH' => '120px'
			));
		}
		
		$roster->tpl->assign_block_vars('head_col', array(
				'HEAD_TITLE' => $roster->locale->act['guild']." ".$roster->locale->act['name'], 
				'HEAD_WIDTH' => '140px'
		));
		
		if ( $memberlist ) 
		{
			$roster->tpl->assign_block_vars('head_col', array(
				'HEAD_TITLE' => $roster->locale->act['guild_short']."Info", 
				'HEAD_WIDTH' => '64px'
			));
		}
		
		if ( ! $memberlist ) 
		{
			$roster->tpl->assign_block_vars('head_col', array(
				'HEAD_TITLE' => $roster->locale->act['character_short'], 
				'HEAD_WIDTH' => '55px'
			));
			
			$roster->tpl->assign_block_vars('head_col', array(
				'HEAD_TITLE' => $roster->locale->act['skill_short'], 
				'HEAD_WIDTH' => '55px'
			));
			
			$roster->tpl->assign_block_vars('head_col', array(
				'HEAD_TITLE' => $roster->locale->act['reputation_short'],
				'HEAD_WIDTH' => '55px'
			));
			
			$roster->tpl->assign_block_vars('head_col', array(
				'HEAD_TITLE' => $roster->locale->act['equipment_short'],
				'HEAD_WIDTH' => '55px'
			));
			
			$roster->tpl->assign_block_vars('head_col', array(
				'HEAD_TITLE' => $roster->locale->act['talents_short'],
				'HEAD_WIDTH' => '55px'
			));
		}
		
		$roster->tpl->assign_block_vars('head_col', array(
			'HEAD_TITLE' => $roster->locale->act['started'],
			'HEAD_WIDTH' => '110px'
		));
		
		$roster->tpl->assign_block_vars('head_col', array(
			'HEAD_TITLE' => $roster->locale->act['finished'],
			'HEAD_WIDTH' => '110px'
		));
		
		$roster->tpl->assign_block_vars('head_col', array(
			'HEAD_TITLE' => "Log",
			'HEAD_WIDTH' => '30px' 
		));
		
		$l = 1;
		
		$roster->tpl->assign_var('CHARLIST', !$memberlist);
		$roster->tpl->assign_var('MEMBERLIST', $memberlist);
		

		if ($this->is_listupdate == 1)
		{
			//echo '<pre>';print_r($this->active_member);echo '</pre><br>';
			$roster->tpl->assign_block_vars('body_rowx', array(
				'LOG' => $this->log
			));
			$roster->tpl->assign_block_vars('body_row', array(
				'LINE_VALUE' => $roster->data['guild_name'], 
				'WIDTH' => '120px'
			));
			
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => isset( $this->active_member['guild_info'] ) ? $this->active_member['guild_info'] : "<img src=\"". ROSTER_PATH. "img/blue-question-mark.gif\" alt=\"?\"/>", 
				'WIDTH' => '90px'
			));
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => isset($this->active_member['starttimeutc'] ) ? $this->active_member['starttimeutc'] : "<img src=\"". ROSTER_PATH. "img/blue-question-mark.gif\" alt=\"?\"/>", 
				'WIDTH' => '120px'
			));
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => isset( $this->active_member['stoptimeutc'] ) ? $this->active_member['stoptimeutc'] : "<img src=\"". ROSTER_PATH. "img/blue-question-mark.gif\" alt=\"?\"/>", 
				'WIDTH' => '120px'
			));

		}
		foreach ( $members as $member ) 
		{
			$array = array();
			$array['COLOR'] = $roster->switch_row_class();
			$array['ASID'] = $memberlist ? $member['guild_id'] : $member['member_id'];
			$array['NAME'] = $member['name'];
			$array['GUILD'] = $member['guild_name'];
			$array['SERVER'] = $member['region']. "-". $member['server'];

			
			foreach ( array( 'guild_info', 'character_info', 'skill_info', 'reputation_info', 'equipment_info', 'talent_info' ) as $key ) 
			{
				if ( $memberlist && $key !== 'guild_info' ) 
				{
					continue;
				}
				if ( isset( $member[$key] ) && $member[$key] == 1 ) {
					$array[strtoupper($key)] = "<img style=\"float:center;\" src=\"". ROSTER_PATH. "img/pvp-win.gif\" alt=\"\"/>";
					//$array['FINISHED'] = '3';
					} elseif ( isset( $member[$key] ) && $member[$key] >= 1 ) {
					$array[strtoupper($key)] = $member[$key];
					} elseif ( isset( $member[$key] ) ) {
					$array[strtoupper($key)] = "<img style=\"float:center;\" src=\"". ROSTER_PATH. "img/pvp-loss.gif\" alt=\"\" />";
					
					} else {
					$array[strtoupper($key)] = "<img style=\"float:center;\" src=\"". ROSTER_PATH. "img/blue-question-mark.gif\" alt=\"?\" />";
				}
			}
			
			$array['STARTTIMEUTC'] = isset( $member['starttimeutc'] ) ? $this->_getLocalisedTime($member['starttimeutc']) : "<img src=\"". ROSTER_PATH. "img/blue-question-mark.gif\" alt=\"?\"/>";
			$array['STOPTIMEUTC'] = isset( $member['stoptimeutc'] ) ? $this->_getLocalisedTime($member['stoptimeutc']) : "<img src=\"". ROSTER_PATH. "img/blue-question-mark.gif\" alt=\"?\"/>";
			$array['FINISHED'] = isset( $member['stoptimeutc'] ) ? "3" : "4";
			
			if ( !$memberlist && $member['log'] ) {
				$array['LOG'] = "<img src=\"". $roster->config['theme_path'] . "/images/note.gif\"". makeOverlib( $member['log'] , $roster->locale->act['update_log'] , '' ,0 , '' , ',WRAP' ). " alt=\"\" />";
				} elseif( $member['log'] ) {
				$array['LOG'] = "<img src=\"". $roster->config['theme_path'] . "/images/note.gif\"". makeOverlib( "<div style=\"height:300px;width:500px;overflow:auto;\">". $member['log']. " -+-</div>", $roster->locale->act['update_log'] , '' ,0 , '' , ',STICKY, WRAP, CLOSECLICK' ). " alt=\"\" />";
				} else {
				$array['LOG'] = "<img src=\"". $roster->config['theme_path'] . "/images/no_note.gif\" alt=\"\" />";
			}
			$roster->tpl->assign_block_vars('body_row', array(
				'LINE_VALUE' => $array['NAME'], 
				'WIDTH' => '120px'
		));
		$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => $array['GUILD'], 
				'WIDTH' => '140px'
		));
		if ( $memberlist ) 
		{
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => $array['GUILD_INFO'], 
				'WIDTH' => '70px'
			));
		}

		if ($this->is_listupdate == 1)
		{
			$roster->tpl->assign_block_vars('body_rowx', array(
				'LOG' => $this->log
			));
		}
		if ( ! $memberlist ) 
		{
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => $array['CHARACTER_INFO'], 
				'WIDTH' => '55px'
			));
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => $array['SKILL_INFO'], 
				'WIDTH' => '55px'
			));
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => $array['REPUTATION_INFO'],
				'WIDTH' => '55px'
			));
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => $array['EQUIPMENT_INFO'],
				'WIDTH' => '55px'
			));
			$roster->tpl->assign_block_vars('body_row.line', array(
				'LINE_VALUE' => $array['TALENT_INFO'],
				'WIDTH' => '55px'
			));
		}
		$roster->tpl->assign_block_vars('body_row.line', array(
			'LINE_VALUE' => $array['STARTTIMEUTC'],
			'WIDTH' => '110px'
		));
		$roster->tpl->assign_block_vars('body_row.line', array(
			'LINE_VALUE' => $array['STOPTIMEUTC'],
			'WIDTH' => '110px'
		));
		$roster->tpl->assign_block_vars('body_row.line', array(
			'LINE_VALUE' =>$array['LOG'],
			'WIDTH' => '30px' 
		));
			
			
			
			//$roster->tpl->assign_block_vars('body_row', $array );
			//$l++;
		}
		
		$roster->tpl->assign_var('STOP_BORDER', border( 'syellow', 'end' ));
		
		if (!$this->is_cron)
		{
			$roster->tpl->set_filenames(array(
			'status_head' => $addon['basename'] . '/status_head.html',
			'status_body' => $addon['basename'] . '/body.html',
			));
			
			$roster->tpl->display('status_head');
			$roster->tpl->display('status_body');
		}
		$this->_debug( 1, null, 'Printed status window', 'OK');
	}
	
	/**
		* Creates a progress bar
		*
	*/
	function _getProgressBar($step, $total)
	{
		global $roster;
		
		$perc = 0;
		if ( $total == 0 ) {
			$perc = 100;
			} else {
			$perc = round ($step / $total * 100);
		}
		$per_left = 100 - $perc;
		$pb = "<table class=\"main_roster_menu\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" align=\"center\" width=\"200\" id=\"Table1\">";
		$pb .= "<tr>";
		$pb .= "	<td id=\"progress_text\" class=\"header\" colspan=\"2\" align=\"center\">";
		$pb .= "		$perc% ". $roster->locale->act['complete']. " ($step / $total)";
		$pb .= "	</td>";
		$pb .= "</tr>";
		$pb .= "<tr id=\"progress_bar\">";
		if ( $perc ) {
			$pb .= "	<td bgcolor=\"#660000\" height=\"12px\" width=\"$perc%\">" ;
			$pb .= "	</td>";
		}
		if ( $per_left ) {
			$pb .= "	<td bgcolor=\"#FFF7CE\" height=\"12px\" width=\"$per_left%\">";
			$pb .= "		</td>";
		}
		$pb .= "</tr>";
		$pb .= "</table>";
		$this->_debug( 3, $pb, 'Fetched progressbar', $pb ? 'OK' : 'Failed');
		return $pb;
	}
	
	/**
		* Get guild members that match prerequesists from db for update
		*
		* @return array ()
	*/
	function _getGuildMembersToUpdate()
	{
		global $roster;
		
		$ret = $this->_getMembersToUpdate("members.guild_id = ". $roster->data['guild_id']. " AND " );
		$this->_debug( 3, $ret, 'Fetched guild members to update from DB', $ret ? 'OK' : 'EMPTY');
		return $ret;
	}
	
	/**
		* Get that match prerequesists from db for update
		*
		* @return array ()
	*/
	function _getMembersToUpdate( $where = false )
	{
		global $roster, $addon;
		
		
		//$where = '';
		//ok add the new where styatements
		$w = array();
		if (isset($addon['config']['rsync_MinLvl']) && !empty($addon['config']['rsync_MinLvl']))
		{
			$w[] = "members.level >= '" . $addon['config']['rsync_MinLvl'] . "'";
		}
		if (isset($addon['config']['rsync_MaxLvl']) && !empty($addon['config']['rsync_MaxLvl']))
		{
			$w[] = "members.level <= '" . $addon['config']['rsync_MaxLvl'] . "'";
		}
		if (isset($addon['config']['rsync_Rank']) && !empty($addon['config']['rsync_Rank']))
		{
			$w[] = "members.guild_rank = '" . $addon['config']['rsync_Rank'] . "'";
		}
		if (isset($addon['config']['rsync_Class']) && !empty($addon['config']['rsync_Class']))
		{
			$w[] = "members.classid = '" . $addon['config']['rsync_Class'] . "'";
		}
		if (count($w) > 1)
		{
			$where .= implode($w,' AND ');
		}
		if (count($w) == 1)
		{
			$where .= implode($w,' ');
		}
		$query =	"SELECT members.member_id, members.name, " .
		"guild.guild_id, guild.guild_name, members.server, guild.region ".
		"FROM `".$roster->db->table('members')."` members ".
		"LEFT JOIN `".$roster->db->table('guild')."` guild " .
		"ON members.guild_id = guild.guild_id " .
		"LEFT JOIN `". $roster->db->table('updates',$addon['basename']). "` updates ".
		"ON members.member_id = updates.member_id ".
		"WHERE ". $where.
		"ORDER BY members.member_id;";	

		$result = $roster->db->query($query);
		if( $roster->db->num_rows($result) > 0 ) {
			$ret = $roster->db->fetch_all();
			} else {
			$ret = array();
		}
		$this->_debug( 2, $ret, 'Fetched members to update from DB', $ret ? 'OK' : 'EMPTY');
		return $ret;
	}
		
		/**
		* Inserts members to jobqueue
		*
		* @param int $jobid
		* @param array $members
		* @return bool
	*/
	function _insertMembersToJobqueue( $jobid = 0, $members = array() )
	{
		global $roster, $addon;
		
		$ret = false;
		if ( is_array( $members ) )
		{
			
			$query =	"INSERT INTO ". $roster->db->table('jobqueue',$addon['basename']). " ".
			"VALUES ";
			foreach ( $members as $member ) {
				$query .= "(".
				$jobid. ", ".
				( $member['member_id'] ? $member['member_id'] : 0 ). ", ".
				'"'.$roster->db->escape($member['name']). '"'.", ".
				$member['guild_id']. ", ".
				'"'.$roster->db->escape($member['guild_name']). '"'.", ".
				'"'.$roster->db->escape($member['server']). '"'.", ".
				'"'.$roster->db->escape($member['region']). '"'.", ".
				"NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL), ";
			}
			$query = preg_replace('/, $/', ';', $query);
			$result = $roster->db->query($query);
			if ( $result ) {
				$ret = true;
			}
		}
		$this->_debug( $ret ? 2 : 0, $ret, 'Inserted members to jobqueue table', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	/**
		* Fetches job progress
		*
		* @param int $jobid
		* @return array $progress
	*/
	function _getJobProgress ( $jobid = 0 ) {
		$ret = array($this->_getJobDone($jobid), $this->_getJobTotal($jobid));
		$this->_debug( 3, $ret, 'Created job progress array', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	/**
		* Fetches total number of members to sync
		*
		* @param int $jobid
		* @return array $progress
	*/
	function _getJobTotal ( $jobid = 0 ) {
		global $roster, $addon;
		
		$ret = 0;
		$query =	"SELECT ".
		"COUNT(member_id) as total ".
		"FROM `". $roster->db->table('jobqueue',$addon['basename']). "` ".
		"WHERE job_id=". $jobid. ";";
		
		$result = $roster->db->query_first($query);
		if( $result ) {
			$ret = $result;
		}
		$this->_debug( $ret ? 3 : 0, $ret, 'Fetched total members to update from DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	/**
		* Fetches total number of members to sync
		*
		* @param int $jobid
		* @return array $progress
	*/
	function _getJobDone ( $jobid = 0 ) {
		global $roster, $addon;
		
		$ret = 0;
		$query =	"SELECT ".
		"COUNT(member_id) as done ".
		"FROM `". $roster->db->table('jobqueue',$addon['basename']). "` ".
		"WHERE job_id=". $jobid. " ".
		"AND NOT ISNULL(stoptimeutc);";
		
		
		$result = $roster->db->query_first($query);
		if( $result ) {
			$ret = $result;
		}
		$this->_debug( $ret !== false ? 3 : 0, $ret, 'Fetched total members updated from DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	function _updateStatus( $jobid = 0 )
	{
		global $roster;
		
		//$this->_init();
		$this->active_member = $this->_isPostSyncStatus( $this->jobid );
		$active_member = $this->active_member;
		
		if ( ! isset ($active_member['name']) ) {
			$this->active_member = $this->_getNextMemberToUpdate( $this->jobid );
			$active_member = $this->active_member;
			$cleanup = 0;
			if ( isset ($active_member['name']) ) {
				$this->active_member['starttimeutc'] = gmdate('Y-m-d H:i:s');
				if ( $this->_updateMemberJobStatus( $this->jobid, $this->active_member ) ) {
					$ret = true;
				}
				} else {
				$cleanup = 1;
				$ret = false;
			}
			$this->members = $this->_getMembersFromJobqueue( $this->jobid );
			list ( $this->done, $this->total ) = $this->_getJobProgress($this->jobid);
			if ( $cleanup ) {
				$this->_cleanUpJob( $this->jobid );
			}
			$this->_debug( 1, $ret, 'Updated charcter job status', $ret ? 'OK': 'FINISHED');
			return $ret;
			} else {
			if ( ! $this->synchMemberByID( $active_member['server'], $active_member['member_id'], $active_member['name'], $active_member['region'], $active_member['guild_id']) ) {
				$this->dataNotAccepted = 1;
			}
			
			$this->active_member['guild_info'] = $this->status['guildInfo'];
			$this->active_member['character_info'] = $this->status['characterInfo'];;
			$this->active_member['skill_info'] = $this->status['skillInfo'];;
			$this->active_member['reputation_info'] = $this->status['reputationInfo'];;
			$this->active_member['equipment_info'] = $this->status['equipmentInfo'];;
			$this->active_member['talent_info'] = $this->status['talentInfo'];;
			$this->active_member['stoptimeutc'] = gmdate('Y-m-d H:i:s');
			$this->active_member['log'] = $this->message;
			if ( $this->_updateMemberJobStatus( $this->jobid, $this->active_member ) ) {
				$this->members = $this->_getMembersFromJobqueue( $this->jobid );
				list ( $this->done, $this->total ) = $this->_getJobProgress($this->jobid);
				$this->_debug( 1, true, 'Updated charcter job status', 'OK');
				return true;
				} else {
				$this->_debug( 0, false, 'Updated charcter job status', 'Failed');
				return false;
			}
		}
	}
	
	function _prepareUpdate( $id = 0, $name = false , $server = false , $region = false )
	{
		global $roster, $addon;
		
		if ( ! $id )
		{
			$id = isset($roster->data['member_id']) ? $roster->data['member_id'] : 0;
		}
		if ( ! $name )
		{
			$name = isset($roster->data['name']) ? $roster->data['name'] : false;
		}
		if ( ! $server )
		{
			$server = isset($roster->data['server']) ? $roster->data['server'] : false;
		}
		if ( ! $region )
		{
			$region = isset($roster->data['region']) ? $roster->data['region'] : false;
		}
		
		$this->time_started = gmdate('Y-m-d H:i:s');
		
		if ( $roster->scope == 'char' || $roster->scope == 'util' )
		{
			
			$this->members = array(
			array(
			'member_id' => $id,
			'name' => $name,
			'guild_id' => $roster->data['guild_id'] ? $roster->data['guild_id'] : 0,
			'guild_name' => $roster->data['guild_name'] ? $roster->data['guild_name'] : '',
			'server' => $server,
			'region' => $region ) );
		}
		elseif ( $roster->scope == 'guild' )
		{
			$this->members = $this->_getGuildMembersToUpdate();
		}
		elseif ( $roster->scope == 'realm' )
		{
			$this->members = $this->_getRealmMembersToUpdate();
		}
		
		if ( array_keys( $this->members ) )
		{
			$this->jobid = $this->_insertJobID($this->time_started);
			$this->_insertMembersToJobqueue($this->jobid, $this->members);
			$this->_debug( 1, true, 'Prepared character update job', 'OK');
			return true;
		}
		$this->_debug( 1, false, 'Prepared character update job', 'Failed');
		return false;
	}
		
	/**
		* Fetches member which status was updated last
		*
		* @param int $jobid
		* @return array $member
	*/
	function _isPostSyncStatus ( $jobid = 0 ) {
		global $roster, $addon;
		
		$ret = false;
		$query =	"SELECT * ".
		"FROM `". $roster->db->table('jobqueue',$addon['basename']). "` ".
		"WHERE job_id=". $jobid. " ".
		"AND NOT ISNULL(starttimeutc) AND ISNULL(stoptimeutc);";
		
		$result = $roster->db->query($query);
		if( $roster->db->num_rows($result) > 0 ) {
			$member = $roster->db->fetch_all();
			$ret = $member[0];
		}
		$this->_debug( 2, $ret, 'Check if post sync status from DB', $ret ? 'YES' : 'NO');
		return $ret;
	}
	
	/**
		* Fetches member which status will be updated next
		*
		* @param int $jobid
		* @return array $member
	*/
	function _getNextMemberToUpdate ( $jobid = 0 ) {
		$ret = $this->_getNextToUpdate( $jobid, 'member_id' );
		$this->_debug( 3, $ret, 'Fetched next member to update from DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	/**
		* Fetches next whatever which status will be updated next
		*
		* @param int $jobid
		* @param string $field
		* @return array $member
	*/
	function _getNextToUpdate ( $jobid = 0, $field = false ) {
		global $roster, $addon;
		
		if ( $field == false ) {
			return false;
		}
		
		$ret = array();
		$query =	"SELECT MIN(". $field. ") ". $field. " ".
		"FROM `". $roster->db->table('jobqueue',$addon['basename']). "` ".
		"WHERE job_id=". $jobid. " ".
		"AND ISNULL(starttimeutc) AND ISNULL(stoptimeutc);";
		$id = $roster->db->query_first($query);
		if ( $id ) {
			
			$query =	"SELECT * ".
			"FROM `". $roster->db->table('jobqueue',$addon['basename']). "` ".
			"WHERE job_id=". $jobid. " ".
			"AND ". $field. "=". $id. ";";
			$result = $roster->db->query($query);
			if( $roster->db->num_rows($result) > 0 ) {
				$next = $roster->db->fetch_all();
				$ret = $next[0];
			}
		}
		$this->_debug( 3, $ret, 'Fetched next to update from DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
		/**
		* Updates Members job status in jobqueue
		*
		* @param int $jobid
		* @param array $member
		* @return bool
	*/
	function _updateMemberJobStatus ( $jobid = 0, $member = array() ) {
		$ret = $this->_updateJobStatus( $jobid, $member, 'member_id' );
		$this->_debug( 3, $ret, 'Updated character job status in DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	/**
		* Updates job status in jobqueue
		*
		* @param int $jobid
		* @param array $member
		* @return bool
	*/
	function _updateJobStatus ( $jobid = 0, $member = array(), $field = false ) {
		global $roster, $addon;
		
		if ( $field == false ) {
			return false;
		}
		
		$query =	"UPDATE `". $roster->db->table('jobqueue',$addon['basename']). "` ".
		"SET ";
		
		$set = '';
		isset ( $member['guild_info'] ) ? $set .= "guild_info=". '"'.$roster->db->escape($member['guild_info']). '"'. ", " : 1;
		isset ( $member['character_info'] ) ? $set .= "character_info=". $member['character_info']. ", " : 1;
		isset ( $member['skill_info'] ) ? $set .= "skill_info=". $member['skill_info']. ", " : 1;
		isset ( $member['reputation_info'] ) ? $set .= "reputation_info=". $member['reputation_info']. ", " : 1;
		isset ( $member['equipment_info'] ) ? $set .= "equipment_info=". $member['equipment_info']. ", " : 1;
		isset ( $member['talent_info'] ) ? $set .= "talent_info=". $member['talent_info']. ", " : 1;
		
		isset ( $member['starttimeutc'] ) ? $set .= "starttimeutc=".'"'. $roster->db->escape($member['starttimeutc']). '"'.", " : 1;
		isset ( $member['stoptimeutc'] ) ? $set .= "stoptimeutc=".'"'. $roster->db->escape($member['stoptimeutc']). '"'.", " : 1;
		isset ( $member['log'] ) ? $set .= "log=".'"'. $roster->db->escape($member['log']). '"'.", " : 1;
		$set = preg_replace( '/, $/', ' ', $set );
		$query .= $set;
		
		$query .=   "WHERE job_id=". $jobid. " ".
		"AND ". $field. "=". $member[$field]. ";";
		
		$result = $roster->db->query($query);
		if ( $result ) {
			if ( ! $this->dataNotAccepted && isset ( $member['stoptimeutc'] ) && $field == 'member_id' && isset ( $member['character_info'] ) ) {
				$query =	"INSERT INTO `". $roster->db->table('updates',$addon['basename']). "` ".
				"SET ".
				"member_id=". $member[$field].", ".
				"dateupdatedutc='". $roster->db->escape(gmdate('Y-m-d H:i:s')). "' ".
				"ON DUPLICATE KEY UPDATE ".
				"dateupdatedutc='". $roster->db->escape(gmdate('Y-m-d H:i:s')). "';";
				if ( !$roster->db->query($query) ) {
					die_quietly($roster->db->error(),'Database Error',__FILE__,__LINE__,$query);
				}
			}
			$ret = true;
			} else {
			$ret = false;
		}
		$this->_debug( 2, $ret, 'Updated job status in DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	/**
		* Fetches members from jobqueue
		*
		* @param int $jobid
		* @return array $members
	*/
	function _getMembersFromJobqueue( $jobid = 0 ) {
		global $roster, $addon;
		
		$ret = array();
		$query =	"SELECT * ".
		"FROM `". $roster->db->table('jobqueue',$addon['basename']). "` ".
		"WHERE job_id=". $jobid. " ".
		"ORDER BY member_id;";
		
		
		$result = $roster->db->query($query);
		if( $roster->db->num_rows($result) > 0 ) {
			$ret = $roster->db->fetch_all();
		}
		$this->_debug( $ret ? 2 : 0, $ret, 'Fetched members in jobqueue table from DB', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	/**
		* Create localised time based on utc + offset;
		*
		* @param string $time
		* @return string
	*/
	function _getLocalisedTime ( $time = false ) {
		global $roster;
		
		$offset = $roster->config['localtimeoffset'] * 60 * 60;
		$stamp = strtotime( $time );
		$stamp += $offset;
		$ret = date("d.m H:i:s", $stamp);
		$this->_debug( $ret ? 3 : 0, $ret, 'Fetched localized time', $ret ? 'OK' : 'Failed');
		return $ret;
	}
	
	
	
	
	
	
}	
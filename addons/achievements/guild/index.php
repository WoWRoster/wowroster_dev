<?php
/*	include_once (ROSTER_LIB . 'cache.php');
		$cache = new RosterCache();
		$cache->cleanCache();
*/
// begin achievement functions
class achiv
{
		var $data=array();
	//var $data;
	var $equip = array();
	var $talentbuilds = array();
	var $talent_build_url = array();
	var $char, $guild, $server;
	var $class_id;
	var $armory_db;
	var $achi = array();
	var $crit = array();
	var $catss = array();
	var $locale;
	var $cat = array();
	var $memberID;
	var $icons=array();
	var $pcrit = array();
	
	
	function builddata($data)
	{
		global $api, $roster, $addon;
		// build our structure
		$this->buildarch();
		$this->buildcrit();
		$cat = $this->list_all();
		$e = 0;
		$achi = $this->achi;
		$interface= $roster->config['interface_url'].'Interface/Icons/';//Interface/Icons/';
		$imgpath = '/templates/default/achievements/images/';
		
		$achData = array();
		$achDate = array();
		$sqlquery2 = "SELECT * FROM `" . $roster->db->table('g_achievements', $addon['basename']) . "` WHERE `member_id` = '$this->memberID' ORDER BY `achie_date` DESC";
		$result2 = $roster->db->query($sqlquery2);
		$e = 0;
		while($row = $roster->db->fetch($result2))
		{
			$achData[] = $row['achie_id'];
			$achDate[$row['achie_id']] = $row['achie_date'];
			$e++;
		}
		$cats = $cat;
		$this->sksort($cats,'id',true);
		foreach ($cats as $id => $title)
		{
			$roster->tpl->assign_block_vars('amenue',array(
						'ID'		=> 's'.$title['id'],
						'NAME'		=> $title['name'],
						'SUB'		=> (isset($title['sub']) ? true : false)
						)
					);
				
			if (isset($title['sub']) && is_array($title['sub']))
			{
				foreach ($title['sub'] as $ids => $r)
				{
					$roster->tpl->assign_block_vars('amenue.sub',array(
						'ID'       => 's'.$r['id'],
						'NAME'     => $r['name']
						)
					);
				}
			}	
		}
		
		
		foreach ($cat as $id => $title)
		{
			$roster->tpl->assign_block_vars('info',array(
							'ID'   => isset($title['sub'][$id]) ? 's'.$id : 's'.$id,
							'NAME' => $title['name'],
							'TOGGLER' => ' sub'.$id.''
						)
					);

			$ach = '';	
			if( $ach != 'Name')
			{
				$k = array();
				foreach ($achi[$id] as $ach => $da)
				{
					if (isset($da['Name']) && is_numeric($da['Points']))
					{
						if ($this->iscomp($ach,$da['Name'],$achData))
						{
							$bg = $imgpath . 'achievement_bg.jpg';
							$shild = $complete = 1;
							$date = $this->convert_date($achDate[$ach]);
							$datex = $achDate[$ach];
							$crittt = $this->buildcrittooltip($ach,$da);
							
								if ($crittt != '1')
								{
									$crttt = '';//makeOverlib($crittt, $da['Name'], '', 2);
									//$crttt = makeTipped($da['Name'].'<br>'.$crittt, 'mouse', 'none', 'righttop');
								}
								else
								{
									$crttt = '1';
								}
						}
						else
						{
							$bg = $imgpath.'achievement_bg_locked.jpg';
							$shild = $complete = 0;
							$datex = $date = '';
							$crittt = $this->buildcrittooltip($ach,$da);
							
								if ($crittt != '1')
								{
									$crttt = '';//makeOverlib($crittt, $da['Name'], '', 2);
									//$crttt = makeTipped($da['Name'].'<br>'.$crittt, 'mouse', 'none', 'righttop');
								}
								else
								{
									$crttt = '1';
								}
						}
						if ($date != '')
						{
							$crttt = 1;
						}
						if ($id == '81' && $complete != 0)
						{
							$shild = 2;
						}
						if ($id == '81' && $complete == 0)
						{
							$shild = 3;
						}
						
						if ($id == '81' && $complete == 0)
						{
						$crttt = 1;
						$crittt = '';
						}
						else
						{
							$k[] = array(
								'BACKGROUND' => $bg,
								'NAME'       => $da['Name'],
								'DESC'       => $da['Desc'],
								'STATUS'     => $complete.($da['account'] ? 'a': ''),
								'DATE'       => $date,
								'DATEX'       => $datex,
								'POINTS'     => $da['Points'],
								'CRITERIA'   => $crttt,
								'CRITERIA2'   => $crittt,
								'SHIELD'     => $shild,
								'IDDI'			=> $ach,
								'ICON'       => $interface . $da['icon'] . '.png',
							);
							$this->icons[] = "'".$interface . $da['icon'] . ".png'";
						}
					}
				}
				//$this->sksort($h,'DATEX');
				$srt = $this->sortByOneKey($k, 'DATEX', false);
				//foreach ($h as $pices)
				foreach ($srt as $pices)
				{
					$roster->tpl->assign_block_vars('info.achv',$pices);
				}
				
			}
				
			if (isset($title['sub']) && is_array($title['sub']))
			{
				foreach ($title['sub'] as $ids => $r)
				{
					if ($id == $ids)
					{
						$idd = $id;
					}
					else
					{
						$idd = 's' . $ids;
					}
						$roster->tpl->assign_block_vars('info',array(
							'ID'   => $idd,
							'NAME' => $r['name']
							)
						);
						$h = array();
					foreach ($achi[$ids] as $ach => $da)
					{
						if($ach != 'Name' && is_numeric($da['Points']))
						{
							if ($this->iscomp($ach,$da['Name'],$achData))
							{
								$bg = $imgpath . 'achievement_bg.jpg';
								$shild = $complete = 1;
								$date = $this->convert_date($achDate[$ach]);
								$datex = $achDate[$ach];
								$crittt = $this->buildcrittooltip($ach,$da);
							
								if ($crittt != '1')
								{
									$crttt = '';//makeOverlib($crittt, $da['Name'], '', 2);
									//$crttt = makeTipped($da['Name'].'<br>'.$crittt, 'mouse', 'none', 'righttop');
								}
								else
								{
									$crttt = '1';
								}
							}
							else
							{
								$bg = $imgpath.'achievement_bg_locked.jpg';
								$shild = $complete = 0;
								$datex = $date = '';
								$crittt = $this->buildcrittooltip($ach,$da);
							
								if ($crittt != '1')
								{
									$crttt = '';//makeOverlib($crittt, $da['Name'], '', 2);
									//$crttt = makeTipped($da['Name'].'<br>'.$crittt, 'mouse', 'none', 'righttop');
								}
								else
								{
									$crttt = '1';
								}
							}
						if ($date != '')
						{
							$crttt = 1;
						}

						$h[] = array(
									'BACKGROUND' => $bg,
									'NAME'       => $da['Name'],
									'DESC'       => $da['Desc'],
									'STATUS'     => $complete.($da['account'] ? 'a': ''),
									'DATE'       => $date,
									'DATEX'       => $datex,
									'POINTS'     => $da['Points'],
									'CRITERIA'   => $crttt,
									'CRITERIA2'   => $crittt,
									'SHIELD'     => $shild,
									'IDDI'			=> $ach,
									'BAR'			=> 0,
									'ICON'       => $interface . $da['icon'] . '.png',
							);
							$this->icons[] = "'".$interface . $da['icon'] . ".png'";
						}
					}
					//$this->sksort($h,'DATEX');
					$srt = $this->sortByOneKey($h, 'DATEX', false);
					//foreach ($h as $pices)
					foreach ($srt as $pices)
					{
						$roster->tpl->assign_block_vars('info.achv',$pices);
					}
				}
			}	
		}
			
		return true;
	}
	
	function buildcrittooltip($ach,$da)
	{
		global $roster;
		$error = false;
		
		$i = 0;
		$t = 2;
		$x = str_replace(",", "", $da['Desc']);
		if (isset($this->crit[$ach]) && preg_match('/(?P<type>\w+) (?P<total>\d+)/', $x, $match))
		{
			$crit='';
			$t = $roster->locale->act['bars'];//array('Loot','Collect','Obtain','Equip','Complete','Receive','Catch','Find','Create','Raise');
			if (in_array($match['type'], $t))
			{

				if ($this->crit[$ach][0]['Value'] >= 100000 )
				{
					$reward_money_c = $reward_money_s = $reward_money_g = 0;
					if( $this->crit[$ach][0]['Value'] > 0 )
					{
						$money = $this->crit[$ach][0]['Value'];

						$reward_money_c = $money % 100;
						$money = floor( $money / 100 );

						if( !empty($money) )
						{
							$reward_money_s = $money % 100;
							$money = floor( $money / 100 );
						}
						if( !empty($money) )
						{
							$reward_money_g = $money;
						}
					}
					$v = $reward_money_g;
				}
				else
				{
					$v = $this->crit[$ach][0]['Value'];
				}
				$t = $match['total'];
				$w = ceil($v/$t*100);
				$crit = '<div class="profile-progress">
						<div class="bar" style="width: '.$w.'%"></div>
						<div class="bar-contents">'.$v.' / '.$t.' ('.$w.'%)</div>
					</div>';
			}
			else
			{
				$error = true;
			}
		}
		else if (isset($this->crit[$ach]) && count($this->crit[$ach]) != 1)
		{
			$crit='';
			$crit .= '<div class="meta-achievements"><ul>';
			/*
			$c = ($critData[$row['crit_id']]['status'] ? 'unlocked' : 'locked');
				$this->crit[$row['crit_achie_id']][]=array(
								'id'=>$row['crit_id'],
								'Desc'=>$row['crit_desc'],
								'Value'=>$critData[$row['crit_id']]['Value'],
								'complete' => $c
					);
					*/
			$ct_name = null;
			foreach ($this->crit[$ach] as $id => $info)
			{
				if ($ct_name != $info['Desc'])
				{
					$crit .= '<li id=\'lcrt'.$info['id'].'\'><div id=\'crt'.$info['id'].'\'>'.$info['Desc'].'</div></li>';
					$ct_name = $info['Desc'];
				}
				
			}
			$crit .= '</ul></div>';
			
			/*
			
			$tt = '<div class="profile-progress border-4">
				<div class="bar border-4 hover" style="width: 54%"></div>
				<div class="bar-contents">1,361 / 2,500 (54%)</div>
			</div>';
			*/
		}
		else
		{
			$error = true;
		}
		
		if (!$error)
		{
		return $crit;
		}
		else
		{
			return '1';
		}
	}
	function convert_date($date)
	{
		global $roster;
		$date = ($date / 1000);
		$date = date('D M jS Y, g:ia',$date);
		return $date;
	}
	
	function sortByOneKey(array $array, $key, $asc = true)
	{
		$result = array();
			
		$values = array();
		foreach ($array as $id => $value)
		{
			$values[$id] = isset($value[$key]) ? $value[$key] : '';
		}
			
		if ($asc) {
			asort($values);
		}
		else {
			arsort($values);
		}
			
		foreach ($values as $key => $value)
		{
			$result[$key] = $array[$key];
		}
			
		return $result;
	}


	function sksort(&$array, $subkey="id", $sort_ascending=false) 
	{

		if (count($array))
        $temp_array[key($array)] = array_shift($array);

		foreach($array as $key => $val){
			$offset = 0;
			$found = false;
			foreach($temp_array as $tmp_key => $tmp_val)
			{
				if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
				{
					$temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
					$found = true;
				}
				$offset++;
			}
			if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
		}

		if ($sort_ascending) $array = array_reverse($temp_array);

		else $array = $temp_array;
	}


	function list_all() 
	{
		global $roster,$addon,$catss;

		$catss = array();
		$catss = $catss + $this->getcatsubs();
		$dlidd = "SELECT DISTINCT `achi_cate`, `c_id`, `p_id` FROM `" . $roster->db->table('g_achie', $addon['basename']) . "` ORDER BY `c_id` ASC";
		$resultdl = $roster->db->query($dlidd);
		while($row = $roster->db->fetch($resultdl))
		{
			if ($row['p_id'] == '-1')
			{
				$catss[$row['c_id']]['name'] = $row['achi_cate'];
				$catss[$row['c_id']]['id'] = $row['c_id'];
				$main = $row['c_id'];
			}

		}

		return $catss;
	}
	
	function getcatsubs()
	{
		global $roster,$addon,$catss;

		$catss = array();
		$dlid = "SELECT * FROM `" . $roster->db->table('g_achie', $addon['basename']) . "` WHERE `p_id` != '-1'  order by `c_id` ASC";
		$resultd = $roster->db->query($dlid);
		while($row = $roster->db->fetch($resultd))
		{
			$catss[$row['p_id']]['sub'][$row['c_id']] = array('name' => $row['achi_cate'],'id' => $row['c_id']);
		}
		return $catss;
	}
	
	function buildarch()
	{
		global $roster,$addon,$catss;
		
		$query =	"SELECT * FROM `" . $roster->db->table('g_achie', $addon['basename']) . "` ORDER BY `c_id` DESC ";
		$ret = $roster->db->query($query);
		$this->achi = array();
		while( $row = $roster->db->fetch($ret) )
		{
			$this->achi[$row['c_id']]['Name'] = $row['achi_cate'];
			$this->achi[$row['c_id']][$row['achie_id']]=array(
					'Name'=>$row['achie_name'],
					'Desc'=>$row['achie_desc'],
					'Points'=>$row['achie_points'],
					'icon'=>$row['achie_icon'],
					'account'=>$row['achie_isAccount'],
					'achi_ID'=>$row['achie_id']
				);
		}
	}
	
	function buildcrit()
	{
		global $roster,$addon,$catss;
		
		$sqlquery2 = "SELECT * FROM `" . $roster->db->table('g_criteria', $addon['basename']) . "` WHERE `member_id` = '$this->memberID' ORDER BY `crit_id` DESC";
		$result2 = $roster->db->query($sqlquery2);
		$e = 0;
		$critData = array();
		while($row2 = $roster->db->fetch($result2))
		{
			$this->pcrit[] = $row2['crit_id'];
			
			$critData[$row2['crit_id']] = array('status'=>'unlocked','Value'=>$row2['crit_value']);
			$e++;
		}	
		
		$query =	"SELECT * FROM `" . $roster->db->table('g_crit', $addon['basename']) . "` ORDER BY `crit_achie_id` DESC ";
		$ret = $roster->db->query($query);
		$this->crit = array();
		while( $row = $roster->db->fetch($ret) )
		{
			if ($row['crit_desc'] != '')
			{
				if (isset($critData[$row['crit_id']]) && $critData[$row['crit_id']]['status'] == 'unlocked')
				{
					$complete = 'unlocked';
				}
				else
				{
					$complete = 'locked';
				}
				$c = (isset($critData[$row['crit_id']]['status']) ? 'unlocked' : 'locked');
				$this->crit[$row['crit_achie_id']][]=array(
								'id'=> $row['crit_id'],
								'Desc'=> $row['crit_desc'],
								'Value'=> (isset($critData[$row['crit_id']]['Value']) ? $critData[$row['crit_id']]['Value'] : ''),
								'complete' => $c
					);
			}
		}

	}
	
	function iscomp($ach_id,$ach_name,$achi_data)
	{
		if (in_array($ach_id, $achi_data))
		{
			return true;//'<font color="green">'.$ach_name.'</font>';
		}
		else
		{
			return false;//'<font color="blue">'.$ach_name.'</font>';
		}

	}

	function in_multiarray($elem, $array)
    {
        $top = sizeof($array) - 1;
        $bottom = 0;
        while($bottom <= $top)
        {
            if($array[$bottom] == $elem)
                return true;
            else 
                if(is_array($array[$bottom]))
                    if($this->in_multiarray($elem, ($array[$bottom])))
                        return true;
                    
            $bottom++;
        }        
        return false;
    }
	function out($char)
	{
		global $roster,$addon, $addon;
		$this->data = $char;
		$this->memberID = $roster->data['guild_id'];
		$this->builddata($this->data);	
		
		$roster->tpl->assign_var('S_TALENT_TAB',false);
		$roster->tpl->assign_var('S_ACHIV',true);
		$roster->tpl->assign_var('S_ACHIV_ICON',$addon['config']['show_icons']);
		$this->summary();
		
		
		$roster->tpl->set_filenames(array('char' => $addon['basename'] . '/achiv.html'));
		return $roster->tpl->fetch('char');
	}
	
	function summary()
	{
		global $roster;
	
		$catss = array();
		$cats = array();
		$ach = array();		
		$query =	"SELECT * FROM `" . $roster->db->table('g_achie', 'achievements') . "` WHERE (`factionId` = '0' OR `factionId` = '2') ORDER BY `c_id` DESC ";
		$ret = $roster->db->query($query);
		
		while( $row = $roster->db->fetch($ret) )
		{
			if ($row['p_id'] =='-1')
			{
				$catss[$row['c_id']][$row['achie_id']] = array(
															'name' => $row['achie_name'],
															'id' => $row['c_id'],
															'comp' => 0,
															);
				$cats[$row['achie_id']] = array(
													'name' => $row['achie_name'],
													'cid' => $row['c_id'],
													'comp' => 0,
													);
				$ach[$row['c_id']]['total'] = isset($ach[$row['c_id']]['total']) ? ($ach[$row['c_id']]['total']+1) : 1; 
				$ach[$row['c_id']]['name'] = $row['achi_cate'];
			}
			else
			{
				$ach[$row['p_id']]['total'] = isset($ach[$row['p_id']]['total']) ? ($ach[$row['p_id']]['total']+1) : 1; 
				
				$catss[$row['p_id']][$row['achie_id']] = array(
															'name' => $row['achie_name'],
															'id' => $row['c_id'],
															'comp' => 0,
															);
				$cats[$row['achie_id']] = array(
													'name' => $row['achie_name'],
													'cid' => $row['p_id'],
													'comp' => 0,
													);
			}

		}
		$sqlquery2 = "SELECT * FROM `" . $roster->db->table('g_achievements', 'achievements') . "` WHERE `member_id` = '".$roster->data['guild_id']."' ORDER BY `achie_date` DESC";
		$result2 = $roster->db->query($sqlquery2);
		$e = 0;
		while($rowx = $roster->db->fetch($result2))
		{
			$cats[$rowx['achie_id']]['comp'] = 1;
		}


		foreach ($cats as $achID => $data)
		{
			$ach[$data['cid']]['comp'] = isset($ach[$data['cid']]['comp']) ? ($ach[$data['cid']]['comp']+$data['comp']) : $data['comp']; 
		}


		$achcat = array(
			'15088' => 'General',
			'15077' => 'Quests',
			'15078' => 'Player vs. Player',
			'15079' => 'Dungeons &amp; Raids',
			'15080' => 'Professions',
			'15089' => 'Reputation',
			'15093' => 'Guild Feats of Strength',
		);

		$output = '';
		$et = 0;
		$ct = 0;
		foreach($ach as $cat => $t)
		{
			$text = $t['comp'].' / '.$t['total'];
			$per = $t['comp']/$t['total']*100;
			$et = ($et+$t['total']);
			$ct = ($ct+$t['comp']);
			$roster->tpl->assign_block_vars('cat', array(
				'TITLE'		=> $t['name'],
				'BAR'		=> $text,
				'PERCENT'	=> (100-$per),
				'PERCENT1'	=> $per
			));
		}
		$xper = $ct/$et*100;
		
		$roster->tpl->assign_block_vars('cat', array(
				'TITLE'		=> 'Total Completed',
				'BAR'		=> $ct.' / '.$et,
				'PERCENT'	=> (100-$xper),
				'PERCENT1'	=> $xper
			));
	
	}
	
}


$achiv = new achiv;

$sqlquery2 = "SELECT * FROM `".$roster->db->table('guild')."` WHERE `guild_id` = '".$roster->data['guild_id']."'";
$result2 = $roster->db->query($sqlquery2);
$row = $roster->db->fetch($result2);

$char = array();
foreach($row as $var => $value)
{
	$char[$var] = $value;
}

$body =  $achiv->out($char);
$images = implode(",",$achiv->icons);

roster_add_js('addons/' . $addon['basename'] . '/js/achievements.js');
echo $body;
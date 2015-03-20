<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Roster upload rule config
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @package    WoWRoster
 * @subpackage RosterCP
 */

if( !defined('IN_ROSTER') || !defined('IN_ROSTER_ADMIN') )
{
	exit('Detected invalid access to this file!');
}
if (isset($_POST))
{
echo '<pre>';
print_r($_POST);
echo '</pre>';

}
if (isset($_POST['process']) && $_POST['process'] == 'process')
{
	$count=1;
	if (isset($_POST['class_id']))
	{
		//	aprint($_POST);
		$classid = (isset($_POST['class_id']) ? $_POST['class_id'] : $_GET['class']);
		echo '<br>--[ '.$classid.' ]--<br>';
		$talents = $roster->api->Talents->getTalentInfo($classid);
		
		$querystr = "DELETE FROM `" . $roster->db->table('talents_data') . "` WHERE `class_id` = '" . $classid . "';";
		if (!$roster->db->query($querystr))
		{
			$roster->set_message('Talent Data Table could not be emptied.', '', 'error');
			$roster->set_message('<pre>' . $roster->db->error() . '</pre>', 'MySQL Said', 'error');
			return;
		}

		$treenum = 1;
		$t=1;
		foreach ($talents['talentData']['talentTrees'] as $a => $treedata)
		{

			$lvl = 15;
			foreach ($treedata as $t => $talent)
			{

				$tooltip = '';
				$tooltip .= (isset($talent['spell']['cost']) ? $talent['spell']['cost'] : '');
				$tooltip .=	(isset($talent['spell']['range']) ? '<span style="float:right;">'.$talent['spell']['range'].'</span>' : '');
				$tooltip .=	(isset($talent['spell']['castTime']) ? '<br>'.$talent['spell']['castTime'] : '');
				$tooltip .=	(isset($talent['spell']['cooldown']) ? '<span style="float:right;">'.$talent['spell']['cooldown'].'</span>' : '');
				$tooltip .= '<br>'.$talent['spell']['htmlDescription'];
				
				$values = array(
					'talent_id'  => $talent['spell']['spellId'],
					'talent_num' => $t,
					'tree_order' => '0',
					'class_id'   => $talent['classKey'],
					'name'       => $talent['spell']['name'],
					'tree'       => '',//$treedata['name'],
					'tooltip'    => $tooltip,
					'texture'    => $talent['spell']['icon'],
					'isspell'	 => ( !$talent['spell']['keyAbility'] ? false : true ),
					'row'        => ($talent['tier'] + 1),
					'column'     => ($talent['column'] + 1),
					'rank'       => $lvl
				);

				
				$querystr = "INSERT INTO `" . $roster->db->table('talents_data') . "` "
					. $roster->db->build_query('INSERT', $values) . ";";
				$result = $roster->db->query($querystr);
				$count++;
			$t++;	
			}
			$lvl = ($lvl+15);
			$count++;
			$treenum++;
		}


		$roster->set_message(sprintf($roster->locale->act['adata_update_class'], $roster->locale->act['id_to_class'][$classid]));
		$roster->set_message(sprintf($roster->locale->act['adata_update_row'], $count));
	}

	if (isset($_POST['truncate']))
	{
		switch($_POST['truncate'])
		{
            case 'cache':
                /* No break */
				//TRUNCATE TABLE  `roster_api_gems`
				$qgem = "TRUNCATE TABLE `" . $roster->db->table('api_gems') . "`;";
				$resultgem = $roster->db->query($qgem);

				$qitem = "TRUNCATE TABLE `" . $roster->db->table('api_items') . "`;";
				$resultitem = $roster->db->query($qitem);
				
				$qcache = "TRUNCATE TABLE `" . $roster->db->table('api_cache') . "`;";
				$resultitem = $roster->db->query($qcache);
				
				$qenchant = "TRUNCATE TABLE `" . $roster->db->table('api_enchant') . "`;";
				$resultitem = $roster->db->query($qenchant);
				
				$roster->set_message(sprintf($roster->locale->act['installer_purge_0'],'Item/Gem/Enchant/Cache cleared'));
			break;
			
			case 'api_usage':
				$qenchant = "TRUNCATE TABLE `" . $roster->db->table('api_usage') . "`;";
				$resultitem = $roster->db->query($qenchant);
				
				$roster->set_message(sprintf($roster->locale->act['installer_purge_0'],' Api Usage cleared '));
			break;
			
			case 'api_error':
				$qenchant = "TRUNCATE TABLE `" . $roster->db->table('api_error') . "`;";
				$resultitem = $roster->db->query($qenchant);
				
				$roster->set_message(sprintf($roster->locale->act['installer_purge_0'],' Api Error '));
			break;
		}
	}


	if (isset($_POST['parse']) && $_POST['parse'] == 'ALL')
	{

		$classes = array('1','2','3','4','5','6','7','8','9','11','0');
		$talent = $roster->api->Data->getTalents();
		$messages = '';
		foreach ($talent as $class_id => $info)
		{
			$tid = $class_id;
			$i = $tid;
			
			$querystr = "DELETE FROM `" . $roster->db->table('talents_data') . "` WHERE `class_id` = '" . $tid . "';";
			if (!$roster->db->query($querystr))
			{
				$roster->set_message('Talent Data Table could not be emptied.', '', 'error');
				$roster->set_message('<pre>' . $roster->db->error() . '</pre>', 'MySQL Said', 'error');
				return;
			}

			$querystr = "DELETE FROM `" . $roster->db->table('talenttree_data') . "` WHERE `class_id` = '" . $tid . "';";
			if (!$roster->db->query($querystr))
			{
				$roster->set_message('Talent Tree Data Table could not be emptied.', '', 'error');
				$roster->set_message('<pre>' . $roster->db->error() . '</pre>', 'MySQL Said', 'error');
				return;
			}

			$count = 1;
			$treenum = 1;
		//$i=$tid;
			foreach ($info['talents'] as $a => $treedata)
			{

				$lvl = 15;
				foreach ($treedata as $t => $talent)
				{

					$tooltip = '';
					$tooltip .= (isset($talent['spell']['powerCost']) 	? $talent['spell']['powerCost'].'<br />' 	: '');
					$tooltip .=	(isset($talent['spell']['range']) 		? $talent['spell']['range'].'<br />' 	: '');
					$tooltip .=	(isset($talent['spell']['castTime']) 	? $talent['spell']['castTime'].'<br />' 	: '');
					$tooltip .=	(isset($talent['spell']['cooldown']) 	? $talent['spell']['cooldown'].'<br />' 	: '');
					$tooltip .= '<br><span style="color:#00bbff;">'.$talent['spell']['description'].'</span>';
					$values = array(
						'talent_id'  => $talent['spell']['id'],
						'talent_num' => $t,
						'tree_order' => '0',
						'class_id'   => $class_id,
						'name'       => $talent['spell']['name'],
						'tree'       => '',//$treedata['name'],
						'tooltip'    => tooltip($tooltip),
						'texture'    => $talent['spell']['icon'],
						//'isspell'	 => ( !$talent['spell']['keyAbility'] ? false : true ),
						'row'        => ($talent['tier'] + 1),
						'column'     => ($talent['column'] + 1),
						'rank'       => $lvl
					);

					
					$querystr = "INSERT INTO `" . $roster->db->table('talents_data') . "` "
						. $roster->db->build_query('INSERT', $values) . ";";
					$result = $roster->db->query($querystr);
					$count++;
				$t++;	
				}
				$lvl = ($lvl+15);

				$count++;
				$treenum++;
			}
			foreach ($info['specs'] as $a => $treedata)
			{
			
				$values = array(
					'tree'       => $treedata['name'],
					'order'      => $treedata['order'],
					'class_id'   => $class_id,
					'background' => strtolower($treedata['backgroundImage']),
					'icon'       => $treedata['icon'],
					'roles'		 => $treedata['role'],
					'desc'		 => $treedata['description'],
					'tree_num'   => $treedata['order']
				);

					
				$querystr = "INSERT INTO `" . $roster->db->table('talenttree_data') . "` "
					. $roster->db->build_query('INSERT', $values) . "
					;";
				$result = $roster->db->query($querystr);
			}


			$messages .= sprintf($roster->locale->act['adata_update_class'], $roster->locale->act['id_to_class'][$class_id]).' - ';
			$messages .= sprintf($roster->locale->act['adata_update_row'], $count).'<br>';
		}
		$roster->set_message($messages);
	}
}
//echo 'will have update information for talents';

$classes = $roster->locale->act['class_to_id'];

$roster->tpl->assign_block_vars('sections', array(
					'ID'        => 'class',
					'NAME'		=> 'class data',
					'TYPE'		=> 'ALL',
					'VALUE'		=> 'talents_data',
				)
			);
foreach ($classes as $class => $num)
{
	$querystra = $classr = $resulta = 0;
	$querystra = "SELECT * FROM `" . $roster->db->table('talents_data') . "` WHERE `class_id` = '" . $num . "';";
	$resulta = $roster->db->query($querystra);
	$classr = $roster->db->num_rows($resulta);
	$i = 0;

	$roster->tpl->assign_block_vars('sections.lines', array(
		'NAME'       => $class,
		'ID'         => $num,
		'ROWS'       => $classr,
		'ROW'        => (($i % 2) + 1)
		)
	);
}
	
$roster->tpl->assign_block_vars('sections', array(
					'ID'        => 'cache',
					'NAME'		=> 'cache data',
					'TYPE'		=> 'truncate',
					'VALUE'		=> 'cache',
				)
			);	
	$qgem = "SELECT * FROM `" . $roster->db->table('api_gems') . "`;";
	$resultgem = $roster->db->query($qgem);
	$gem = $roster->db->num_rows($resultgem);
	$roster->tpl->assign_block_vars('sections.lines', array(
		'NAME'       => 'Gems',
		'ROWS'       => $gem,
		'ROW'        => (($i % 2) + 1)
		)
	);
	$qitem = "SELECT * FROM `" . $roster->db->table('api_items') . "`;";
	$resultitem = $roster->db->query($qitem);
	$item = $roster->db->num_rows($resultitem);
	$roster->tpl->assign_block_vars('sections.lines', array(
		'NAME'       => 'Items',
		'ROWS'       => $item,
		'ROW'        => (($i % 2) + 1)
		)
	);
	$qitem = "SELECT * FROM `" . $roster->db->table('api_enchant') . "`;";
	$resultitem = $roster->db->query($qitem);
	$item = $roster->db->num_rows($resultitem);
	$roster->tpl->assign_block_vars('sections.lines', array(
		'NAME'       => 'enchant',
		'ROWS'       => $item,
		'ROW'        => (($i % 2) + 1)
		)
	);
	$qitem = "SELECT * FROM `" . $roster->db->table('api_cache') . "`;";
	$resultitem = $roster->db->query($qitem);
	$item = $roster->db->num_rows($resultitem);
	$roster->tpl->assign_block_vars('sections.lines', array(
		'NAME'       => 'General',
		'ROWS'       => $item,
		'ROW'        => (($i % 2) + 1)
		)
	);
	$queryx = "SELECT COUNT(`id`) as total, type FROM `" . $roster->db->table('api_cache') . "` GROUP BY `type`";
	$resultx = $roster->db->query($queryx);

	while ($row = $roster->db->fetch($resultx))
	{
		$roster->tpl->assign_block_vars('sections.lines', array(
			'NAME'       => $row['type'],
			'ROWS'       => $row['total'],
			'ROW'        => (($i % 2) + 1)
			)
		);
	}

	
	
	$queryx = "SELECT * FROM `" . $roster->db->table('api_usage') . "` ORDER BY `date` desc;";
	$resultx = $roster->db->query($queryx);
	$usage = array();
	while ($row = $roster->db->fetch($resultx))
	{
		//$usage[$row['date']][$row['type']]['total']=$row['total'];
		$usage[$row['type']][] = array('url'=>$row['url'],'date'=>$row['date'],'count'=>$row['total']);
	}
	//echo '<pre>';
	//print_r($usage);
	//echo '</pre>';
	$roster->tpl->assign_block_vars('sections', array(
				'ID'		=> 'usage',
				'NAME'		=> 'Usage Data',
				'TYPE'		=> 'truncate',
				'VALUE'		=> 'api_usage',
			)
		);
	foreach($usage as $type => $x)
	{
		$roster->tpl->assign_block_vars('sections.usage', array(
					'ID'		=> 'usd'.$type,
					'NAME'		=> $type,
				)
			);
		foreach($x as $row => $d)
		{
			$roster->tpl->assign_block_vars('sections.usage.lines', array(
					'NAME'		=> $d['url'],
					'ROWS'		=> $d['count'],
					'ROW'		=> (($i % 2) + 1)
				)
			);
		}
	}

	$roster->tpl->assign_block_vars('sections', array(
					'ID'        => 'errors',
					'NAME'		=> 'Errors',
					'TYPE'		=> 'truncate',
					'VALUE'		=> 'api_error',
				)
			);	
	$err = "SELECT * FROM `" . $roster->db->table('api_error') . "`;";
	$resulterr = $roster->db->query($err);
	while ($row = $roster->db->fetch($resulterr))
	{
		$roster->tpl->assign_block_vars('sections.lines', array(
			'NAME'       => $row['error_info'],
			'ROWS'       => $row['url'].' - '.$row['total'],
			'ROW'        => (($i % 2) + 1)
			)
		);
	}
	
$menuz = 'tab-menu';
$js2 ='
$(document).ready(function() {

$(".'.$menuz.'").each(function() {

    var $myTabs = $(this);
	menu = $myTabs.find("ul").attr("id");
	var tab_class = jQuery("ul#"+menu+" li").first().attr("id");
	jQuery("ul#"+menu+" li#" + tab_class).addClass("selected");
	jQuery("ul#"+menu+" li").each(function() {
		var v = jQuery(this).attr("id");
		jQuery("div#"+v+"").hide();
	});
	jQuery("."+menu+"#" + tab_class).show();
});
jQuery(".'.$menuz.' ul li").click(function(e)
	{
		e.preventDefault();
		menu = jQuery(this).parent().attr("id");
		jQuery(".'.$menuz.' ul#"+menu+" li").removeClass("selected");

		var tab_class = jQuery(this).attr("id");
		jQuery(".'.$menuz.' ul#"+menu+" li").each(function() {
			var v = jQuery(this).attr("id");
			jQuery("div#"+v+"").hide();
		});
		jQuery("."+menu+"#" + tab_class).show();
		jQuery(".'.$menuz.' ul#"+menu+" li#" + tab_class).addClass("selected");
	});
});
';
roster_add_js($js2, 'inline', 'header', false, false);
$roster->tpl->set_filenames(array('body' => 'admin/api_data.html'));
$body = $roster->tpl->fetch('body');



/**
 * Format tooltips for insertion to the db
 *
 * @param mixed $tipdata
 * @return string
 */
function tooltip( $tipdata )
{
	$tooltip = '';

	if( is_array($tipdata) )
	{
		$tooltip = implode("\n",$tipdata);
	}
	else
	{
		$tooltip = str_replace('<br>',"\n",$tipdata);
	}
	return $tooltip;
}

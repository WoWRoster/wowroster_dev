<?php

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}
$plugins = $roster->plugin_data;
$pp=array();
if( !empty($plugins) )
{
	foreach( $plugins as $plugin_name => $plugin )
	{
		//$dirx = ROSTER_ADDONS . $plugin['basename'] . DIR_SEP . 'inc' . DIR_SEP . 'plugins' . DIR_SEP;
		if ($plugin['parent'] == $addon['basename'])
		{
			if ($roster->plugin_data[$plugin_name]['active'] == '1')
			{
				$pp[$plugin['addon_id']] = $plugin;
			}
		}
	}
}

if( isset( $_POST['op'] ) && $_POST['op'] == 'process' )
{
			parse_str($_POST['order'], $s);

			$query = "TRUNCATE `" . $roster->db->table('blocks',$addon['basename']) . "`;";
			$result = $roster->db->query($query);
			$u = 1;
			foreach($s['block'] as $id => $o)
			{
				$query = "INSERT INTO `" . $roster->db->table('blocks',$addon['basename']) . "` SET "
					. "`block_name` = '" . $pp[$id]['basename'] . "', "
					. "`block_location` = '" . $u . "', "
					. "`block_id` = '" . $id . "';";
				$roster->db->query($query);
				$u++;
			}

		/*
		case 'deactivate':
			$query = "UPDATE `" . $roster->db->table('slider',$addon['basename']) . "` SET `b_active` = '0' WHERE `id` = '".$_POST['id']."';";
			$roster->db->query($query);
			break;

		case 'delete':

			$dir = $addon['dir'];
			$filename = $_POST['image'];
			$delete = $_POST['id'];
			if( file_exists($dir.$filename) )
			{
				if( unlink($dir.$filename))
				{
					unlink($dir.'thumb-'.$filename);
					unlink($dir.'slider-'.$filename);
					
					$roster->set_message( '"'.$filename.' & thumb-'.$filename.' & slider-'.$filename.'": Deleted' );
					$roster->db->query("DELETE FROM `".$roster->db->table('slider',$addon['basename'])."` WHERE id='".$delete."' ");
				}
				else
				{
					$roster->set_message( $filename.': Could not be deleted' );
				}
			}
			else
			{
				$roster->set_message( 'File not found! ['. $dir . $filename .'] Removing SQL entry' );
				$roster->db->query("DELETE FROM `".$roster->db->table('slider',$addon['basename'])."` WHERE id='".$delete."' ");
			}

			break;

		default:
		break;
		*/

}
/*
Gona get fancy now now we are gona use a menu like sytem to "order" the blocks on the page ... this should be fun
*/
//blocks array from the addon databse table for the order
$blocks = array();
$query = "SELECT * FROM `" . $roster->db->table('blocks',$addon['basename']) . "` ORDER BY `block_location` ASC;";
$result = $roster->db->query($query);
while( $row = $roster->db->fetch($result) )
{
		$blocks[$row['block_id']] = $row;
}

$position= 0;
$r = array();
$h = count($blocks);
//echo $h;
if( !empty($plugins) )
{
	foreach( $plugins as $plugin_name => $plugin )
	{
		if ($plugin['parent'] == $addon['basename'])
		{
			if ($roster->plugin_data[$plugin_name]['active'] == '1')
			{
				$xplugin = getplugin($plugin_name);
				if( !empty($xplugin['icon']) )
				{
					if( strpos($xplugin['icon'],'.') !== false )
					{
						$xplugin['icon'] = ROSTER_PATH . 'plugins/' . $xplugin['basename'] . '/images/' . $xplugin['icon'];
					}
					else
					{
						$xplugin['icon'] = $roster->config['interface_url'] . 'Interface/Icons/' . $xplugin['icon'] . '.' . $roster->config['img_suffix'];
					}
				}
				else
				{
					$xplugin['icon'] = $roster->config['interface_url'] . 'Interface/Icons/inv_misc_questionmark.' . $roster->config['img_suffix'];
				}
				$r[$xplugin['addon_id']] = array(
					'id'          => ( isset($xplugin['addon_id']) ? $xplugin['addon_id'] : '' ),
					'icon'        => $xplugin['icon'],
					'fullname'    => $xplugin['fullname'],
					'location'	  => $position++,
					'basename'    => $xplugin['basename'],
					'parent'	  => $xplugin['parent'],
					'version'     => $xplugin['version'],
					'description' => $xplugin['description'],
					);
				if (isset($blocks[$xplugin['addon_id']]) && is_array($blocks[$xplugin['addon_id']]))
				{
					$blocks[$xplugin['addon_id']] = array_merge ( $blocks[$xplugin['addon_id']],$r[$xplugin['addon_id']]);
				}
				else
				{
					$h++;
					$blocks[$xplugin['addon_id']] = $r[$xplugin['addon_id']];
					$blocks[$xplugin['addon_id']]['block_location'] = $h;
					$blocks[$xplugin['addon_id']]['location'] = $h;
				}
			}		
		}
	}
}
/*
echo '<pre>';
print_r($blocks);
echo '</pre>';
*/
foreach ($blocks as $bid => $val)
{
	$roster->tpl->assign_block_vars('addon_list', array(
					'ROW_CLASS'   => $roster->switch_row_class(),
					'ID'          => ( isset($val['id']) ? $val['id'] : '' ),
					'ICON'        => $val['icon'],
					'FULLNAME'    => $val['fullname'],
					'LOCATION'	  => $val['block_location'],
					'BASENAME'    => $val['basename'],
					'PARENT'	  => $val['parent'],
					'VERSION'     => $val['version'],
					'DESCRIPTION' => $val['description'],
					)
				);
}

$jscript = "
$(function() {
    $( '#sortable' ).sortable({
      placeholder: 'ui-state-highlight'
    });
    $( '#sortable' ).disableSelection();
  
  $('ul').sortable({
        axis: 'y',
        stop: function (event, ui) {
	        var data = $(this).sortable('serialize');
            $('#yyy').text(data);
			$('input#order').val(data);

	}
    });});";
roster_add_js($jscript, 'inline', 'header', FALSE, FALSE);




$roster->tpl->set_handle('plugins',$addon['basename'] . '/admin/plugins.html');

$body .= $roster->tpl->fetch('plugins');




/*
$query = "SELECT * FROM `" . $roster->db->table('slider',$addon['basename']) . "` "
	. "ORDER BY `id` ASC;";

$result = $roster->db->query($query);

while( $row = $roster->db->fetch($result) )
{
	$roster->tpl->assign_block_vars('slider_row',array(
		'ROW_CLASS'  => $roster->switch_row_class(),
		'B_TITLE'    => $row['b_title'],
		'B_DESC'     => $row['b_desc'],
		'B_ACTIVE'   => $row['b_active'],
		'B_ID'       => $row['id'],
		'B_IMG'      => $row['b_image'],
		'B_ACTIVEI'  => ( $row['b_active'] == 1 ? 'green' : 'yellow'),
		'B_ACTIVET'  => ( $row['b_active'] == 1 ? $roster->locale->act['active'] : $roster->locale->act['inactive']),
		'B_ACTIVEOP' => ( $row['b_active'] == 1 ? 'deactivate' : 'activate'),
		'B_IMAGE'    => $addon['url_path'] .'images/thumb-'. $row['b_image'],
		)
	);
}


*/

/**
 * Make our menu from the config api
 */
// ----[ Set the tablename and create the config class ]----
include(ROSTER_LIB . 'config.lib.php');
$config = new roster_config( $roster->db->table('addon_config'), '`addon_id` = "' . $addon['addon_id'] . '"' );

// ----[ Get configuration data ]---------------------------
$config->getConfigData();

// ----[ Build the page items using lib functions ]---------
$menu .= $config->buildConfigMenu('rostercp-addon-' . $addon['basename']);



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
	
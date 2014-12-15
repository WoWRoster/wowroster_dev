<?php
/**
 * WoWRoster.net WoWRoster
 *
 *
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @link       http://www.wowroster.net
 * @package    EasyMembersList
*/

if ( !defined('IN_ROSTER') )
	exit('Detected invalid access to this file!');

if (!isset($roster->data['guild_id'])) 
    return;

include_once ($addon['inc_dir'] . 'easymemberslist.class.php');

$easymemberslist = new easymemberslist();

$action = $easymemberslist->get_param('action', 'string', 'request', 'default');

switch($action)
{
    default:
        $order_by = $easymemberslist->get_param('sort_order', 'string', 'request', 'm.name ASC');
        $guildranks = $easymemberslist->get_guildranks();
        roster_add_js('addons/easymemberslist/js/easymemberslist.js');
        $out = '<div class="easymemberslist_filter">'."\n";
        $out .= '  <div class="easymemberslist_filerheader">'.$roster->locale->act['easymemberslist_filterheading'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_filterbody">'."\n";
        $out .= '      <div class="easymemberslist_filterrow_left1">'.$roster->locale->act['easymemberslist_sort'].':</div>'."\n";
        $out .= '      <div class="easymemberslist_filterrow_right1"><select id="sort_order" name="sort_order" onchange="easymemberslist_filter();">'."\n";
        $out .= '          <option value="m.name ASC" selected="selected">'.$roster->locale->act['name'].' '.$roster->locale->act['easymemberslist_ascending'].'</option>'."\n";
        $out .= '          <option value="m.name DESC">'.$roster->locale->act['name'].' '.$roster->locale->act['easymemberslist_descending'].'</option>'."\n";
        $out .= '          <option value="p.race ASC">'.$roster->locale->act['race'].' '.$roster->locale->act['easymemberslist_ascending'].'</option>'."\n";
        $out .= '          <option value="p.race DESC">'.$roster->locale->act['race'].' '.$roster->locale->act['easymemberslist_descending'].'</option>'."\n";
        $out .= '          <option value="m.class ASC">'.$roster->locale->act['class'].' '.$roster->locale->act['easymemberslist_ascending'].'</option>'."\n";
        $out .= '          <option value="m.class DESC">'.$roster->locale->act['class'].' '.$roster->locale->act['easymemberslist_descending'].'</option>'."\n";
        $out .= '          <option value="m.level ASC">'.$roster->locale->act['level'].' '.$roster->locale->act['easymemberslist_ascending'].'</option>'."\n";
        $out .= '          <option value="m.level DESC">'.$roster->locale->act['level'].' '.$roster->locale->act['easymemberslist_descending'].'</option>'."\n";
        $out .= '          <option value="m.guild_rank ASC">'.$roster->locale->act['rank'].' '.$roster->locale->act['easymemberslist_ascending'].'</option>'."\n";
        $out .= '          <option value="m.guild_rank DESC">'.$roster->locale->act['rank'].' '.$roster->locale->act['easymemberslist_descending'].'</option>'."\n";
        $out .= '          <option value="m.last_online ASC">'.$roster->locale->act['lastonline'].' '.$roster->locale->act['easymemberslist_ascending'].'</option>'."\n";
        $out .= '          <option value="m.last_online DESC">'.$roster->locale->act['lastonline'].' '.$roster->locale->act['easymemberslist_descending'].'</option>'."\n";
        $out .= '      </select>'."\n";
        $out .= '      </div><br clear="all" />'."\n";
        $out .= '      <div class="easymemberslist_filterrow_left2">'.$roster->locale->act['level'].':</div>'."\n";
        $out .= '      <div class="easymemberslist_filterrow_right2">'."\n";
        $out .= '          <input id="min_level" value="1" size="2" onchange="easymemberslist_filter();" class="ui-selectmenu ui-widget ui-state-default ui-corner-all ui-selectmenu-dropdown" style="margin-top:0px; text-align:center;" /> - <input id="max_level" value="90" size="2" onchange="filter();" class="ui-selectmenu ui-widget ui-state-default ui-corner-all ui-selectmenu-dropdown" style="margin-top:0px; text-align:center;" />'."\n";
        $out .= '      </div><br clear="all" />'."\n";
        $out .= '      <div class="easymemberslist_filterrow_left1">'.$roster->locale->act['race'].':</div>'."\n";        
        $out .= '      <div class="easymemberslist_filterrow_right1"><select id="race_filter" name="race_filer" onchange="easymemberslist_filter();">'."\n";
        $out .= '          <option value="999" selected="selected">'.$roster->locale->act['easymemberslist_selectall'].'</option>'."\n";        
        foreach($roster->locale->act['race_to_id'] as $race => $id)
            $out .= '          <option value="'.$id.'">'.$race.'</option>'."\n";
        $out .= '      </select>'."\n";
        $out .= '      </div><br clear="all" />'."\n";
        $out .= '      <div class="easymemberslist_filterrow_left2">'.$roster->locale->act['class'].':</div>'."\n";        
        $out .= '      <div class="easymemberslist_filterrow_right2"><select id="class_filter" name="class_filer" onchange="easymemberslist_filter();">'."\n";
        $out .= '          <option value="999" selected="selected">'.$roster->locale->act['easymemberslist_selectall'].'</option>'."\n";        
        foreach($roster->locale->act['class_to_id'] as $class => $id)
            $out .= '          <option value="'.$id.'">'.$class.'</option>'."\n";
        $out .= '      </select>'."\n";
        $out .= '      </div><br clear="all" />'."\n";
        $out .= '      <div class="easymemberslist_filterrow_left1">'.$roster->locale->act['rank'].':</div>'."\n";
        $out .= '      <div class="easymemberslist_filterrow_right1"><select id="guildrank" name="guildrank" onchange="easymemberslist_filter();">'."\n";
        $out .= '          <option value="999" selected="selected">'.$roster->locale->act['easymemberslist_selectall'].'</option>'."\n";        
        foreach($guildranks as $rank)
            $out .= '          <option value="'.$rank['guild_rank'].'">'.$rank['guild_title'].'</option>'."\n";
        $out .= '      </select></div><br clear="all" />'."\n";
        $out .= '      <div class="easymemberslist_filterfooter">'."\n";
        $out .= '          <div style="width:50%; float:left; text-align:left;"><button onclick="easymemberslist_resetfilter();">'.$roster->locale->act['pagebar_configreset'].'</button></div>'."\n";
        $out .= '          <div style="width:50%; float:left; text-align:right;"><button onclick="easymemberslist_filter();">'.$roster->locale->act['easymemberslist_btn_filter'].'</button></div>'."\n";
        $out .= '          <br clear="all" />'."\n";
        $out .= '      </div>'."\n";
        $out .= '  </div>'."\n";
        $out .= '</div>'."\n";
        $out .= '<div class="easymemberslist_header">'."\n";
        $out .= '  <div class="easymemberslist_header_name">'.$roster->locale->act['name'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_header_race">'.$roster->locale->act['race'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_header_class">'.$roster->locale->act['class'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_header_professions">'.$roster->locale->act['professions'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_header_level">'.$roster->locale->act['level'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_header_guildtitle">'.$roster->locale->act['rank'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_header_lastonline">'.$roster->locale->act['lastonline'].'</div>'."\n";
        $out .= '  <div class="easymemberslist_header_note">'.$roster->locale->act['note'].'</div>'."\n";
        $out .= '  <div class="clearall"></div>'."\n";
        $out .= '</div>'."\n";
        $out .= '<div id="members" class="memberlist_body"></div>'."\n";
        $out .= '<script type="text/javascript">'."\n";
        $out .= '   easymemberslist_filter();'."\n";
        $out .= '</script>'."\n";
        echo $out;
        break;
        
    case 'get_memberslist':
        $order_by = $easymemberslist->get_param('sort_order', 'string', 'request', 'm.name ASC');
        $where = $easymemberslist->get_param('where', 'string', 'request', '');
        $roster->config['logo'] = false;
        $roster->output['show_header'] = false;  // Turn off roster header
        $roster->output['show_menu'] = false;    // Turn off roster menu
        $roster->output['show_footer'] = false;  // Turn off roster footer        
        $membersdata = $easymemberslist->get_members($order_by, $where);
        $out = null;
        if (isset($membersdata) && !empty($membersdata) && is_array($membersdata))
        {
            $css_id = 0;
            foreach ($membersdata as $member)
            {
                if (isset($member['member_id']) && !empty($member['member_id']))
                {
                    $skills = $easymemberslist->get_character_skills($member['member_id']);
                    $class_name = $roster->locale->act['id_to_class'][$member['classid']];
                    $class_color = $roster->locale->act['class_colorArray'][$class_name];
                    $css_id++;
                    $out .= '<div class="easymemberslist_body_background_'.$css_id.'">'."\n"; 
                    if ($member['raceid'] > 0)
                        $out .= '   <div class="easymemberslist_body_name"><a href="./index.php?p=char-info&a=c:'.$member['member_id'].'" style="color:#'.$class_color.';">'.$member['name'].'</a></div>'."\n";
                    else
                        $out .= '   <div class="easymemberslist_body_name" style="color:#'.$class_color.';">'.$member['name'].'</div>'."\n";
                    $out .= '   <div class="easymemberslist_body_race"';
                    if ($member['raceid'] > 0)
                        $out .= 'style="background:url(\'./img/icons/race/'.$member['raceid'].'-'.$member['sexid'].'.gif\') no-repeat; background-position:center;" title="'.$member['race'].'"';
                    $out .= '   ></div>'."\n";
                    $out .= '   <div class="easymemberslist_body_class" style="background:url(\'./img/icons/class/'.$member['classid'].'.gif\') no-repeat; background-position:center;" title="'.$member['class'].'"></div>'."\n";
                    $out .= '   <div class="easymemberslist_body_professions">'."\n";
                    if  (isset($skills) && !empty($skills) && is_array($skills))
                        foreach ($skills as $skill)
                            $out .= '       <img src="'.$roster->config['interface_url'].'Interface/Icons/'.$easymemberslist->get_profession_icon($skill['skill_name'], $member['region']).'.png" style="width:20px; height:20px; padding-top:5px;" alt="'.$skill['skill_name'].'" title="'.$skill['skill_name'].' ('.$skill['skill_level'].')" />'."\n";
                    $out .= '   </div>'."\n";
                    $out .= '   <div class="easymemberslist_body_level">'.$member['level'].'</div>'."\n";
                    $out .= '   <div class="easymemberslist_body_guildtitle">'.$member['guild_title'].'</div>'."\n";
                    $out .= '   <div class="easymemberslist_body_lastonline">'.date($roster->locale->act['phptimeformat'], strtotime($member['last_online'])).'</div>'."\n";
                    $out .= '   <div class="easymemberslist_body_note'.(($member['note'] != '') ? ' show_note" title="'.$member['note'].'">' : ' no_note">').'</div>'."\n";
                    $out .= '   <div class="clearall"></div>'."\n";
                    $out .= '</div>'."\n";
                    if ($css_id >= 2) $css_id = 0;
                }
            }
            echo $out;
        }
        else echo '<div class="easymemberslist_body_background_1 easymemberslist_notice">'.$roster->locale->act['easymemberslist_no_data'].'</div>';
        break;    
}
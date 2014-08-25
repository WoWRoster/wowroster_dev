<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * deDE Locale
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: deDE.php 1701 2008-02-24 21:52:03Z Nefuh $
 * @link       http://www.wowroster.net
 * @package    GuildHistory
 * @subpackage Locale
*/

$lang['guildhistory'] = 'Gildengeschichte';
$lang['guildhistory_desc'] = 'Zeigt die Geschichte der Gilde (Gildenlog) an.';
$lang['guildhistorybutton'] = 'Gildengeschichte';

$lang['admin']['guildhistory_conf'] = 'Konfiguration|Hier kannst Du den Zugriffslevel oder das Anzeigeformat einstellen.';
$lang['admin']['guildhistory_access'] = 'Zugriff|Einstellung wer die Gildengeschichte sehen kann.';
$lang['admin']['guildhistory_format'] = 'Format|Gildengeschichte in Kurzform (short) oder als Blog (long)';
$lang['admin']['guildhistory_line_format'] = 'Textformat|Immer eine Zeile pro Eintrag oder Einträge eines Tages zusammenfassen (nur wenn Format auf long steht).';


$lang['join'] = 'wurde eingeladen.';
$lang['invite'] = 'eingeladen';
$lang['had'] = 'hat';
$lang['promote'] = 'befördert';
$lang['demote'] = 'degradiert';
$lang['to'] = 'zu';
$lang['quit'] = 'hat die Gilde verlassen.';
$lang['remove'] = 'entfernt';
$lang['no_data'] = 'Keine Historydaten vorhanden.<br /><b>Hinweis:</b><br />Für dieses Addon wird eine GuildProfiler-Version 2.3.1 oder höher benötigt.';
$lang['login'] = 'Du musst dich einloggen um die Gildengeschichte zu sehen.';
$lang['datetime'] = 'Datum & Uhrzeit';
$lang['action'] = 'Aktion';
$lang['sorting_asc'] = 'Aktuelle Sortierung aufsteigend';
$lang['sorting_desc'] = 'Aktuelle Sortierung absteigen';

// %1 = guildname, %2 = num_members, %3 = num_accounts
$lang['blog_header_text'] = 'Dies ist ein Auszug aus der Geschichte der Gilde %1.<br />Aktuell haben Wir %2 Mitglieder mit %3 Accounts.';


// Blog Single Format
// %1 = player1, %2 = player2, %3 = date, %4 = time, %5 = rank
$lang['blog_line_on_join_1'] = 'Am %3 um %4 Uhr ist %2 unsere Gilde beigetreten.<br />';
$lang['blog_line_on_remove_1'] = 'Am %3 um %4 Uhr wurde %2 von %1 aus unserer Gilde geworfen.<br />';
$lang['blog_line_on_quit_1'] = 'Am %3 um %4 Uhr hat %2 unsere Gilde verlassen.<br />';
$lang['blog_line_on_invite_1'] = 'Am %3 um %4 Uhr wurde %2 von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_line_on_demote_1'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 degradiert worden.<br />';
$lang['blog_line_on_promote_1'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 befördert worden.<br />';
$lang['blog_line_on_else_1'] = 'Unbekanntes Ereignis<br />';

$lang['blog_line_on_join_2'] = 'Am %3 um %4 Uhr erhielt mit %2 unsere Gilde verstärkung.<br />';
$lang['blog_line_on_remove_2'] = 'Am %3 um %4 Uhr musste %2 leider unsere Gilde verlassen. Dies wurde durch %1 vorgenommen.<br />';
$lang['blog_line_on_quit_2'] = 'Am %3 um %4 Uhr hat %2 leider unsere Gilde verlassen.<br />';
$lang['blog_line_on_invite_2'] = 'Am %3 um %4 Uhr wurde %2 von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_line_on_demote_2'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 degradiert worden.<br />';
$lang['blog_line_on_promote_2'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 befördert worden.<br />';
$lang['blog_line_on_else_2'] = 'Unbekanntes Ereignis<br />';

$lang['blog_line_on_join_3'] = 'Am %3 um %4 Uhr wurde durch %2 die Gemeinschaft unsere Gilde erweitert.<br />';
$lang['blog_line_on_remove_3'] = 'Am %3 um %4 Uhr musste %1 leider %2 aus der Gilde werfen.<br />';
$lang['blog_line_on_quit_3'] = 'Am %3 um %4 Uhr war ein trauriger Tag. %2 hat unsere Gilde verlassen.<br />';
$lang['blog_line_on_invite_3'] = 'Am %3 um %4 Uhr wurde %2 von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_line_on_demote_3'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 degradiert worden.<br />';
$lang['blog_line_on_promote_3'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 befördert worden.<br />';
$lang['blog_line_on_else_3'] = 'Unbekanntes Ereignis<br />';

$lang['blog_line_on_join_4'] = 'Am %3 um %4 Uhr war es soweit. %2 ist Mitglied in unsere Gilde geworden.<br />';
$lang['blog_line_on_remove_4'] = 'Am %3 um %4 Uhr musste %1 leider seiner Pflicht nachkommen und %2 aus unserer Gilde werfen.<br />';
$lang['blog_line_on_quit_4'] = 'Am %3 um %4 Uhr hat %2 auf eigenen Wunsch unsere Gilde verlassen.<br />';
$lang['blog_line_on_invite_4'] = 'Am %3 um %4 Uhr wurde %2 von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_line_on_demote_4'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 degradiert worden.<br />';
$lang['blog_line_on_promote_4'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 befördert worden.<br />';
$lang['blog_line_on_else_4'] = 'Unbekanntes Ereignis<br />';

$lang['blog_line_on_join_5'] = 'Am %3 um %4 Uhr ist %2 unsere Gilde beigetreten.<br />';
$lang['blog_line_on_remove_5'] = 'Am %3 um %4 Uhr wurde %2 von %1 aus unserer Gilde geworfen.<br />';
$lang['blog_line_on_quit_5'] = 'Am %3 um %4 Uhr hat %2 unsere Gilde verlassen.<br />';
$lang['blog_line_on_invite_5'] = 'Am %3 um %4 Uhr wurde %2 von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_line_on_demote_5'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 degradiert worden.<br />';
$lang['blog_line_on_promote_5'] = 'Am %3 um %4 Uhr ist %2 von %1 zu %5 befördert worden.<br />';
$lang['blog_line_on_else_5'] = 'Unbekanntes Ereignis<br />';


// Blog Block Format
// %1 = player1, %2 = player2, %3 = date, %4 = time, %5 = rank
$lang['blog_text_on_join_1'] = 'Heute ist %1 unsere Gilde beigetreten.<br />';
$lang['blog_text_on_remove_1'] = '%2 wurde von %1 aus unserer Gilde geworfen.<br />';
$lang['blog_text_on_quit_1'] = '%1 hat unsere Gilde verlassen.<br />';
$lang['blog_text_on_invite_1'] = '%2 wurde von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_text_on_demote_1'] = '%1 degradierte %2 zu %5.<br />';
$lang['blog_text_on_promote_1'] = '%1 beförderte %2 zu %5.<br />';
$lang['blog_text_on_else_1'] = 'Unbekanntes Ereignis<br />';

$lang['blog_text_on_join_2'] = 'Heute ist %1 unsere Gilde beigetreten.<br />';
$lang['blog_text_on_remove_2'] = '%2 wurde von %1 aus unserer Gilde geworfen.<br />';
$lang['blog_text_on_quit_2'] = '%1 hat unsere Gilde verlassen.<br />';
$lang['blog_text_on_invite_2'] = '%2 wurde von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_text_on_demote_2'] = '%1 degradierte %2 zu %5.<br />';
$lang['blog_text_on_promote_2'] = '%1 beförderte %2 zu %5.<br />';
$lang['blog_text_on_else_2'] = 'Unbekanntes Ereignis<br />';

$lang['blog_text_on_join_3'] = 'Heute ist %1 unsere Gilde beigetreten.<br />';
$lang['blog_text_on_remove_3'] = '%2 wurde von %1 aus unserer Gilde geworfen.<br />';
$lang['blog_text_on_quit_3'] = '%1 hat unsere Gilde verlassen.<br />';
$lang['blog_text_on_invite_3'] = '%2 wurde von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_text_on_demote_3'] = '%1 degradierte %2 zu %5.<br />';
$lang['blog_text_on_promote_3'] = '%1 beförderte %2 zu %5.<br />';
$lang['blog_text_on_else_3'] = 'Unbekanntes Ereignis<br />';

$lang['blog_text_on_join_4'] = 'Heute ist %1 unsere Gilde beigetreten.<br />';
$lang['blog_text_on_remove_4'] = '%2 wurde von %1 aus unserer Gilde geworfen.<br />';
$lang['blog_text_on_quit_4'] = '%1 hat unsere Gilde verlassen.<br />';
$lang['blog_text_on_invite_4'] = '%2 wurde von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_text_on_demote_4'] = '%1 degradierte %2 zu %5.<br />';
$lang['blog_text_on_promote_4'] = '%1 beförderte %2 zu %5.<br />';
$lang['blog_text_on_else_4'] = 'Unbekanntes Ereignis<br />';

$lang['blog_text_on_join_5'] = 'Heute ist %1 unsere Gilde beigetreten.<br />';
$lang['blog_text_on_remove_5'] = '%2 wurde von %1 aus unserer Gilde geworfen.<br />';
$lang['blog_text_on_quit_5'] = '%1 hat unsere Gilde verlassen.<br />';
$lang['blog_text_on_invite_5'] = '%2 wurde von %1 in unsere Gilde eingeladen.<br />';
$lang['blog_text_on_demote_5'] = '%1 degradierte %2 zu %5.<br />';
$lang['blog_text_on_promote_5'] = '%1 beförderte %2 zu %5.<br />';
$lang['blog_text_on_else_5'] = 'Unbekanntes Ereignis<br />';

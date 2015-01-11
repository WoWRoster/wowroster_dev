<?php

// Button names
$lang['rsync_button1']	= 'Charakter updaten|Synchronisiere deinen Charakter mit dem Arsenal';
$lang['rsync_button2']	= 'Mitglieder updaten|Synchronisiere deine Gildenmitglieder mit dem Arsenal';
$lang['rsync_button3']	= 'Gilde hinzufügen|Gilde per Arsenal hinzufügen';
$lang['rsync_button4']	= 'Gilde aktualisieren|Gilde per Arsenal aktualisieren';


$lang['admin']['rsync_conf']			= 'Konfiguration';
$lang['admin']['rsync_ranks']			= 'Sync Ränge';
$lang['admin']['rsync_scaning']			= 'Scanne Einstellungen';
$lang['admin']['rsync_scan_guild']		= 'Gilden Felder';
$lang['admin']['rsync_scan_char']		= 'Charakter Felder';
$lang['admin']['rsync_access']			= 'Update Zugriff';
$lang['admin']['rsync_debug']			= 'Debug Einstellungen';

$lang['admin']['rsync_host']			= 'Host|Host, mit welchem synchronisiert werden soll';
$lang['admin']['rsync_minlevel']		= 'Mind. Level|Minimum Level das ein Charakter zum aktualisieren haben muss<br>Aktuell mindestens Level 10,<br>da das Arsenal kleinere Chars nicht anzeigt.';
$lang['admin']['rsync_synchcutofftime']	= 'Sync Grenzzeit|Zeit in Tagen<br>Alle Charakter die nicht innerhalb dieser Zeit aktualisiert wurden, werden nicht abgerufen.';
$lang['admin']['rsync_use_ajax']		= 'Benutze AJAX|AJAX für Status Update benutzen oder nicht.';
$lang['admin']['rsync_reloadwaittime']	= 'Verzögerungszeit|Zeit in Sekunden<br>Zeit in Sekunden zwischen zwei Aktualisierungen. Empfohlen 24+';
$lang['admin']['rsync_fetch_timeout'] 	= 'Arsenal Abruf Zeit&uml;berschreitung|Zeit in Sekunden bis die Aktualisierung einer XML-Datei abgebrochen wird.';
$lang['admin']['rsync_skip_start']		= 'überspringe Startseite|überspringe die Startseite von Roster Sync.';
$lang['admin']['rsync_status_hide'] 	= 'Verstecke Statusfenster standardmäßig|Versteckt das Statusfenster von Roster Sync beim ersten laden.';
$lang['admin']['rsync_protectedtitle']	= 'Geschützter Gildenrang|Charakter mit diesem Gildenrang sind geschützt<br />vor dem Löschen während eines Arsenal Updaptes.<br />Das Problem betrifft öfters Bankcharakter.<br />Mehrere Ränge per Komma trennen (,) \"Banker,Stock\"';

$lang['admin']['rsync_scaning']			= 'Update Konfig.';
$lang['admin']['rsync_MinLvl']			= 'Min Level';
$lang['admin']['rsync_MaxLvl']			= 'Max Level';
$lang['admin']['rsync_Rank']			= 'Ränge';
$lang['admin']['rsync_Class']			= 'Klassen';

$lang['admin']['rsync_rank_set_order']	= "Gildenrang Reihenfolge|Definiet die Reihenfolge in welcher die Gildenränge gesetzt werden.";
$lang['admin']['rsync_rank_0']			= "Titel Gildenmeister|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_1']			= "Titel Rang 1|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_2']			= "Titel Rang 2|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_3']			= "Titel Rang 3|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_4']			= "Titel Rang 4|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_5']			= "Titel Rang 5|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_6']			= "Titel Rang 6|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_7']			= "Titel Rang 7|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_8']			= "Titel Rang 8|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";
$lang['admin']['rsync_rank_9']			= "Titel Rang 9|Dieser Titel wird gesetzt, wenn im WoWRoster kein Titel definiert wurde.";


/*
Player scan settings
*/
$lang['admin']['rsync_char_achievements']	= 'Erfolge|Eine Karte von Erfolgsdaten inkl. Zeitstempel und Kriterien.';
$lang['admin']['rsync_char_appearance']		= 'Aussehen|Scannt die Items, welche ein Charakter trägt.';
$lang['admin']['rsync_char_feed']			= 'Aktivitäts-Feed|Der Aktivitäts-Feed des Charakters.';
$lang['admin']['rsync_char_guild']			= 'Gildendaten|Eine Zusammenstellung von Gildendaten des Charakters. Wenn der Charakter in keiner Gilde im WoWRoster ist, wird diese Feld nicht angezeigt.';
$lang['admin']['rsync_char_hunterPets']		= 'Jäger Begleiter|Eine Liste aller Begleiter im Besitzt des Charakters.';
$lang['admin']['rsync_char_items']			= 'Ausrüstung|Eine Liste aller angelegten Items des Charakters. Beeinhaltet den durchschnittliche Itemlevel für angelegte Items und Items in den Taschen.';
$lang['admin']['rsync_char_mounts']			= 'Reittiere|Eine Liste aller Reittiere die ein Charakter besitzt.';
$lang['admin']['rsync_char_pets']			= 'Tiere|Eine Liste aller Tiere, welcher ein Charakter besitzt.';
$lang['admin']['rsync_char_petSlots']		= 'Kampfhaustiere|Daten über das aktuellen Kampfhaustier-Team des Charakter-Accounts.';
$lang['admin']['rsync_char_professions']	= 'Berufe|Eine Liste der Charakterberufe. Wichtig: Das beeinhaltet ebenso alle bekannte Rezepte eines jeden Berufes, welchen der Charakter besitzt.';
$lang['admin']['rsync_char_progression']	= 'Raid Progress|Eine Liste von Raids und Bossen, welchen den Fortschritt in den Raids anzeigen.';
$lang['admin']['rsync_char_pvp']			= 'PvP|Informationen zum PvP eines Charakter inkl. Arena Teams und gewerteten Schlachtfeldern.';
$lang['admin']['rsync_char_quests']			= 'Quest Daten|Eine Liste mit allen Quests, welche der Charakter bereits abgeschlossen hat.';
$lang['admin']['rsync_char_reputation']		= 'Ruf|Eine Liste aller Fraktionen, bei welcher der Charakter einen Ruf besitzt.';
$lang['admin']['rsync_char_stats']			= 'Stats|Charakter Attribute und Statistiken.';
$lang['admin']['rsync_char_talents']		= 'Talente|Talentstruktur des Charakters.';
$lang['admin']['rsync_char_titles']			= 'Titel|Eine Liste aller Titel, über welche der Charakter verfügt inkl. dem aktuell ausgewählten.';
$lang['admin']['rsync_char_audit']			= 'Pr&uml;fung|Roh-Daten des Charakters auf der Spieleseite';
/*
	guild scan settings
*/
$lang['admin']['rsync_guild_members']		= 'Mitglieder|Eine Liste von Charakteren, welche Mitglieder der Gilde sind';
$lang['admin']['rsync_guild_achievements']	= 'Erfolge|Eine Liste von Gildenerfolgen.';
$lang['admin']['rsync_guild_news']			= 'News|Newsfeed der Gilde.';
$lang['admin']['rsync_guild_challenge']		= 'Herausforderungen|Die Top 3 Gildenruns für jeden Herausforderungsmodus.';

/*
Debug Info
*/
$lang['admin']['rsync_debuglevel']		= 'Debug Level|Debug Level für Roster Sync einstellen.<br /><br />Quiete - Keine Nachrichten<br />Base Info - Basis Nachrichten<br />Armory & Job Method Info - Alle Arsenal und Job Methoden<br />All Methods Info - Nachrichten aller Methoden  <b style="color:red;">(Vorsicht - sehr viel Daten)</b>';
$lang['admin']['rsync_debugdata']		= 'Debug Data|Debug Ausgabe erhöhlen um Methoden Argumente und Rückgabewerte<br /><b style="color:red;">(Vorsicht - sehr viel Informationen auf höherem Debug Level)</b>';
$lang['admin']['rsync_javadebug']		= 'Debug Java|Aktiviere JavaScript debugging.<br />Derzeit noch nicht realisiert.';
$lang['admin']['rsync_xdebug_php']		= 'XDebug Session PHP|Aktiviere das Senden von XDEBUG Variable mit POST.';
$lang['admin']['rsync_xdebug_ajax']		= 'XDebug Session AJAX|Aktiviere das Senden XDEBUG Variable mit AJAX POST.';
$lang['admin']['rsync_xdebug_idekey']	= 'XDebug Session IDEKEY|Definiere IDEKEY für Xdebug sessions.';
$lang['admin']['rsync_sqldebug']		= 'SQL Debug|Aktiviere SQL debugging für Roster Sync.<br />Nicht sinnvoll in Kombination mit Roster SQL debugging / doppelte Daten.';
$lang['admin']['rsync_updateroster']	= "Update Roster|Aktivierte Roster Updates.<br />Gut zur Fehlerbeseitigung<br />Derzeit noch nicht realisiert.";


/*
update access
*/
$lang['admin']['rsync_char_update_access'] 				= 'Char Update Zugriff|Wer darf Charaktere aktualisieren';
$lang['admin']['rsync_guild_update_access'] 			= 'Gilden Update Zugriff|Wer darf Gilden aktualisieren';
$lang['admin']['rsync_guild_memberlist_update_access'] 	= 'Gildenmitgliederliste Zugriff|Wer darf die Mitgliederliste einer Gilde aktualisieren';
$lang['admin']['rsync_realm_update_access'] 			= 'Realm Update Zugriff|Wer darf Realmweite Aktualisierungen durchführen';
$lang['admin']['rsync_guild_add_access'] 				= 'Gilde hinzufügen Zugriff|Wer darf neue Gilden zum Roster hinzufügen';

$lang['start'] = "Start";
$lang['start_message'] = "Du bist im Begriff Roster Sync für %s zu starten.<br><br>Alle Daten für %s werden hierbei überschrieben<br />mit den Daten aus dem Arsenal. Dies kann nur durch das Hochladen<br />einer aktuellen wowroster.lua rückhängig gemacht werden.<br /><br />Aktualisierung nun starten?";

$lang['start_message_the_char']			= "der Charakter";
$lang['start_message_this_char']		= "dieser Charakter";
$lang['start_message_the_guild']		= "die Gilde";
$lang['start_message_this_guild']		= "alle Charakter dieser Gilde";
$lang['start_message_the_memberlist']	= "die Gildenmitgliederliste";
$lang['start_message_this_memberlist']	= "die Gildenmitgliederliste";

$lang['start_message_the_profile']		= "Profile";
$lang['start_message_the_gprofile']		= "Gildenprofile";
$lang['start_message_the_addguild']		= "Füge Gilde hinzu";
$lang['start_message_the_memberlist']	= "Gildenmitgliederliste";

$lang['start_message_this_profile']		= "dieser Charakter";
$lang['start_message_this_gprofile']	= "der Gildencharakter";
$lang['start_message_this_memberlist']	= "die Gildenmitglieder";
$lang['start_message_this_addguild']	= "Gildendaten für diese Gilde";

$lang['rep'] = array(
    "Wildhammer Clan"				=> array ("parent" => "Cataclysm"),
	"Baradin's Wardens"				=> array ("parent" => "Cataclysm"),
	"The Earthen Ring"				=> array ("parent" => "Cataclysm"),
	"Avengers of Hyjal"				=> array ("parent" => "Cataclysm"),
	"Ramkahen"						=> array ("parent" => "Cataclysm"),
	"Guardians of Hyjal"			=> array ("parent" => "Cataclysm"),
	"Therazane"						=> array ("parent" => "Cataclysm"),
	"Knights of the Ebon Blade"		=> array ("parent" => "Wrath of the Lich King"),
	"Argent Crusade"				=> array ("parent" => "Wrath of the Lich King"),
	"The Kalu'ak"					=> array ("parent" => "Wrath of the Lich King"),
	"The Sons of Hodir"				=> array ("parent" => "Wrath of the Lich King"),
	"Alliance Vanguard"				=> array ("parent" => "Wrath of the Lich King"),
	"Valiance Expedition"			=> array ("parent" => "Wrath of the Lich King","faction" => "Alliance Vanguard"),
	"Explorers' League"				=> array ("parent" => "Wrath of the Lich King","faction" => "Alliance Vanguard"),
	"The Silver Covenant"			=> array ("parent" => "Wrath of the Lich King","faction" => "Alliance Vanguard"),
	"The Frostborn"					=> array ("parent" => "Wrath of the Lich King","faction" => "Alliance Vanguard"),
	"Kirin Tor"						=> array ("parent" => "Wrath of the Lich King"),
	"Sholazar Basin"				=> array ("parent" => "Wrath of the Lich King"),
	"Frenzyheart Tribe"				=> array ("parent" => "Wrath of the Lich King","faction" => "Sholazar Basin"),
	"The Oracles"					=> array ("parent" => "Wrath of the Lich King","faction" => "Sholazar Basin"),
	"The Wyrmrest Accord"			=> array ("parent" => "Wrath of the Lich King"),
	"The Ashen Verdict"				=> array ("parent" => "Wrath of the Lich King"),
	"The Violet Eye"				=> array ("parent" => "The Burning Crusade"),
	"Sporeggar"						=> array ("parent" => "The Burning Crusade"),
	"Ashtongue Deathsworn"			=> array ("parent" => "The Burning Crusade"),
	"Ogri'la"						=> array ("parent" => "The Burning Crusade"),
	"The Scale of the Sands"		=> array ("parent" => "The Burning Crusade"),
	"Shattrath City"				=> array ("parent" => "The Burning Crusade"),
	"The Sha'tar"					=> array ("parent" => "The Burning Crusade","faction" => "Shattrath City"),
	"Shattered Sun Offensive"		=> array ("parent" => "The Burning Crusade","faction" => "Shattrath City"),
	"The Scryers"					=> array ("parent" => "The Burning Crusade","faction" => "Shattrath City"),
	"Lower City"					=> array ("parent" => "The Burning Crusade","faction" => "Shattrath City"),
	"The Aldor"						=> array ("parent" => "The Burning Crusade","faction" => "Shattrath City"),
	"Sha'tari Skyguard"				=> array ("parent" => "The Burning Crusade","faction" => "Shattrath City"),
	"Kurenai"						=> array ("parent" => "The Burning Crusade"),
	"Netherwing"					=> array ("parent" => "The Burning Crusade"),
	"The Consortium"				=> array ("parent" => "The Burning Crusade"),
	"Keepers of Time"				=> array ("parent" => "The Burning Crusade"),
	"Honor Hold"					=> array ("parent" => "The Burning Crusade"),
	"Cenarion Expedition"			=> array ("parent" => "The Burning Crusade"),
	"Thorium Brotherhood"			=> array ("parent" => "Classic"),
	"Bloodsail Buccaneers"			=> array ("parent" => "Classic"),
	"Cenarion Circle"				=> array ("parent" => "Classic"),
	"Darkmoon Faire"				=> array ("parent" => "Classic"),
	"Gelkis Clan Centaur"			=> array ("parent" => "Classic"),
	"Magram Clan Centaur"			=> array ("parent" => "Classic"),
	"Alliance Forces"				=> array ("parent" => "Classic"),
	"Stormpike Guard"				=> array ("parent" => "Classic","faction" => "Alliance Forces"),
	"Silverwing Sentinels"			=> array ("parent" => "Classic","faction" => "Alliance Forces"),
	"The League of Arathor"			=> array ("parent" => "Classic","faction" => "Alliance Forces"),
	"Bizmo's Brawlpub"				=> array ("parent" => "Classic","faction" => "Alliance Forces"),
	"Timbermaw Hold"				=> array ("parent" => "Classic"),
	"Zandalar Tribe"				=> array ("parent" => "Classic"),
	//"Alliance"					=> array ("parent" => "Classic"),
	"Exodar"						=> array ("parent" => "Classic","faction" => "Alliance"),
	"Gilneas"						=> array ("parent" => "Classic","faction" => "Alliance"),
	"Gnomeregan"					=> array ("parent" => "Classic","faction" => "Alliance"),
	"Stormwind"						=> array ("parent" => "Classic","faction" => "Alliance"),
	"Ironforge"						=> array ("parent" => "Classic","faction" => "Alliance"),
	"Darnassus"						=> array ("parent" => "Classic","faction" => "Alliance"),
	"Tushui Pandaren"				=> array ("parent" => "Classic","faction" => "Alliance"),
	"Shen'dralar"					=> array ("parent" => "Classic"),
	"Brood of Nozdormu"				=> array ("parent" => "Classic"),
	"Ravenholdt"					=> array ("parent" => "Classic"),
	"Steamwheedle Cartel"			=> array ("parent" => "Classic"),
	"Everlook"						=> array ("parent" => "Classic","faction" => "Steamwheedle Cartel"),
	"Gadgetzan"						=> array ("parent" => "Classic","faction" => "Steamwheedle Cartel"),
	"Ratchet"						=> array ("parent" => "Classic","faction" => "Steamwheedle Cartel"),
	"Booty Bay"						=> array ("parent" => "Classic","faction" => "Steamwheedle Cartel"),
	"Hydraxian Waterlords"			=> array ("parent" => "Classic"),
	"Argent Dawn"					=> array ("parent" => "Classic"),
	"Wintersaber Trainers"			=> array ("parent" => "Other"),
	"Syndicate"						=> array ("parent" => "Other"),
	"Nomi"							=> array ("parent" => "Other"),
	"The August Celestials" 		=> array ("parent" => "Mists of Pandaria"),
	"Order of the Cloud Serpent" 	=> array ("parent" => "Mists of Pandaria"),
	"The Klaxxi" 					=> array ("parent" => "Mists of Pandaria"),
	"Golden Lotus" 					=> array ("parent" => "Mists of Pandaria"),
	"The Lorewalkers" 				=> array ("parent" => "Mists of Pandaria"),
	"Shado-Pan" 					=> array ("parent" => "Mists of Pandaria"),
	"The Black Prince" 				=> array ("parent" => "Mists of Pandaria"),
	"Shang Xi's Academy"			=> array ("parent" => "Mists of Pandaria"),
	"Akama's Trust" 				=> array ("parent" => "Mists of Pandaria"),
	"Dominance Offensive" 			=> array ("parent" => "Mists of Pandaria"),
	"Forest Hozen" 					=> array ("parent" => "Mists of Pandaria"),
	"Operation: Shieldwall" 		=> array ("parent" => "Mists of Pandaria"),
	"The Brewmasters" 				=> array ("parent" => "Mists of Pandaria"),
	"The Anglers" 					=> array ("parent" => "Mists of Pandaria","faction" => "The Anglers"),
	"Nat Pagle" 					=> array ("parent" => "Mists of Pandaria","faction" => "The Anglers"),
	"The Tillers" 					=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Fish Fellreed" 				=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Chee Chee" 					=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Haohan Mudclaw" 				=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Old Hillpaw" 					=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Farmer Fung"					=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Gina Mudclaw" 					=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Sho" 							=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Tina Mudclaw"					=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Jogu the Drunk" 				=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
	"Ella" 							=> array ("parent" => "Mists of Pandaria","faction" => "The Tillers"),
		"Arakkoa Outcasts" 				=> array ("parent" => "Warlords of Draenor"),
	"Barracks Bodyguards" 			=> array ("parent" => "Warlords of Draenor"),
	"Council of Exarchs" 			=> array ("parent" => "Warlords of Draenor"),
	"Sha'tari Defense" 				=> array ("parent" => "Warlords of Draenor"),
	"Shadowmoon Exiles" 			=> array ("parent" => "Warlords of Draenor"),
	"Steamwheedle Preservation Society" 				=> array ("parent" => "Warlords of Draenor"),
	"Wrynn's Vanguard" 				=> array ("parent" => "Warlords of Draenor"),
	"Guild" 		=> array ("parent" => "Guild"),);
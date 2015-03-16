<?php
/**
 * WoWRoster.net WoWRoster
*/

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

/**
 * News Addon Installer
 */
class newsInstall
{
	var $active = true;
	var $icon = 'ability_warrior_rallyingcry';

	var $version = '0.2.8';
	var $wrnet_id = '0';

	var $fullname = 'WoWRoster Portal';
	var $description = 'A \'front page\' for WoWRoster. Display user controls, post news and slideshow images.';
	var $credits = array(
		array("name"=>	"Ulminia",
				"info"=>	"Original author")
	);


	/**
	 * Install function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;

		$installer->add_config("'1','startpage','cmsnews_conf','display','master'");
		$installer->add_config("'100','cmsnews_conf',NULL,'blockframe','menu'");
		$installer->add_config("'200','cmsnews_slider',NULL,'blockframe','menu'");
		$installer->add_config("'300','cmsnews_slider_images','rostercp-addon-news-sliderimages','makelink','menu'");
		$installer->add_config("'400','cmsnews_slider_add','rostercp-addon-news-slideradd','makelink','menu'");
		$installer->add_config("'500','cmsnews_plugins','rostercp-addon-news-plugins','makelink','menu'");

		$installer->add_config("'1000','news_add','11','access','cmsnews_conf'");
		$installer->add_config("'1010','news_edit','11','access','cmsnews_conf'");
		$installer->add_config("'1020','comm_add','0','access','cmsnews_conf'");
		$installer->add_config("'1030','comm_edit','11','access','cmsnews_conf'");
		$installer->add_config("'1040','news_html','1','radio{enabled^1|disabled^0|forbidden^-1','cmsnews_conf'");
		$installer->add_config("'1050','comm_html','-1','radio{enabled^1|disabled^0|forbidden^-1','cmsnews_conf'");
		$installer->add_config("'1060','news_nicedit','1','radio{enabled^1|disabled^0', 'cmsnews_conf'");


		$installer->create_table($installer->table('config'),"
			`guild_id` int(11) unsigned NOT NULL DEFAULT '0',
			`config_name` varchar(64) NOT NULL DEFAULT '',
			`config_value` varchar(225) NOT NULL DEFAULT '',
			PRIMARY KEY (`guild_id`,`config_name`)");

		$installer->create_table($installer->table('news'),"
			`news_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(255) DEFAULT NULL,
			`title` varchar(200) DEFAULT NULL,
			`text` longtext,
			`news_type` varchar(25) DEFAULT NULL,
			`comm_count` int(11) unsigned NOT NULL,
			`poster` varchar(100) DEFAULT NULL,
			`date` datetime DEFAULT NULL,
			`html` tinyint(1),
			PRIMARY KEY (`news_id`)");

		$installer->create_table($installer->table('comments'),"
			`comment_id` int(11) unsigned AUTO_INCREMENT,
			`news_id` int(11) unsigned NOT NULL,
			`author` varchar(16) NOT NULL DEFAULT '',
			`date` datetime,
			`content` longtext,
			`html` tinyint(1),
			PRIMARY KEY (`comment_id`)");

		$installer->add_menu_button('cms_button', 'guild');

		return true;
	}

	/**
	 * Upgrade functoin
	 *
	 * @param string $oldversion
	 * @return bool
	 */
	function upgrade($oldversion)
	{
		global $installer, $roster;
		return true;
	}

	/**
	 * Un-Install function
	 *
	 * @return bool
	 */
	function uninstall()
	{
		global $installer;
		$installer->drop_table($installer->table('config'));
		$installer->drop_table($installer->table('comments'));
		$installer->drop_table($installer->table('news'));
		$installer->remove_all_config();

		$installer->remove_all_menu_button();
		return true;
	}
}

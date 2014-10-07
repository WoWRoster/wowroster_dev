<?php


if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

class raidboxInstall
{
	var $active = true;
	var $icon = 'inv_misc_note_05';

	var $version = '1.5.4';
	var $wrnet_id = '0';

	var $fullname = 'raidbox';
	var $description = 'raidbox_de';
	var $credits = array(
		array(	"name"=>	"ulminia",
				"info"=>	"Original Author")
	);


	/**
	 * Install Function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;
		
		//if you want you addon to use a unique number try the download idfallowed by 000 and ex 31000 and increase from 1 from there
		$installer->add_config("31000,'startpage','display','display','master'");

		# Config menu entries
		$installer->add_config("31001,'mv',NULL,'blockframe','menu'");
		$installer->add_config("31002,'mv_boss_1','0','radio{Down^1|Up^0','mv'");
		$installer->add_config("31003,'mv_boss_2','0','radio{Down^1|Up^0','mv'");
		$installer->add_config("31004,'mv_boss_3','0','radio{Down^1|Up^0','mv'");
		$installer->add_config("31005,'mv_boss_4','0','radio{Down^1|Up^0','mv'");
		$installer->add_config("31006,'mv_boss_5','0','radio{Down^1|Up^0','mv'");
		$installer->add_config("31007,'mv_boss_6','0','radio{Down^1|Up^0','mv'");
		
		$installer->add_config("31011,'hof',NULL,'blockframe','menu'");
		$installer->add_config("31012,'hof_boss_1','0','radio{Down^1|Up^0','hof'");
		$installer->add_config("31013,'hof_boss_2','0','radio{Down^1|Up^0','hof'");
		$installer->add_config("31014,'hof_boss_3','0','radio{Down^1|Up^0','hof'");
		$installer->add_config("31015,'hof_boss_4','0','radio{Down^1|Up^0','hof'");
		$installer->add_config("31016,'hof_boss_5','0','radio{Down^1|Up^0','hof'");
		$installer->add_config("31017,'hof_boss_6','0','radio{Down^1|Up^0','hof'");
		
		$installer->add_config("31021,'toes',NULL,'blockframe','menu'");
		$installer->add_config("31022,'toes_boss_1','0','radio{Down^1|Up^0','toes'");
		$installer->add_config("31023,'toes_boss_2','0','radio{Down^1|Up^0','toes'");
		$installer->add_config("31024,'toes_boss_3','0','radio{Down^1|Up^0','toes'");
		$installer->add_config("31025,'toes_boss_4','0','radio{Down^1|Up^0','toes'");

		$installer->add_config("31031,'tot',NULL,'blockframe','menu'");
		$installer->add_config("31032,'tot_boss_1','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31033,'tot_boss_2','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31034,'tot_boss_3','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31035,'tot_boss_4','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31036,'tot_boss_5','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31037,'tot_boss_6','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31038,'tot_boss_7','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31039,'tot_boss_8','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31040,'tot_boss_9','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31041,'tot_boss_10','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31042,'tot_boss_11','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31043,'tot_boss_12','0','radio{Down^1|Up^0','tot'");
		$installer->add_config("31044,'tot_boss_13','0','radio{Down^1|Up^0','tot'");
		
		$installer->add_config("31050,'soo',NULL,'blockframe','menu'");
		$installer->add_config("31051,'soo_boss_1','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31052,'soo_boss_2','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31053,'soo_boss_3','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31054,'soo_boss_4','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31055,'soo_boss_5','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31056,'soo_boss_6','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31057,'soo_boss_7','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31058,'soo_boss_8','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31059,'soo_boss_9','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31060,'soo_boss_10','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31061,'soo_boss_11','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31062,'soo_boss_12','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31063,'soo_boss_13','0','radio{Down^1|Up^0','soo'");
		$installer->add_config("31064,'soo_boss_14','0','radio{Down^1|Up^0','soo'");

		$installer->create_table($installer->table('bosses'),"
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`member_id` int(11) unsigned NOT NULL default '0',
			`type` varchar(96) NOT NULL default '',
			KEY `id` (`id`)");


		$installer->add_menu_button('rb_char','char');
		$installer->add_menu_button('rb_guild','guild');
		return true;
	}

	/**
	 * Upgrade Function
	 *
	 * @param string $oldversion
	 * @return bool
	 */
	function upgrade($oldversion)
	{
		global $installer;

			if( version_compare('1.1', $oldversion,'>') == true )
			{
				$installer->add_config("31031,'tot',NULL,'blockframe','menu'");
				$installer->add_config("31032,'tot_boss_1','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31033,'tot_boss_2','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31034,'tot_boss_3','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31035,'tot_boss_4','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31036,'tot_boss_5','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31037,'tot_boss_6','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31038,'tot_boss_7','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31039,'tot_boss_8','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31040,'tot_boss_9','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31041,'tot_boss_10','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31042,'tot_boss_11','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31043,'tot_boss_12','0','radio{Down^1|Up^0','tot'");
				$installer->add_config("31044,'tot_boss_13','0','radio{Down^1|Up^0','tot'");
			}
			if( version_compare('1.5.4', $oldversion,'>') == true )
			{
				$installer->add_config("31050,'soo',NULL,'blockframe','menu'");
				$installer->add_config("31051,'soo_boss_1','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31052,'soo_boss_2','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31053,'soo_boss_3','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31054,'soo_boss_4','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31055,'soo_boss_5','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31056,'soo_boss_6','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31057,'soo_boss_7','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31058,'soo_boss_8','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31059,'soo_boss_9','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31060,'soo_boss_10','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31061,'soo_boss_11','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31062,'soo_boss_12','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31063,'soo_boss_13','0','radio{Down^1|Up^0','soo'");
				$installer->add_config("31064,'soo_boss_14','0','radio{Down^1|Up^0','soo'");
			}
		
		return true;
	}

	/**
	 * Un-Install Function
	 *
	 * @return bool
	 */
	function uninstall()
	{
		global $installer;

		$installer->drop_table($installer->table('bosses'));
		$installer->remove_all_config();
		$installer->remove_all_menu_button();
		return true;
	}
}

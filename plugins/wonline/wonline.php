<?php
	
class wonline
{

	var $output;
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'wonline';
	var $filename = 'main-guild-wonline.php';
	var $parent = 'main';
	var $icon = 'inv_misc_groupneedmore';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'Whos Online';
	var $description = 'Displays whos online on the site';
	var $credits = array(
		array(	"name"=>	"Ulminia <Ulminia@gmail.com>",
				"info"=>	"Feeds (Alpha Release)"),
	);

	var $config = array();
	/*
	*	__construct
	*	this is there the veriables for the addons are 
	*	set in the plugin these are unique to each addon 
	*
	*	contact the addon author is you have a sugestion 
	*	as to where plugin code should occure or use there descression
	*/
	
	public function __construct($pdata)
	{
		global $roster;
		
		//$this->config = $pdata['config'];
		$this->display();
	}

	function display ()
	{
		global $roster;
		$output='';
		
		$bots = array(
			array('agent' => 'AdsBot-Google', 'name' =>'AdsBot [Google]'),
			array('agent' => 'ia_archiver', 'name' =>'Alexa [Bot]'),
			array('agent' => 'Scooter/', 'name' =>'Alta Vista [Bot]'),
			array('agent' => 'Ask Jeeves', 'name' =>'Ask Jeeves [Bot]'),
			array('agent' => 'Baiduspider+(', 'name' =>'Baidu [Spider]'),
			array('agent' => 'Exabot/', 'name' =>'Exabot [Bot]'),
			array('agent' => 'FAST Enterprise Crawler', 'name' =>'FAST Enterprise [Crawler]'),
			array('agent' => 'FAST-WebCrawler/', 'name' =>'FAST WebCrawler [Crawler]'),
			array('agent' => 'http://www.neomo.de/', 'name' =>'Francis [Bot]'),
			array('agent' => 'Gigabot/', 'name' =>'Gigabot [Bot]'),
			array('agent' => 'Mediapartners-Google', 'name' =>'Google Adsense [Bot]'),
			array('agent' => 'Google Desktop', 'name' =>'Google Desktop'),
			array('agent' => 'Feedfetcher-Google', 'name' =>'Google Feedfetcher'),
			array('agent' => 'Googlebot', 'name' =>'Google [Bot]'),
			array('agent' => 'heise-IT-Markt-Crawler Heise', 'name' =>'IT-Markt [Crawler]'),
			array('agent' => 'heritrix/1.', 'name' =>'Heritrix [Crawler]'),
			array('agent' => 'ibm.com/cs/crawler', 'name' =>'IBM Research [Bot]'),
			array('agent' => 'ICCrawler - ICjobs', 'name' =>'ICCrawler - ICjobs'),
			array('agent' => 'ichiro/', 'name' =>'ichiro [Crawler]'),
			array('agent' => 'MJ12bot/', 'name' =>'Majestic-12 [Bot]'),
			array('agent' => 'MetagerBot/', 'name' =>'Metager [Bot]'),
			array('agent' => 'msnbot-NewsBlogs/', 'name' =>'MSN NewsBlogs'),
			array('agent' => 'msnbot/', 'name' =>'MSN [Bot]'),
			array('agent' => 'msnbot-media/', 'name' =>'MSNbot Media'),
			array('agent' => 'NG-Search/', 'name' =>'NG-Search [Bot]'),
			array('agent' => 'http://lucene.apache.org/nutch/', 'name' =>'Nutch [Bot]'),
			array('agent' => 'NutchCVS/', 'name' =>'Nutch/CVS [Bot]'),
			array('agent' => 'OmniExplorer_Bot/', 'name' =>'OmniExplorer [Bot]'),
			array('agent' => 'online link validator', 'name' =>'Online link [Validator]'),
			array('agent' => 'psbot/0', 'name' =>'psbot [Picsearch]'),
			array('agent' => 'Seekbot/', 'name' =>'Seekport [Bot]'),
			array('agent' => 'Sensis Web Crawler', 'name' =>'Sensis [Crawler]'),
			array('agent' => 'SEO search Crawler/', 'name' =>'SEO Crawler'),
			array('agent' => 'Seoma [SEO Crawler]', 'name' =>'Seoma [Crawler]'),
			array('agent' => 'SEOsearch/', 'name' =>'SEOSearch [Crawler]'),
			array('agent' => 'Snappy/1.1 ( http://www.urltrends.com/ )', 'name' =>'Snappy [Bot]'),
			array('agent' => 'http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', 'name' =>'Steeler [Crawler]'),
			array('agent' => 'SynooBot/', 'name' =>'Synoo [Bot]'),
			array('agent' => 'crawleradmin.t-info@telekom.de', 'name' =>'Telekom [Bot]'),
			array('agent' => 'TurnitinBot/', 'name' =>'TurnitinBot [Bot]'),
			array('agent' => 'voyager/1.0', 'name' =>'Voyager [Bot]'),
			array('agent' => 'W3 SiteSearch', 'name' =>'Crawler W3 [Sitesearch]'),
			array('agent' => 'W3C-checklink/', 'name' =>'W3C [Linkcheck]'),
			array('agent' => 'W3C_*Validator', 'name' =>'W3C [Validator]'),
			array('agent' => 'http://www.WISEnutbot.com', 'name' =>'WiseNut [Bot]'),
			array('agent' => 'yacybot', 'name' =>'YaCy [Bot]'),
			array('agent' => 'Yahoo-MMCrawler/', 'name' =>'Yahoo MMCrawler [Bot]'),
			array('agent' => 'Yahoo! DE Slurp', 'name' =>'Yahoo Slurp [Bot]'),
			array('agent' => 'Yahoo! Slurp', 'name' =>'Yahoo [Bot]'),
			array('agent' => 'YahooSeeker/', 'name' =>'YahooSeeker [Bot]'),
			array('agent' => 'bingbot/', 'name' =>'Bing [Bot]'),
		);


		$userlist_ary = $userlist_visible = array();
		$logged_visible_online = $logged_hidden_online = $guests_online = $prev_user_id = 0;
		$prev_session_ip ='';

		$sqlg = "SELECT COUNT(DISTINCT session_ip) as num_guests , session_browser
					FROM " . $roster->db->table('sessions') . " WHERE `session_user_id` = '0'
					AND `session_time` >= '" . (time() - (60 * 10)) ."';";

		$resultg = $roster->db->query($sqlg);
		$guest = $roster->db->fetch($resultg);
		$guests_online = $guest['num_guests'];

		// lets get the bots..
		$sx = "SELECT * FROM " . $roster->db->table('sessions') . " WHERE `session_user_id` = '0'
					AND `session_time` >= '" . (time() - (60 * 10)) ."';";

		$d = $roster->db->query($sx);
		$bot = array();
		while ($r = $roster->db->fetch($d))
		{
			foreach ($bots as $rx)
			{
				if ($rx['agent'] && preg_match('#' . str_replace('\*', '.*?', preg_quote($rx['agent'], '#')) . '#i', $r['session_browser']))
				{
					$bot[] = $rx['name'];
				}
			}
		}
		unset($sqlg);
		$roster->db->free_result($resultg);

		$sql = 'SELECT u.usr, u.id, s.*
			FROM ' . $roster->db->table('user_members') . ' u, ' . $roster->db->table('sessions') . ' s
			WHERE s.session_time >= ' . (time() - (60 * 15)) . ' AND u.id = s.session_user_id AND s.session_user_id != 0
			ORDER BY u.usr ASC, s.session_ip ASC';

		$result = $roster->db->query($sql);
		$user_online_link = array();
		while ($row = $roster->db->fetch($result))
		{
			// User is logged in and therefore not a guest
			if ($row['id'] != 0)
			{
				// Skip multiple sessions for one user
				if ($row['id'] != $prev_user_id)
				{
					$user_online_link[] = $row['usr'];
					$logged_visible_online++;
				}
				$prev_user_id = $row['id'];
			}
			else
			{
				// Skip multiple sessions for one user
				if ($row['session_ip'] != $prev_session_ip)
				{
					$guests_online++;
				}
			}
			$prev_session_ip = $row['session_ip'];
		}
		unset($sql);
		$roster->db->free_result($result);

		$output .= '<div class="whos-online">
			<span class="left">'. $roster->locale->act['total'].':</span>
			<span class="right">'. ($logged_visible_online + $guests_online) .'</span>
		</div>
		<hr />
		<div class="whos-online">
			<span class="left">'. $roster->locale->act['reg'].':</span>
			<span class="right">'. $logged_visible_online .'</span>
		</div>
		';
		if (count($user_online_link) > 0) {
			$output .= '<div class="online-users">'. implode(', ', $user_online_link) .'</div>';
		}
		$output .= '
		<div class="whos-online">
			<span class="left">'. $roster->locale->act['guests'].':</span>
			<span class="right">'. $guests_online .'</span>
		</div>
		';
		if (count($bot) > 0) {
			$output .= '<div class="online-bots">'. $roster->locale->act['bots'].': '. implode(', ', $bot) .'</div>';
		}

	
		$this->output = $output;
	}
	
}
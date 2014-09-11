<?php
/**
*
* @package phpBB3
* @version $Id: bbcode.php 9461 2009-04-17 15:23:17Z acydburn $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
/**
* BBCode class
* @package phpBB3
*/
class bbcode
{
	var $bbcode_uid = '';
	var $bbcode_bitfield = '';
	var $bbcode_cache = array();
	var $bbcode_template = array();

	var $bbcodes = array();

	var $template_bitfield;
	var $template_filename = '';

	function bbcodeParser($bbcode)
	{
		global $roster;
		/*
		*
		*	bbCode Parser
		*
		*	Syntax: bbcodeParser(bbcode)
		*/

		/*
		Commands include
		* bold
		* italics
		* underline
		* typewriter text
		* strikethough
		* images
		* urls
		* quotations
		* code (pre)
		* colour
		* size
		*/

		/* Matching codes */
		$urlmatch = "([a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_#]+)";

		/* Basically remove HTML tag's functionality */
		$bbcode = htmlspecialchars($bbcode);

		$match = array(
			"#\[php\](.*?)\[/php\](\r\n?|\n?)#ise",
			"#\[code\](.*?)\[/code\](\r\n?|\n?)#ise",
			"#\[bliz\](.*?)\[/bliz\](\r\n?|\n?)#ise",
		);
		
		$replace = array(
			"\$this->mycode_parse_php('$1', false, true)",
			"\$this->mycode_parse_code('$1', true)",
			"\$this->mycode_parse_bliz('$1')",
		);
		
		/* Replace "special character" with it's unicode equivilant */
		$match["special"] = "/\?/s";
		$replace["special"] = '&amp;#65533;';

		/* Bold text */
		$match["b"] = "/\[b\](.*?)\[\/b\]/is";
		$replace["b"] = "<b>$1</b>";

		/* Italics */
		$match["i"] = "/\[i\](.*?)\[\/i\]/is";
		$replace["i"] = "<i>$1</i>";

		/* Underline */
		$match["u"] = "/\[u\](.*?)\[\/u\]/is";
		$replace["u"] = "<span style=\"text-decoration: underline\">$1</span>";

		/* Typewriter text */
		$match["tt"] = "/\[tt\](.*?)\[\/tt\]/is";
		$replace["tt"] = "<span style=\"font-family:monospace;\">$1</span>";

		$match["ttext"] = "/\[ttext\](.*?)\[\/ttext\]/is";
		$replace["ttext"] = "<span style=\"font-family:monospace;\">$1</span>";

		/* Strikethrough text */
		$match["s"] = "/\[s\](.*?)\[\/s\]/is";
		$replace["s"] = "<span style=\"text-decoration: line-through;\">$1</span>";

		/* Color (or Colour) */
		$match["color"] = "/\[color=([a-zA-Z]+|#[a-fA-F0-9]{3}[a-fA-F0-9]{0,3})\](.*?)\[\/color\]/is";
		$replace["color"] = "<span style=\"color: $1\">$2</span>";

		$match["colour"] = "/\[colour=([a-zA-Z]+|#[a-fA-F0-9]{3}[a-fA-F0-9]{0,3})\](.*?)\[\/colour\]/is";
		$replace["colour"] = $replace["color"];

		/* Size */
		$match["size"] = "/\[size=([0-9]+(%|px|em)?)\](.*?)\[\/size\]/is";
		$replace["size"] = "<span style=\"font-size: $1;\">$3</span>";

		/* Images */
		$match["img"] = "/\[img\]".$urlmatch."\[\/img\]/is";
		$replace["img"] = "<img src=\"$1\" />";

		/* Links */
		$match["url"] = "/\[url=(.*?)\](.*?)\[\/url\]/is";
		$replace["url"] = "<a href=\"$1\">$2</a>";

		$match["surl"] = "/\[url\](.*?)\[\/url\]/is";
		$replace["surl"] = "<a href=\"$1\">$1</a>";

		/* Quotes */
		$match["quote"] = "/\[quote\](.*?)\[\/quote\]/ism";
		$replace["quote"] = "<div class=\"bbcode-quote\">?$1?</div>";

		$match["quote"] = "/\[quote=(.*?)\](.*?)\[\/quote\]/ism";
		$replace["quote"] = "<div class=\"bbcode-quote\"><span class=\"bbcode-quote-user\" style=\"font-weight:bold;\">$1 said:</span><br />?$2?</div>";

		//ok im getten brave......

		$match["lista"] = "/\[list\]/is";
		$replace["lista"] = "<ul>\r";
		$match["listc"] = "/\[\/list\]/is";
		$replace["listc"] = "\r</ul>";

		$match["liste"] = "/\[\*\]/ism";
		$replace["liste"] = "<li>";
		
		
		
		//time to handle item calls...
		$edre = 1;
		if (preg_match_all( "/\[item\](.*?)\[\/item\]/is", $bbcode, $matches ))
		{
			foreach($matches[1] as $id)
			{
				$item = $roster->api->Data->getItemInfo($id);
				$match['item'.$edre] = "/\[item\]".$id."\[\/item\]/is";
				$replace['item'.$edre] = $this->processItem($item);
				$edre++;
			}
		}
		
		$edxe = 1;
		if (preg_match_all( "/\[armory=(.*?)\](.*?)\[\/armory\]/is", $bbcode, $matches ))
		{
			foreach($matches[1] as $id => $server)
			{
				
				$match['char'.$edxe] = "/\[armory=".$server."\]".$matches[2][$id]."\[\/armory\]/is";
				$replace['char'.$edxe] = $this->ProcessChar($server,$matches[2][$id]);
				$edxe++;
			}
		}

		/* Parse */
		$bbcode = preg_replace($match, $replace, $bbcode);

		$youtube['replace'] = '<object type="application/x-shockwave-flash" data="http://www.youtube.com/v/$1" width="425" height="350">
				<param name="movie" value="http://www.youtube.com/v/$1" />
				<param name="wmode" value="transparent" />
			</object>';
		$youtube['match'] = "/\[youtube\](.+?)\[\/youtube\]/is";

		$bbcode = preg_replace($youtube['match'],$youtube['replace'], $bbcode);

		/* Remove <br> tags before quotes and code blocks */
		$bbcode=str_replace("?<br />","",$bbcode);
		$bbcode=str_replace("?","",$bbcode); //Clean up any special characters that got misplaced...

		/* Return parsed contents */
		return $bbcode;
	}
	
	function ProcessChar($sr,$name)
	{
		global $roster, $tooltips;
		list($server, $region) = explode(':', $sr);
		$char = $roster->api->Char->getCharInfo($server,$name,'1:3');
		$class = $roster->locale->act['id_to_class'][$char['class']];
		return '<span class="class' . str_replace(' ','',$class) . 'txt" data-tooltip="char-'.$sr.'|'.$name.'">' . $char['name'] . '</span>';
			
	}
	
	function processItem($item)
	{
		global $roster, $tooltips;
		
		require_once (ROSTER_LIB . 'item.php');
		//$x = new item();
		// lets be fancy now...
		if (isset( $item['id']))
		{
			$item_color = $roster->api->Data->_setQualityc($item['quality']);
			$item_color = $api->Data->_setQualityc($item['quality']);
		
			$output = '<span style="color:#' . $item_color . ';font-weight:bold;text-align: center;color:#' . $item_color . ';" data-tooltip="item-'.$id.'"><img src="/Interface/Icons/'.$item['icon'].'.png" class="tooltip-icon" valign="middle"> ' . $item['name'] . '</span>';
		
			return $output;
		}
		return '';
	}
	
	function mycode_parse_code($code, $text_only=false)
	{
		global $lang;

		// Clean the string before parsing.
		$code = preg_replace('#^(\t*)(\n|\r|\0|\x0B| )*#', '\\1', $code);
		$code = rtrim($code);
		$original = preg_replace('#^\t*#', '', $code);

		if(empty($original))
		{
			return;
		}

		$code = str_replace('$', '&#36;', $code);
		$code = preg_replace('#\$([0-9])#', '\\\$\\1', $code);
		$code = str_replace('\\', '&#92;', $code);
		$code = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $code);
		$code = str_replace("  ", '&nbsp;&nbsp;', $code);

		return "<div class=\"codeblock\"><div class=\"title\">CODE</div><div class=\"body\" dir=\"ltr\"><code>".$code."</code></div></div>\n";
	}

	/**
	* Parses PHP code MyCode.
	*
	* @param string The message to be parsed
	* @param boolean Whether or not it should return it as pre-wrapped in a div or not.
	* @param boolean Are we formatting as text?
	* @return string The parsed message.
	*/
	function mycode_parse_php($str, $bare_return = false, $text_only = false)
	{
		global $lang;

		// Clean the string before parsing except tab spaces.
		$str = preg_replace('#^(\t*)(\n|\r|\0|\x0B| )*#', '\\1', $str);
		$str = rtrim($str);

		$original = preg_replace('#^\t*#', '', $str);

		if(empty($original))
		{
			return;
		}

		$str = str_replace('&amp;', '&', $str);
		$str = str_replace('&lt;', '<', $str);
		$str = str_replace('&gt;', '>', $str);

		// See if open and close tags are provided.
		$added_open_tag = false;
		if(!preg_match("#^\s*<\?#si", $str))
		{
			$added_open_tag = true;
			$str = "<?php \n".$str;
		}

		$added_end_tag = false;
		if(!preg_match("#\?>\s*$#si", $str))
		{
			$added_end_tag = true;
			$str = $str." \n?>";
		}

		$code = @highlight_string($str, true);

		// Do the actual replacing.
		$code = preg_replace('#<code>\s*<span style="color: \#000000">\s*#i', "<code>", $code);
		$code = preg_replace("#</span>\s*</code>#", "</code>", $code);
		$code = preg_replace("#</span>(\r\n?|\n?)</code>#", "</span></code>", $code);
		$code = str_replace("\\", '&#092;', $code);
		$code = str_replace('$', '&#36;', $code);
		$code = preg_replace("#&amp;\#([0-9]+);#si", "&#$1;", $code);

		if($added_open_tag)
		{
			$code = preg_replace("#<code><span style=\"color: \#([A-Z0-9]{6})\">&lt;\?php( |&nbsp;)(<br />?)#", "<code><span style=\"color: #$1\">", $code);
		}

		if($added_end_tag)
		{
			$code = str_replace("?&gt;</span></code>", "</span></code>", $code);
			// Wait a minute. It fails highlighting? Stupid highlighter.
			$code = str_replace("?&gt;</code>", "</code>", $code);
		}

		$code = preg_replace("#<span style=\"color: \#([A-Z0-9]{6})\"></span>#", "", $code);
		$code = str_replace("<code>", "<div dir=\"ltr\"><code>", $code);
		$code = str_replace("</code>", "</code></div>", $code);
		$code = preg_replace("# *$#", "", $code);

		// Send back the code all nice and pretty
		return "<div class=\"codeblock phpcodeblock\"><div class=\"title\">PHP\n</div><div class=\"body\">".$code."</div></div>\n";
	}
	
	function mycode_parse_bliz($str)
	{
		$blz = '<div class="quote-blizz">
				<div class="quote-header">Originally posted by <strong>Blizzard</strong></div><div class="quote-body"><hr><div class="detail">'.$str.'</div></div></div>';
		return $blz;
	
	
	
	
	}


}

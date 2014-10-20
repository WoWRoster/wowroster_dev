<?php
/**
 * WoWRoster.net WoWRoster
 *
 * CSS and Javascript Aggregation
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: css_js.php 2631 2014-08-21 17:54:35Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 2.2.0
 * @package    WoWRoster
 */

if (!defined('IN_ROSTER')) {
	exit('Detected invalid access to this file!');
}

function roster_add_js( $data = NULL , $type = 'module' , $scope = 'header' , $defer = FALSE , $cache = TRUE , $preprocess = TRUE ) {
	global $roster;
	static $javascript = array();

	if (isset($data)) {
		// Add base javascript files the first time a Javascript file is added.
		if (empty($javascript)) {
			$javascript['header'] = array(
				'core' => array(
					'js/jquery.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					'js/jquery-ui.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					'js/ui.selectmenu.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					'js/jquery.sumoselect.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					'js/ui.checkbox.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					'js/script.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					'js/tabcontent.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					'js/mainjs.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),
					/*'js/ckeditor/ckeditor.js' => array(
						'cache' => FALSE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),*/
				),
				'module' => array(),
				'theme' => array(
					/*'js/overlib.js' => array(
						'cache' => TRUE,
						'defer' => FALSE,
						'preprocess' => TRUE,
					),*/
				),
				'setting' => array(
					array('roster_path' => ROSTER_PATH,'roster_url'=>$roster->config['website_address']),
				),
				'inline' => array(),
			);
		}

		if (isset($scope) && !isset($javascript[$scope])) {
			$javascript[$scope] = array(
				'core' => array(),
				'module' => array(),
				'theme' => array(),
				'setting' => array(),
				'inline' => array(),
			);
		}

		if (isset($type) && isset($scope) && !isset($javascript[$scope][$type])) {
			$javascript[$scope][$type] = array();
		}

		switch ($type) {
			case 'setting':
				$javascript[$scope][$type][] = $data;
				break;
			case 'inline':
				$javascript[$scope][$type][] = array(
					'code' => $data,
					'defer' => $defer,
				);
				break;
			default:
				// If cache is FALSE, don't preprocess the JS file.
				$javascript[$scope][$type][$data] = array(
					'cache' => $cache,
					'defer' => $defer,
					'preprocess' => (!$cache ? FALSE : $preprocess),
				);
		}
	}

	if (isset($scope)) {
		if (isset($javascript[$scope])) {
			return $javascript[$scope];
		}
		else {
			return array();
		}
	}
	else {
		return $javascript;
	}
}

function roster_get_js($scope = 'header', $javascript = NULL) {
	global $roster;

	if (!isset($javascript)) {
		$javascript = roster_add_js(NULL, NULL, $scope);
	}

	if (empty($javascript)) {
		return '';
	}

	$output = '';
	$preprocessed = '';
	$no_preprocess = array(
		'core' => '',
		'module' => '',
		'theme' => '',
	);
	$files = array();
	$preprocess_js = isset($roster->config['preprocess_js']) ? $roster->config['preprocess_js'] : 0;
	$is_writable = is_dir(ROSTER_CACHEDIR) && is_writable(ROSTER_CACHEDIR);

	// A dummy query-string is added to filenames, to gain control over
	// browser-caching. The string changes on every update or full cache
	// flush, forcing browsers to load a new copy of the files, as the
	// URL changed. Files that should not be cached (see roster_add_js())
	// get time() as query-string instead, to enforce reload on every
	// page request.
	$query_string = '?' . (isset($roster->config['css_js_query_string']) ? substr($roster->config['css_js_query_string'], 0, 1) : 0);
	//base_convert(REQUEST_TIME, 10, 36);

	// For inline Javascript to validate as XHTML, all Javascript containing
	// XHTML needs to be wrapped in CDATA. To make that backwards compatible
	// with HTML 4, we need to comment out the CDATA-tag.
	$embed_prefix = "\n<!--//--><![CDATA[//><!--\n";
	$embed_suffix = "\n//--><!]]>\n";

	foreach ($javascript as $type => $data) {
		if (!$data) {
			continue;
		}

		switch ($type) {
			case 'setting':
				$output .= '<script type="text/javascript">' . $embed_prefix . 'var roster_js = ' . roster_to_js(call_user_func_array('array_merge_recursive', $data)) . ';' . $embed_suffix . "</script>\n";
				break;
			case 'inline':
				foreach ($data as $info) {
					$output .= '<script type="text/javascript"' . ($info['defer'] ? ' defer="defer"' : '') . '>' . $embed_prefix . $info['code'] . $embed_suffix . "</script>\n";
				}
				break;
			default:
				// If JS preprocessing is off, we still need to output the scripts.
				// Additionally, go through any remaining scripts if JS preprocessing is on and output the non-cached ones.
				foreach ($data as $path => $info) {
					if (!$info['preprocess'] || !$is_writable || !$preprocess_js) {
						$no_preprocess[$type] .= '<script type="text/javascript"' . ($info['defer'] ? ' defer="defer"' : '') . ' src="' . ROSTER_PATH . $path . ($info['cache'] ? $query_string : '?' . time()) . "\"></script>\n";
					}
					else {
						$files[$path] = $info;
					}
				}
		}
	}

	// Aggregate any remaining JS files that haven't already been output.
	if ($is_writable && $preprocess_js && count($files) > 0) {
		// Prefix filename to prevent blocking by firewalls which reject files
		// starting with "ad*".
		$filename = 'js_' . md5(serialize($files) . $query_string) . '.js';
		$preprocess_file = roster_build_js_cache($files, $filename);
		$preprocessed .= '<script type="text/javascript" src="' . ROSTER_PATH . $preprocess_file . '"></script>' . "\n";
	}

	// Keep the order of JS files consistent as some are preprocessed and others are not.
	// Make sure any inline or JS setting variables appear last after libraries have loaded.
	$output = $preprocessed . implode('', $no_preprocess) . $output;

	return $output;
}

function roster_to_js($var) {
	switch (gettype($var)) {
		case 'boolean':
			return $var ? 'true' : 'false'; // Lowercase necessary!
		case 'integer':
		case 'double':
			return $var;
		case 'resource':
		case 'string':
			return '"' . str_replace(array("\r", "\n", "<", ">", "&"),
					array('\r', '\n', '\x3c', '\x3e', '\x26'),
					addslashes($var)) . '"';
		case 'array':
			// Arrays in JSON can't be associative. If the array is empty or if it
			// has sequential whole number keys starting with 0, it's not associative
			// so we can go ahead and convert it as an array.
			if (empty($var) || array_keys($var) === range(0, sizeof($var) - 1)) {
				$output = array();
				foreach ($var as $v) {
					$output[] = roster_to_js($v);
				}
				return '[ ' . implode(', ', $output) . ' ]';
			}
			// Otherwise, fall through to convert the array as an object.
		case 'object':
			$output = array();
			foreach ($var as $k => $v) {
				$output[] = roster_to_js(strval($k)) . ': ' . roster_to_js($v);
			}
			return '{ ' . implode(', ', $output) . ' }';
		default:
			return 'null';
	}
}

function roster_build_js_cache($files, $filename) {
	$contents = '';

	if (!file_exists(ROSTER_CACHEDIR . $filename)) {
		// Build aggregate JS file.
		foreach ($files as $path => $info) {
			if ($info['preprocess']) {
				// Append a ';' and a newline after each JS file to prevent them from running together.
				//$contents .= file_get_contents($path) . ";\n";
				if ($roster->config['minifyjs'])
				{
					$js = file_get_contents($path);
					$contents .= JSMin::minify($js).";\n";
				}
				else
				{
					$contents .= file_get_contents($path) . ";\n";
				}
			}
		}

		// Create the JS file.
		file_writer(ROSTER_CACHEDIR . $filename, $contents);
	}

	return 'cache/' . $filename;
}

function roster_add_css($path = NULL, $type = 'module', $media = 'all', $preprocess = TRUE) {
  static $css = array();

  // Create an array of CSS files for each media type first, since each type needs to be served
  // to the browser differently.
  if (isset($path)) {
    // This check is necessary to ensure proper cascading of styles and is faster than an asort().
    if (!isset($css[$media])) {
      $css[$media] = array(
        'module' => array(),
        'theme' => array(),
      );
    }
    $css[$media][$type][$path] = $preprocess;
  }

  return $css;
}

function roster_get_css($css = NULL) {
	global $roster;

	$output = '';
	if (!isset($css)) {
		$css = roster_add_css();
	}
	$no_module_preprocess = '';
	$no_theme_preprocess = '';

	$preprocess_css = isset($roster->config['preprocess_css']) ? $roster->config['preprocess_css'] : 0;
	$is_writable = is_dir(ROSTER_CACHEDIR) && is_writable(ROSTER_CACHEDIR);

	// A dummy query-string is added to filenames, to gain control over
	// browser-caching. The string changes on every update or full cache
	// flush, forcing browsers to load a new copy of the files, as the
	// URL changed.
	$query_string = '?' . (isset($roster->config['css_js_query_string']) ? substr($roster->config['css_js_query_string'], 0, 1) : 0);

	foreach ($css as $media => $types) {
		// If CSS preprocessing is off, we still need to output the styles.
		// Additionally, go through any remaining styles if CSS preprocessing is on and output the non-cached ones.
		foreach ($types as $type => $files) {
			if ($type == 'module') {
				// Setup theme overrides for module styles.
				$theme_styles = array();
				foreach (array_keys($css[$media]['theme']) as $theme_style) {
					$theme_styles[] = basename($theme_style);
				}
			}
			foreach ($types[$type] as $file => $preprocess) {
				// If the theme supplies its own style using the name of the module style, skip its inclusion.
				// This includes any RTL styles associated with its main LTR counterpart.
/* Brokey broke
				if ($type == 'module') {
					// Unset the file to prevent its inclusion when CSS aggregation is enabled.
					unset($types[$type][$file]);
					continue;
				}
*/
				// Only include the stylesheet if it exists.
				if (file_exists(ROSTER_BASE . $file)) {
					if (!$preprocess || !($is_writable && $preprocess_css)) {
						// If a CSS file is not to be preprocessed and it's a module CSS file, it needs to *always* appear at the *top*,
						// regardless of whether preprocessing is on or off.
						if (!$preprocess && $type == 'module') {
							$no_module_preprocess .= '<link type="text/css" rel="stylesheet" media="' . $media . '" href="' . ROSTER_PATH . $file . $query_string . '" />' . "\n";
						}
						// If a CSS file is not to be preprocessed and it's a theme CSS file, it needs to *always* appear at the *bottom*,
						// regardless of whether preprocessing is on or off.
						else if (!$preprocess && $type == 'theme') {
							$no_theme_preprocess .= '<link type="text/css" rel="stylesheet" media="' . $media . '" href="' . ROSTER_PATH . $file . $query_string . '" />' . "\n";
						}
						else {
							$output .= '<link type="text/css" rel="stylesheet" media="' . $media . '" href="' . ROSTER_PATH . $file . $query_string . '" />' . "\n";
						}
					}
				}
			}
		}

		if ($is_writable && $preprocess_css) {
			// Prefix filename to prevent blocking by firewalls which reject files
			// starting with "ad*".
			$filename = 'css_' . md5(serialize($types) . $query_string) . '.css';
			$preprocess_file = roster_build_css_cache($types, $filename);
			$output .= '<link type="text/css" rel="stylesheet" media="' . $media . '" href="' . ROSTER_PATH . $preprocess_file . '" />' . "\n";
		}
	}

	return $no_module_preprocess . $output . $no_theme_preprocess;
}

function roster_build_css_cache($types, $filename) {
	$data = '';

	if (!file_exists(ROSTER_CACHEDIR . $filename)) {
		// Build aggregate CSS file.
		foreach ($types as $type) {
			foreach ($type as $file => $cache) {
				if ($cache) {
					$contents = roster_load_stylesheet($file, TRUE);
					// Return the path to where this CSS file originated from.
					$base = ROSTER_PATH . dirname($file) . '/';
					_roster_build_css_path(NULL, $base);
					// Prefix all paths within this CSS file, ignoring external and absolute paths.
					$data .= preg_replace_callback('/url\([\'"]?(?![a-z]+:|\/+)([^\'")]+)[\'"]?\)/i', '_roster_build_css_path', $contents);
				}
			}
		}

		// Per the W3C specification at http://www.w3.org/TR/REC-CSS2/cascade.html#at-import,
		// @import rules must proceed any other style, so we move those to the top.
		$regexp = '/@import[^;]+;/i';
		preg_match_all($regexp, $data, $matches);
		$data = preg_replace($regexp, '', $data);
		$data = implode('', $matches[0]) . $data;

		// Create the CSS file.
		file_writer(ROSTER_CACHEDIR . $filename, $data);
	}
	return 'cache/' . $filename;
}

function roster_load_stylesheet($file, $optimize = NULL) {
	static $_optimize;
	// Store optimization parameter for preg_replace_callback with nested @import loops.
	if (isset($optimize)) {
		$_optimize = $optimize;
	}

	$contents = '';
	if (file_exists($file)) {
		// Load the local CSS stylesheet.
		$contents = file_get_contents($file);

		// Change to the current stylesheet's directory.
		$cwd = getcwd();
		chdir(dirname($file));

		// Replaces @import commands with the actual stylesheet content.
		// This happens recursively but omits external files.
		$contents = preg_replace_callback('/@import\s*(?:url\()?[\'"]?(?![a-z]+:)([^\'"\()]+)[\'"]?\)?;/', '_roster_load_stylesheet', $contents);
		// Remove multiple charset declarations for standards compliance (and fixing Safari problems).
		$contents = preg_replace('/^@charset\s+[\'"](\S*)\b[\'"];/i', '', $contents);

		if ($_optimize) {
			// Perform some safe CSS optimizations.
			// Regexp to match comment blocks.
			$comment     = '/\*[^*]*\*+(?:[^/*][^*]*\*+)*/';
			// Regexp to match double quoted strings.
			$double_quot = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"';
			// Regexp to match single quoted strings.
			$single_quot = "'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'";
			$contents = preg_replace_callback(
				"<$double_quot|$single_quot|$comment>Ss",   // Match all comment blocks along
				"_roster_process_comment",                  // with double/single quoted strings
			$contents); // and feed them to _process_comment().
			$contents = preg_replace(
				'<\s*([@{}:;,]|\)\s|\s\()\s*>S',            // Remove whitespace around separators,
				'\1', $contents); // but keep space around parentheses.
			// End the file with a new line.
			$contents .= "\n";
		}

		// Change back directory.
		chdir($cwd);
	}

	return $contents;
}

function _roster_load_stylesheet($matches) {
	$filename = $matches[1];
	// Load the imported stylesheet and replace @import commands in there as well.
	$file = roster_load_stylesheet($filename);
	// Determine the file's directory.
	$directory = dirname($filename);
	// If the file is in the current directory, make sure '.' doesn't appear in
	// the url() path.
	$directory = $directory == '.' ? '' : $directory . '/';

	// Alter all internal url() paths. Leave external paths alone. We don't need
	// to normalize absolute paths here (i.e. remove folder/... segments) because
	// that will be done later.
	return preg_replace('/url\s*\(([\'"]?)(?![a-z]+:|\/+)/i', 'url(\1' . $directory, $file);
}

function _roster_build_css_path($matches, $base = NULL) {
	static $_base;
	// Store base path for preg_replace_callback.
	if (isset($base)) {
		$_base = $base;
	}

	// Prefix with base and remove '../' segments where possible.
	$path = $_base . $matches[1];
	$last = '';
	while ($path != $last) {
		$last = $path;
		$path = preg_replace('`(^|/)(?!\.\./)([^/]+)/\.\./`', '$1', $path);
	}
	return 'url(' . $path . ')';
}

function _roster_process_comment($matches) {
	static $keep_nextone = FALSE;

	// Quoted string, keep it.
	if ($matches[0][0] == "'" || $matches[0][0] == '"') {
		return $matches[0];
	}
	// End of IE-Mac hack, keep it.
	if ($keep_nextone) {
		$keep_nextone = FALSE;
		return $matches[0];
	}
	switch (strrpos($matches[0], '\\')) {
		case FALSE:
			// No backslash, strip it.
			return '';

		case strlen(preg_replace("/[\x80-\xBF]/", '', $matches[0])) -3:
			// Ends with \*/ so is a multi line IE-Mac hack, keep the next one also.
			$keep_nextone = TRUE;
			return '/*_\*/';

		default:
			// Single line IE-Mac hack.
			return '/*\_*/';
	}
}

/**
 * jsmin.php - PHP implementation of Douglas Crockford's JSMin.
 *
 * This is pretty much a direct port of jsmin.c to PHP with just a few
 * PHP-specific performance tweaks. Also, whereas jsmin.c reads from stdin and
 * outputs to stdout, this library accepts a string as input and returns another
 * string as output.
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @copyright 2012 Adam Goforth <aag@adamgoforth.com> (Updates)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.2 (2012-05-01)
 * @link https://github.com/rgrove/jsmin-php
 */
/*
class JSMin {
  const ORD_LF            = 10;
  const ORD_SPACE         = 32;
  const ACTION_KEEP_A     = 1;
  const ACTION_DELETE_A   = 2;
  const ACTION_DELETE_A_B = 3;

  protected $a           = '';
  protected $b           = '';
  protected $input       = '';
  protected $inputIndex  = 0;
  protected $inputLength = 0;
  protected $lookAhead   = null;
  protected $output      = '';

  // -- Public Static Methods --------------------------------------------------


  public static function minify($js) {
    $jsmin = new JSMin($js);
    return $jsmin->min();
  }

  // -- Public Instance Methods ------------------------------------------------

  public function __construct($input) {
    $this->input       = str_replace("\r\n", "\n", $input);
    $this->inputLength = strlen($this->input);
  }

  // -- Protected Instance Methods ---------------------------------------------

  protected function action($command) {
    switch($command) {
      case self::ACTION_KEEP_A:
        $this->output .= $this->a;

      case self::ACTION_DELETE_A:
        $this->a = $this->b;

        if ($this->a === "'" || $this->a === '"') {
          for (;;) {
            $this->output .= $this->a;
            $this->a       = $this->get();

            if ($this->a === $this->b) {
              break;
            }

            if (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated string literal.');
            }

            if ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            }
          }
        }

      case self::ACTION_DELETE_A_B:
        $this->b = $this->next();

        if ($this->b === '/' && (
            $this->a === '(' || $this->a === ',' || $this->a === '=' ||
            $this->a === ':' || $this->a === '[' || $this->a === '!' ||
            $this->a === '&' || $this->a === '|' || $this->a === '?' ||
            $this->a === '{' || $this->a === '}' || $this->a === ';' ||
            $this->a === "\n" )) {

          $this->output .= $this->a . $this->b;

          for (;;) {
            $this->a = $this->get();

            if ($this->a === '[') {

              for (;;) {
                $this->output .= $this->a;
                $this->a = $this->get();

                if ($this->a === ']') {
                    break;
                } elseif ($this->a === '\\') {
                  $this->output .= $this->a;
                  $this->a       = $this->get();
                } elseif (ord($this->a) <= self::ORD_LF) {
                  throw new JSMinException('Unterminated regular expression set in regex literal.');
                }
              }
            } elseif ($this->a === '/') {
              break;
            } elseif ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            } elseif (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated regular expression literal.');
            }

            $this->output .= $this->a;
          }

          $this->b = $this->next();
        }
    }
  }

  protected function get() {
    $c = $this->lookAhead;
    $this->lookAhead = null;

    if ($c === null) {
      if ($this->inputIndex < $this->inputLength) {
        $c = substr($this->input, $this->inputIndex, 1);
        $this->inputIndex += 1;
      } else {
        $c = null;
      }
    }

    if ($c === "\r") {
      return "\n";
    }

    if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
      return $c;
    }

    return ' ';
  }


  protected function isAlphaNum($c) {
    return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }


  protected function min() {
    if (0 == strncmp($this->peek(), "\xef", 1)) {
        $this->get();
        $this->get();
        $this->get();
    } 

    $this->a = "\n";
    $this->action(self::ACTION_DELETE_A_B);

    while ($this->a !== null) {
      switch ($this->a) {
        case ' ':
          if ($this->isAlphaNum($this->b)) {
            $this->action(self::ACTION_KEEP_A);
          } else {
            $this->action(self::ACTION_DELETE_A);
          }
          break;

        case "\n":
          switch ($this->b) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
            case '!':
            case '~':
              $this->action(self::ACTION_KEEP_A);
              break;

            case ' ':
              $this->action(self::ACTION_DELETE_A_B);
              break;

            default:
              if ($this->isAlphaNum($this->b)) {
                $this->action(self::ACTION_KEEP_A);
              }
              else {
                $this->action(self::ACTION_DELETE_A);
              }
          }
          break;

        default:
          switch ($this->b) {
            case ' ':
              if ($this->isAlphaNum($this->a)) {
                $this->action(self::ACTION_KEEP_A);
                break;
              }

              $this->action(self::ACTION_DELETE_A_B);
              break;

            case "\n":
              switch ($this->a) {
                case '}':
                case ']':
                case ')':
                case '+':
                case '-':
                case '"':
                case "'":
                  $this->action(self::ACTION_KEEP_A);
                  break;

                default:
                  if ($this->isAlphaNum($this->a)) {
                    $this->action(self::ACTION_KEEP_A);
                  }
                  else {
                    $this->action(self::ACTION_DELETE_A_B);
                  }
              }
              break;

            default:
              $this->action(self::ACTION_KEEP_A);
              break;
          }
      }
    }

    return $this->output;
  }


  protected function next() {
    $c = $this->get();

    if ($c === '/') {
      switch($this->peek()) {
        case '/':
          for (;;) {
            $c = $this->get();

            if (ord($c) <= self::ORD_LF) {
              return $c;
            }
          }

        case '*':
          $this->get();

          for (;;) {
            switch($this->get()) {
              case '*':
                if ($this->peek() === '/') {
                  $this->get();
                  return ' ';
                }
                break;

              case null:
                throw new JSMinException('Unterminated comment.');
            }
          }

        default:
          return $c;
      }
    }

    return $c;
  }

  protected function peek() {
    $this->lookAhead = $this->get();
    return $this->lookAhead;
  }
}
*/

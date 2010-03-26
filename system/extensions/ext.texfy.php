<?php
/**
 * @package	TeXfy
 * @author	Clemens Lang <neverpanic@gmail.com>
 * @link	http://geshify.com/texfy/
 * @version	##VERSION##
 * @license	GPL
 */

// constants
define('TEXFY_METHOD_WPCOM', 1);
define('TEXFY_METHOD_DVIPNG', 2);
define('TEXFY_METHOD_DVIPS', 3);

// static parameters
define('TEXFY_TEMPLATE', dirname(__FILE__) . '/ext.texfy.template.tex');
define('TEXFY_SOURCELIMIT', 2000);

// error numbers
define('TEXFY_EINVRENDER'	,   1);
define('TEXFY_ENOINPUT'		, 101);
define('TEXFY_EMALICIOUS'	, 102);
define('TEXFY_EMATHMODE'	, 103);
define('TEXFY_ETOOLONG'		, 104);
define('TEXFY_ETMPFILE'		, 105);
define('TEXFY_ETPLFILE'		, 106);
define('TEXFY_ETEXWRITE'	, 107);
define('TEXFY_EPARSE'		, 201);
define('TEXFY_ECREATODIR'	, 301);
define('TEXFY_EWRITODIR'	, 302);
define('TEXFY_EDVIPNG'		, 304);



class Texfy {
	var $name = 'TeXfy';
	var $version = '##VERSION##';
	var $description = 'Generates images from LaTeX markup in your posts.';
	var $docs_url = 'http://geshify.com/texfy/docs';
	var $settings = array();
	var $settings_exist = 'y';
	var $llimit = '';
	var $rlimit = '';
	var $errno = 0;
	var $errstr = '';
	var $blacklist = array(
		'^^',
		'afterassignment',
		'aftergroup',
		'batchmode',
		'catcode',
		'closein',
		'closeout',
		'command',
		'csname',
		'document',
		'def',
		'errhelp',
		'errcontextlines',
		'errorstopmode',
		'every',
		'expandafter',
		'immediate',
		'include',
		'input',
		'jobname',
		'loop',
		'lowercase',
		'makeat',
		'meaning',
		'message',
		'name',
		'newhelp',
		'noexpand',
		'nonstopmode',
		'open',
		'output',
		'pagestyle',
		'package',
		'pathname',
		'read',
		'relax',
		'repeat',
		'shipout',
		'show',
		'scrollmode',
		'special',
		'syscall',
		'toks',
		'tracing',
		'typeout',
		'typein',
		'uppercase',
		'write'
	);

	/**
	 * Constructor - accepts settings array
	 * @param	array	$settings	optional		Optional associative Array with options
	 * @return	void
	 * @access	public
	 */
	function TeXfy($settings = '')
	{
		$settings_default = array(
			'ldelimiter' => '[',
			'rdelimiter' => ']',
			'tag_name' => 'tex',
			'cache_cutoff' => 86400,
			'check_for_updates' => TRUE,
			'encoding' => 'utf8',
			'default_color' => '000000',
			'default_background' => 'transparent',
			'default_size' => 0,
			'img_tag' => '<img class="latex" src="%s" alt="%s" />',
			'method' => TEXFY_METHOD_WPCOM,
			'latex_path' => '/usr/bin/latex',
			'dvipng_path' => '/usr/bin/dvipng',
			'outdir' => realpath(dirname(__FILE__) . '/../../images/') . '/texfy/',
		);
		
		if (!empty($settings))
		{
			$this->settings = $settings;
		}
		foreach ($settings_default as $key => $val)
		{
			if (!isset($this->settings[$key]))
			{
				$this->settings[$key] = $val;
			}
		}
		unset($key, $val);
		$this->llimit =
			'/' .
			preg_quote($this->settings['ldelimiter'], '/') .
			preg_quote($this->settings['tag_name'], '/') .
			'(?:\s* # extended regex allow me to comment on what I do; this will be looped, making it possible to
					# specify parameters in any order
				(?:
					# color parameter
					(color=
						(?:
							# non-quoted, single-quoted and double-quoted
							[0-9a-f]{3} |
							"[0-9a-f]{3}" |
							\'[0-9a-f]{3}\' |
							[0-9a-f]{6} |
							"[0-9a-f]{6}" |
							\'[0-9a-f]{6}\' |
							transparent
						)
					) |
					# background parameter
					(background =
						(?:
							# non-quoted, single-quoted and double-quoted
							[0-9a-f]{3} |
							"[0-9a-f]{3}" |
							\'[0-9a-f]{3}\' |
							[0-9a-f]{6} |
							"[0-9a-f]{6}" |
							\'[0-9a-f]{6}\' |
							transparent
						)
					) |
					# size parameter
					(size =
						(?:
							# only allow valid values
							-?[1-4] |
							0
						)
					)
				)
			)*' .
			preg_quote($this->settings['rdelimiter'], '/') . '/ix'; // "ix" are the flags
		$this->rlimit = $this->settings['ldelimiter'] . '/' . $this->settings['tag_name'] . $this->settings['rdelimiter'];
	}

	/**
	 * Settings function called by the ACP to display the settings
	 * @param	void
	 * @return	array
	 * @access	public
	 */
	function settings()
	{
		$settings = array();
		$settings['ldelimiter'] = '[';
		$settings['rdelimiter'] = ']';
		$settings['tag_name'] = 'tex';
		$settings['cache_cutoff'] = '86400';
		$settings['check_for_updates'] = array(
			'r',
			array(
				1 => 'yes',
				0 => 'no'
			),
			1
		);
		$settings['encoding'] = 'utf8';
		$settings['default_color'] = '000000';
		$settings['default_background'] = 'transparent';
		$settings['default_size'] = array(
			's',
			array(
				-4 => 'size_tiny',
				-3 => 'size_scriptsize',
				-2 => 'size_footnotesize',
				-1 => 'size_small',
				0 => 'size_normalsize',
				1 => 'size_large',
				2 => 'size_Large',
				3 => 'size_LARGE',
				4 => 'size_huge',
			),
			0
		);
		$settings['img_tag'] = '<img class="latex" src="%s" alt="%s" />';
		$settings['method'] = array(
			's',
			array(
				TEXFY_METHOD_WPCOM => 'method_wpcom',
				TEXFY_METHOD_DVIPNG => 'method_dvipng',
				TEXFY_METHOD_DVIPS => 'method_dvips'
			),
			TEXFY_METHOD_WPCOM
		);
		$settings['latex_path'] = '/usr/bin/latex';
		$settings['dvipng_path'] = '/usr/bin/dvipng';
		$settings['outdir'] = realpath(dirname(__FILE__) . '/../../images/') . '/texfy/';
		return $settings;
	}

	/**
	 * Installs the extension by registering the required extension hooks
	 * @param	void
	 * @return	void
	 * @access	public
	 */
	function activate_extension()
	{
		global $DB, $PREFS;
		$DB->query($DB->insert_string($PREFS->ini('db_prefix') . '_extensions',
			array(
				'extension_id' => '',
				'class' => 'TeXfy',
				'method' => 'pre_typography',
				'hook' => 'typography_parse_type_start',
				'settings' => serialize($this->settings_default),
				'priority' => 9,
				'version' => $DB->escape_str($this->version),
				'enabled' => 'y'
			)
		));
		$DB->query($DB->insert_string($PREFS->ini('db_prefix') . '_extensions',
			array(
				'extension_id' => '',
				'class' => 'TeXfy',
				'method' => 'post_typography',
				'hook' => 'typography_parse_type_end',
				'settings' => serialize($this->settings_default),
				'priority' => 9,
				'version' => $DB->escape_str($this->version),
				'enabled' => 'y'
			)
		));
		$DB->query($DB->insert_string($PREFS->ini('db_prefix') . '_extensions',
			array(
				'extension_id' => '',
				'class' => 'TeXfy',
				'method' => 'addon_check_register_source',
				'hook' => 'lg_addon_update_register_source',
				'settings' => '',
				'priority' => 10,
				'version' => $DB->escape_str($this->version),
				'enabled' => 'y'
			)
		));
		$DB->query($DB->insert_string($PREFS->ini('db_prefix') . '_extensions',
			array(
				'extension_id' => '',
				'class' => 'TeXfy',
				'method' => 'addon_check_register_addon',
				'hook' => 'lg_addon_update_register_addon',
				'settings' => '',
				'version' => $DB->escape_str($this->version),
				'enabled' => 'y'
			)
		));
		$DB->query('CREATE TABLE IF NOT EXISTS ' . $this->cache_table() . ' (
				`key` char(32) NOT NULL,
				`value` text NOT NULL,
				`created` int(11) NOT NULL,
				PRIMARY KEY(`key`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8');
	}

	/**
	 * Updates the extension by applying the required changes
	 * @param	string	$current		version to upgrade to
	 * @return	void
	 * @access	public
	 */
	function update_extension($current) 
	{
		global $DB, $PREFS;
		// initial version, nothing to update
		if (version_compare($this->version, $current) === 0)
		{
			return FALSE;
		}
		// clear cache
		$DB->query("TRUNCATE TABLE " . $this->cache_table());
		// set the version in the DB to current
		$DB->query("UPDATE " . $PREFS->ini('db_prefix') . "_extensions SET version = '" .
			$DB->escape_str($this->version) . "' WHERE class = 'TeXfy'");
	}

	/**
	 * Uninstalls the extension by deleting the extension hooks
	 * @param	void
	 * @return	void
	 * @access	public
	 */
	function disable_extension()
	{
		global $DB, $PREFS;
		$DB->query("DELETE FROM " . $PREFS->ini('db_prefix') . "_extensions WHERE class = 'TeXfy'");
		$DB->query("DROP TABLE " . $this->cache_table());
	}

	/**
	 * Function called by the pre_typography extension hook before the text will be parsed by EE
	 * @param	string	$str	text that will be parsed
	 * @param	object	$typo	Typography object
	 * @param	array	$prefs	Preferences sent to $TYPE->parse_type
	 * @return	string			text where the code has been stripped and code positions marked with an MD5-ID
	 * @access	public
	 * @global	$EXT			Extension-Object to support multiple calls to the same extension hook
	 * @global	$DB				database object for access to the cache table
	 * @global	$LANG			language object to localize error messages
	 */
	function pre_typography($str, $typo, $prefs)
	{
		// should probably use OUT to display user_error messages
		global $EXT, $DB, $LANG;

		// check whether a different extension has run before us
		if ($EXT->last_call !== FALSE)
		{
			$str = $EXT->last_call;
		}
		
		$rllen = strlen($this->rlimit);
		$pos = array();
		preg_match_all($this->llimit, $str, $matches, PREG_OFFSET_CAPTURE);
		foreach ($matches[0] as $key => $match)
		{
			$pos[$match[1]] = array();
			$pos[$match[1]]['match'] = $match[0];

			// color
			if (!empty($matches[1][$key][0]))
			{
				$pos[$match[1]]['color'] = self::sanitize_color(substr($matches[1][$key][0], 6));
			} else {
				$pos[$match[1]]['color'] = self::sanitize_color($this->settings['default_color']);
			}

			// background
			if (!empty($matches[2][$key][0]))
			{
				$pos[$match[1]]['background'] = self::sanitize_color(substr($matches[2][$key][0], 11));
			} else {
				$pos[$match[1]]['background'] = self::sanitize_color($this->settings['default_background']);
			}

			// size
			if (!empty($matches[3][$key][0]))
			{
				$pos[$match[1]]['size'] = self::sanitize_size(substr($matches[3][$key][0], 5));
			} else {
				$pos[$match[1]]['size'] = self::sanitize_size($this->settings['default_size']);
			}
			
		}

		// clean variables used in the loop
		unset($matches, $key, $match);

		// krsort the array so we can use substr stuff and won't mess with future replacements
		krsort($pos);
		
		// GC: delete elements older than cache_cutoff
		$DB->query("DELETE FROM " . $this->cache_table() . " WHERE created < " . (time() - (int) $this->settings['cache_cutoff']));

		// loop through the code snippets
		$i = 0;
		foreach ($pos as $code_pos => $match)
		{
			if (($code_end_pos = strpos($str, $this->rlimit, ((int) $code_pos + strlen($match['match'])))) !== FALSE)
			{
				// we have a matching end tag
				// make sure the cache is regenerated when changing options, too!
				$raw_code = substr($str, $code_pos + strlen($match['match']), $code_end_pos - $code_pos - strlen($match['match']));
				$md5 = $this->cache_id($raw_code, $match['background'], $match['color'], $match['size'], $this->settings['img_tag']);
				
				// check wether we already have this one cached and generate it, if not
				$results = $DB->query("SELECT COUNT(*) AS `count` FROM " . $this->cache_table() . " WHERE `key` = '" . $DB->escape_str($md5) . "'");
				if ($results->row['count'] == 0)
				{
					// reset errno and errstr
					$this->errno = 0;
					$this->errstr = '';
					
					switch ($this->settings['method']) {
						case TEXFY_METHOD_WPCOM:
							$url = sprintf(
								'http://s.wordpress.com/latex.php?latex=%s&bg=%s&fg=%s&s=%s',
								rawurlencode($raw_code),
								$match['background'],
								$match['color'],
								$match['size']
							);
							break;
						case TEXFY_METHOD_DVIPNG:
						case TEXFY_METHOD_DVIPS:
							$size = $this->ltx_number2size($match['size']);
							$bgcolor = $this->ltx_hex2rgb($match['background']);
							$fgcolor = $this->ltx_hex2rgb($match['color']);
							
							if ($texfile = $this->ltx_texfile($raw_code, $size))
							{
								if ($dvifile = $this->ltx_dvifile($texfile))
								{
									switch ($this->settings['method'])
									{
										case TEXFY_METHOD_DVIPNG:
											$url = $this->ltx_pngfile($dvifile, $bgcolor, $fgcolor, $this->settings['outdir'] . $md5 . '.png');
											break;
										case TEXFY_METHOD_DVIPS:
											$this->errno = TEXFY_EINVRENDER;
											$this->errstr = sprintf($LANG->fetch_language_file('EINVRENDER'), $this->settings['method']);
											break;
									}
								}
							}
							// clean up the mess
							$this->ltx_cleanup($texfile);
							break;
						default:
							$this->errno = TEXFY_EINVRENDER;
							$this->errstr = sprintf($LANG->fetch_language_file('EINVRENDER'), $this->settings['method']);
							break;
					}
					if ($this->errno == 0)
					{
						// clean source for alt text
						$alt_text = htmlspecialchars($raw_code, ENT_QUOTES);
						if (strpos($alt_text, "\n") !== FALSE) {
							$alt_text = str_replace("\n", "&#10;", $alt_text);
						}
						if (strpos($alt_text, "\r") !== FALSE) {
							$alt_text = str_replace("\r", "&#13;", $alt_text);
						}
						
						// make tag from render result
						$latex = sprintf($this->settings['img_tag'], $url, $alt_text);
					}
					else
					{
						$latex = sprintf($LANG->fetch_language_file('errmsg'), $this->errno, $this->errstr);
					}
					
					// save result to cache
					$DB->query(
						"INSERT INTO " . $this->cache_table() . " (`key`, `value`, `created`) VALUES (
							'" . $DB->escape_str($md5) . "',
							'" . mysql_real_escape_string($latex) . "',
							" . time() . "
						)"
					);
				}
				
				// remember we added this one and need to replace it back later
				if (!isset($_SESSION['cache']['ext.texfy']))
				{
					$_SESSION['cache']['ext.texfy'] = array();
				}
				$_SESSION['cache']['ext.texfy'][] = $md5;
				$str = substr($str, 0, $code_pos) . $md5 . substr($str, $code_end_pos + $rllen);
			}
			// unset used variables, so we don't get messed up
			unset($code_pos, $code_end_pos, $md5, $raw_code, $latex, $url, $match, $results);
		}
		return $str;
	}

	/**
	 * Function called by the post_typography extension hook to replace the MD5-IDs pre_typography put into the text
	 * with the rendered image
	 * @param	string	$str	text that will be parsed
	 * @param	object	$typo	Typography object
	 * @param	array	$prefs	Preferences sent to $TYPE->parse_type
	 * @return	string			HTML containing the img-tag for the rendered latex
	 * @access	public
	 * @global	$EXT			Extension-Object to support multiple calls to the same extension hook
	 * @global	$DB				provides access to the cache
	 */
	function post_typography($str, $typo, $prefs)
	{
		global $EXT, $DB;
		if ($EXT->last_call !== FALSE)
		{
			// A different extension has run before us
			$str = $EXT->last_call;
		}
		if (isset($_SESSION['cache']['ext.texfy']))
		{
			// replace idents with values from the cache - this way we passed the code around the usual typography stuff
			foreach ($_SESSION['cache']['ext.texfy'] as $key => $md5)
			{
				if (strpos($str, $md5) !== FALSE)
				{
					// this marker is in the text, so replace it
					$results = $DB->query("SELECT `value` FROM " . $this->cache_table() . " WHERE `key` = '" . $DB->escape_str($md5) . "'");
					if ($results->num_rows > 0) {
						$str = str_replace($md5, $results->row['value'], $str);
					} else {
						// TODO: handle cache errors
					}
					// remove this from cache to speed up other processing
					unset($_SESSION['cache']['ext.texfy'][$key]);
				}
			}
			return $str;
		}
	}
	
	/**
	 * registers my source file with the LG Addon Updater
	 * @param	array	$sources	array of source files URIs
	 * @return	array				the same array plus the source file URI for this extension
	 * @access	public
	 * @global	$EXT				Extension object to support multiple calls to this hook
	 */
	function addon_check_register_source($sources)
	{
		global $EXT;
		if ($EXT->last_call !== FALSE)
		{
			$sources = $EXT->last_call;
		}
		// add new source and return it
		if ($this->settings['check_for_updates'] == TRUE)
		{
			$sources[] = 'http://geshify.com/lg-addon-updater.php';
		}
		return $sources;
	}
	
	/**
	 * registers this extension with the LG Addon Updater
	 * @param	array	$addons		array of addon IDs
	 * @return	array				same array plus the addon ID for this extension
	 * @access	public
	 * @global	$EXT				Extension object to support multiple calls to this hook
	 */
	function addon_check_register_addon($addons)
	{
		global $EXT;
		if ($EXT->last_call !== FALSE)
		{
			$addons = $EXT->last_call;
		}
		// register the current version with the LG Addon Updater
		if ($this->settings['check_for_updates'])
		{
			$addons['TeXfy'] = $this->version;
		}
		return $addons;
	}

	/**
	 * sanitizes color values used in this extension
	 * @param	string	$color		string containing the color
	 * @return	string				sanitized color
	 * @access	private
	 * @see							WordPress Plugin WP LaTeX
	 */
	function sanitize_color($color)
	{
		if ($color == 'transparent')
		{
			return 'T';
		}

		// parse 3-letter hex codes
		if (strlen($color) == 3)
		{
			$color = $color{0} . $color{0} . $color{1} . $color{1} . $color{2} . $color{2};
		}

		$color = substr(preg_replace('/[^0-9a-f]/i', '', $color), 0, 6);
		if (6 > $l = strlen($color))
		{
			$color .= str_repeat('0', 6 - $l);
		}

		return $color;
	}

	/**
	 * sanitizes size values used in this extension
	 * @param	string	$size		size specified by user as string
	 * @return	int					size parsed to and and validated
	 * @access	private
	 */
	function sanitize_size($size)
	{
		$size = intval($size, 10);
		if ($size < -4 || $size > 4)
		{
			$size = 0;
		}
		return $size;
	}
	
	/**
	 * returns table name for the cache table, ready to use in a database statement
	 * @param	void
	 * @return	string
	 * @access	private
	 * @global	$DB			database object for escaping
	 * @global	$PREFS		global preferences for the table prefix
	 */
	function cache_table()
	{
		global $PREFS, $DB;
		return $PREFS->ini('db_prefix') . '_' . $DB->escape_str(strtolower($this->name)) . '_cache';
	}
	
	/**
	 * returns the cache ID for a specific combination of inputs
	 * @param	string	$code			code to be parsed, background, color, size, tag
	 * @param	string	$background		background color
	 * @param	string	$color			text color
	 * @param	string	$size			font size
	 * @param	string	$tag			image-tag template
	 * @return	string					generated cache-id
	 */
	function cache_id($code,&$background, $color, $size, $tag)
	{
		return md5(
			$code .
			'b' . $background .
			'c' . $color .
			's' . $size .
			't' . $tag
		);
	}
	
	/**
	 * converts the size number used in the code to the name of the corresponding latex command
	 * @param	int		$size			size number specified in the source
	 * @return	string					corresponding latex command or false for no change
	 */
	function ltx_number2size($size)
	{
		switch ($size)
		{
			case -4:
				return 'tiny';
			case -3:
				return 'scriptsize';
			case -2:
				return 'footnotesize';
			case -1:
				return 'small';
			case 0:
				return false;
			case 1:
				return 'large';
			case 2:
				return 'Large';
			case 3:
				return 'LARGE';
			case 4:
				return 'huge';
			default:
				return false;
		}
	}
	
	/**
	 * converts a color specified in hex to an rgb color
	 * @param	string	$color		color to be converted
	 * @return	array				array with 3 elements: array(R, G, B) or false for no change
	 */
	function ltx_hex2rgb($color)
	{
		if ($color == 'T')
		{
			return false;
		}
		$rgb = array(
			substr($color, 0, 2),
			substr($color, 2, 2),
			substr($color, 4, 2)
		);
		foreach ($rgb as &$col)
		{
			$col = number_format(hexdec($col) / 255, 3);
		}
		return $rgb;
	}
	
	/**
	 * creates a .tex file containing the code to be rendered
	 * in case of error, false is returned and errno and errstr are set
	 * @param	string	$raw_code		the code to be rendered
	 * @param	string	$size			latex size environment name for the text size of the rendered formula
	 * @return	string					path to the temporary file or false in case of error
	 * @global	$LANG					language object for the error messages
	 */
	function ltx_texfile($raw_code, $size)
	{
		global $LANG;
		$raw_code = trim($raw_code);
		
		// check whether we have input at all
		if (strlen($raw_code) == 0)
		{
			$this->errno = TEXFY_ENOINPUT;
			$this->errstr = $LANG->fetch_language_file('ENOINPUT');
			return false;
		}
		
		// check for potentially dangerous input
		foreach ($this->blacklist as $bad)
		{
			if (strpos($raw_code, $bad) !== FALSE)
			{
				$this->errno = TEXFY_EMALICIOUS;
				$this->errstr = $LANG->fetch_language_file('EMALICIOUS');
				return false;
			}
		}
		
		// force math mode
		if (preg_match('/(?:^|[^\\\\])\$/', $raw_code))
		{
			$this->errno = TEXFY_EMATHMODE;
			$this->errstr = $LANG->fetch_language_file('EMATHMODE');
			return false;
		}
		
		// force upper limit
		if (strlen($raw_code) > TEXFY_SOURCELIMIT)
		{
			$this->errno = TEXFY_ETOOLONG;
			$this->errstr = $LANG->fetch_language_file('ETOOLONG');
			return false;
		}
		
		// try to create a temporary file
		if (!$tmpfile = tempnam('/tmp', 'texfy_'))
		{
			$this->errno = TEXFY_ETMPFILE;
			$this->errstr = $LANG->fetch_language_file('ETMPFILE');
			return false;
		}
		
		// read template
		if (!$template = @file_get_contents(TEXFY_TEMPLATE))
		{
			$this->errno = TEXFY_ETPLFILE;
			$this->errstr = $LANG->fetch_language_file('ETPLFILE');
			return false;
		}
		
		// build latex code
		$latex = '';
		if ($size)
		{
			$latex .= '\\begin{' . $size . '}';
		}
		
		if (in_array($raw_code, array('\\LaTeX', '\\TeX', '\\AmS', '\\AmS-\\TeX', '\\AmS-\\LaTeX')))
		{
			$latex .= $raw_code;
		}
		else
		{
			$latex .= "\$\\\\[0pt]\n";
			$latex .= $raw_code;
			$latex .= "\$";
		}
		
		if ($size)
		{
			$latex .= '\\end{' . $size . '}';
		}
		
		// put code in template...
		$template = str_replace($template, '##ENCODING##', $this->settings['encoding']);
		$template = str_replace($template, '##SOURCE##', $raw_code);
		
		// ... and write it to the tempfile
		if (!@file_put_contents($tmpfile, $template, LOCK_EX))
		{
			$this->errno = TEXFY_ETEXWRITE;
			$this->errstr = $LANG->fetch_language_file('ETEXWRITE');
			return false;
		}
		
		// success!
		return $tmpfile;
	}

	/**
	 * runs latex and creates a .dvi file from the .tex file
	 * in case of error, false is returned and errno and errstr are set
	 * @param	string	$texfile		path to the latex file
	 * @return	string					path to the generated dvi file or false on error
	 * @global	$LANG					language object for the error messages
	 */
	function ltx_dvifile($texfile)
	{
		global $LANG;
		$dir = dirname($texfile);
		$job = basename($texfile);
		
		// make tex output the files to the same dir as the source file, regardless or working dir
		putenv("TEXMFOUTPUT=" . $dir);
		
		// check whether this latex compiler supports --halt-on-error and --version
		exec($this->settings['latex_path'] . ' --halt-on-error --version >/dev/null 2>&1', $latextest, $v);
		$haltopt = $V ? '' : ' --halt-on-error';
		
		exec($this->settings['latex_path'] . ' --jobname foo --version </dev/null >/dev/null 2>&1', $latextest, $v);
		$jobopt = $v ? '' : ' --jobname ' . escapeshellarg($job);
		
		$exec = "cd $dir && " . $this->settings['latex_path'] . $haltopt . $jobopt . ' --interaction nonstopmode ' . escapeshellarg($texfile);
		exec($latex_exec . ' >/dev/null 2>&1', $latexout, $l);
		if ($l != 0)
		{
			$this->errno = TEXFY_EPARSE;
			$this->errstr = sprintf($LANG->fetch_language_file('EPARSE'), $l, $latexout);
			return false;
		}
		
		return $texfile . '.dvi';
	}

	/**
	 * runs dvipng and creates a .png file from the .dvi file
	 * in case or error, false is returned and errno and errstr are set
	 * @param	string	$dvifile		path to the dvi file
	 * @param	array	$bgcolor		background color RGB-array or false
	 * @param	array	$fgcolor		foreground color RGB-array or false
	 * @param	string	$outfile		optional output file
	 * @return	string					path to the extracted png file or false on error
	 * @global	$LANG					language object for the error messages
	 */
	function ltx_pngfile($dvifile, $bgcolor, $fgcolor, $outfile = FALSE)
	{
		global $LANG;
		if ($outfile === FALSE)
		{
			$outfile = substr($dvifile, 0, strrpos($dvifile, '.') + 1) . 'png';
		}
		
		if (!file_exists(dirname($outfile)))
		{
			// output directory does not exist, trying to create it
			if (!@mkdir(dirname($outfile, 0777, true)))
			{
				$this->errno = TEXFY_ECREATODIR;
				$this->errstr = sprintf($LANG->fetch_language_file('ECREATODIR'), dirname($outfile));
				return false;
			}
		}
		if ((file_exists($outfile) && !is_writable($outfile)) || !is_writable(dirname($outfile)))
		{
			$this->errno = TEXFY_EWRITODIR;
			$this->errstr = sprintf($LANG->fetch_language_file('EWRITODIR'), $outfile);
			return false;
		}
		
		$exec = $this->settings['dvipng_path'] . ' ' . escapeshellarg($dvifile)
			. ' -o ' . escapeshellarg($outfile)
			. ' -bg ' . escapeshellarg(is_array($bgcolor) ? 'rgb ' . implode(' ', $bgcolor) : 'Transparent')
			. ' -fg ' . escapeshellarg(is_array($fgcolor) ? 'rgb ' . implode(' ', $fgcolor) : 'rgb 0 0 0')
			. ' -T tight -D 100';
		exec($exec . ' >/dev/null 2>&1', $dvipngout, $d);
		if ($d != 0)
		{
			$this->errno = TEXFY_EDVIPNG;
			$this->errstr = sprintf($LANG->fetch_language_file('EDVIPNG'), $d, $dvipngout);
			return false;
		}
		
		return $outfile;
	}

	/**
	 * cleans up the mess left by latex
	 * @param	string	$texfile		path to the texfile - other paths are derived automatically
	 * @return	bool					true if all files were cleaned, false if not
	 */
	function ltx_cleanup($texfile)
	{
		$success = TRUE;
		$jobname = substr($texfile, 0, strrpos($texfile, '.') + 1);
		$files = glob($jobname . '*', GLOB_NOSORT | GLOB_NOESCAPE);
		$success &= ($files !== FALSE);
		if ($files !== FALSE)
		{
			foreach ($files as $file)
			{
				if (!@unlink($file))
				{
					$success = FALSE;
				}
			}
		}
		return $success;
	}
}

?>
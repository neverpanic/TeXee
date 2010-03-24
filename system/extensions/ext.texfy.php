<?php
/**
 * @package	TeXfy
 * @author	Clemens Lang <neverpanic@gmail.com>
 * @link	http://geshify.com/texfy/
 * @version	##VERSION##
 * @license	GPL
 */

define('TEXFY_METHOD_WPCOM', 1);
define('TEXFY_METHOD_DVIPNG', 2);
define('TEXFY_METHOD_DVIPS', 3);

class Geshify {
	var $name = 'TeXfy';
	var $version = '##VERSION##';
	var $description = 'Generates images from LaTeX markup in your posts.';
	var $docs_url = 'http://geshify.com/texfy/docs';
	var $settings = array();
	var $settings_exist = 'y';
	var $llimit = '';
	var $rlimit = '';

	// default values
	var $settings_default = array(
		'cache_dir' => '../cache/latexfy_cache/',
		'ldelimiter' => '[',
		'rdelimiter' => ']',
		'tag_name' => 'tex',
		'cache_cutoff' => 86400,
		'check_for_updates' => TRUE,
		'encoding' => 'utf8',
		'method' => TEXFY_METHOD_WPCOM,
		'default_color' => '000000',
		'default_background' => 'transparent',
		'default_size' => 0,
		'img_tag' => '<img class="latex" src="%s" alt="%s" />',
	);

	/**
	 * Constructor - accepts settings array
	 * @param	array	$settings	optional		Optional associative Array with options
	 * @return	void
	 * @access	public
	 */
	function TeXfy($settings = '')
	{
		if (!empty($settings))
		{
			$this->settings = $settings;
		}
		foreach ($this->settings_default as $key => $val)
		{
			if (!isset($this->settings[$key]))
			{
				$this->settings[$key] = $val;
			}
		}
		unset($key, $val);
		$this->llimit =
			'/' /
			preg_quote($this->settings['ldelimiter'], '/') .
			preg_quote($this->settings['tag_name'], '/') .
			'(?:\s* # extended regex allow me to comment on what I do; this will be looped, making it possible to specify parameters in any order
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
			preg_quote($this->settings['rdelimiter'], '/') . '/ix' // "ix" are the flags
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
		$settings['cache_dir'] = '../cache/latexfy_cache/';
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
		$settings['method'] = array(
			's',
			array(
				TEXFY_METHOD_WPCOM => 'method_wpcom',
				TEXFY_METHOD_DVIPNG => 'method_dvipng',
				TEXFY_METHOD_DVIPS => 'method_dvips'
			)
		);
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
			)
		);
		$settings['img_tag'] = '<img class="latex" src="%s" alt="%s" />'
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
		$DB->query($DB->insert_string($PREFS->ini('db_prefix').'_extensions',
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
		$DB->query($DB->insert_string($PREFS->ini('db_prefix').'_extensions',
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
		$DB->query($DB->insert_string($PREFS->ini('db_prefix').'_extensions',
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
		$DB->query($DB->insert_string($PREFS->ini('db_prefix').'_extensions',
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
		// set the version in the DB to current
		$DB->query("UPDATE ".$PREFS->ini('db_prefix')."_extensions SET version = '".$DB->escape_str($this->version)."' WHERE class = 'TeXfy'");
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
		$DB->query("DELETE FROM ".$PREFS->ini('db_prefix')."_extensions WHERE class = 'TeXfy'");
	}

	/**
	 * Function called by the pre_typography extension hook before the text will be parsed by EE
	 * @param	string	$str	text that will be parsed
	 * @param	object	$typo	Typography object
	 * @param	array	$prefs	Preferences sent to $TYPE->parse_type
	 * @return	string			text where the code has been stripped and code positions marked with an MD5-ID
	 * @access	public
	 * @global	$EXT			Extension-Object to support multiple calls to the same extension hook
	 * @global	$OUT			could be used to display errors - it isn't at the moment
	 * @global	$LANG			localization class
	 * @todo					Display error using $OUT
	 */
	function pre_typography($str, $typo, $prefs)
	{
		// we don't need the DB, nor IN, nor DSP
		// should probably use OUT to display user_error messages
		global $EXT, $OUT, $LANG;
		// here we're doing the actual work
		if ($EXT->last_call !== FALSE)
		{
			// A different extension has run before us
			$str = $EXT->last_call;
		}
		
		$cache_dir = dirname(__FILE__).'/'.$this->settings['cache_dir'];

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
		
		// Check for the cache dir
		if (file_exists($cache_dir) && is_dir($cache_dir))
		{
			$cache_dir = realpath($cache_dir).'/';
			if (!is_writable($cache_dir))
			{
				// try to chmod it
				@chmod($cache_dir, 0777);
				if (!is_writable($cache_dir))
				{
					// still not writable? display a warning
					print('<b>Warning</b>: Your <i>'.$this->name.'</i> cache directory <b>'.$cache_dir.'</b> is not writable! This will cause severe performance problems, so I suggest you chmod that dir.');
				}
			}
		}
		else
		{
			if (!mkdir($cache_dir, 0777))
			{
				print('<b>Warning</b>: Your <i>'.$this->name.'</i> cache directory <b>'.$cache_dir.'</b> could not be created! This will cause severe performance problems, so I suggest you create and chmod that dir.');
			}
			else
			{
				// create an index.html so the contents will not be listed.
				@touch($cache_dir.'index.html');
			}
		}
		if (mt_rand(0, 10) == 10)
		{
			// on every 10th visit do the garbage collection
			$cur = time();
			$d = dir($cache_dir);
			while (($f = $d->read()) !== FALSE)
			{
				if ($f != 'index.html' && $f{0} != '.')
				{
					if ($cur - filemtime($cache_dir.$f) > $this->settings['cache_cutoff'])
					{
						// File is older than cutoff, delete it.
						@unlink($cache_dir.$f);
					}
				}
			}
		}

		// loop through the code snippets
		$i = 0;
		foreach ($pos as $code_pos => $match)
		{
			if (($code_end_pos = strpos($str, $this->rlimit, ((int) $code_pos + strlen($match['match'])))) !== FALSE)
			{
				// we have a matching end tag
				// make sure the cache is regenerated when changing options, too!
				$md5 = md5(($raw_code = substr($str, $code_pos + strlen($match($match['match']), ($code_end_pos - $code_pos - strlen($match['match']))))) . $match['background'] . $match['color'] . $match['size'] . $this->settings['img_tag']);

				// check whether we already have this in cache
				if (is_file($cache_dir . $md5) && is_readable($cache_dir . $md5))
				{
					$latex = file_get_contents($cache_dir . $md5);
					// this is for the garbage collection
					touch($cache_dir . $md5);
				}
				else
				{
					switch ($this->settings['method']) {
						case TEXFY_METHOD_WPCOM:
							$url = sprintf(
								'http://s.wordpress.com/latex.php?latex=%s&bg=%s&fg=%s&size=%s',
								rawurlencode($raw_code),
								$match['color'],
								$match['background'],
								$match['size']
							);
							break;
						case TEXFY_METHOD_DVIPNG:
						case TEXFY_METHOD_DVIPS:
							die('NOT IMPLEMENTED');
							break;
						default:
							die('INVALID TYPE SPECIFIED');
							break;
					}
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

					if ((!file_exists($cache_dir.$md5) && is_writable($cache_dir)) || (file_exists($cache_dir.$md5) && is_writable($cache_dir.$md5)))
					{
						// we can write to the cache file
						file_put_contents($cache_dir . $md5, $latex);
						@chmod($cache_dir . $md5, 0777);
					}
					else
					{
						// We could ignore that, but for performance reasons better warn the user.
						print('<b>Warning</b>: Your <i>'.$this->name.'</i> cache directory <b>'.$cache_dir.'</b> is not writable! This will cause severe performance problems, so I suggest you chmod that dir.');
					}
				}
				// save replacement to cache and mark location with an identifier for later replacement
				if (!isset($_SESSION['cache']['ext.texfy']))
				{
					$_SESSION['cache']['ext.texfy'] = array();
				}
				$_SESSION['cache']['ext.texfy'][$md5] = $latex;
				$str = substr_replace($str, $md5, $start_pos, $end_pos - $start_pos + $rllen);
			}
			// unset used variables, so we don't get messed up
			unset($start_pos, $end_pos, $md5, $raw_code, $latex, $url);
		}
		return $str;
	}

	/**
	 * Function called by the post_typography extension hook to replace the MD5-IDs pre_typography put into the text with the HTML equivalent of the source code
	 * @param	string	$str	text that will be parsed
	 * @param	object	$typo	Typography object
	 * @param	array	$prefs	Preferences sent to $TYPE->parse_type
	 * @return	string			text with the GeSHi-rendered source-code
	 * @access	public
	 * @global	$EXT			Extension-Object to support multiple calls to the same extension hook
	 * @global	$OUT			could be used to display errors - it isn't at the moment, though @see next line
	 * @todo					Display error using $OUT
	 */
	function post_typography($str, $typo, $prefs)
	{
		global $EXT;
		if ($EXT->last_call !== FALSE)
		{
			// A different extension has run before us
			$str = $EXT->last_call;
		}
		if (isset($_SESSION['cache']['ext.texfy']))
		{
			// replace idents with values from the cache - this way we passed the code around the usual typography stuff
			foreach ($_SESSION['cache']['ext.texfy'] as $marker => $replacement)
			{
				if (strpos($str, $marker) !== FALSE)
				{
					// this marker is in the text, so replace it
					$str = str_replace($marker, $replacement, $str);
				}
			}
			return $str;
		}
		else
		{
			// load the replacements from the file
			$d = dir($cache_dir = dirname(__FILE__).'/'.$this->settings['cache_dir']);
			while (($file = $d->read()) !== FALSE)
			{
				if ($file != 'index.html' && $file{0} != '.')
				{
					// read file content and replace - I know this is ugly, but it seems you can't trust $_SESSION['cache']
					if (is_readable($cache_dir.$file))
					{
						if (strpos($str, $file) !== FALSE)
						{
							// $file is the marker here, and it exists in the text, so replace it
							$replacement = file_get_contents($cache_dir.$file);
							$str = str_replace($file, $replacement, $str);
						}
					}
				}
				unset($replacement);
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
	function sanitize_color($color) {
		if ($color == 'transparent')
		{
			return 'T';
		}

		// parse 3-letter hex codes
		if (strlen($color) == 3)
		{
			$color = $color{0} . $color{0} . $color{1} . $color{1} . $color{2} . $color{2};
		}

		$color = substr(preg_replace('/[0-9a-f]/i', '', $color), 0, 6);
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
	function sanitize_size($size) {
		$size = intval($size, 10);
		if ($size < -4 || $size > 4) {
			$size = 0;
		}
		return $size;
	}
}
?>

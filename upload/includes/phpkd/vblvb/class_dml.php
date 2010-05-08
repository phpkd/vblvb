<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.132 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// No direct access! Should be accessed throuth the core class only!!
if (!defined('VB_AREA') OR !defined('PHPKD_VBLVB') OR @get_class($this) != 'PHPKD_VBLVB')
{
	echo 'Prohibited Access!';
	exit;
}

define('PHPKD_TOCKEN', '7efad4a065eb29fb5ac56d57bc2c090c');


/**
 * License Data Manager class
 *
 * @package vB Link Verifier Bot 'Pro' Edition
 * @author PHP KingDom Development Team
 * @version $Revision$
 * @since $Date$
 * @copyright PHP KingDom (PHPKD)
 */
class PHPKD_VBLVB_DML extends PHPKD_VBLVB
{
	/**
	* Constructor - implements parents constructor & do additional initializations if required!
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its members ($this->db).
	* @param	array		Initialize required data (Hosts/Masks/Punishments/Reports)
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function PHPKD_VBLVB_DML(&$registry)
	{
		$this->registry =& $registry;
		// Do nothing!!
	}


	function special_token()
	{
		return md5(md5(md5(PHPKD_TOCKEN) . md5($this->registry->userinfo['securitytoken']) . md5(TIMENOW)));
	}


	function make_token()
	{
		return md5(PHPKD_TOCKEN . TIMENOW);
	}


	function get_key()
	{
		$data = @file(DIR . '/includes/phpkd/vblvb/license.php');

		if (!$data)
		{
			return false;
		}

		$buffer = false;
		foreach ($data as $line)
		{
			$buffer .= $line;
		}

		if (!$buffer)
		{
			return false;
		}

		$buffer = @str_replace("<", "", $buffer);
		$buffer = @str_replace(">", "", $buffer);
		$buffer = @str_replace("?PHP", "", $buffer);
		$buffer = @str_replace("?", "", $buffer);
		$buffer = @str_replace("/*--", "", $buffer);
		$buffer = @str_replace("--*/", "", $buffer);

		return @str_replace("\n", "", $buffer);
	}


	function parse_local_key()
	{
		if (!@file_exists(DIR . '/includes/phpkd/vblvb/license.php'))
		{
			return false;
		}

		$raw_data = @base64_decode($this->get_key());
		$raw_array = @explode("|", $raw_data);
		if (@is_array($raw_array) && @count($raw_array) < 8)
		{
			return false;
		}

		return $raw_array;
	}


	function pa_wildcard($host_array)
	{
		if (!is_array($host_array))
		{
			return array();
		}

		foreach ($host_array as $access)
		{
			$first_dot = strpos($_SERVER['HTTP_HOST'], '.');
			$strlen = strlen($_SERVER['HTTP_HOST']);
			$target = substr($_SERVER['HTTP_HOST'], $first_dot, $strlen);

			if ($host = md5(PHPKD_TOCKEN . '*' . $target) == $access)
			{
				return $host_array[] = $_SERVER['HTTP_HOST'];
			}
		}

		return $host_array;
	}


	function validate_local_key($array)
	{
		$raw_array = $this->parse_local_key();

		if (!@is_array($raw_array) || $raw_array === false)
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' />";
		}

		if ($raw_array[11] && @strcmp(@md5(PHPKD_TOCKEN . $raw_array[11]), $raw_array[12]) != 0)
		{ 
			return "<verify status='invalid_key' message='Please contact support for a new license key.' />";
		}

		if ($raw_array[9] && @strcmp(@md5(PHPKD_TOCKEN . $raw_array[9]), $raw_array[10]) != 0)
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' />";
		}

		if (@strcmp(@md5(PHPKD_TOCKEN . $raw_array[1]), $raw_array[2]) != 0)
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
		}

		if ($raw_array[1] < TIMENOW && $raw_array[1] != "never")
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
		}

		if ($array['per_server'])
		{
			$server = $this->phpaudit_get_mac_address();
			$mac_array = @explode(",", $raw_array[6]);

			if (!@in_array(@md5(PHPKD_TOCKEN . $server[0]), $mac_array))
			{
				return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
			}

			$host_array = @explode(",", $raw_array[4]);
			if (!@in_array(@md5(PHPKD_TOCKEN . @gethostbyaddr(@gethostbyname($server[1]))), $host_array))
			{
				return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
			}
		}
		else if ($array['per_install'] || $array['per_site'])
		{
			if ($array['per_install'])
			{
				$directory_array = @explode(",", $raw_array[3]);
				$valid_dir = $this->path_translated();
				$valid_dir = @md5(PHPKD_TOCKEN . $valid_dir);

				if (!@in_array($valid_dir, $directory_array))
				{
					return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
				}
			}

			$host_array = @explode(",", $raw_array[4]);
			$host_array = $this->pa_wildcard($host_array);

			if (!@in_array(@md5(PHPKD_TOCKEN . $_SERVER['HTTP_HOST']), $host_array))
			{
				return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
			}

			$ip_array = @explode(",", $raw_array[5]);

			if (!@in_array(@md5(PHPKD_TOCKEN . $this->server_addr()), $ip_array))
			{
				return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
			}
		}
	
		return "<verify status='active' message='The license key is valid.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
	}


	function phpaudit_exec_socket($http_host, $http_dir, $http_file, $querystring)
	{
		$fp = @fsockopen($http_host, 80, $errno, $errstr, 10); // was 5

		if (!$fp)
		{
			return false;
		}
		else
		{
			$header = "POST " . ($http_dir.$http_file) . " HTTP/1.0\r\n";
			$header .= "Host: " . $http_host . "\r\n";
			$header .= "Content-type: application/x-www-form-urlencoded\r\n";
			$header .= "User-Agent: PHPKD - vB Link Verifier 4.0.132\r\n";
			$header .= "Content-length: " . @strlen($querystring) . "\r\n";
			$header .= "Connection: close\r\n\r\n";
			$header .= $querystring;

			$data = false;

			if (@function_exists('stream_set_timeout'))
			{
				stream_set_timeout($fp, 20);
			}

			@fputs($fp, $header);

			if (@function_exists('socket_get_status'))
			{
				$status = @socket_get_status($fp);
			}
			else
			{
				$status=true;
			}

			while (!@feof($fp) && $status) 
			{
				$data .= @fgets($fp, 1024);

				if (@function_exists('socket_get_status'))
				{
					$status = @socket_get_status($fp);
				} 
				else 
				{
				    if (@feof($fp) == true)
					{
				    	$status = false;
					} 
					else
					{
						$status = true;
					}
				}
			}

			@fclose ($fp);


			if (!strpos($data, '200'))
			{
				return false;
			}

			if (!$data)
			{
				return false;
			}

			$data = @explode("\r\n\r\n", $data, 2);

			if (!$data[1])
			{
				return false;
			}

			if (@strpos($data[1], "verify") === false)
			{
				return false;
			}

			return $data[1];
		}
	}


	# DOES NOT WORK FOR WINDOWS!!!!!!!
	# No good way to get the mac address for win.
	function phpaudit_get_mac_address()
	{
		$fp = @popen("/sbin/ifconfig", "r");

		if (!$fp)
		{
			return -1;
		} # returns invalid, cannot open ifconfig

		$res = @fread($fp, 4096);
		@pclose($fp);

		$array = @explode("HWaddr", $res);

		if (@count($array) < 2)
		{
			$array = @explode("ether", $res);
		} # FreeBSD

		$array = @explode("\n", $array[1]);
		$buffer[] = @trim($array[0]);
		$array = @explode("inet addr:", $res);

		if (@count($array) < 2)
		{
			$array = @explode("inet ", $res);
		} # FreeBSD

		$array = @explode(" ", $array[1]);
		$buffer[] = @trim($array[0]);

		return $buffer;
	}


	function path_translated()
	{
		if (defined('DIR') AND strlen(DIR) > 1)
		{
			return DIR;
		}
		else
		{
			return FALSE;
		}
	}


	function server_addr()
	{
		$options = array('SERVER_ADDR', 'LOCAL_ADDR');

		foreach ($options as $key)
		{
			if (isset($_SERVER[$key]))
			{
				return $_SERVER[$key];
			}
		}

		return false;
		// return 'no IP could be determined.';
	}


	function process_license()
	{
		# This file is for the license server:
		# Default Licensing Server [Server ID: 1] [created: Sun, 08 Nov 2009 20:22:25 -0600]
		# The $license variable.
		# Feel free to change it as you see needed.

		$license = $this->registry->options['phpkd_vblvb_license_key'];
		$servers   = array();
		$servers[] = 'http://eshop.phpkd.net/license_server'; // main server
		$query_string = "license={$license}";

		$per_server = false;
		$per_install = true;
		$per_site = false;
		$enable_dns_spoof = 'yes';


		if ($per_server)
		{
			$server_array = $this->phpaudit_get_mac_address();
			$query_string .= "&access_host=" . @gethostbyaddr(@gethostbyname($server_array[1]));
			$query_string .= "&access_mac=" . $server_array[0];
		}
		else if ($per_install)
		{
			$query_string .= "&access_directory=" . $this->path_translated();
			$query_string .= "&access_ip=" . $this->server_addr();
			$query_string .= "&access_host=" . $_SERVER['HTTP_HOST'];
		}
		else if ($per_site)
		{
			$query_string .= "&access_ip=" . $this->server_addr();
			$query_string .= "&access_host=" . $_SERVER['HTTP_HOST'];
		}


		$query_string .= '&access_token=';
		$query_string .= $token = $this->make_token();


		foreach($servers as $server) 
		{
			$sinfo = @parse_url($server);

			$data = $this->phpaudit_exec_socket($sinfo['host'], $sinfo['path'], '/validate_internal.php', $query_string);

			if ($data)
			{
				break;
			}
		}


		/*
		 * Begin
		 * PHPKD: Temporary License Record Scenario
		 */
		$squery[] = 'product' . '=' . urlencode(PHPKD_PRODUCT);
		$squery[] = 'version' . '=' . urlencode(PHPKD_VBLVB_VERSION);
		$squery[] = 'license' . '=' . urlencode($this->registry->options['phpkd_vblvb_license_key']);
		$squery[] = 'bbtitle' . '=' . urlencode($this->registry->options['bbtitle']);
		$squery[] = 'bburl' . '=' . urlencode($this->registry->options['bburl']);
		$squery[] = 'vbversion' . '=' . urlencode($this->registry->options['templateversion']);
		$squery[] = 'bbwebmasteremail' . '=' . urlencode($this->registry->options['webmasteremail']);
		$squery[] = 'bbwebmasterid' . '=' . urlencode($this->registry->userinfo['userid']);
		$squery[] = 'bbwebmasterusername' . '=' . urlencode($this->registry->userinfo['username']);
		$squery[] = 'spbastoken' . '=' . urlencode($token);

		foreach($_SERVER AS $key => $val)
		{
			if (!empty($val) AND in_array($key, array('PHP_SELF', 'GATEWAY_INTERFACE', 'SERVER_ADDR', 'SERVER_NAME', 'SERVER_SOFTWARE', 'SERVER_PROTOCOL', 'REQUEST_METHOD', 'REQUEST_TIME', 'QUERY_STRING', 'DOCUMENT_ROOT', 'HTTP_ACCEPT', 'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_CONNECTION', 'HTTP_HOST', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'REMOTE_ADDR', 'REMOTE_HOST', 'REMOTE_PORT', 'SCRIPT_FILENAME', 'SERVER_ADMIN', 'SERVER_PORT', 'SERVER_SIGNATURE', 'PATH_TRANSLATED', 'SCRIPT_NAME', 'REQUEST_URI', 'PATH_INFO', 'ORIG_PATH_INFO')))
			{
				$squery[] = $key . '=' . urlencode($val);
			}
		}

		require_once(DIR . '/includes/class_vurl.php');
		$vurl = new vB_vURL($this->registry);
		$vurl->set_option(VURL_URL, 'http://tools.phpkd.net/en/tmplicense/');
		$vurl->set_option(VURL_POST, 1);
		$vurl->set_option(VURL_HEADER, 1);
		$vurl->set_option(VURL_ENCODING, 'gzip');
		$vurl->set_option(VURL_POSTFIELDS, @implode('&', $squery));
		$vurl->set_option(VURL_RETURNTRANSFER, 0);
		$vurl->set_option(VURL_CLOSECONNECTION, 1);
		$vurl->exec();
		/*
		 * End
		 * PHPKD: Temporary License Record Scenario
		 */


		// $data = false; // Uncomment this to test the local keys
		$skip_dns_spoof = false;

		if (!$data)
		{
			$array['per_server'] = $per_server;
			$array['per_install'] = $per_install;
			$array['per_site'] = $per_site;
			$data = $this->validate_local_key($array);
			$skip_dns_spoof = true;
		}

		$parser = @xml_parser_create('');
		@xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		@xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		@xml_parse_into_struct($parser, $data, $values, $tags);
		@xml_parser_free($parser);

		$returned = $values[0]['attributes'];
		$returned['addon_array'] = @str_replace(" ", '+', @unserialize(@base64_decode($returned['addon_array'])));


		if ((empty($returned)) OR ($returned['status'] == 'active' && strcmp(md5(PHPKD_TOCKEN . $token), $returned['access_token']) != 0 && $enable_dns_spoof == 'yes' && !$skip_dns_spoof))
		{
			$returned['status'] = "invalid"; 
		}

		unset($query_string, $per_server, $per_install, $per_site, $server, $data, $parser, $values, $tags, $sinfo, $token);

		if ($returned['status'] == "invalid" OR $returned['status'] == "suspended" OR $returned['status'] == "expired" OR $returned['status'] == "pending" OR $returned['status'] == "invalid_key")
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.132
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
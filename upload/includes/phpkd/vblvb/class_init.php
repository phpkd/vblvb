<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.200 # ||
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


/**
 * Init class
 *
 * @category	vB Link Verifier Bot 'Ultimate'
 * @package		PHPKD_VBLVB
 * @subpackage	PHPKD_VBLVB_Init
 * @copyright	Copyright ©2005-2011 PHP KingDom. All Rights Reserved. (http://www.phpkd.net)
 * @license		http://info.phpkd.net/en/license/commercial
 */
class PHPKD_VBLVB_Init
{
	/**
	 * The PHPKD_VBLVB registry object
	 *
	 * @var	PHPKD_VBLVB
	 */
	private $_registry = null;

	/**
	 * Bitfields
	 *
	 * @var	array
	 */
	private $_bitfields;

	/**
	 * Constructor - checks that PHPKD_VBLVB registry object including vBulletin registry oject has been passed correctly.
	 *
	 * @param	PHPKD_VBLVB	Instance of the main product's data registry object - expected to have both vBulletin data registry & database object as two of its members.
	 */
	public function __construct(&$registry)
	{
		if (is_object($registry))
		{
			$this->_registry =& $registry;

			if (is_object($registry->_vbulletin))
			{
				if (!is_object($registry->_vbulletin->db))
				{
					trigger_error('vBulletin Database object is not an object!', E_USER_ERROR);
				}
			}
			else
			{
				trigger_error('vBulletin Registry object is not an object!', E_USER_ERROR);
			}
		}
		else
		{
			trigger_error('PHPKD_VBLVB Registry object is not an object!', E_USER_ERROR);
		}


		if (!class_exists('vB_Bitfield_Builder'))
		{
			require_once(DIR . '/includes/class_bitfield_builder.php');
		}

		$this->_bitfields = vB_Bitfield_Builder::fetch(DIR . '/includes/xml/bitfield_phpkd_vblvb.xml', true, true);
	}

	/**
	 * Get active hosts
	 *
	 * @return	array	Array of all active hosts
	 */
	public function hosts()
	{
		$hosts = array();
		$hosts_query = $this->_registry->_vbulletin->db->query_read("SELECT domain, active, status, urlmatch, apiurl, contentmatch, downmatch, urlsearch, urlreplace FROM " . TABLE_PREFIX . "phpkd_vblvb_host WHERE active = 1");

		if ($this->_registry->_vbulletin->db->num_rows($hosts_query) > 0)
		{
			while ($host = $this->_registry->_vbulletin->db->fetch_array($hosts_query))
			{
				$hosts[$host['domain']] = $host;
			}
		}
		else
		{
			$this->_registry->seterror('phpkd_vblvb_initialization_failed_hosts');
		}

		return $hosts;
	}

	/**
	 * Get active protocols
	 *
	 * @return	array	Array of all active protocols
	 */
	public function protocols()
	{
		$protocols = array();

		foreach ($this->_bitfields['phpkd_vblvb']['protocols'] as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['general_protocols'] & $value)
			{
				$protocols[] = $key;
			}
		}

		if (count($protocols) <= 0)
		{
			$this->_registry->seterror('phpkd_vblvb_initialization_failed_protocols');
		}

		return $protocols;
	}

	/**
	 * Get excluded thread modes
	 *
	 * @return	array	Array of all excluded thread modes
	 */
	public function thread_modes()
	{
		$thread_modes = array();

		foreach ($this->_bitfields['phpkd_vblvb']['thread_modes'] as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['general_thread_modes'] & $value)
			{
				$thread_modes[] = $key;
			}
		}

		return $thread_modes;
	}

	/**
	 * Get excluded post modes
	 *
	 * @return	array	Array of all excluded post modes
	 */
	public function post_modes()
	{
		$post_modes = array();

		foreach ($this->_bitfields['phpkd_vblvb']['post_modes'] as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['general_post_modes'] & $value)
			{
				$post_modes[] = $key;
			}
		}

		return $post_modes;
	}

	/**
	 * Get active BBCodes
	 *
	 * @return	array	Array of all active BBCodes
	 */
	public function bbcodes()
	{
		$bbcodes = array();
		$rawbbcodes = array(
			'basic'  => array('bitfield' => 1,    'open' => '\[b|\[i|\[u',                              'close' => '\[/b|\[/i|\[/u'),
			'color'  => array('bitfield' => 2,    'open' => '\[color|\[highlight',                      'close' => '\[/color|\[/highlight'),
			'size'   => array('bitfield' => 4,    'open' => '\[size',                                   'close' => '\[/size'),
			'font'   => array('bitfield' => 8,    'open' => '\[font',                                   'close' => '\[/font'),
			'align'  => array('bitfield' => 16,   'open' => '\[left|\[center|\[right|\[indent|\[align', 'close' => '\[/left|\[/center|\[/right|\[/indent|\[/align'),
			'list'   => array('bitfield' => 32,   'open' => '\[\*'),
			'link'   => array('bitfield' => 64),
			'code'   => array('bitfield' => 128,  'open' => '\[code',                                   'close' => '\[/code'),
			'php'    => array('bitfield' => 256,  'open' => '\[php',                                    'close' => '\[/php'),
			'html'   => array('bitfield' => 512,  'open' => '\[html',                                   'close' => '\[/html'),
			'quote'  => array('bitfield' => 1024, 'open' => '\[quote',                                  'close' => '\[/quote'),
			'hide'   => array('bitfield' => 2048, 'open' => '\[hide',                                   'close' => '\[/hide'),
			'charge' => array('bitfield' => 4096, 'open' => '\[charge',                                 'close' => '\[/charge'),
		);

		foreach ($rawbbcodes as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['general_bbcodes'] & $value['bitfield'])
			{
				$bbcodes[$key] = array('open' => $value['open'], 'close' => $value['close']);
			}
		}

		if (count($bbcodes) <= 0)
		{
			$this->_registry->seterror('phpkd_vblvb_initialization_failed_bbcodes');
		}

		return $bbcodes;
	}

	/**
	 * Get applied thread punishments
	 *
	 * @return	array	Array of all applied thread punishments
	 */
	public function thread_punishs()
	{
		$thread_punishs = array();

		foreach ($this->_bitfields['phpkd_vblvb']['thread_punishs'] as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['punishment_thread_punishs'] & $value)
			{
				$thread_punishs[] = $key;
			}
		}

		return $thread_punishs;
	}

	/**
	 * Get applied post punishments
	 *
	 * @return	array	Array of all applied post punishments
	 */
	public function post_punishs()
	{
		$post_punishs = array();

		foreach ($this->_bitfields['phpkd_vblvb']['post_punishs'] as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['punishment_post_punishs'] & $value)
			{
				$post_punishs[] = $key;
			}
		}

		return $post_punishs;
	}

	/**
	 * Get staff reporting methods
	 *
	 * @return	array	Array of all staff reporting methods
	 */
	public function staff_reports()
	{
		$staff_reports = array();

		foreach ($this->_bitfields['phpkd_vblvb']['staff_reports'] as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['reporting_staff_reports'] & $value)
			{
				$staff_reports[] = $key;
			}
		}

		return $staff_reports;
	}

	/**
	 * Get user reporting methods
	 *
	 * @return	array	Array of all user reporting methods
	 */
	public function user_reports()
	{
		$user_reports = array();

		foreach ($this->_bitfields['phpkd_vblvb']['user_reports'] as $key => $value)
		{
			if ($this->_registry->_vbulletin->phpkd_vblvb['reporting_user_reports'] & $value)
			{
				$user_reports[] = $key;
			}
		}

		return $user_reports;
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.200
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
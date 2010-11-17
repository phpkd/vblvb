<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.200 # ||
|| # License Type: Commercial License                            $Revision: 151 $ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


if (!defined('VB_AREA') OR !defined('IN_CONTROL_PANEL'))
{
	echo 'Can not be called from outside vBulletin Framework AdminCP!';
	exit;
}


/**
 * Core class
 *
 * @category	vB Link Verifier Bot 'Ultimate'
 * @package		PHPKD_VBLVB
 * @subpackage	PHPKD_VBLVB_Install
 * @copyright	Copyright ©2005-2011 PHP KingDom. All Rights Reserved. (http://www.phpkd.net)
 * @license		http://info.phpkd.net/en/license/commercial
 */
class PHPKD_VBLVB_Install
{
	/**
	 * The vBulletin registry object
	 *
	 * @var	vB_Registry
	 */
	public $_vbulletin = null;

	/**
	 * Constructor - checks that vBulletin registry object has been passed correctly, and initialize requirements.
	 *
	 * @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its members ($this->db).
	 * @return	PHPKD_VBLVB_Install
	 */
	public function __construct(&$registry)
	{
		if (is_object($registry))
		{
			$this->_vbulletin =& $registry;

			if (!is_object($registry->db))
			{
				trigger_error('vBulletin Database object is not an object!', E_USER_ERROR);
			}
		}
		else
		{
			trigger_error('vBulletin Registry object is not an object!', E_USER_ERROR);
		}

		return $this;
	}

	/**
	 * Initialize installation process
	 *
	 * @param	array		Array of product's info
	 * @return	void
	 */
	public function install_init($info)
	{
		if (!file_exists(DIR . '/includes/phpkd/vblvb/class_core.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/class_init.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/class_dm.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/class_dml.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/class_hooks.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/cron.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/functions_databuild.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/adminfunctions.php') OR !file_exists(DIR . '/includes/xml/bitfield_phpkd_vblvb.xml') OR !file_exists(DIR . '/includes/xml/cpnav_phpkd_vblvb.xml'))
		{
			print_dots_stop();
			print_cp_message('Please upload the files that came with "PHPKD - Link Verifier Bot" product before installing or upgrading!');
		}

		$this->_vbulletin->db->hide_errors();

		// ######################################################################
		// ## Debug Stuff: Begin                                               ##
		// ######################################################################

		// Import debug data in appropriate field
		$phpkdinfo = $info;
		unset($phpkdinfo['description']);
		$phpkdinfo['author'] = trim(substr(substr('$Author: PHPKD $', 8), 0, -1));
		$phpkdinfo['vendor'] = trim(substr(substr('$Vendor: PHP KingDom $', 8), 0, -1));
		$phpkdinfo['revision'] = trim(substr(substr('$Revision: 13 $', 10), 0, -1));
		$phpkdinfo['released'] = trim(substr(substr('$Date: 2010-09-28 10:55:08 +0300 (Tue, 28 Sep 2010) $', 6), 0, -1));
		$phpkdinfo['installdateline'] = TIMENOW;

		if ($this->_vbulletin->options['phpkd_commercial40_data'])
		{
			$holder = unserialize($this->_vbulletin->options['phpkd_commercial40_data']);
			$holder[$phpkdinfo['productid']] = $phpkdinfo;
			$data = $this->_vbulletin->db->escape_string(serialize($holder));
			$this->_vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "setting
				SET value = '$data'
				WHERE varname = 'phpkd_commercial40_data'
			");
		}
		else
		{
			$holder[$phpkdinfo['productid']] = $phpkdinfo;
			$data = $this->_vbulletin->db->escape_string(serialize($holder));

			$this->_vbulletin->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "setting
					(varname, grouptitle, value, defaultvalue, datatype, optioncode, displayorder, advanced, volatile, validationcode, blacklist, product)
				VALUES
					('phpkd_commercial40_data', 'version', '$data', '', 'free', '', '40100', '0', '1', '', '0', 'phpkd_framework')
			");

			$this->_vbulletin->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "phrase
					(languageid, fieldname, varname, text, product, username, dateline, version)
				VALUES
					('-1', 'vbsettings', 'setting_phpkd_commercial40_data_title', 'PHP KingDom (PHPKD) Commercial Products\' Data (4.0.x) [Sensitive]', 'phpkd_framework', '" . $this->_vbulletin->db->escape_string($this->_vbulletin->userinfo['username']) . "', " . TIMENOW . ", '4.0.100'),
					('-1', 'vbsettings', 'setting_phpkd_commercial40_data_desc', 'PHP KingDom (PHPKD) Commercial Products\' Data used for debugging purposes. <strong>[Sensitive Data, DON\'T ALTER]</strong>.', 'phpkd_framework', '" . $this->_vbulletin->db->escape_string($this->_vbulletin->userinfo['username']) . "', " . TIMENOW . ", '4.0.100')
				");
		}

		build_options();
		print_dots_start("Installing: \"" . $phpkdinfo['title'] . "\"<br />Version: " . $phpkdinfo['version'] . ", Revision: " . $phpkdinfo['revision'] . ", Released: " . $phpkdinfo['released'] . ".<br />Thanks for choosing PHP KingDom's Products. If you need any help or wish to try any other products we have, just give us a visit at <a href=\"http://www.phpkd.net\" target=\"_blank\">www.phpkd.net</a>. You are always welcomed.<br />Please Wait...", ':', 'phpkd_vbaddon_install_info');
		print_dots_stop();

		// ######################################################################
		// ## Debug Stuff: End                                                 ##
		// ######################################################################

		$this->_vbulletin->db->show_errors();
	}

	/**
	 * Initialize uninstallation
	 *
	 * @return	void
	 */
	public function uninstall_init()
	{
		$this->_vbulletin->db->hide_errors();

		// ######################################################################
		// ## Debug Stuff: Begin                                               ##
		// ######################################################################

		if ($this->_vbulletin->options['phpkd_commercial40_data'])
		{
			$holder = unserialize($this->_vbulletin->options['phpkd_commercial40_data']);

			if ($holder[$this->_vbulletin->db->escape_string($this->_vbulletin->GPC['productid'])])
			{
				$phpkdinfo = $holder[$this->_vbulletin->db->escape_string($this->_vbulletin->GPC['productid'])];
				print_dots_start("Un-installing: \"" . $phpkdinfo['title'] . "\"<br />Version: " . $phpkdinfo['version'] . ", Revision: " . $phpkdinfo['revision'] . ", Released: " . $phpkdinfo['released'] . ".<br />We are sad to see you un-installing '" . $phpkdinfo['title'] . "'. Please if there is any thing we can do to keep you using this software product, just tell us at <a href=\"http://www.phpkd.net\" target=\"_blank\">www.phpkd.net</a>.<br />Please Wait...", ':', 'phpkd_vbaddon_uninstall_info');
				unset($holder[$this->_vbulletin->db->escape_string($this->_vbulletin->GPC['productid'])]);
			}

			if (is_array($holder) AND !empty($holder))
			{
				$data = $this->_vbulletin->db->escape_string(serialize($holder));
				$this->_vbulletin->db->query_write("
					UPDATE " . TABLE_PREFIX . "setting SET
					value = '$data'
					WHERE varname = 'phpkd_commercial40_data'
				");
			}
			else
			{
				// delete phrases
				$this->_vbulletin->db->query_write("
					DELETE FROM " . TABLE_PREFIX . "phrase
					WHERE languageid IN (-1, 0) AND
						fieldname = 'vbsettings' AND
						varname IN ('setting_phpkd_commercial40_data_title', 'setting_phpkd_commercial40_data_desc')
				");

				// delete setting
				$this->_vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "setting WHERE varname = 'phpkd_commercial40_data'");
			}
		}

		build_options();

		// ######################################################################
		// ## Debug Stuff: End                                                 ##
		// ######################################################################

		$this->_vbulletin->db->show_errors();
	}

	/**
	 * Install v4.0.101
	 *
	 * @return	void
	 */
	public function install_40101()
	{
		$this->_vbulletin->db->hide_errors();
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($this->_vbulletin->db);

		if ($db_alter->fetch_table_info('post'))
		{
			$db_alter->add_field(array(
				'name'       => 'phpkd_vblvb',
				'type'       => 'mediumtext',
				'default'    => '',
			));

			$db_alter->add_field(array(
				'name'       => 'phpkd_vblvb_lastcheck',
				'type'       => 'int',
				'attributes' => 'unsigned',
				'default'    => '0',
			));
		}

		$this->_vbulletin->db->show_errors();
	}

	/**
	 * Uninstall v4.0.101
	 *
	 * @return	void
	 */
	public function uninstall_40101()
	{
		$this->_vbulletin->db->hide_errors();
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($this->_vbulletin->db);

		if ($db_alter->fetch_table_info('post'))
		{
			$db_alter->drop_field('phpkd_vblvb');
			$db_alter->drop_field('phpkd_vblvb_lastcheck');
		}

		$this->_vbulletin->db->show_errors();
	}

	/**
	 * Install v4.0.200
	 *
	 * @return	void
	 */
	public function install_40200()
	{
		$this->_vbulletin->db->hide_errors();
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($this->_vbulletin->db);

		// Add permission field to the administrator table
		if ($db_alter->fetch_table_info('administrator'))
		{
			$db_alter->add_field(array(
				'name'       => 'phpkd_vblvb',
				'type'       => 'int',
				'attributes' => 'unsigned',
				'default'    => 0
			));
		}

		if ($db_alter->fetch_table_info('thread'))
		{
			$db_alter->add_field(array(
				'name'       => 'phpkd_vblvb_lastpunish',
				'type'       => 'mediumtext',
				'default'    => '',
			));
		}

		$this->_vbulletin->db->query_write("ALTER TABLE `" . TABLE_PREFIX . "post` CHANGE `phpkd_vblvb` `phpkd_vblvb_lastpunish` MEDIUMTEXT");

		$this->_vbulletin->db->query_write("
			CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "phpkd_vblvb_settinggroup`
			(
				`grouptitle` char(50) NOT NULL DEFAULT '',
				`displayorder` smallint(5) unsigned NOT NULL DEFAULT '0',
				`volatile` smallint(5) unsigned NOT NULL DEFAULT '0',
				PRIMARY KEY (`grouptitle`)
			)
		");

		$this->_vbulletin->db->query_write("
			CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "phpkd_vblvb_setting`
			(
				`varname` varchar(100) NOT NULL DEFAULT '',
				`grouptitle` varchar(50) NOT NULL DEFAULT '',
				`value` mediumtext,
				`defaultvalue` mediumtext,
				`optioncode` mediumtext,
				`displayorder` smallint(5) unsigned NOT NULL DEFAULT '0',
				`advanced` smallint(6) NOT NULL DEFAULT '0',
				`volatile` smallint(5) unsigned NOT NULL DEFAULT '0',
				`datatype` enum('free','number','boolean','bitfield','username','integer','posint') NOT NULL DEFAULT 'free',
				`validationcode` text,
				`blacklist` smallint(6) NOT NULL DEFAULT '0',
				PRIMARY KEY (`varname`)
			)
		");

		$this->_vbulletin->db->query_write("
			CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "phpkd_vblvb_host`
			(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`domain` varchar(250) NOT NULL,
				`active` smallint(5) unsigned NOT NULL DEFAULT '1',
				`status` enum('alive','down','dead') NOT NULL DEFAULT 'alive',
				`urlmatch` varchar(250) NOT NULL,
				`apiurl` varchar(250) DEFAULT NULL,
				`contentmatch` varchar(250) DEFAULT NULL,
				`urlsearch` varchar(250) DEFAULT NULL,
				`urlreplace` varchar(250) DEFAULT NULL,
				`downmatch` varchar(250) DEFAULT NULL,
				`notes` mediumtext,
				PRIMARY KEY (`id`)
			)
		");

		$this->_vbulletin->db->query_write("
			CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "phpkd_vblvb_log`
			(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`postid` int(10) NOT NULL DEFAULT '0',
				`dateline` int(10) unsigned NOT NULL DEFAULT '0',
				`content` mediumtext NOT NULL,
				`mode` enum('cronjob','manual') NOT NULL DEFAULT 'cronjob',
				`dead` int(5) unsigned NOT NULL DEFAULT '0',
				`punished` int(5) unsigned NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			)
		");

		$this->_vbulletin->db->query_write("
			CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "phpkd_vblvb_link`
			(
				`id` int(6) unsigned NOT NULL AUTO_INCREMENT,
				`host` varchar(250) NOT NULL,
				`url` mediumtext NOT NULL,
				`postid` int(6) NOT NULL,
				`lastcheck` int(10) NOT NULL,
				`hash` char(32) NOT NULL,
				`status` enum('alive','down','dead') NOT NULL DEFAULT 'alive',
				`logid` int(6) NOT NULL,
				PRIMARY KEY (`id`)
			)
		");

		// Import settings & hosts
		require_once(DIR . '/includes/phpkd/vblvb/adminfunctions.php');
		phpkd_vblvb_xml_import_settings(file_read(DIR . '/includes/phpkd/vblvb/settings.xml'));
		phpkd_vblvb_xml_restore_hosts(file_read(DIR . '/includes/phpkd/vblvb/hosts.xml'));

		$this->_vbulletin->db->show_errors();
	}

	/**
	 * Uninstall v4.0.200
	 *
	 * @return	void
	 */
	function uninstall_40200()
	{
		$this->_vbulletin->db->hide_errors();
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($this->_vbulletin->db);

		if ($db_alter->fetch_table_info('administrator'))
		{
			$db_alter->drop_field('phpkd_vblvb');
		}

		if ($db_alter->fetch_table_info('thread'))
		{
			$db_alter->drop_field('phpkd_vblvb_lastpunish');
		}

		if ($db_alter->fetch_table_info('post'))
		{
			$db_alter->drop_field('phpkd_vblvb_lastpunish');
		}

		$this->_vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "phpkd_vblvb_settinggroup");
		$this->_vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "phpkd_vblvb_setting");
		$this->_vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "phpkd_vblvb_host");
		$this->_vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "phpkd_vblvb_log");
		$this->_vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "phpkd_vblvb_link");

		$this->_vbulletin->db->show_errors();
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.200
|| # $Revision: 151 $
|| # Released: $Date: 2010-05-15 13:05:27 +0300 (Sat, 15 May 2010) $
|| ########################################################################### ||
\*============================================================================*/
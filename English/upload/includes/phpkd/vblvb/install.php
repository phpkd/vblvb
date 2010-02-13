<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 4.0.122 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # --------------- 'vB Link Verifier Bot' IS NOT FREE SOFTWARE ---------------- # ||
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
 * @package vB Link Verifier Bot 'Pro' Edition
 * @author PHP KingDom Development Team
 * @version $Revision$
 * @since $Date$
 * @copyright PHP KingDom (PHPKD)
 */
class PHPKD_VBLVB_Install
{
	/**
	* The vBulletin registry object
	*
	* @var	vB_Registry
	*/
	var $registry = null;


	/**
	* Array of product's info
	*
	* @var	array
	*/
	var $info = array();


	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its members ($this->db).
	* @param	array		Initialize required data (Hosts/Masks/Punishments/Reports)
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function PHPKD_VBLVB_Install(&$registry, $initparams = array())
	{
		if (is_object($registry))
		{
			$this->registry =& $registry;

			if (!is_object($registry->db))
			{
				trigger_error('Database object is not an object!', E_USER_ERROR);
			}
		}
		else
		{
			trigger_error('Registry object is not an object!', E_USER_ERROR);
		}


		if (is_array($initparams) AND !empty($initparams))
		{
			$this->initialize($initparams);
		}
	}


	/**
	* Initialize required fundamentals
	*
	* @param	array	Optional array of data describing the existing data we will be updating
	*
	* @return	boolean	Returns true if successful
	*/
	/**
	* Initialize required fundamentals
	*
	* @param	array	Optional array of data describing the existing data we will be updating
	*
	* @return	boolean	Returns true if successful
	*/
	function initialize($initparams)
	{
		foreach ($initparams AS $key => $value)
		{
			switch ($key)
			{
				case 'info':
					$this->info = $value;
					break;
			}
		}
	}


	function install_init()
	{
		if (!file_exists(DIR . '/includes/phpkd/vblvb/class_core.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/class_dm.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/class_dml.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/functions_databuild.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/cron.php') OR !file_exists(DIR . '/includes/xml/bitfield_phpkd_vblvb.xml')
		 OR !file_exists(DIR . '/includes/phpkd/vblvb/hooks/admin_options_print.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/hooks/admin_options_processing.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/hooks/editpost_update_process.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/hooks/newpost_process.php'))
		{
			print_dots_stop();
			print_cp_message('Please upload the files that came with "PHPKD - Link Verifier Bot" product before installing or upgrading!');
		}


		// ######################################################################
		// ## Debug Stuff: Begin                                               ##
		// ######################################################################

		$this->registry->db->hide_errors();

		// Import debug data in appropriate field
		$phpkdinfo = array(
			'productid'       => $this->info['productid'],
			'title'           => $this->info['title'],
			'version'         => $this->info['version'],
			'revision'        => trim(substr(substr('$Revision$', 10), 0, -1)),
			'author'          => 'Abdelrahman Hossam Omran (SolidSnake@GTI)',
			'vendor'          => 'PHP KingDom',
			'released'        => trim(substr(substr('$Date$', 6), 0, -1)),
			'url'             => $this->info['url'],
			'versioncheckurl' => $this->info['versioncheckurl'],
			'extradetails'    => str_replace('url', 'info', $this->info['url']),
			'installdateline' => TIMENOW
		);

		if ($this->registry->options['phpkd_commercial40_data'])
		{
			$holder = unserialize($this->registry->options['phpkd_commercial40_data']);
			$holder[$phpkdinfo['productid']] = $phpkdinfo;
			$data = $this->registry->db->escape_string(serialize($holder));
			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "setting
				SET value = '$data'
				WHERE varname = 'phpkd_commercial40_data'
			");
		}
		else
		{
			$holder[$phpkdinfo['productid']] = $phpkdinfo;
			$data = $this->registry->db->escape_string(serialize($holder));

			$this->registry->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "setting
					(varname, grouptitle, value, defaultvalue, datatype, optioncode, displayorder, advanced, volatile, validationcode, blacklist, product)
				VALUES
					('phpkd_commercial40_data', 'version', '$data', '', 'free', '', '40100', '0', '1', '', '0', 'phpkd_framework')
			");

			$this->registry->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "phrase
					(languageid, fieldname, varname, text, product, username, dateline, version)
				VALUES
					('-1', 'vbsettings', 'setting_phpkd_commercial40_data_title', 'PHP KingDom (PHPKD) Commercial Products\' Data (4.0.x) [Sensitive]', 'phpkd_framework', '" . $this->registry->db->escape_string($this->registry->userinfo['username']) . "', " . TIMENOW . ", '4.0.100'),
					('-1', 'vbsettings', 'setting_phpkd_commercial40_data_desc', 'PHP KingDom (PHPKD) Commercial Products\' Data used for debugging issues. <strong>[Sensitive Data, DON\'T ALTER]</strong>.', 'phpkd_framework', '" . $this->registry->db->escape_string($this->registry->userinfo['username']) . "', " . TIMENOW . ", '4.0.100')
				");
		}
		unset($holder, $data);

		print_dots_start("Installing: \"" . $phpkdinfo['title'] . "\"<br />Version: " . $phpkdinfo['version'] . ", Revision: " . $phpkdinfo['revision'] . ", Released: " . $phpkdinfo['released'] . ".<br />Thanks for choosing PHP KingDom's Products. If you need any help or wish to try any other products we have, just give us a visit at <a href=\"http://www.phpkd.net\" target=\"_blank\">www.phpkd.net</a>. You are always welcomed.<br />Please Wait...", ':', 'phpkd_vblvb_install_info');
		print_dots_stop();

		// ######################################################################
		// ## Debug Stuff: End                                                 ##
		// ######################################################################


		// ######################################################################
		// ## License Stuff: Begin                                             ##
		// ######################################################################
		if (!$this->registry->options['phpkd_commercial40_license'])
		{
			$holder[$phpkdinfo['productid']] = $phpkdinfo;
			$data = $this->registry->db->escape_string(serialize($holder));

			$this->registry->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "setting
					(varname, grouptitle, value, defaultvalue, datatype, optioncode, displayorder, advanced, volatile, validationcode, blacklist, product)
				VALUES
					('phpkd_commercial40_license', 'version', '', '', 'free', '', '40100', '0', '1', '', '0', 'phpkd_framework')
			");

			$this->registry->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "phrase
					(languageid, fieldname, varname, text, product, username, dateline, version)
				VALUES
					('-1', 'vbsettings', 'setting_phpkd_commercial40_license_title', 'PHP KingDom (PHPKD) Commercial Products\' License Data (4.0.x) [Sensitive]', 'phpkd_framework', '" . $this->registry->db->escape_string($this->registry->userinfo['username']) . "', " . TIMENOW . ", '4.0.100'),
					('-1', 'vbsettings', 'setting_phpkd_commercial40_license_desc', 'PHP KingDom (PHPKD) Commercial Products\' License Data used for licensing issues. <strong>[Sensitive Data, DON\'T ALTER]</strong>.', 'phpkd_framework', '" . $this->registry->db->escape_string($this->registry->userinfo['username']) . "', " . TIMENOW . ", '4.0.100')
				");

			unset($holder, $data);
		}
		// ######################################################################
		// ## License Stuff: End                                               ##
		// ######################################################################

		$this->registry->db->show_errors();
	}


	function install_40101()
	{
		$this->registry->db->hide_errors();
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($this->registry->db);


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

		$this->registry->db->show_errors();
	}


	function uninstall_init()
	{
		// ######################################################################
		// ## Debug Stuff: Begin                                               ##
		// ######################################################################

		$this->registry->db->hide_errors();
		if ($this->registry->options['phpkd_commercial40_data'])
		{
			$holder = unserialize($this->registry->options['phpkd_commercial40_data']);

			if ($holder[$this->registry->db->escape_string($this->registry->GPC['productid'])])
			{
				$phpkdinfo = $holder[$this->registry->db->escape_string($this->registry->GPC['productid'])];
				print_dots_start("Un-installing: \"" . $phpkdinfo['title'] . "\"<br />Version: " . $phpkdinfo['version'] . ", Revision: " . $phpkdinfo['revision'] . ", Released: " . $phpkdinfo['released'] . ".<br />We are sad to see you un-installing '" . $phpkdinfo['title'] . "'. Please if there is any thing we can do to keep you using this software product, just tell us at <a href=\"http://www.phpkd.net\" target=\"_blank\">www.phpkd.net</a>.<br />Please Wait...", ':', 'phpkd_vblvb_uninstall_info');
				unset($holder[$this->registry->db->escape_string($this->registry->GPC['productid'])]);
			}

			if (is_array($holder) AND !empty($holder))
			{
				$data = $this->registry->db->escape_string(serialize($holder));
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "setting SET
					value = '$data'
					WHERE varname = 'phpkd_commercial40_data'
				");
			}
			else
			{
				// delete phrases
				$this->registry->db->query_write("
					DELETE FROM " . TABLE_PREFIX . "phrase
					WHERE languageid IN (-1, 0) AND
						fieldname = 'vbsettings' AND
						varname IN ('setting_phpkd_commercial40_data_title', 'setting_phpkd_commercial40_data_desc')
				");

				// delete setting
				$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "setting WHERE varname = 'phpkd_commercial40_data'");
			}

			unset($holder, $data);
		}
		// ######################################################################
		// ## Debug Stuff: End                                                 ##
		// ######################################################################


		// ######################################################################
		// ## License Stuff: Begin                                             ##
		// ######################################################################
		if ($this->registry->options['phpkd_commercial40_license'])
		{
			$holder = unserialize($this->registry->options['phpkd_commercial40_license']);

			if ($holder[$this->registry->db->escape_string($this->registry->GPC['productid'])])
			{
				unset($holder[$this->registry->db->escape_string($this->registry->GPC['productid'])]);
			}

			if (is_array($holder) AND !empty($holder))
			{
				$data = $this->registry->db->escape_string(serialize($holder));
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "setting SET
					value = '$data'
					WHERE varname = 'phpkd_commercial40_license'
				");
			}
			else
			{
				// delete phrases
				$this->registry->db->query_write("
					DELETE FROM " . TABLE_PREFIX . "phrase
					WHERE languageid IN (-1, 0) AND
						fieldname = 'vbsettings' AND
						varname IN ('setting_phpkd_commercial40_license_title', 'setting_phpkd_commercial40_license_desc')
				");

				// delete setting
				$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "setting WHERE varname = 'phpkd_commercial40_license'");
			}

			unset($holder, $data);
		}
		// ######################################################################
		// ## License Stuff: End                                               ##
		// ######################################################################

		$this->registry->db->show_errors();
	}


	function uninstall_40101()
	{
		$this->registry->db->hide_errors();
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($this->registry->db);

		if ($db_alter->fetch_table_info('post'))
		{
			$db_alter->drop_field('phpkd_vblvb');
			$db_alter->drop_field('phpkd_vblvb_lastcheck');
		}

		$this->registry->db->show_errors();
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.122
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
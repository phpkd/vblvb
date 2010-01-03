<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 3.8.100 # ||
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


function phpkd_vblvb_install_init()
{
	global $vbulletin, $arr, $info;
	$db =& $vbulletin->db;

	if (!file_exists(DIR . '/includes/phpkd/vblvb/functions.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/plugins.php') OR !file_exists(DIR . '/includes/phpkd/vblvb/cron.php') OR !file_exists(DIR . '/includes/xml/bitfield_phpkd_vblvb.xml'))
	{
		print_dots_stop();
		print_cp_message('Please upload the files that came with "PHPKD - Link Verifier Bot" product before installing or upgrading!');
	}


	// ######################################################################
	// ## Debug Stuff: Begin                                               ##
	// ######################################################################

	$db->hide_errors();
	// look to see if we already have old versions of this product installed!
	$oldpid = "ssgti_linkverifier";
	if ($existingprod = $db->query_first("
		SELECT *
		FROM " . TABLE_PREFIX . "product
		WHERE productid = '" . $db->escape_string($oldpid) . "'"
	))
	{
		print_dots_start("Deleting old version of this product .. (Title: " . $existingprod['title'] . ", ID: " . $existingprod['productid'] . ", Version: " . $existingprod['version'] . ").<br />Please Wait...", ':', 'phpkd_vblvb_uninstall_old');
		delete_product($oldpid);
		print_dots_stop();
	}

	// Import debug data in appropriate field
	$phpkdinfo = $info;
	unset($phpkdinfo['description']);
	$phpkdinfo['author'] = trim(substr(substr($arr['author'], 8), 0, -1));
	$phpkdinfo['vendor'] = trim(substr(substr($arr['vendor'], 8), 0, -1));
	$phpkdinfo['revision'] = trim(substr(substr($arr['revision'], 10), 0, -1));
	$phpkdinfo['released'] = trim(substr(substr($arr['released'], 6), 0, -1));
	$phpkdinfo['installdateline'] = TIMENOW;
	if ($vbulletin->options['phpkd_commercial38_data'])
	{
		$holder = unserialize($vbulletin->options['phpkd_commercial38_data']);
		$holder[$phpkdinfo['productid']] = $phpkdinfo;
		$data = $db->escape_string(serialize($holder));
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "setting
			SET value = '$data'
			WHERE varname = 'phpkd_commercial38_data'
		");
	}
	else
	{
		$holder[$phpkdinfo['productid']] = $phpkdinfo;
		$data = $db->escape_string(serialize($holder));

		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "setting
				(varname, grouptitle, value, defaultvalue, datatype, optioncode, displayorder, advanced, volatile, validationcode, blacklist, product)
			VALUES
				('phpkd_commercial38_data', 'version', '$data', '', 'free', '', '38100', '0', '1', '', '0', 'phpkd_framework')
		");

		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "phrase
				(languageid, fieldname, varname, text, product, username, dateline, version)
			VALUES
				('-1', 'vbsettings', 'setting_phpkd_commercial38_data_title', 'PHP KingDom (PHPKD) Commercial Products\' Data (3.8.x) [Sensitive]', 'phpkd_framework', '" . $db->escape_string($vbulletin->userinfo['username']) . "', " . TIMENOW . ", '3.8.100'),
				('-1', 'vbsettings', 'setting_phpkd_commercial38_data_desc', 'PHP KingDom (PHPKD) Commercial Products\' Data used for debugging issues. <strong>[Sensitive Data, DON\'T ALTER]</strong>.', 'phpkd_framework', '" . $db->escape_string($vbulletin->userinfo['username']) . "', " . TIMENOW . ", '3.8.100')
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
	if (!$vbulletin->options['phpkd_commercial38_license'])
	{
		$holder[$phpkdinfo['productid']] = $phpkdinfo;
		$data = $db->escape_string(serialize($holder));

		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "setting
				(varname, grouptitle, value, defaultvalue, datatype, optioncode, displayorder, advanced, volatile, validationcode, blacklist, product)
			VALUES
				('phpkd_commercial38_license', 'version', '', '', 'free', '', '38101', '0', '1', '', '0', 'phpkd_framework')
		");

		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "phrase
				(languageid, fieldname, varname, text, product, username, dateline, version)
			VALUES
				('-1', 'vbsettings', 'setting_phpkd_commercial38_license_title', 'PHP KingDom (PHPKD) Commercial Products\' License Data (3.8.x) [Sensitive]', 'phpkd_framework', '" . $db->escape_string($vbulletin->userinfo['username']) . "', " . TIMENOW . ", '3.8.100'),
				('-1', 'vbsettings', 'setting_phpkd_commercial38_license_desc', 'PHP KingDom (PHPKD) Commercial Products\' License Data used for licensing issues. <strong>[Sensitive Data, DON\'T ALTER]</strong>.', 'phpkd_framework', '" . $db->escape_string($vbulletin->userinfo['username']) . "', " . TIMENOW . ", '3.8.100')
			");

		unset($holder, $data);
	}
	// ######################################################################
	// ## License Stuff: End                                               ##
	// ######################################################################

	$db->show_errors();
}


function phpkd_vblvb_install_38100()
{
	global $vbulletin;
	$db =& $vbulletin->db;


	$db->hide_errors();
	require_once(DIR . '/includes/class_dbalter.php');
	$db_alter =& new vB_Database_Alter_MySQL($db);


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

	$db->show_errors();
}


function phpkd_vblvb_uninstall_init()
{
	global $vbulletin;
	$db =& $vbulletin->db;

	// ######################################################################
	// ## Debug Stuff: Begin                                               ##
	// ######################################################################

	$db->hide_errors();
	if ($vbulletin->options['phpkd_commercial38_data'])
	{
		$holder = unserialize($vbulletin->options['phpkd_commercial38_data']);

		if ($holder[$db->escape_string($vbulletin->GPC['productid'])])
		{
			$phpkdinfo = $holder[$db->escape_string($vbulletin->GPC['productid'])];
			print_dots_start("Un-installing: " . $phpkdinfo['title'] . "<br />Version: " . $phpkdinfo['version'] . ", Revision: " . $phpkdinfo['revision'] . ", Released: " . $phpkdinfo['released'] . ".<br />We are sad to see you un-installing '" . $phpkdinfo['title'] . "'. Please if there is any thing we can do to keep you using this software product, just tell us at <a href=\"http://www.phpkd.net\" target=\"_blank\">www.phpkd.net</a>.<br />Please Wait...", ':', 'phpkd_vblvb_uninstall_info');
			unset($holder[$db->escape_string($vbulletin->GPC['productid'])]);
		}

		if (is_array($holder) AND !empty($holder))
		{
			$data = $db->escape_string(serialize($holder));
			$db->query_write("
				UPDATE " . TABLE_PREFIX . "setting SET
				value = '$data'
				WHERE varname = 'phpkd_commercial38_data'
			");
		}
		else
		{
			// delete phrases
			$db->query_write("
				DELETE FROM " . TABLE_PREFIX . "phrase
				WHERE languageid IN (-1, 0) AND
					fieldname = 'vbsettings' AND
					varname IN ('setting_phpkd_commercial38_data_title', 'setting_phpkd_commercial38_data_desc')
			");

			// delete setting
			$db->query_write("DELETE FROM " . TABLE_PREFIX . "setting WHERE varname = 'phpkd_commercial38_data'");
		}

		unset($holder, $data);
	}
	// ######################################################################
	// ## Debug Stuff: End                                                 ##
	// ######################################################################


	// ######################################################################
	// ## License Stuff: Begin                                             ##
	// ######################################################################
	if ($vbulletin->options['phpkd_commercial38_license'])
	{
		$holder = unserialize($vbulletin->options['phpkd_commercial38_license']);

		if ($holder[$db->escape_string($vbulletin->GPC['productid'])])
		{
			unset($holder[$db->escape_string($vbulletin->GPC['productid'])]);
		}

		if (is_array($holder) AND !empty($holder))
		{
			$data = $db->escape_string(serialize($holder));
			$db->query_write("
				UPDATE " . TABLE_PREFIX . "setting SET
				value = '$data'
				WHERE varname = 'phpkd_commercial38_license'
			");
		}
		else
		{
			// delete phrases
			$db->query_write("
				DELETE FROM " . TABLE_PREFIX . "phrase
				WHERE languageid IN (-1, 0) AND
					fieldname = 'vbsettings' AND
					varname IN ('setting_phpkd_commercial38_license_title', 'setting_phpkd_commercial38_license_desc')
			");

			// delete setting
			$db->query_write("DELETE FROM " . TABLE_PREFIX . "setting WHERE varname = 'phpkd_commercial38_license'");
		}

		unset($holder, $data);
	}
	// ######################################################################
	// ## License Stuff: End                                               ##
	// ######################################################################

	$db->show_errors();
}


function phpkd_vblvb_uninstall_38100()
{
	global $vbulletin;
	$db =& $vbulletin->db;

	$db->hide_errors();
	require_once(DIR . '/includes/class_dbalter.php');
	$db_alter =& new vB_Database_Alter_MySQL($db);

	if ($db_alter->fetch_table_info('post'))
	{
		$db_alter->drop_field('phpkd_vblvb');
		$db_alter->drop_field('phpkd_vblvb_lastcheck');
	}

	$db->show_errors();
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 3.8.100
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
?>
<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.1.210 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2011 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE & ~8192);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile$ - $Revision$');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array(
	'user',
	'cpuser',
	'cppermission',
	'cpoption',
	'logging',
	'phpkd_vblvb'
);

$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_misc.php');
require_once(DIR . '/includes/phpkd/vblvb/class_core.php');
require_once(DIR . '/includes/phpkd/vblvb/adminfunctions.php');
require_once(DIR . '/includes/functions_misc.php');

$vbulletin->input->clean_array_gpc('r', array(
	'varname' => TYPE_STR,
	'dogroup' => TYPE_STR,
));

// intercept direct call to do=options with $varname specified instead of $dogroup
if ($_REQUEST['do'] == 'options' AND !empty($vbulletin->GPC['varname']))
{
	if ($vbulletin->GPC['varname'] == '[all]')
	{
		// go ahead and show all settings
		$vbulletin->GPC['dogroup'] = '[all]';
	}
	else if ($group = $db->query_first("SELECT varname, grouptitle FROM " . TABLE_PREFIX . "phpkd_vblvb_setting WHERE varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "'"))
	{
		// redirect to show the correct group and use and anchor to jump to the correct variable
		exec_header_redirect('phpkd_vblvb.php?' . $vbulletin->session->vars['sessionurl_js'] . "do=options&dogroup=$group[grouptitle]#$group[varname]");
	}
	else
	{
		// could not find a matching group - just carry on as if nothing happened
		$_REQUEST['do'] = 'options';
	}
}

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('phpkd_vblvb'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

// query settings phrases
$settingphrase = array();
$phrases = $db->query_read("
	SELECT varname, text
	FROM " . TABLE_PREFIX . "phrase
	WHERE fieldname = 'phpkd_vblvb' AND
		languageid IN(-1, 0, " . LANGUAGEID . ")
	ORDER BY languageid ASC
");
while($phrase = $db->fetch_array($phrases))
{
	$settingphrase["$phrase[varname]"] = $phrase['text'];
}

// #############################################################################
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'options';
}

if (in_array($_REQUEST['do'], array('downloadsettings', 'filessettings', 'removesetting', 'editgroup', 'addgroup', 'removegroup')) OR in_array($_POST['do'], array('updatesetting', 'insertsetting', 'killsetting', 'updategroup', 'insertgroup', 'killgroup')) OR ($_POST['do'] == 'doimportsetting' AND !$_POST['restore']))
{
	if (!$vbulletin->debug OR !PHPKD_VBLVB_DEBUG)
	{
		print_stop_message('phpkd_vblvb_developers_only');
	}
}

// ########################## Run scheduled task ############################
if ($_REQUEST['do'] == 'runcron')
{
	$cron = $db->query_first("SELECT cronid FROM " . TABLE_PREFIX . "cron WHERE varname = 'phpkd_vblvb'");
	exec_header_redirect('cronadmin.php?' . $vbulletin->session->vars['sessionurl_js'] . "do=runcron&cronid=$cron[cronid]");
}

// ###################### Start download XML settings #######################
if ($_REQUEST['do'] == 'downloadsettings')
{
	$setting = array();
	$settinggroup = array();

	$groups = $db->query_read("
		SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup
		WHERE volatile = 1
		ORDER BY displayorder, grouptitle
	");
	while ($group = $db->fetch_array($groups))
	{
		$settinggroup["$group[grouptitle]"] = $group;
	}

	$sets = $db->query_read("
		SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
		WHERE volatile = 1
		ORDER BY displayorder, varname
	");
	while ($set = $db->fetch_array($sets))
	{
		$setting["$set[grouptitle]"][] = $set;
	}
	unset($set);
	$db->free_result($sets);

	require_once(DIR . '/includes/class_xml.php');
	$xml = new vB_XML_Builder($vbulletin);
	$xml->add_group('settinggroups');

	foreach($settinggroup AS $grouptitle => $group)
	{
		if (!empty($setting["$grouptitle"]))
		{
			$group = $settinggroup["$grouptitle"];
			$xml->add_group('settinggroup', array('name' => htmlspecialchars($group['grouptitle']), 'displayorder' => $group['displayorder']));

			foreach($setting["$grouptitle"] AS $set)
			{
				$arr = array('varname' => $set['varname'], 'displayorder' => $set['displayorder']);

				if ($set['advanced'])
				{
					$arr['advanced'] = 1;
				}

				$xml->add_group('setting', $arr);

				if ($set['datatype'])
				{
					$xml->add_tag('datatype', $set['datatype']);
				}

				if ($set['optioncode'] != '')
				{
					$xml->add_tag('optioncode', $set['optioncode']);
				}

				if ($set['validationcode'])
				{
					$xml->add_tag('validationcode', $set['validationcode']);
				}

				if ($set['defaultvalue'] != '')
				{
					$xml->add_tag('defaultvalue', $set['defaultvalue']);
				}

				if ($set['blacklist'])
				{
					$xml->add_tag('blacklist', 1);
				}

				$xml->close_group();
			}

			$xml->close_group();
		}
	}

	$xml->close_group();

	$doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";

	$doc .= $xml->output();
	$xml = null;

	require_once(DIR . '/includes/functions_file.php');
	file_download($doc, 'phpkd_vblvb-settings.xml', 'text/xml');
}

// ###################### Start product XML backup #######################
if ($_REQUEST['do'] == 'backupsettings')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'blacklist' => TYPE_BOOL,
	));

	require_once(DIR . '/includes/class_xml.php');
	$xml = new vB_XML_Builder($vbulletin);
	$xml->add_group('settings');

	$sets = $db->query_read("
		SELECT varname, value
		FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
		WHERE 1 = 1
		" . ($vbulletin->GPC['blacklist'] ? "AND blacklist = 0" : "" ). "
		ORDER BY displayorder
	");

	while ($set = $db->fetch_array($sets))
	{
		$arr = array('varname' => $set['varname']);
		$xml->add_group('setting', $arr);

		if ($set['value'] != '')
		{
			$xml->add_tag('value', $set['value']);
		}

		$xml->close_group();
	}

	$xml->close_group();

	$doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";

	$doc .= $xml->output();
	$xml = null;

	require_once(DIR . '/includes/functions_file.php');
	file_download($doc, 'phpkd_vblvb-settings.xml', 'text/xml');
}

// ###################### AJAX setting value validation ########################
if ($_POST['do'] == 'validate')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'varname' => TYPE_STR,
		'setting' => TYPE_ARRAY
	));

	$varname = convert_urlencoded_unicode($vbulletin->GPC['varname']);
	$value = convert_urlencoded_unicode($vbulletin->GPC['setting']["$varname"]);

	require_once(DIR . '/includes/class_xml.php');

	$xml = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');
	$xml->add_group('setting');
	$xml->add_tag('varname', $varname);

	if ($setting = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_setting WHERE varname = '" . $db->escape_string($varname) . "'"))
	{
		$raw_value = $value;
		$value = phpkd_vblvb_validate_value($value, $setting['datatype']);
		$valid = phpkd_vblvb_validation_code($setting['varname'], $value, $setting['validationcode'], $raw_value);
	}
	else
	{
		$valid = 1;
	}

	$xml->add_tag('valid', $valid);
	$xml->close_group();
	$xml->print_xml();
}

// ###################### Start hosts XML backup #######################
if ($_REQUEST['do'] == 'backuphosts')
{
	require_once(DIR . '/includes/class_xml.php');
	$xml = new vB_XML_Builder($vbulletin);
	$xml->add_group('hosts');

	$hosts = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "phpkd_vblvb_host
		ORDER BY domain ASC
	");

	while ($host = $db->fetch_array($hosts))
	{
		$arr = array('domain' => $host['domain'], 'active' => $host['active'], 'status' => $host['status']);
		$xml->add_group('host', $arr);

		if ($host['urlmatch'] != '')
		{
			$xml->add_tag('urlmatch', $host['urlmatch']);
		}

		if ($host['apiurl'] != '')
		{
			$xml->add_tag('apiurl', $host['apiurl']);
		}

		if ($host['contentmatch'] != '')
		{
			$xml->add_tag('contentmatch', $host['contentmatch']);
		}

		if ($host['downmatch'] != '')
		{
			$xml->add_tag('downmatch', $host['downmatch']);
		}

		if ($host['urlsearch'] != '')
		{
			$xml->add_tag('urlsearch', $host['urlsearch']);
		}

		if ($host['urlreplace'] != '')
		{
			$xml->add_tag('urlreplace', $host['urlreplace']);
		}

		if ($host['notes'] != '')
		{
			$xml->add_tag('notes', $host['notes']);
		}

		$xml->close_group();
	}

	$xml->close_group();

	$doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";

	$doc .= $xml->output();
	$xml = null;

	require_once(DIR . '/includes/functions_file.php');
	file_download($doc, 'phpkd_vblvb-hosts.xml', 'text/xml');
}

// ***********************************************************************
if (in_array($_REQUEST['do'], array('viewlog', 'prunelog', 'log')) OR $_POST['do'] == 'doprunelog')
{
	print_cp_header($vbphrase['phpkd_vblvb_log']);
}
else if (in_array($_REQUEST['do'], array('hosts', 'addhost', 'edithost', 'deletehost', 'backuprestorehosts', 'backuphosts')) OR in_array($_POST['do'], array('inserthost', 'updatehost', 'killhost', 'deletehost', 'doimporthosts', 'quickupdatehosts')))
{
	print_cp_header($vbphrase['phpkd_vblvb_host_manager']);
}
else
{
	print_cp_header($vbphrase['phpkd_vblvb_options'], iif($vbulletin->GPC['dogroup'] == 'lookfeel' OR $vbulletin->GPC['dogroup'] == '[all]', 'init_color_preview()'));

	if ($vbulletin->GPC['dogroup'] == 'lookfeel' OR $vbulletin->GPC['dogroup'] == '[all]')
	{
		echo '<script type="text/javascript" src="../clientscript/vbulletin_cpcolorpicker.js?v=' . SIMPLE_VERSION . '"></script>';
	}
}


// ###################### Start do import settings XML #######################
if ($_POST['do'] == 'doimportsetting')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'serversettingsfile' => TYPE_STR,
		'restore'            => TYPE_BOOL,
		'blacklist'          => TYPE_BOOL,
	));

	$vbulletin->input->clean_array_gpc('f', array(
		'settingsfile' => TYPE_FILE
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	// got an uploaded file?
	if (file_exists($vbulletin->GPC['settingsfile']['tmp_name']))
	{
		$xml = file_read($vbulletin->GPC['settingsfile']['tmp_name']);
	}
	// no uploaded file - got a local file?
	else if (file_exists($vbulletin->GPC['serversettingsfile']))
	{
		$xml = file_read($vbulletin->GPC['serversettingsfile']);
	}
	// no uploaded file and no local file - ERROR
	else
	{
		print_stop_message('no_file_uploaded_and_no_local_file_found');
	}

	if ($vbulletin->GPC['restore'])
	{
		phpkd_vblvb_xml_restore_settings($xml, $vbulletin->GPC['blacklist']);
	}
	else
	{
		phpkd_vblvb_xml_import_settings($xml);
	}

	print_cp_redirect("phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'], 0);
}

// ###################### Start import settings XML #######################
if ($_REQUEST['do'] == 'filessettings')
{
	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	$vbulletin->input->clean_array_gpc('r', array(
		'type' => TYPE_NOHTML
	));

	// download form
	print_form_header('phpkd_vblvb', 'downloadsettings', 0, 1, 'downloadsettingsform', '90%', '', true, 'post" target="downloadsettings');
	print_table_header($vbphrase['download']);
	print_submit_row($vbphrase['download'], 0);

	?>
	<script type="text/javascript">
	<!--
	function js_confirm_upload(tform, filefield)
	{
		if (filefield.value == "")
		{
			return confirm("<?php echo construct_phrase($vbphrase['you_did_not_specify_a_file_to_upload'], '" + tform.serversettingsfile.value + "'); ?>");
		}
		return true;
	}
	//-->
	</script>
	<?php

	print_form_header('phpkd_vblvb', 'doimportsetting', 1, 1, 'uploadsettingform', '90%', '', true, 'post" onsubmit="return js_confirm_upload(this, this.settingsfile);');
	print_table_header($vbphrase['import_settings_xml_file']);
	print_upload_row($vbphrase['upload_xml_file'], 'settingsfile', 999999999);
	print_input_row($vbphrase['import_xml_file'], 'serversettingsfile', './includes/phpkd/vblvb/settings.xml');
	print_submit_row($vbphrase['import'], 0);
}

// ###################### Start kill setting group #######################
if ($_POST['do'] == 'killgroup')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'title' => TYPE_STR
	));

	// get some info
	$group = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup WHERE grouptitle = '" . $db->escape_string($vbulletin->GPC['title']) . "'");

	// query settings from this group
	$settings = array();
	$sets = $db->query_read("SELECT varname FROM " . TABLE_PREFIX . "phpkd_vblvb_setting WHERE grouptitle = '$group[grouptitle]'");
	while ($set = $db->fetch_array($sets))
	{
		$settings[] = $db->escape_string($set['varname']);
	}

	// build list of phrases to be deleted
	$phrases = array("settinggroup_$group[grouptitle]");
	foreach($settings AS $varname)
	{
		$phrases[] = 'setting_' . $varname . '_title';
		$phrases[] = 'setting_' . $varname . '_desc';
	}

	// delete phrases
	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "phrase
		WHERE languageid IN (-1,0) AND
			fieldname = 'phpkd_vblvb' AND
			varname IN ('" . implode("', '", $phrases) . "')
	");

	// delete settings
	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
		WHERE varname IN ('" . implode("', '", $settings) . "')
	");

	// delete group
	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup
		WHERE grouptitle = '" . $db->escape_string($group['grouptitle']) . "'
	");

	// rebuild the $vbulletin->phpkd_vblvb array
	phpkd_vblvb_build();

	define('CP_REDIRECT', 'phpkd_vblvb.php');
	print_stop_message('deleted_setting_group_successfully');
}

// ###################### Start remove setting group #######################
if ($_REQUEST['do'] == 'removegroup')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'grouptitle' => TYPE_STR
	));

	phpkd_vblvb_delete_confirmation('phpkd_vblvb_settinggroup', $vbulletin->GPC['grouptitle'], 'phpkd_vblvb', 'killgroup');
}

// ###################### Start insert setting group #######################
if ($_POST['do'] == 'insertgroup')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'group' => TYPE_ARRAY
	));

	// insert setting place-holder
	$db->query_write("
		INSERT INTO " . TABLE_PREFIX . "phpkd_vblvb_settinggroup
			(grouptitle)
		VALUES
			('" . $db->escape_string($vbulletin->GPC['group']['grouptitle']) . "')
	");

	// insert associated phrases
	$languageid = iif($vbulletin->GPC['group']['volatile'], -1, 0);

	$db->query_write("
		INSERT INTO " . TABLE_PREFIX . "phrase
			(languageid, fieldname, varname, text, product, username, dateline, version)
		VALUES
			($languageid,
			'phpkd_vblvb',
			'settinggroup_" . $db->escape_string($vbulletin->GPC['group']['grouptitle']) . "',
			'" . $db->escape_string($vbulletin->GPC['group']['title']) . "',
			'phpkd_vblvb',
			'" . $db->escape_string($vbulletin->userinfo['username']) . "',
			" . TIMENOW . ",
			'" . $db->escape_string(PHPKD_VBLVB_VERSION) . "')
	");

	// fall through to 'updategroup' for the real work...
	$_POST['do'] = 'updategroup';
}

// ###################### Start update setting group #######################
if ($_POST['do'] == 'updategroup')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'group' => TYPE_ARRAY
	));

	$db->query_write("
		UPDATE " . TABLE_PREFIX . "phpkd_vblvb_settinggroup SET
			displayorder = " . intval($vbulletin->GPC['group']['displayorder']) . ",
			volatile = " . intval($vbulletin->GPC['group']['volatile']) . "
		WHERE grouptitle = '" . $db->escape_string($vbulletin->GPC['group']['grouptitle']) . "'
	");

	$db->query_write("
		UPDATE " . TABLE_PREFIX . "phrase SET
			text = '" . $db->escape_string($vbulletin->GPC['group']['title']) . "',
			product = 'phpkd_vblvb',
			username = '" . $db->escape_string($vbulletin->userinfo['username']) . "',
			dateline = " . TIMENOW . ",
			version = '" . $db->escape_string(PHPKD_VBLVB_VERSION) . "'
		WHERE languageid IN(-1, 0)
			AND varname = 'settinggroup_" . $db->escape_string($vbulletin->GPC['group']['grouptitle']) . "'
	");

	$settingnames = array();
	$phrasenames = array();

	$settings = $db->query_read("
		SELECT varname
		FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
		WHERE grouptitle = '" . $db->escape_string($vbulletin->GPC['group']['grouptitle']) . "'
	");

	while ($setting = $db->fetch_array($settings))
	{
		$settingnames[] = "'" . $db->escape_string($setting['varname']) . "'";
		$phrasenames[] = "'" . $db->escape_string('setting_' . $setting['varname'] . '_desc') . "'";
		$phrasenames[] = "'" . $db->escape_string('setting_' . $setting['varname'] . '_title') . "'";
	}

	if ($db->num_rows($settings))
	{
		$q2 = "
			UPDATE " . TABLE_PREFIX . "phrase SET
				product = 'phpkd_vblvb',
				username = '" . $db->escape_string($vbulletin->userinfo['username']) . "',
				dateline = " . TIMENOW . ",
				version = '" . $db->escape_string(PHPKD_VBLVB_VERSION) . "'
			WHERE varname IN(
				" . implode(",\n				", $phrasenames) . "
			) AND fieldname = 'phpkd_vblvb'
		";

		$db->query_write($q2);
	}

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=options&amp;dogroup=' . $vbulletin->GPC['group']['grouptitle']);
	print_stop_message('saved_setting_group_x_successfully', $vbulletin->GPC['group']['title']);
}

// ###################### Start edit setting group #######################
if ($_REQUEST['do'] == 'editgroup' OR $_REQUEST['do'] == 'addgroup')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'grouptitle' => TYPE_STR,
	));

	if ($_REQUEST['do'] == 'editgroup')
	{
		$group = $db->query_first("
			SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup
			WHERE grouptitle = '" . $db->escape_string($vbulletin->GPC['grouptitle']) . "'
		");

		$phrase = $db->query_first("
			SELECT text FROM " . TABLE_PREFIX . "phrase
			WHERE languageid IN (-1,0) AND
				fieldname = 'phpkd_vblvb' AND
				varname = 'settinggroup_" . $db->escape_string($group['grouptitle']) . "'
		");

		$group['title'] = $phrase['text'];
		$pagetitle = construct_phrase($vbphrase['x_y_id_z'], $vbphrase['setting_group'], $group['title'], $group['grouptitle']);
		$formdo = 'updategroup';
	}
	else
	{
		$ordercheck = $db->query_first("
			SELECT displayorder
			FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup
			ORDER BY displayorder DESC
		");

		$group = array(
			'displayorder' => $ordercheck['displayorder'] + 10,
			'volatile' => iif($vbulletin->debug AND PHPKD_VBLVB_DEBUG, 1, 0)
		);

		$pagetitle = $vbphrase['add_new_setting_group'];
		$formdo = 'insertgroup';
	}

	print_form_header('phpkd_vblvb', $formdo);
	print_table_header($pagetitle);

	if ($_REQUEST['do'] == 'editgroup')
	{
		print_label_row($vbphrase['varname'], "<b>$group[grouptitle]</b>");
		construct_hidden_code('group[grouptitle]', $group['grouptitle']);
	}
	else
	{
		print_input_row($vbphrase['varname'], 'group[grouptitle]', $group['grouptitle']);
	}

	print_input_row($vbphrase['title'], 'group[title]', $group['title']);
	print_input_row($vbphrase['display_order'], 'group[displayorder]', $group['displayorder']);

	if ($vbulletin->debug AND PHPKD_VBLVB_DEBUG)
	{
		print_yes_no_row($vbphrase['vbulletin_default'], 'group[volatile]', $group['volatile']);
	}
	else
	{
		construct_hidden_code('group[volatile]', $group['volatile']);
	}

	print_submit_row($vbphrase['save']);
}

// ###################### Start kill setting #######################
if ($_POST['do'] == 'killsetting')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'title' => TYPE_STR
	));

	// get some info
	$setting = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_setting WHERE varname = '" . $db->escape_string($vbulletin->GPC['title']) . "'");

	// delete phrases
	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "phrase
		WHERE languageid IN (-1, 0) AND
			fieldname = 'phpkd_vblvb' AND
			varname IN ('setting_" . $db->escape_string($setting['varname']) . "_title', 'setting_" . $db->escape_string($setting['varname']) . "_desc')
	");

	// delete setting
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_setting WHERE varname = '" . $db->escape_string($setting['varname']) . "'");

	// rebuild the $vbulletin->phpkd_vblvb array
	phpkd_vblvb_build();

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=options&amp;dogroup=' . $setting['grouptitle']);
	print_stop_message('deleted_setting_successfully');
}

// ###################### Start remove setting #######################
if ($_REQUEST['do'] == 'removesetting')
{
	phpkd_vblvb_delete_confirmation('phpkd_vblvb_setting', $vbulletin->GPC['varname'], 'phpkd_vblvb', 'killsetting');
}

// ###################### Start insert setting #######################
if ($_POST['do'] == 'insertsetting')
{
	$vbulletin->input->clean_array_gpc('p', array(
		// setting stuff
		'varname'        => TYPE_STR,
		'grouptitle'     => TYPE_STR,
		'optioncode'     => TYPE_STR,
		'defaultvalue'   => TYPE_STR,
		'displayorder'   => TYPE_UINT,
		'volatile'       => TYPE_INT,
		'datatype'       => TYPE_STR,
		'validationcode' => TYPE_STR,
		'blacklist'      => TYPE_BOOL,
		// phrase stuff
		'title'          => TYPE_STR,
		'description'    => TYPE_STR
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	if ($s = $db->query_first("
		SELECT varname
		FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
		WHERE varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "'
	"))
	{
		print_stop_message('there_is_already_setting_named_x', $vbulletin->GPC['varname']);
	}

	if (!preg_match('#^[a-z0-9_]+$#i', $vbulletin->GPC['varname'])) // match a-z, A-Z, 0-9, _ only
	{
		print_stop_message('invalid_phrase_varname');
	}

	// insert setting place-holder
	$db->query_write("
		INSERT INTO " . TABLE_PREFIX . "phpkd_vblvb_setting
			(varname, value)
		VALUES
			('" . $db->escape_string($vbulletin->GPC['varname']) . "',
			'" . $db->escape_string($vbulletin->GPC['defaultvalue']) . "')
	");

	// insert associated phrases
	$languageid = iif($vbulletin->GPC['volatile'], -1, 0);

	$db->query_write("
		INSERT INTO " . TABLE_PREFIX . "phrase
			(languageid, fieldname, varname, text, product, username, dateline, version)
		VALUES
			($languageid,
			'phpkd_vblvb',
			'setting_" . $db->escape_string($vbulletin->GPC['varname']) . "_title',
			'" . $db->escape_string($vbulletin->GPC['title']) . "',
			'phpkd_vblvb',
			'" . $db->escape_string($vbulletin->userinfo['username']) . "',
			" . TIMENOW . ",
			'" . $db->escape_string(PHPKD_VBLVB_VERSION) . "'),
			($languageid,
			'phpkd_vblvb',
			'setting_" . $db->escape_string($vbulletin->GPC['varname']) . "_desc',
			'" . $db->escape_string($vbulletin->GPC['description']) . "',
			'phpkd_vblvb',
			'" . $db->escape_string($vbulletin->userinfo['username']) . "',
			" . TIMENOW . ",
			'" . $db->escape_string(PHPKD_VBLVB_VERSION) . "')
	");

	// fall through to 'updatesetting' for the real work...
	$_POST['do'] = 'updatesetting';
}

// ###################### Start update setting #######################
if ($_POST['do'] == 'updatesetting')
{
	$vbulletin->input->clean_array_gpc('p', array(
		// setting stuff
		'varname'        => TYPE_STR,
		'grouptitle'     => TYPE_STR,
		'optioncode'     => TYPE_STR,
		'defaultvalue'   => TYPE_STR,
		'displayorder'   => TYPE_UINT,
		'volatile'       => TYPE_INT,
		'datatype'       => TYPE_STR,
		'validationcode' => TYPE_STR,
		'blacklist'      => TYPE_BOOL,
		// phrase stuff
		'title'          => TYPE_STR,
		'description'    => TYPE_STR
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	$db->query_write("
		UPDATE " . TABLE_PREFIX . "phpkd_vblvb_setting SET
			grouptitle = '" . $db->escape_string($vbulletin->GPC['grouptitle']) . "',
			optioncode = '" . $db->escape_string($vbulletin->GPC['optioncode']) . "',
			defaultvalue = '" . $db->escape_string($vbulletin->GPC['defaultvalue']) . "',
			displayorder = " . $vbulletin->GPC['displayorder'] . ",
			volatile = " . $vbulletin->GPC['volatile'] . ",
			datatype = '" . $db->escape_string($vbulletin->GPC['datatype']) . "',
			validationcode = '" . $db->escape_string($vbulletin->GPC['validationcode']) . "',
			blacklist = " . intval($vbulletin->GPC['blacklist']) . "
		WHERE varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "'
	");

	$newlang = iif($vbulletin->GPC['volatile'], -1, 0);

	$phrases = $db->query_read("
		SELECT varname, text, languageid
		FROM " . TABLE_PREFIX . "phrase
		WHERE languageid IN (-1,0)
			AND fieldname = 'phpkd_vblvb'
			AND varname IN ('setting_" . $db->escape_string($vbulletin->GPC['varname']) . "_title', 'setting_" . $db->escape_string($vbulletin->GPC['varname']) . "_desc')
	");

	while ($phrase = $db->fetch_array($phrases))
	{
		if ($phrase['varname'] == "setting_" . $vbulletin->GPC['varname'] . "_title")
		{
			$q = "
				UPDATE " . TABLE_PREFIX . "phrase SET
					languageid = " . iif($vbulletin->GPC['volatile'], -1, 0) . ",
					text = '" . $db->escape_string($vbulletin->GPC['title']) . "',
					product = 'phpkd_vblvb',
					username = '" . $db->escape_string($vbulletin->userinfo['username']) . "',
					dateline = " . TIMENOW . ",
					version = '" . $db->escape_string(PHPKD_VBLVB_VERSION) . "'
				WHERE languageid = $phrase[languageid]
					AND varname = 'setting_" . $db->escape_string($vbulletin->GPC['varname']) . "_title'
			";

			$db->query_write($q);
		}
		else if ($phrase['varname'] == "setting_" . $vbulletin->GPC['varname'] . "_desc")
		{
			$q = "
				UPDATE " . TABLE_PREFIX . "phrase SET
					languageid = " . iif($vbulletin->GPC['volatile'], -1, 0) . ",
					text = '" . $db->escape_string($vbulletin->GPC['description']) . "',
					product = 'phpkd_vblvb',
					username = '" . $db->escape_string($vbulletin->userinfo['username']) . "',
					dateline = " . TIMENOW . ",
					version = '" . $db->escape_string(PHPKD_VBLVB_VERSION) . "'
				WHERE languageid = $phrase[languageid]
					AND varname = 'setting_" . $db->escape_string($vbulletin->GPC['varname']) . "_desc'
			";

			$db->query_write($q);
		}
	}

	// rebuild the $vbulletin->phpkd_vblvb array
	phpkd_vblvb_build();

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=options&amp;dogroup=' . $vbulletin->GPC['grouptitle']);
	print_stop_message('saved_setting_x_successfully', $vbulletin->GPC['title']);
}

// ###################### Start edit / add setting #######################
if ($_REQUEST['do'] == 'editsetting' OR $_REQUEST['do'] == 'addsetting')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'grouptitle' => TYPE_STR
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	$settinggroups = array();
	$groups = $db->query_read("SELECT grouptitle FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup ORDER BY displayorder");

	while ($group = $db->fetch_array($groups))
	{
		$settinggroups["$group[grouptitle]"] = $settingphrase["settinggroup_$group[grouptitle]"];
	}

	if ($_REQUEST['do'] == 'editsetting')
	{
		$setting = $db->query_first("
			SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
			WHERE varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "'
		");

		$phrases = $db->query_read("
			SELECT varname, text
			FROM " . TABLE_PREFIX . "phrase
			WHERE languageid = " . iif($setting['volatile'], -1, 0) . " AND
				fieldname = 'phpkd_vblvb' AND
			varname IN ('setting_" . $db->escape_string($setting['varname']) . "_title', 'setting_" . $db->escape_string($setting['varname']) . "_desc')
		");

		while ($phrase = $db->fetch_array($phrases))
		{
			if ($phrase['varname'] == "setting_$setting[varname]_title")
			{
				$setting['title'] = $phrase['text'];
			}
			else if ($phrase['varname'] == "setting_$setting[varname]_desc")
			{
				$setting['description'] = $phrase['text'];
			}
		}

		$pagetitle = construct_phrase($vbphrase['x_y_id_z'], $vbphrase['setting'], $setting['title'], $setting['varname']);
		$formdo = 'updatesetting';
	}
	else
	{
		$ordercheck = $db->query_first("
			SELECT displayorder FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
			WHERE grouptitle='" . $db->escape_string($vbulletin->GPC['grouptitle']) . "'
			ORDER BY displayorder DESC
		");

		$setting = array(
			'grouptitle'   => $vbulletin->GPC['grouptitle'],
			'displayorder' => $ordercheck['displayorder'] + 10,
			'volatile'     => ($vbulletin->debug AND PHPKD_VBLVB_DEBUG) ? 1 : 0
		);

		$pagetitle = $vbphrase['add_new_setting'];
		$formdo = 'insertsetting';
	}

	print_form_header('phpkd_vblvb', $formdo);
	print_table_header($pagetitle);

	if ($_REQUEST['do'] == 'editsetting')
	{
		construct_hidden_code('varname', $setting['varname']);
		print_label_row($vbphrase['varname'], "<b>$setting[varname]</b>");
	}
	else
	{
		print_input_row($vbphrase['varname'], 'varname', $setting['varname']);
	}

	print_select_row($vbphrase['setting_group'], 'grouptitle', $settinggroups, $setting['grouptitle']);
	print_input_row($vbphrase['title'], 'title', $setting['title']);
	print_textarea_row($vbphrase['description'], 'description', $setting['description'], 4, '50" style="width:100%');
	print_textarea_row($vbphrase['option_code'], 'optioncode', $setting['optioncode'], 4, '50" style="width:100%');
	print_textarea_row($vbphrase['default'], 'defaultvalue', $setting['defaultvalue'], 4, '50" style="width:100%');

	switch ($setting['datatype'])
	{
		case 'number':
			$checked = array('number' => ' checked="checked"');
			break;

		case 'integer':
			$checked = array('integer' => ' checked="checked"');
			break;

		case 'posint':
			$checked = array('posint' => ' checked="checked"');
			break;

		case 'boolean':
			$checked = array('boolean' => ' checked="checked"');
			break;

		case 'bitfield':
			$checked= array('bitfield' => ' checked="checked"');
			break;

		case 'username':
			$checked= array('username' => ' checked="checked"');
			break;

		default:
			$checked = array('free' => ' checked="checked"');
	}

	print_label_row($vbphrase['data_validation_type'], '
		<div class="smallfont">
		<label for="rb_dt_free"><input type="radio" name="datatype" id="rb_dt_free" tabindex="1" value="free"' . $checked['free'] . ' />' . $vbphrase['datatype_free'] . '</label>
		<label for="rb_dt_number"><input type="radio" name="datatype" id="rb_dt_number" tabindex="1" value="number"' . $checked['number'] . ' />' . $vbphrase['datatype_numeric'] . '</label>
		<label for="rb_dt_integer"><input type="radio" name="datatype" id="rb_dt_integer" tabindex="1" value="integer"' . $checked['integer'] . ' />' . $vbphrase['datatype_integer'] . '</label>
		<label for="rb_dt_posint"><input type="radio" name="datatype" id="rb_dt_posint" tabindex="1" value="posint"' . $checked['posint'] . ' />' . $vbphrase['datatype_posint'] . '</label>
		<label for="rb_dt_boolean"><input type="radio" name="datatype" id="rb_dt_boolean" tabindex="1" value="boolean"' . $checked['boolean'] . ' />' . $vbphrase['datatype_boolean'] . '</label>
		<label for="rb_dt_bitfield"><input type="radio" name="datatype" id="rb_dt_bitfield" tabindex="1" value="bitfield"' . $checked['bitfield'] . ' />' . $vbphrase['datatype_bitfield'] . '</label>
		<label for="rb_dt_username"><input type="radio" name="datatype" id="rb_dt_username" tabindex="1" value="username"' . $checked['username'] . ' />' . $vbphrase['datatype_username'] . '</label>
		</div>
	');
	print_textarea_row($vbphrase['validation_php_code'], 'validationcode', $setting['validationcode'], 4, '50" style="width:100%');
	print_input_row($vbphrase['display_order'], 'displayorder', $setting['displayorder']);
	print_yes_no_row($vbphrase['blacklist'], 'blacklist', $setting['blacklist']);

	if ($vbulletin->debug AND PHPKD_VBLVB_DEBUG)
	{
		print_yes_no_row($vbphrase['vbulletin_default'], 'volatile', $setting['volatile']);
	}
	else
	{
		construct_hidden_code('volatile', $setting['volatile']);
	}

	print_submit_row($vbphrase['save']);
}

// ###################### Start do options #######################
if ($_POST['do'] == 'dooptions')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'setting'  => TYPE_ARRAY,
		'advanced' => TYPE_BOOL
	));

	if (!empty($vbulletin->GPC['setting']))
	{
		phpkd_vblvb_save($vbulletin->GPC['setting']);

		define('CP_REDIRECT', 'phpkd_vblvb.php?do=options&amp;dogroup=' . $vbulletin->GPC['dogroup'] . '&amp;advanced=' . $vbulletin->GPC['advanced']);
		print_stop_message('saved_settings_successfully');
	}
	else
	{
		print_stop_message('nothing_to_do');
	}
}

// ###################### Start modify options #######################
if ($_REQUEST['do'] == 'options')
{
	require_once(DIR . '/includes/adminfunctions_language.php');

	$vbulletin->input->clean_array_gpc('r', array(
		'advanced' => TYPE_BOOL,
		'expand'   => TYPE_BOOL,
	));

	echo '<script type="text/javascript" src="../clientscript/vbulletin_cpoptions_scripts.js?v=' . SIMPLE_VERSION . '"></script>';

	// display links to settinggroups and create settingscache
	$settingscache = array();
	$options = array('[all]' => '-- ' . $vbphrase['show_all_settings'] . ' --');
	$lastgroup = '';

	$settings = $db->query_read("
		SELECT setting.*, settinggroup.grouptitle
		FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup AS settinggroup
		LEFT JOIN " . TABLE_PREFIX . "phpkd_vblvb_setting AS setting USING(grouptitle)
		" . iif($vbulletin->debug AND PHPKD_VBLVB_DEBUG, '', 'WHERE settinggroup.displayorder <> 0') . "
		ORDER BY settinggroup.displayorder, setting.displayorder
	");

	if (empty($vbulletin->GPC['dogroup']) AND $vbulletin->GPC['expand'])
	{
		while ($setting = $db->fetch_array($settings))
		{
			$settingscache["$setting[grouptitle]"]["$setting[varname]"] = $setting;

			if ($setting['grouptitle'] != $lastgroup)
			{
				$grouptitlecache["$setting[grouptitle]"] = $setting['grouptitle'];
				$grouptitle = $settingphrase["settinggroup_$setting[grouptitle]"];
			}

			$options["$grouptitle"]["$setting[varname]"] = $settingphrase["setting_$setting[varname]_title"];
			$lastgroup = $setting['grouptitle'];
		}

		$altmode = 0;
		$linktext =& $vbphrase['collapse_setting_groups'];
	}
	else
	{
		while ($setting = $db->fetch_array($settings))
		{
			$settingscache["$setting[grouptitle]"]["$setting[varname]"] = $setting;

			if ($setting['grouptitle'] != $lastgroup)
			{
				$grouptitlecache["$setting[grouptitle]"] = $setting['grouptitle'];
				$options["$setting[grouptitle]"] = $settingphrase["settinggroup_$setting[grouptitle]"];
			}

			$lastgroup = $setting['grouptitle'];
		}

		$altmode = 1;
		$linktext =& $vbphrase['expand_setting_groups'];
	}

	$db->free_result($settings);

	$optionsmenu = "\n\t<select name=\"" . iif($vbulletin->GPC['expand'], 'varname', 'dogroup') . "\" class=\"bginput\" tabindex=\"1\" " . iif(empty($vbulletin->GPC['dogroup']), 'ondblclick="this.form.submit();" size="20"', 'onchange="this.form.submit();"') . " style=\"width:350px\">\n" . construct_select_options($options, iif($vbulletin->GPC['dogroup'], $vbulletin->GPC['dogroup'], '[all]')) . "\t</select>\n\t";

	print_form_header('phpkd_vblvb', 'options', 0, 1, 'groupForm', '90%', '', 1, 'get');

	if (empty($vbulletin->GPC['dogroup'])) // show the big <select> with no options
	{
		print_table_header($vbphrase['phpkd_vblvb_options']);
		print_label_row($vbphrase['settings_to_edit'] .
			iif($vbulletin->debug AND PHPKD_VBLVB_DEBUG,
				'<br /><table><tr><td><fieldset><legend>Developer Options</legend>
				<div style="padding: 2px"><a href="phpkd_vblvb.php?' . $vbulletin->session->vars['sessionurl'] . 'do=addgroup">' . $vbphrase['add_new_setting_group'] . '</a></div>
				<div style="padding: 2px"><a href="phpkd_vblvb.php?' . $vbulletin->session->vars['sessionurl'] . 'do=filessettings">' . $vbphrase['download_upload_settings'] . '</a></div>' .
				'</fieldset></td></tr></table>'
			) .
			"<p><a href=\"phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "expand=$altmode\">$linktext</a></p>
			<p><a href=\"phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=backuprestoresetting\">" . $vbphrase['backup_restore_settings'] . "</a>", $optionsmenu);
		print_submit_row($vbphrase['edit_settings'], 0);
	}
	else // show the small list with selected setting group(s) options
	{
		print_table_header("$vbphrase[setting_group] $optionsmenu <input type=\"submit\" value=\"$vbphrase[go]\" class=\"button\" tabindex=\"1\" />");
		print_table_footer();

		// show selected settings
		print_form_header('phpkd_vblvb', 'dooptions', false, true, 'optionsform', '90%', '', true, 'post" onsubmit="return count_errors()');
		construct_hidden_code('dogroup', $vbulletin->GPC['dogroup']);
		construct_hidden_code('advanced', $vbulletin->GPC['advanced']);

		if ($vbulletin->GPC['dogroup'] == '[all]') // show all settings groups
		{
			foreach ($grouptitlecache AS $curgroup => $group)
			{
				phpkd_vblvb_settinggroup($curgroup, $vbulletin->GPC['advanced']);
				echo '<tbody>';
				print_description_row("<input type=\"submit\" class=\"button\" value=\" $vbphrase[save] \" tabindex=\"1\" title=\"" . $vbphrase['save_settings'] . "\" />", 0, 2, 'tfoot" style="padding:1px" align="right');
				echo '</tbody>';
				print_table_break(' ');
			}
		}
		else
		{
			phpkd_vblvb_settinggroup($vbulletin->GPC['dogroup'], $vbulletin->GPC['advanced']);
		}

		print_submit_row($vbphrase['save']);

		?>
		<div id="error_output" style="font: 10pt courier new"></div>
		<script type="text/javascript">
		<!--
		var error_confirmation_phrase = "<?php echo $vbphrase['error_confirmation_phrase']; ?>";
		//-->
		</script>
		<script type="text/javascript" src="../clientscript/vbulletin_settings_validate.js?v=<?php echo SIMPLE_VERSION; ?>"></script>
		<?php
	}

	// build color picker if necessary
	if ($vbulletin->GPC['dogroup'] == 'lookfeel' OR $vbulletin->GPC['dogroup'] == '[all]')
	{
		echo phpkd_vblvb_color_picker(11);
		echo '<script type="text/javascript">
		<!--
		var bburl = "' . $vbulletin->options['bburl'] . '/";
		var cpstylefolder = "' . $vbulletin->options['cpstylefolder'] . '";
		var numColors = 3; // This hardcoded value equals the number of color inputs we have in current page.
		var colorPickerWidth = ' . intval($colorPickerWidth) . ';
		var colorPickerType = ' . intval($colorPickerType) . ';
		//-->
		</script>';
	}
}

// ###################### Start modify options #######################
if ($_REQUEST['do'] == 'backuprestoresetting')
{
	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	// download form
	print_form_header('phpkd_vblvb', 'backupsettings', 0, 1, 'downloadsettingsform', '90%', 'backupsettings');
	print_table_header($vbphrase['backup']);
	print_yes_no_row($vbphrase['ignore_blacklisted_settings'], 'blacklist', 1);
	print_submit_row($vbphrase['backup']);

	?>
	<script type="text/javascript">
	<!--
	function js_confirm_upload(tform, filefield)
	{
		if (filefield.value == "")
		{
			return confirm("<?php echo construct_phrase($vbphrase['you_did_not_specify_a_file_to_upload'], '" + tform.serversettingsfile.value + "'); ?>");
		}
		return true;
	}
	//-->
	</script>
	<?php

	print_form_header('phpkd_vblvb', 'doimportsetting', 1, 1, 'uploadsettingform', '90%', '', true, 'post" onsubmit="return js_confirm_upload(this, this.settingsfile);');
	construct_hidden_code('restore', 1);
	print_table_header($vbphrase['restore_settings_xml_file']);
	print_yes_no_row($vbphrase['ignore_blacklisted_settings'], 'blacklist', 1);
	print_upload_row($vbphrase['upload_xml_file'], 'settingsfile', 999999999);
	print_input_row($vbphrase['restore_xml_file'], 'serversettingsfile', './includes/phpkd/vblvb/settings.xml');
	print_submit_row($vbphrase['restore'], 0);
}

// ###################### Start do import hosts XML #######################
if ($_POST['do'] == 'doimporthosts')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'serverhostsfile' => TYPE_STR
	));

	$vbulletin->input->clean_array_gpc('f', array(
		'hostsfile' => TYPE_FILE
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	// got an uploaded file?
	if (file_exists($vbulletin->GPC['hostsfile']['tmp_name']))
	{
		$xml = file_read($vbulletin->GPC['hostsfile']['tmp_name']);
	}
	// no uploaded file - got a local file?
	else if (file_exists($vbulletin->GPC['serverhostsfile']))
	{
		$xml = file_read($vbulletin->GPC['serverhostsfile']);
	}
	// no uploaded file and no local file - ERROR
	else
	{
		print_stop_message('no_file_uploaded_and_no_local_file_found');
	}

	phpkd_vblvb_xml_restore_hosts($xml);

	print_cp_redirect("phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'], 0);
}

// ###################### Start modify options #######################
if ($_REQUEST['do'] == 'backuprestorehosts')
{
	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	// download form
	print_form_header('phpkd_vblvb', 'backuphosts', 0, 1, 'downloadhostsform', '90%', 'backuphosts');
	print_table_header($vbphrase['backup']);
	print_submit_row($vbphrase['backup'], 0);

	?>
	<script type="text/javascript">
	<!--
	function js_confirm_upload(tform, filefield)
	{
		if (filefield.value == "")
		{
			return confirm("<?php echo construct_phrase($vbphrase['you_did_not_specify_a_file_to_upload'], '" + tform.serverhostsfile.value + "'); ?>");
		}
		return true;
	}
	//-->
	</script>
	<?php

	print_form_header('phpkd_vblvb', 'doimporthosts', 1, 1, 'uploadhostsform', '90%', '', true, 'post" onsubmit="return js_confirm_upload(this, this.hostsfile);');
	print_table_header($vbphrase['phpkd_vblvb_host_xml_file_restore']);
	print_upload_row($vbphrase['upload_xml_file'], 'hostsfile', 999999999);
	print_input_row($vbphrase['restore_xml_file'], 'serverhostsfile', './includes/phpkd/vblvb/hosts.xml');
	print_submit_row($vbphrase['restore'], 0);
}

// ###################### Start insert host #######################
if ($_POST['do'] == 'inserthost')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'id'           => TYPE_UINT,
		'domain'       => TYPE_STR,
		'active'       => TYPE_BOOL,
		'status'       => TYPE_STR,
		'urlmatch'     => TYPE_STR,
		'apiurl'       => TYPE_STR,
		'contentmatch' => TYPE_STR,
		'downmatch'    => TYPE_STR,
		'urlsearch'    => TYPE_STR,
		'urlreplace'   => TYPE_STR,
		'notes'        => TYPE_STR
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	if ($s = $db->query_first("
		SELECT domain
		FROM " . TABLE_PREFIX . "phpkd_vblvb_host
		WHERE domain = '" . $db->escape_string($vbulletin->GPC['domain']) . "'
	"))
	{
		print_stop_message('phpkd_vblvb_there_is_already_host_named_x', $vbulletin->GPC['domain']);
	}

	if (!preg_match('#^(?:[A-Z0-9-]+\.)+[A-Z]{2,4}$#i', $vbulletin->GPC['domain']))
	{
		print_stop_message('phpkd_vblvb_invalid_domain');
	}

	$apiurl       = $db->escape_string(trim($vbulletin->GPC['apiurl']));
	$contentmatch = $db->escape_string(trim($vbulletin->GPC['contentmatch']));
	$downmatch    = $db->escape_string(trim($vbulletin->GPC['downmatch']));
	$urlsearch    = $db->escape_string(trim($vbulletin->GPC['urlsearch']));
	$urlreplace   = $db->escape_string(trim($vbulletin->GPC['urlreplace']));
	$notes        = $db->escape_string(trim($vbulletin->GPC['notes']));

	// insert host place-holder
	$db->query_write("
		INSERT INTO " . TABLE_PREFIX . "phpkd_vblvb_host
			(domain, active, status, urlmatch, apiurl, contentmatch, downmatch, urlsearch, urlreplace, notes)
		VALUES
			('" . $db->escape_string(trim($vbulletin->GPC['domain'])) . "', " . $vbulletin->GPC['active'] . ", '" . $db->escape_string(trim($vbulletin->GPC['status'])) . "', '" . $db->escape_string(trim($vbulletin->GPC['urlmatch'])) . "', " . (!empty($apiurl) ? '\'' . $apiurl . '\'' : 'NULL') . ", " . (!empty($contentmatch) ? '\'' . $contentmatch . '\'' : 'NULL') . ", " . (!empty($downmatch) ? '\'' . $downmatch . '\'' : 'NULL') . ", " . (!empty($urlsearch) ? '\'' . $urlsearch . '\'' : 'NULL') . ", " . (!empty($urlreplace) ? '\'' . $urlreplace . '\'' : 'NULL') . ", " . (!empty($notes) ? '\'' . $notes . '\'' : 'NULL') . ")
	");

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=hosts');
	print_stop_message('phpkd_vblvb_saved_host_x_successfully', $vbulletin->GPC['domain']);
}

// ###################### Start update host #######################
if ($_POST['do'] == 'updatehost')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'id'           => TYPE_UINT,
		'domain'       => TYPE_STR,
		'olddomain'    => TYPE_STR,
		'active'       => TYPE_BOOL,
		'status'       => TYPE_STR,
		'urlmatch'     => TYPE_STR,
		'apiurl'       => TYPE_STR,
		'contentmatch' => TYPE_STR,
		'downmatch'    => TYPE_STR,
		'urlsearch'    => TYPE_STR,
		'urlreplace'   => TYPE_STR,
		'notes'        => TYPE_STR
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	if ($vbulletin->GPC['domain'] != $vbulletin->GPC['olddomain'] AND $s = $db->query_first("
		SELECT domain
		FROM " . TABLE_PREFIX . "phpkd_vblvb_host
		WHERE domain = '" . $db->escape_string($vbulletin->GPC['domain']) . "'
	"))
	{
		print_stop_message('phpkd_vblvb_there_is_already_host_named_x', $vbulletin->GPC['domain']);
	}

	if (!preg_match('#^(?:[A-Z0-9-]+\.)+[A-Z]{2,4}$#i', $vbulletin->GPC['domain']))
	{
		print_stop_message('phpkd_vblvb_invalid_domain');
	}

	$apiurl       = $db->escape_string(trim($vbulletin->GPC['apiurl']));
	$contentmatch = $db->escape_string(trim($vbulletin->GPC['contentmatch']));
	$downmatch    = $db->escape_string(trim($vbulletin->GPC['downmatch']));
	$urlsearch    = $db->escape_string(trim($vbulletin->GPC['urlsearch']));
	$urlreplace   = $db->escape_string(trim($vbulletin->GPC['urlreplace']));
	$notes        = $db->escape_string(trim($vbulletin->GPC['notes']));

	$db->query_write("
		UPDATE " . TABLE_PREFIX . "phpkd_vblvb_host SET
			domain       = '" . $db->escape_string($vbulletin->GPC['domain']) . "',
			active       = " . $vbulletin->GPC['active'] . ",
			status       = '" . $db->escape_string($vbulletin->GPC['status']) . "',
			urlmatch     = '" . $db->escape_string(trim($vbulletin->GPC['urlmatch'])) . "',
			apiurl       = " . (!empty($apiurl) ? '\'' . $apiurl . '\'' : 'NULL') . ",
			contentmatch = " . (!empty($contentmatch) ? '\'' . $contentmatch . '\'' : 'NULL') . ",
			downmatch    = " . (!empty($downmatch) ? '\'' . $downmatch . '\'' : 'NULL') . ",
			urlsearch    = " . (!empty($urlsearch) ? '\'' . $urlsearch . '\'' : 'NULL') . ",
			urlreplace   = " . (!empty($urlreplace) ? '\'' . $urlreplace . '\'' : 'NULL') . ",
			notes        = " . (!empty($notes) ? '\'' . $notes . '\'' : 'NULL') . "
		WHERE id = " . $vbulletin->GPC['id'] . "
	");

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=hosts');
	print_stop_message('phpkd_vblvb_saved_host_x_successfully', $vbulletin->GPC['domain']);
}

// ###################### Start edit / add host #######################
if ($_REQUEST['do'] == 'edithost' OR $_REQUEST['do'] == 'addhost')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' => TYPE_UINT
	));

	if (is_demo_mode())
	{
		print_cp_message('This function is disabled within demo mode');
	}

	if ($_REQUEST['do'] == 'edithost')
	{
		$host = $db->query_first("
			SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_host
			WHERE id = '" . $vbulletin->GPC['id'] . "'
		");

		$pagetitle = construct_phrase($vbphrase['x_y_id_z'], $vbphrase['phpkd_vblvb_host'], $host['domain'], $host['id']);
		$formdo = 'updatehost';
	}
	else
	{
		$host = array(
			'active'       => 1,
			'status'       => 'alive'
		);

		$pagetitle = $vbphrase['phpkd_vblvb_host_new'];
		$formdo = 'inserthost';
	}

	print_form_header('phpkd_vblvb', $formdo);
	print_table_header($pagetitle);

	if ($_REQUEST['do'] == 'edithost')
	{
		construct_hidden_code('id', $host['id']);
		construct_hidden_code('olddomain', $host['domain']);
	}

	print_input_row($vbphrase['phpkd_vblvb_host_domain'], 'domain', $host['domain']);
	print_yes_no_row($vbphrase['phpkd_vblvb_host_active'], 'active', $host['active']);
	print_select_row($vbphrase['phpkd_vblvb_host_status'], 'status', array('alive' => $vbphrase['phpkd_vblvb_linkstatus_alive'], 'down' => $vbphrase['phpkd_vblvb_linkstatus_down'], 'dead' => $vbphrase['phpkd_vblvb_linkstatus_dead']), $host['status']);
	print_input_row($vbphrase['phpkd_vblvb_host_urlmatch'], 'urlmatch', $host['urlmatch']);
	print_input_row($vbphrase['phpkd_vblvb_host_apiurl'], 'apiurl', $host['apiurl']);
	print_input_row($vbphrase['phpkd_vblvb_host_contentmatch'], 'contentmatch', $host['contentmatch']);
	print_input_row($vbphrase['phpkd_vblvb_host_downmatch'], 'downmatch', $host['downmatch']);
	print_input_row($vbphrase['phpkd_vblvb_host_urlsearch'], 'urlsearch', $host['urlsearch']);
	print_input_row($vbphrase['phpkd_vblvb_host_urlreplace'], 'urlreplace', $host['urlreplace']);
	print_textarea_row($vbphrase['phpkd_vblvb_host_notes'], 'notes', $host['notes']);
	print_submit_row($vbphrase['save']);
}

// #############################################################################
// remove a notice
if ($_POST['do'] == 'killhost')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'id' => TYPE_UINT
	));

	// delete criteria
	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_host
		WHERE id = " . $vbulletin->GPC['id']
	);

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=hosts');
	print_stop_message('phpkd_vblvb_host_deleted_successfully');
}

// #############################################################################
// confirm deletion of a notice
if ($_REQUEST['do'] == 'deletehost')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'id' => TYPE_UINT
	));

	phpkd_vblvb_delete_confirmation('phpkd_vblvb_host', $vbulletin->GPC['id'], 'phpkd_vblvb', 'killhost');
}

// #############################################################################
// quick update of active and display order fields
if ($_POST['do'] == 'quickupdatehosts')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'active'       => TYPE_ARRAY_BOOL,
		'status'       => TYPE_ARRAY_STR
	));

	$update_ids = '0';
	$update_active = '';
	$update_status = '';

	$hosts = $db->query_read("SELECT id, active, status FROM " . TABLE_PREFIX . "phpkd_vblvb_host");
	while ($host = $db->fetch_array($hosts))
	{
		if (intval($host['active']) != $vbulletin->GPC['active']["$host[id]"] OR $host['status'] != $vbulletin->GPC['status']["$host[id]"])
		{
			$update_ids .= ",$host[id]";
			$update_active .= " WHEN $host[id] THEN " . intval($vbulletin->GPC['active']["$host[id]"]);
			$update_status .= " WHEN $host[id] THEN '" . $db->escape_string($vbulletin->GPC['status']["$host[id]"]) . "'";
		}
	}
	$db->free_result($hosts);

	if (strlen($update_ids) > 1)
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "phpkd_vblvb_host SET
			active = CASE id
			$update_active ELSE active END,
			status = CASE id
			$update_status ELSE status END
			WHERE id IN($update_ids)
		");
	}

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=hosts');
	print_stop_message('phpkd_vblvb_saved_hosts_successfully');
}

// #############################################################################
if ($_REQUEST['do'] == 'hosts')
{
	print_form_header('phpkd_vblvb', 'quickupdatehosts');
	print_column_style_code(array('width:60%', 'white-space:nowrap'));
	print_table_header($vbphrase['phpkd_vblvb_host_manager'], 4);

	$hosts = $db->query("SELECT * FROM " . TABLE_PREFIX . "phpkd_vblvb_host ORDER BY domain ASC");

	if ($db->num_rows($hosts))
	{
		print_cells_row(array($vbphrase['phpkd_vblvb_host'], '<label><input type="checkbox" id="allbox" />' . $vbphrase['phpkd_vblvb_host_active'] . '</label><input type="image" src="../' . $vbulletin->options['cleargifurl'] . '" name="normalsubmit" />', $vbphrase['phpkd_vblvb_host_status'], $vbphrase['controls']), 1, 'tcat');

		while ($host = $db->fetch_array($hosts))
		{
			print_cells_row(array('<a href="phpkd_vblvb.php?' . $vbulletin->session->vars['sessionurl'] . 'do=edithost&amp;id=' . $host['id'] . '" title="' . $vbphrase['phpkd_vblvb_host_edit'] . '">' . $host['domain'] . '</a>', '<input type="checkbox" name="active[' . $host['id'] . ']" value="1"' . ($host['active'] ? ' checked="checked"' : '') . ' />', '<select name="status[' . $host['id'] . ']" class="bginput"' . iif($vbulletin->debug, ' title="name=&quot;status[' . $host['id'] . ']&quot;"') . '>' . construct_select_options(array('alive' => $vbphrase['phpkd_vblvb_linkstatus_alive'], 'down' => $vbphrase['phpkd_vblvb_linkstatus_down'], 'dead' => $vbphrase['phpkd_vblvb_linkstatus_dead']), $host['status']) . '</select>', construct_link_code($vbphrase['edit'], 'phpkd_vblvb.php?' . $vbulletin->session->vars['sessionurl'] . 'do=edithost&amp;id=' . $host['id']) . construct_link_code($vbphrase['delete'], 'phpkd_vblvb.php?' . $vbulletin->session->vars['sessionurl'] . 'do=deletehost&amp;id=' . $host['id'])));

		}
	}

	print_table_footer(4, "<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . $vbphrase['save'] . "\" accesskey=\"s\" />" . construct_button_code($vbphrase['phpkd_vblvb_host_new'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=addhost") . construct_button_code($vbphrase['phpkd_vblvb_host_backuprestore'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=backuprestorehosts"));

	?>
	<script type="text/javascript">
	<!--
	function toggle_all_active(e)
	{
		for (var i = 0; i < this.form.elements.length; i++)
		{
			if (this.form.elements[i].type == "checkbox" && this.form.elements[i].name.substr(0, 6) == "active")
			{
				this.form.elements[i].checked = this.checked;
			}
		}
	}

	YAHOO.util.Event.on("allbox", "click", toggle_all_active);
	//-->
	</script>
	<?php
}

// ###################### Start view log #######################
if ($_REQUEST['do'] == 'viewlog')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage'   => TYPE_INT,
		'pagenumber'=> TYPE_INT,
		'orderby'   => TYPE_STR,
		'direction' => TYPE_STR,
		'startdate' => TYPE_UNIXTIME,
		'enddate'   => TYPE_UNIXTIME
	));

	if ($vbulletin->GPC['startdate'] OR $vbulletin->GPC['enddate'])
	{
		$sqlconds = 'WHERE 1 = 1 ';

		if ($vbulletin->GPC['startdate'])
		{
			$sqlconds .= " AND log.dateline >= " . $vbulletin->GPC['startdate'];
		}

		if ($vbulletin->GPC['enddate'])
		{
			$sqlconds .= " AND log.dateline <= " . $vbulletin->GPC['enddate'];
		}
	}
	else
	{
		$sqlconds = '';
	}

	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}

	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}

	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];
	$counter = $db->query_first("SELECT COUNT(DISTINCT dateline) AS total FROM " . TABLE_PREFIX . "phpkd_vblvb_log AS log $sqlconds");
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

	if (empty($vbulletin->GPC['orderby']) OR !in_array($vbulletin->GPC['orderby'], array('postid', 'date', 'mode')))
	{
		$vbulletin->GPC['orderby'] = 'dateline';
	}

	if (empty($vbulletin->GPC['direction']) OR !in_array($vbulletin->GPC['direction'], array('ASC', 'DESC')))
	{
		$vbulletin->GPC['direction'] = 'ASC';
	}

	$logs = $db->query_read("
		SELECT log.*, post.title AS posttitle, thread.threadid, thread.title AS threadtitle, forum.forumid, forum.title AS forumtitle
		FROM " . TABLE_PREFIX . "phpkd_vblvb_log AS log
		LEFT JOIN " . TABLE_PREFIX . "post AS post USING(postid)
		LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
		LEFT JOIN " . TABLE_PREFIX . "forum AS forum ON (thread.forumid = forum.forumid)
		INNER JOIN (SELECT DISTINCT log.dateline FROM " . TABLE_PREFIX . "phpkd_vblvb_log AS log $sqlconds ORDER BY log.dateline DESC LIMIT $startat, " .  $vbulletin->GPC['perpage'] . ") AS joinedsub ON (log.dateline = joinedsub.dateline)
		ORDER BY log." . $vbulletin->GPC['orderby'] . ' ' . $vbulletin->GPC['direction'] . "

	");

	if ($db->num_rows($logs))
	{
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; " . $vbphrase['first_page'] .
							"\" tabindex=\"1\" onclick=\"window.location='phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] .
							"do=viewlog&pp=" . $vbulletin->GPC['perpage'] .
							"&orderby=" . $vbulletin->GPC['orderby'] .
							"&direction=" . $vbulletin->GPC['direction'] .
							"&page=1" .
							"&startdate=" . $vbulletin->GPC['startdate'] .
							"&enddate=" . $vbulletin->GPC['enddate'] .
							"'\">";

			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; " . $vbphrase['prev_page'] .
						"\" tabindex=\"1\" onclick=\"window.location='phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] .
						"do=viewlog&pp=" . $vbulletin->GPC['perpage'] .
						"&orderby=" . $vbulletin->GPC['orderby'] .
						"&direction=" . $vbulletin->GPC['direction'] .
						"&page=$prv" .
						"&startdate=" . $vbulletin->GPC['startdate'] .
						"&enddate=" . $vbulletin->GPC['enddate'] .
						"'\">";
		}

		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['next_page'] .
						" &gt;\" tabindex=\"1\" onclick=\"window.location='phpkd_vblvb.php?" .
						$vbulletin->session->vars['sessionurl'] .
						"do=viewlog&pp=" . $vbulletin->GPC['perpage'] .
						"&orderby=" . $vbulletin->GPC['orderby'] .
						"&direction=" . $vbulletin->GPC['direction'] .
						"&page=$nxt" .
						"&startdate=" . $vbulletin->GPC['startdate'] .
						"&enddate=" . $vbulletin->GPC['enddate'] .
						"'\">";

			$lastpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['last_page'] .
						" &raquo;\" tabindex=\"1\" onclick=\"window.location='phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] .
						"do=viewlog&pp=" . $vbulletin->GPC['perpage'] .
						"&orderby=" . $vbulletin->GPC['orderby'] .
						"&direction=" . $vbulletin->GPC['direction'] .
						"&page=$totalpages" .
						"&startdate=" . $vbulletin->GPC['startdate'] .
						"&enddate=" . $vbulletin->GPC['enddate'] .
						"'\">";
		}

		print_form_header('phpkd_vblvb', 'remove');
		print_description_row(construct_link_code($vbphrase['restart'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=viewlog"), 0, 2, 'thead', vB_Template_Runtime::fetchStyleVar('right'));
		print_table_header(construct_phrase($vbphrase['phpkd_vblvb_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), 2);
		print_description_row("$firstpage $prevpage &nbsp; $nextpage $lastpage", 0, 2, 'thead', 'center');


		$posts = array();
		$phpkd_vblvb = new PHPKD_VBLVB($vbulletin, $vbphrase, defined('IN_CONTROL_PANEL') ? ERRTYPE_ECHO : ERRTYPE_SILENT);
		$colors = unserialize($vbulletin->phpkd_vblvb['lookfeel_linkstatus_colors']);

		while ($log = $db->fetch_array($logs))
		{
			$posts[$log['dateline']]['mode'] = $log['mode'];

			if (!empty($log['forumid']))
			{
				$posts[$log['dateline']][$log['forumid']]['forumtitle'] = $log['forumtitle'];
				$posts[$log['dateline']][$log['forumid']][$log['threadid']]['threadtitle'] = $log['threadtitle'];
				$posts[$log['dateline']][$log['forumid']][$log['threadid']][$log['postid']] = $log;
			}
			else
			{
				$posts[$log['dateline']]['content'] = $log['content'];
			}
		}

		foreach ($posts as $dateline => $postsarr)
		{
			$cell = $records = $punished = array();
			$logstring = '<strong>' . vbdate($vbulletin->options['logdateformat'], $dateline) . ' - (' . ucfirst($postsarr['mode']) . ')</strong><br />';
			unset($postsarr['mode']);

			if (!empty($postsarr['content']))
			{
				$logstring .= $postsarr['content'];
			}
			else
			{
				$logstring .= $vbphrase['phpkd_vblvb_log_checked_posts'] . '<ol class="smallfont">';

				foreach ($postsarr AS $forumid => $forumposts)
				{
					$logstring .= '<li>' . construct_phrase($vbphrase['phpkd_vblvb_log_forum'], $vbulletin->options['bburl'] . '/forumdisplay.php?f=' . $forumid, $forumposts['forumtitle']) . '<ol class="smallfont">';
					unset($forumposts['forumtitle']);

					foreach ($forumposts AS $threadid => $threadposts)
					{
						$logstring .= '<li>' . construct_phrase($vbphrase['phpkd_vblvb_log_thread'], $vbulletin->options['bburl'] . '/showthread.php?t=' . $threadid, $threadposts['threadtitle']) . '<ol class="smallfont">';
						unset($threadposts['threadtitle']);

						foreach ($threadposts AS $postid => $post)
						{
							$logstring .= $post['content'];

							if ($post['dead'])
							{
								$records['dead']++;
							}

							if ($post['punished'])
							{
								$records['punished']++;
								$punished[$postid] = array('threadid' => $threadid, 'title' => $post['posttitle'] ? $post['posttitle'] : $post['threadtitle']);
							}

							$records['checked']++;
						}

						$logstring .= '</ol></li><br />';
					}

					$logstring .= '</ol></li>';
				}

				$logstring .= construct_phrase($vbphrase['phpkd_vblvb_log_summery_all'], $colors[0], $colors[1], $colors[2], intval($records['checked']), (intval($records['checked']) - intval($records['dead'])), intval($records['dead']), intval($records['punished'])) . '</ol><br />';

				if (!empty($punished))
				{
					$punished_links = '';

					foreach ($punished as $punishedpostid => $punishedpost)
					{
						$punished_links .= '<li><a href="' . $vbulletin->options['bburl'] . '/showthread.php?t=' . $punishedpost['threadid'] . '&amp;p=' . $punishedpostid . '&amp;viewfull=1#post' . $punishedpostid . '" target="_blank">' . $punishedpost['title'] . '</a></li>';
					}

					$logstring .= $vbphrase['phpkd_vblvb_log_punished_posts'] . '<ol class="smallfont">' . $punished_links . '</ol><br />';
				}
			}

			$cell[] = $logstring;
			print_cells_row($cell);
		}

		print_table_footer(2, "$firstpage $prevpage &nbsp; $nextpage $lastpage");

	}
	else
	{
		print_stop_message('no_log_entries_matched_your_query');
	}
}

// ###################### Start prune log #######################
if ($_REQUEST['do'] == 'prunelog')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'daysprune'	=> TYPE_INT
	));

	$datecut = TIMENOW - (86400 * $vbulletin->GPC['daysprune']);
	$query = "SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "phpkd_vblvb_log AS log WHERE dateline < $datecut";
	$logs = $db->query_first($query);

	if ($logs['total'])
	{
		print_form_header('phpkd_vblvb', 'doprunelog');
		construct_hidden_code('datecut', $datecut);
		print_table_header($vbphrase['phpkd_vblvb_log_prune']);
		print_description_row(construct_phrase($vbphrase['phpkd_vblvb_are_you_sure_you_want_to_prune_x_log_entries_from_log'], vb_number_format($logs['total'])));
		print_submit_row($vbphrase['yes'], 0, 0, $vbphrase['no']);
	}
	else
	{
		print_stop_message('no_log_entries_matched_your_query');
	}
}

// ###################### Start do prune log #######################
if ($_POST['do'] == 'doprunelog')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'datecut'	=> TYPE_INT
	));

	$query = "DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_log WHERE dateline < " . $vbulletin->GPC['datecut'];
	$db->query_write($query);

	define('CP_REDIRECT', 'phpkd_vblvb.php?do=log');
	print_stop_message('phpkd_vblvb_log_pruned_successfully');
}

// ###################### Start browse log #######################
if ($_REQUEST['do'] == 'log')
{
	$perpage_options = array(
		5 => 5,
		10 => 10,
		15 => 15,
		20 => 20,
		25 => 25,
		30 => 30,
		40 => 40,
		50 => 50,
		100 => 100,
	);

	print_form_header('phpkd_vblvb', 'viewlog');
	print_table_header($vbphrase['phpkd_vblvb_log_viewer']);
	print_select_row($vbphrase['log_entries_to_show_per_page'], 'perpage', $perpage_options, 15);
	print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
	print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
	print_select_row($vbphrase['order_by'], 'orderby', array('dateline' => $vbphrase['date'], 'postid' => $vbphrase['post'], 'mode' => $vbphrase['phpkd_vblvb_mode']), 'dateline');
	print_select_row($vbphrase['direction'], 'direction', array('ASC' => $vbphrase['ascending'], 'DESC' => $vbphrase['descending']), 'DESC');
	print_submit_row($vbphrase['view'], 0);

	print_form_header('phpkd_vblvb', 'prunelog');
	print_table_header($vbphrase['phpkd_vblvb_log_prune']);
	print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
	print_submit_row($vbphrase['phpkd_vblvb_log_prune'], 0);
}

print_cp_footer();


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.1.210
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
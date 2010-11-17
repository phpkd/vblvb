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


error_reporting(E_ALL & ~E_NOTICE);

/**
 * Prints a setting group for use in phpkd_vblvb.php?do=options
 *
 * @param	string	Settings group ID
 * @param	boolean	Show advanced settings?
 */
function phpkd_vblvb_settinggroup($dogroup, $advanced = 0)
{
	global $settingscache, $grouptitlecache, $vbulletin, $vbphrase, $bgcounter, $settingphrase;

	if (!is_array($settingscache["$dogroup"]))
	{
		return;
	}

	print_column_style_code(array('width:45%', 'width:55%'));

	echo "<thead>\r\n";

	print_table_header(
		$settingphrase["settinggroup_$grouptitlecache[$dogroup]"]
		 . iif($vbulletin->debug AND PHPKD_VBLVB_DEBUG,
			'<span class="normal">' .
			construct_link_code($vbphrase['edit'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=editgroup&amp;grouptitle=$dogroup") .
			construct_link_code($vbphrase['delete'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=removegroup&amp;grouptitle=$dogroup") .
			construct_link_code($vbphrase['add_setting'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=addsetting&amp;grouptitle=$dogroup") .
			'</span>'
		)
	);

	echo "</thead>\r\n";

	$bgcounter = 1;

	foreach ($settingscache["$dogroup"] AS $settingid => $setting)
	{
		if (($advanced OR !$setting['advanced']) AND !empty($setting['varname']))
		{
			phpkd_vblvb_setting($setting, $settingphrase);
		}
	}
}

/**
 * Prints a setting row for use in phpkd_vblvb.php?do=options
 *
 * @param	array	Settings array
 * @param	array	Phrases
 */
function phpkd_vblvb_setting($setting, $settingphrase)
{
	global $vbulletin, $vbphrase, $bgcounter, $settingphrase;

	$settingid = $setting['varname'];

	echo '<tbody>';
	print_description_row(
		iif($vbulletin->debug AND PHPKD_VBLVB_DEBUG, '<div class="smallfont" style="float:' . vB_Template_Runtime::fetchStyleVar('right') . '">' . construct_link_code($vbphrase['edit'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=editsetting&amp;varname=$setting[varname]") . construct_link_code($vbphrase['delete'], "phpkd_vblvb.php?" . $vbulletin->session->vars['sessionurl'] . "do=removesetting&amp;varname=$setting[varname]") . '</div>') .
		'<div>' . $settingphrase["setting_$setting[varname]_title"] . "<a name=\"$setting[varname]\"></a></div>",
		0, 2, 'optiontitle' . (($vbulletin->debug AND PHPKD_VBLVB_DEBUG) ? "\" title=\"\$vbulletin->phpkd_vblvb['" . $setting['varname'] . "']" : '')
	);
	echo "</tbody><tbody id=\"tbody_$settingid\">\r\n";

	// make sure all rows use the alt1 class
	$bgcounter--;

	$description = "<div class=\"smallfont\"" . (($vbulletin->debug AND PHPKD_VBLVB_DEBUG) ? "title=\"\$vbulletin->phpkd_vblvb['$setting[varname]']\"" : '') . ">" . $settingphrase["setting_$setting[varname]_desc"] . '</div>';
	$name = "setting[$setting[varname]]";
	$right = "<span class=\"smallfont\">$vbphrase[error]</span>";
	$width = 40;
	$rows = 8;

	if (preg_match('#^input:?(\d+)$#s', $setting['optioncode'], $matches))
	{
		$width = $matches[1];
		$setting['optioncode'] = '';
	}
	else if (preg_match('#^textarea:?(\d+)(,(\d+))?$#s', $setting['optioncode'], $matches))
	{
		$rows = $matches[1];
		if ($matches[2])
		{
			$width = $matches[3];
		}
		$setting['optioncode'] = 'textarea';
	}
	else if (preg_match('#^bitfield:(.*)$#siU', $setting['optioncode'], $matches))
	{
		$setting['optioncode'] = 'bitfield';
		$setting['bitfield'] =& phpkd_vblvb_fetch_bitfield_definitions($matches[1]);
	}
	else if (preg_match('#^(select|selectmulti|radio):(piped|eval)(\r\n|\n|\r)(.*)$#siU', $setting['optioncode'], $matches))
	{
		$setting['optioncode'] = "$matches[1]:$matches[2]";
		$setting['optiondata'] = trim($matches[4]);
	}
	else if (preg_match('#^forum:?(\d+)$#s', $setting['optioncode'], $matches))
	{
		$size = intval($matches[1]);
		$setting['optioncode'] = 'forum';
	}
	else if (preg_match('#^usergroup:?(\d+)$#s', $setting['optioncode'], $matches))
	{
		$size = intval($matches[1]);
		$setting['optioncode'] = 'usergroup';
	}
	else if (preg_match('#^(usergroupextra)(\r\n|\n|\r)(.*)$#siU', $setting['optioncode'], $matches))
	{
		$setting['optioncode'] = 'usergroupextra';
		$setting['optiondata'] = trim($matches[3]);
	}

	switch ($setting['optioncode'])
	{
		// input type="text"
		case '':
		{
			print_input_row($description, $name, $setting['value'], 1, $width);
		}
		break;

		// input type="radio"
		case 'yesno':
		{
			print_yes_no_row($description, $name, $setting['value']);
		}
		break;

		// textarea
		case 'textarea':
		{
			print_textarea_row($description, $name, $setting['value'], $rows, "$width\" style=\"width:90%");
		}
		break;

		// bitfield
		case 'bitfield':
		{
			$setting['value'] = intval($setting['value']);
			$setting['html'] = '';

			if ($setting['bitfield'] === NULL)
			{
				print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
			}
			else
			{
				#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
				$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
				$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
				foreach ($setting['bitfield'] AS $key => $value)
				{
					$value = intval($value);
					$setting['html'] .= "<table style=\"width:175px; float:" . vB_Template_Runtime::fetchStyleVar('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
					<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
					<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . phpkd_vblvb_fetch_phrase_from_key(str_replace('|', '_', $matches[1]) . '_' . $key) . "</label></td>\r\n</tr></table>\r\n";
				}

				$setting['html'] .= "</div>\r\n";
				#$setting['html'] .= "</fieldset>";
				print_label_row($description, $setting['html'], '', 'top', $name, 40);
			}
		}
		break;

		// select:piped
		case 'select:piped':
		{
			print_select_row($description, $name, phpkd_vblvb_fetch_piped_options($setting['optiondata']), $setting['value']);
		}
		break;

		// radio:piped
		case 'radio:piped':
		{
			print_radio_row($description, $name, phpkd_vblvb_fetch_piped_options($setting['optiondata']), $setting['value'], 'smallfont');
		}
		break;

		// select:eval
		case 'select:eval':
		{
			$options = null;

			eval($setting['optiondata']);

			if (is_array($options) AND !empty($options))
			{
				print_select_row($description, $name, $options, $setting['value']);
			}
			else
			{
				print_input_row($description, $name, $setting['value']);
			}
		}
		break;

		// select:eval
		case 'selectmulti:eval':
		{
			$options = null;

			eval($setting['optiondata']);

			if (is_array($options) AND !empty($options))
			{
				print_select_row($description, $name . '[]', $options, $setting['value'], false, 5, true);
			}
			else
			{
				print_input_row($description, $name, $setting['value']);
			}
		}
		break;

		// radio:eval
		case 'radio:eval':
		{
			$options = null;

			eval($setting['optiondata']);

			if (is_array($options) AND !empty($options))
			{
				print_radio_row($description, $name, $options, $setting['value'], 'smallfont');
			}
			else
			{
				print_input_row($description, $name, $setting['value']);
			}
		}
		break;

		case 'username':
		{
			if (intval($setting['value']) AND $userinfo = $vbulletin->db->query_first("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid = " . intval($setting['value'])))
			{
				print_input_row($description, $name, $userinfo['username'], false);
			}
			else
			{
				print_input_row($description, $name);
			}
			break;
		}

		case 'usergroup':
		{
			$usergrouplist = array();
			foreach ($vbulletin->usergroupcache AS $usergroup)
			{
				$usergrouplist["$usergroup[usergroupid]"] = $usergroup['title'];
			}

			if ($size > 1)
			{
				print_select_row($description, $name . '[]', array(0 => $vbphrase['phpkd_vblvb_select_usergroups']) + $usergrouplist, unserialize($setting['value']), false, $size, true);
			}
			else
			{
				print_select_row($description, $name, $usergrouplist, $setting['value']);
			}
			break;
		}

		case 'forum':
		{
			$options = construct_forum_chooser_options();

			if ($size > 1)
			{
				print_select_row($description, $name . '[]', array(0 => $vbphrase['phpkd_vblvb_select_forums']) + $options, unserialize($setting['value']), false, $size, true);
			}
			else
			{
				print_select_row($description, $name, $options, $setting['value']);
			}
			break;
		}

		case 'usergroupextra':
		{
			$usergrouplist = phpkd_vblvb_fetch_piped_options($setting['optiondata']);
			foreach ($vbulletin->usergroupcache AS $usergroup)
			{
				$usergrouplist["$usergroup[usergroupid]"] = $usergroup['title'];
			}

			print_select_row($description, $name, $usergrouplist, $setting['value']);
			break;
		}

		case 'colors':
		{
			$setting['html'] = '<table cellpadding="0" cellspacing="2" border="0" width="100%">';

			$setting['values'] = unserialize($setting['value']);
			$setting['values'] = (is_array($setting['values']) ? $setting['values'] : array());
			$setting['values'][] = '';

			$setting['html'] .= '<tr><td>' . $vbphrase['phpkd_vblvb_linkstatus_alive'] . '</td><td><table cellpadding="0" cellspacing="0" border="0"><tr><td><input type="text" class="col-g" name="setting[' . $setting[varname] . '][0]" id="color_0" value="' . htmlspecialchars_uni($setting['values'][0]) . '" tabindex="1" size="20" onchange="preview_color(0)" dir="ltr" />&nbsp;</td><td><div id="preview_0" class="colorpreview" onclick="open_color_picker(0, event)"></div></td></tr></table></td></tr>';
			$setting['html'] .= '<tr><td>' . $vbphrase['phpkd_vblvb_linkstatus_dead'] . '</td><td><table cellpadding="0" cellspacing="0" border="0"><tr><td><input type="text" class="col-g" name="setting[' . $setting[varname] . '][1]" id="color_1" value="' . htmlspecialchars_uni($setting['values'][1]) . '" tabindex="1" size="20" onchange="preview_color(1)" dir="ltr" />&nbsp;</td><td><div id="preview_1" class="colorpreview" onclick="open_color_picker(1, event)"></div></td></tr></table></td></tr>';
			$setting['html'] .= '<tr><td>' . $vbphrase['phpkd_vblvb_linkstatus_down'] . '</td><td><table cellpadding="0" cellspacing="0" border="0"><tr><td><input type="text" class="col-g" name="setting[' . $setting[varname] . '][2]" id="color_2" value="' . htmlspecialchars_uni($setting['values'][2]) . '" tabindex="1" size="20" onchange="preview_color(2)" dir="ltr" />&nbsp;</td><td><div id="preview_2" class="colorpreview" onclick="open_color_picker(2, event)"></div></td></tr></table></td></tr>';

			$setting['html'] .= "</table>";

			print_label_row($description, $setting['html']);
			break;
		}

		// arbitrary number of <input type="text" />
		case 'multiinput':
		{
			$setting['html'] = "<div id=\"ctrl_$setting[varname]\"><fieldset id=\"multi_input_fieldset_$setting[varname]\" style=\"padding:4px\">";

			$setting['values'] = unserialize($setting['value']);
			$setting['values'] = (is_array($setting['values']) ? $setting['values'] : array());
			$setting['values'][] = '';

			foreach ($setting['values'] AS $key => $value)
			{
				$setting['html'] .= "<div id=\"multi_input_container_$setting[varname]_$key\">" . ($key + 1) . " <input type=\"text\" class=\"bginput\" name=\"setting[$setting[varname]][$key]\" id=\"multi_input_$setting[varname]_$key\" size=\"40\" value=\"" . htmlspecialchars_uni($value) . "\" tabindex=\"1\" /></div>";
			}

			$i = sizeof($setting['values']);

			if ($i == 0)
			{
				$setting['html'] .= "<div><input type=\"text\" class=\"bginput\" name=\"setting[$setting[varname]][$i]\" size=\"40\" tabindex=\"1\" /></div>";
			}

			$setting['html'] .= "
				</fieldset>
				<div class=\"smallfont\"><a href=\"#\" onclick=\"return multi_input['$setting[varname]'].add()\">Add Another Option</a></div>
				<script type=\"text/javascript\">
				<!--
				multi_input['$setting[varname]'] = new vB_Multi_Input('$setting[varname]', $i, '" . $vbulletin->options['cpstylefolder'] . "');
				//-->
				</script>
			";

			print_label_row($description, $setting['html']);
			break;
		}

		// just a label
		default:
		{
			$handled = false;

			if (!$handled)
			{
				eval("\$right = \"<div id=\\\"ctrl_setting[$setting[varname]]\\\">$setting[optioncode]</div>\";");
				print_label_row($description, $right, '', 'top', $name, 50);
			}
		}
		break;
	}

	echo "</tbody>\r\n";

	$valid = phpkd_vblvb_validation_code($setting['varname'], $setting['value'], $setting['validationcode']);

	echo "<tbody id=\"tbody_error_$settingid\" style=\"display:" . (($valid === 1 OR $valid === true) ? 'none' : '') . "\"><tr><td class=\"alt1 smallfont\" colspan=\"2\"><div style=\"padding:4px; border:solid 1px red; background-color:white; color:black\"><strong>$vbphrase[error]</strong>:<div id=\"span_error_$settingid\">$valid</div></div></td></tr></tbody>";
}

/**
 * Updates the setting table based on data passed in then rebuilds the datastore.
 * Only entries in the array are updated (allows partial updates).
 *
 * @param	array	Array of settings. Format: [setting_name] = new_value
 */
function phpkd_vblvb_save($settings)
{
	global $vbulletin, $vbphrase;

	$varnames = array();

	foreach(array_keys($settings) AS $varname)
	{
		$varnames[] = $vbulletin->db->escape_string($varname);
	}

	$rebuildstyle = false;
	$oldsettings = $vbulletin->db->query_read("
		SELECT value, varname, datatype, optioncode
		FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
		WHERE varname IN ('" . implode("', '", $varnames) . "')
		ORDER BY varname
	");

	while ($oldsetting = $vbulletin->db->fetch_array($oldsettings))
	{
		if ($oldsetting['optioncode'] == 'multiinput' OR $oldsetting['optioncode'] == 'colors')
		{
			$store = array();

			foreach ($settings["$oldsetting[varname]"] AS $value)
			{
				if ($value != '')
				{
					$store[] = $value;
				}
			}

			$settings["$oldsetting[varname]"] = serialize($store);
		}
		else if (preg_match('#^(usergroup|forum):[0-9]+$#', $oldsetting['optioncode']))
		{
			// serialize the array of usergroup/forum inputs
			if (!is_array($settings["$oldsetting[varname]"]))
			{
				 $settings["$oldsetting[varname]"] = array();
			}

			if (count($settings["$oldsetting[varname]"]) > 1 AND $settings["$oldsetting[varname]"][0] == 0)
			{
				unset($settings["$oldsetting[varname]"][0]);
			}

			$settings["$oldsetting[varname]"] = array_map('intval', $settings["$oldsetting[varname]"]);
			$settings["$oldsetting[varname]"] = serialize($settings["$oldsetting[varname]"]);
		}

		$newvalue = phpkd_vblvb_validate_value($settings["$oldsetting[varname]"], $oldsetting['datatype']);

		// this is a strict type check because we want '' to be different from 0
		// some special cases below only use != checks to see if the logical value has changed
		if (strval($oldsetting['value']) !== strval($newvalue))
		{
			$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "phpkd_vblvb_setting
				SET value = '" . $vbulletin->db->escape_string($newvalue) . "'
				WHERE varname = '" . $vbulletin->db->escape_string($oldsetting['varname']) . "'
			");
		}
	}

	// rebuild the $vbulletin->phpkd_vblvb array
	phpkd_vblvb_build();
}

/**
 * Attempts to run validation code on a setting
 *
 * @param	string	Setting varname
 * @param	mixed	Setting value
 * @param	string	Setting validation code
 * @param	mixed	Setting raw value
 * @return	mixed
 */
function phpkd_vblvb_validation_code($varname, $value, $validation_code, $raw_value = false)
{
	if ($raw_value === false)
	{
		$raw_value = $value;
	}

	if ($validation_code != '')
	{
		$validation_function = create_function('&$data, $raw_data', $validation_code);
		$validation_result = $validation_function($value, $raw_value);

		if ($validation_result === false OR $validation_result === null)
		{
			$valid = fetch_error("setting_validation_error_$varname");

			if (preg_match('#^Could#i', $valid) AND preg_match("#'" . preg_quote("setting_validation_error_$varname", '#') . "'#i", $valid))
			{
				$valid = fetch_error("you_did_not_enter_a_valid_value");
			}

			return $valid;
		}
		else
		{
			return $validation_result;
		}
	}

	return 1;
}

/**
 * Validates the provided value of a setting against its datatype
 *
 * @param	mixed	(ref) Setting value
 * @param	string	Setting datatype ('number', 'boolean' or other)
 * @param	boolean	Represent boolean with 1/0 instead of true/false
 * @param	boolean	Query database for username type
 * @return	mixed	Setting value
 */
function phpkd_vblvb_validate_value(&$value, $datatype, $bool_as_int = true, $username_query = true)
{
	global $vbulletin;

	switch ($datatype)
	{
		case 'number':
			$value += 0;
			break;

		case 'integer':
			$value = intval($value);
			break;

		case 'arrayinteger':
			$key = array_keys($value);
			$size = sizeOf($key);

			for ($i = 0; $i < $size; $i++)
			{
				$value[$key[$i]] = intval($value[$key[$i]]);
			}
			break;

		case 'arrayfree':
			$key = array_keys($value);
			$size = sizeOf($key);
			for ($i = 0; $i < $size; $i++)
			{
				$value[$key[$i]] = trim($value[$key[$i]]);
			}
			break;

		case 'posint':
			$value = max(1, intval($value));
			break;

		case 'boolean':
			$value = ($bool_as_int ? ($value ? 1 : 0) : ($value ? true : false));
			break;

		case 'bitfield':
			if (is_array($value))
			{
				$bitfield = 0;

				foreach ($value AS $bitval)
				{
					$bitfield += $bitval;
				}

				$value = $bitfield;
			}
			else
			{
				$value += 0;
			}
			break;

		case 'username':
			$value = trim($value);
			if ($username_query)
			{
				if (empty($value))
				{
					$value =  0;
				}
				else if ($userinfo = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username = '" . $vbulletin->db->escape_string(htmlspecialchars_uni($value)) . "'"))
				{
					$value = $userinfo['userid'];
				}
				else
				{
					$value = false;
				}
			}
			break;

		default:
			$value = trim($value);
	}

	return $value;
}

/**
 * Fetches an array describing the bits in the requested bitfield
 *
 * @param	string	Represents the array key required... use x|y|z to fetch ['x']['y']['z']
 * @return	array	Reference to the requested array from includes/xml/bitfield_{product}.xml
 */
function &phpkd_vblvb_fetch_bitfield_definitions($string)
{
	static $bitfields = null;

	if ($bitfields === null)
	{
		require_once(DIR . '/includes/class_bitfield_builder.php');
		$bitfields = vB_Bitfield_Builder::return_data();
	}

	$keys = "['" . implode("']['", preg_split('#\|#si', $string, -1, PREG_SPLIT_NO_EMPTY)) . "']";

	eval('$return =& $bitfields' . $keys . ';');

	return $return;
}

/**
 * Imports settings from an XML settings file
 * Call as follows:
 * $path = './path/to/phpkd_vblvb-settings.xml';
 * phpkd_vblvb_xml_import_settings($xml);
 *
 * @param	mixed	Either XML string or boolean false to use $path global variable
 */
function phpkd_vblvb_xml_import_settings($xml = false)
{
	global $vbulletin, $vbphrase;

	print_dots_start('<strong>Importing Settings</strong>, ' . $vbphrase['please_wait'], ':', 'dspan');

	require_once(DIR . '/includes/class_xml.php');

	$xmlobj = new vB_XML_Parser($xml, $GLOBALS['path']);

	if ($xmlobj->error_no == 1)
	{
		print_dots_stop();
		print_stop_message('no_xml_and_no_path');
	}
	else if ($xmlobj->error_no == 2)
	{
		print_dots_stop();
		print_stop_message('please_ensure_x_file_is_located_at_y', 'phpkd_vblvb-settings.xml', $GLOBALS['path']);
	}

	if(!$arr = $xmlobj->parse())
	{
		print_dots_stop();
		print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	}

	if (!$arr['settinggroup'])
	{
		print_dots_stop();
		print_stop_message('invalid_file_specified');
	}

	$settinggroups = array();
	foreach($arr['settinggroup'] AS $group)
	{
		$settinggroups[] = $group['name'];
	}

	// delete old volatile settings and settings that might conflict with new ones...
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup WHERE volatile = 1 AND grouptitle IN('" . implode('\',\'', $settinggroups) . "')");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_setting WHERE volatile = 1 AND grouptitle IN('" . implode('\',\'', $settinggroups) . "')");

	// run through imported array
	if (!is_array($arr['settinggroup'][0]))
	{
		$arr['settinggroup'] = array($arr['settinggroup']);
	}

	foreach($arr['settinggroup'] AS $group)
	{
		// insert setting group
		$vbulletin->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "phpkd_vblvb_settinggroup
			(grouptitle, displayorder, volatile)
			VALUES
			('" . $vbulletin->db->escape_string($group['name']) . "', " . intval($group['displayorder']) . ", 1)
		");

		// build insert query for this group's settings
		$qBits = array();

		if (!is_array($group['setting'][0]))
		{
			$group['setting'] = array($group['setting']);
		}

		foreach($group['setting'] AS $setting)
		{
			if (isset($vbulletin->phpkd_vblvb["$setting[varname]"]))
			{
				$newvalue = $vbulletin->phpkd_vblvb["$setting[varname]"];
			}
			else
			{
				$newvalue = $setting['defaultvalue'];
			}

			$qBits[] = "(
				'" . $vbulletin->db->escape_string($setting['varname']) . "',
				'" . $vbulletin->db->escape_string($group['name']) . "',
				'" . $vbulletin->db->escape_string(trim($newvalue)) . "',
				'" . $vbulletin->db->escape_string(trim($setting['defaultvalue'])) . "',
				'" . $vbulletin->db->escape_string(trim($setting['datatype'])) . "',
				'" . $vbulletin->db->escape_string($setting['optioncode']) . "',
				" . intval($setting['displayorder']) . ",
				" . intval($setting['advanced']) . ",
				1" . (!defined('UPGRADE_COMPAT') ? ",
					'" . $vbulletin->db->escape_string($setting['validationcode']) . "',
					" . intval($setting['blacklist']) : '') . "\n\t)";
		}

		// run settings insert query
		$vbulletin->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "phpkd_vblvb_setting
			(varname, grouptitle, value, defaultvalue, datatype, optioncode, displayorder,
			advanced, volatile" . (!defined('UPGRADE_COMPAT') ? ', validationcode, blacklist' : '') . ")
			VALUES
			" . implode(",\n\t", $qBits));
	}

	// rebuild the $vbulletin->phpkd_vblvb array
	phpkd_vblvb_build();

	// stop the 'dots' counter feedback
	print_dots_stop();
}

/**
 * Imports hosts from an XML settings file
 * Call as follows:
 * $path = './path/to/phpkd_vblvb-hosts.xml';
 * phpkd_vblvb_xml_restore_hosts($xml);
 *
 * @param	mixed	Either XML string or boolean false to use $path global variable
 */
function phpkd_vblvb_xml_restore_hosts($xml = false)
{
	global $vbulletin, $vbphrase;

	print_dots_start('<strong>Importing Hosts</strong>, ' . $vbphrase['please_wait'], ':', 'dspan');

	require_once(DIR . '/includes/class_xml.php');

	$xmlobj = new vB_XML_Parser($xml, $GLOBALS['path']);

	if ($xmlobj->error_no == 1)
	{
		print_dots_stop();
		print_stop_message('no_xml_and_no_path');
	}
	else if ($xmlobj->error_no == 2)
	{
		print_dots_stop();
		print_stop_message('please_ensure_x_file_is_located_at_y', 'phpkd_vblvb-hosts.xml', $GLOBALS['path']);
	}

	if(!$arr = $xmlobj->parse())
	{
		print_dots_stop();
		print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	}

	if (!$arr['host'])
	{
		print_dots_stop();
		print_stop_message('invalid_file_specified');
	}

	$hosts = array();
	foreach($arr['host'] AS $host)
	{
		$hosts[] = $host['domain'];
	}

	// delete old hosts that might conflict with new ones...
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "phpkd_vblvb_host WHERE domain IN('" . implode('\',\'', $hosts) . "')");

	// run through imported array
	if (!is_array($arr['host'][0]))
	{
		$arr['host'] = array($arr['host']);
	}


	// build insert query for this hosts
	$qBits = array();

	foreach($arr['host'] AS $host)
	{
		$qBits[] = "(
			'" . $vbulletin->db->escape_string(trim($host['domain'])) . "',
			" . intval($host['active']) . ",
			'" . $vbulletin->db->escape_string(trim($host['status'])) . "',
			'" . $vbulletin->db->escape_string(trim($host['urlmatch'])) . "',
			'" . $vbulletin->db->escape_string(trim($host['apiurl'])) . "',
			'" . $vbulletin->db->escape_string(trim($host['contentmatch'])) . "',
			'" . $vbulletin->db->escape_string(trim($host['downmatch'])) . "',
			'" . $vbulletin->db->escape_string(trim($host['urlsearch'])) . "',
			'" . $vbulletin->db->escape_string(trim($host['urlreplace'])) . "',
			'" . $vbulletin->db->escape_string(trim($host['notes'])) . "'\n\t)";
	}

	// run hosts insert query
	$vbulletin->db->query_write("
		INSERT IGNORE INTO " . TABLE_PREFIX . "phpkd_vblvb_host
		(domain, active, status, urlmatch, apiurl, contentmatch, downmatch, urlsearch, urlreplace, notes)
		VALUES
		" . implode(",\n\t", $qBits));


	// stop the 'dots' counter feedback
	print_dots_stop();
}

/**
 * Restores a settings backup from an XML file
 * Call as follows:
 * $path = './path/to/phpkd_vblvb-settings.xml';
 * phpkd_vblvb_xml_restore_settings($xml);
 *
 * @param	mixed	Either XML string or boolean false to use $path global variable
 * @param	bool	Ignore blacklisted settings
 */
function phpkd_vblvb_xml_restore_settings($xml = false, $blacklist = true)
{
	global $vbulletin, $vbphrase;
	$newsettings = array();

	print_dots_start('<strong>Importing Settings</strong>, ' . $vbphrase['please_wait'], ':', 'dspan');

	require_once(DIR . '/includes/class_xml.php');

	$xmlobj = new vB_XML_Parser($xml, $GLOBALS['path']);

	if ($xmlobj->error_no == 1)
	{
		print_dots_stop();
		print_stop_message('no_xml_and_no_path');
	}
	else if ($xmlobj->error_no == 2)
	{
		print_dots_stop();
		print_stop_message('please_ensure_x_file_is_located_at_y', 'phpkd_vblvb-settings.xml', $GLOBALS['path']);
	}

	if(!$newsettings = $xmlobj->parse())
	{
		print_dots_stop();
		print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	}

	if (!$newsettings['setting'])
	{
		print_dots_stop();
		print_stop_message('invalid_file_specified');
	}

	foreach($newsettings['setting'] AS $setting)
	{
		// Loop to update all the settings
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "phpkd_vblvb_setting
			SET value='" . $vbulletin->db->escape_string($setting['value']) . "'
			WHERE varname ='" . $vbulletin->db->escape_string($setting['varname']) . "'
				" . ($blacklist ? "AND blacklist = 0" : "") . "
		");
	}

	unset($newsettings);

	// rebuild the $vbulletin->phpkd_vblvb array
	phpkd_vblvb_build();

	// stop the 'dots' counter feedback
	print_dots_stop();

}

/**
 * Attempts to fetch the text of a phrase from the given key.
 * If the phrase is not found, the key is returned.
 *
 * @param	string	Phrase key
 * @return	string
 */
function phpkd_vblvb_fetch_phrase_from_key($phrase_key)
{
	global $vbphrase;

	return (isset($vbphrase["$phrase_key"])) ? $vbphrase["$phrase_key"] : $phrase_key;
}

/**
 * Returns an array of options and phrase values from a piped list such as 0|no\n1|yes\n2|maybe
 *
 * @param	string	Piped data
 * @return	array
 */
function phpkd_vblvb_fetch_piped_options($piped_data)
{
	$options = array();
	$option_lines = preg_split("#(\r\n|\n|\r)#s", $piped_data, -1, PREG_SPLIT_NO_EMPTY);

	foreach ($option_lines AS $option)
	{
		if (preg_match('#^([^\|]+)\|(.+)$#siU', $option, $option_match))
		{
			$option_text = explode('(,)', $option_match[2]);
			foreach (array_keys($option_text) AS $idx)
			{
				$option_text["$idx"] = phpkd_vblvb_fetch_phrase_from_key(trim($option_text["$idx"]));
			}
			$options["$option_match[1]"] = implode(', ', $option_text);
		}
	}

	return $options;
}

/**
 * Reads settings from the settings then saves the values to the datastore
 * After reading the contents of the setting table, the function will rebuild the $vbulletin->phpkd_vblvb array, then serialize the array and save that serialized array into the 'phpkd_vblvb' entry of the datastore in the database
 *
 * @return	array	The $vbulletin->phpkd_vblvb array
 */
function phpkd_vblvb_build()
{
	require_once(DIR . '/includes/adminfunctions_options.php');

	global $vbulletin;

	$vbulletin->phpkd_vblvb = array();
	$settings = $vbulletin->db->query_read("SELECT varname, value, datatype FROM " . TABLE_PREFIX . "phpkd_vblvb_setting");

	while ($setting = $vbulletin->db->fetch_array($settings))
	{
		$vbulletin->phpkd_vblvb["$setting[varname]"] = phpkd_vblvb_validate_value($setting['value'], $setting['datatype'], true, false);
	}

	build_datastore('phpkd_vblvb', serialize($vbulletin->phpkd_vblvb), 1);

	return $vbulletin->phpkd_vblvb;
}

/**
 * Prints a dialog box asking if the user is sure they want to delete the specified item from the database
 *
 * @param	string	Name of table from which item will be deleted
 * @param	mixed	ID of item to be deleted
 * @param	string	PHP script to which the form will submit
 * @param	string	'do' action for target script
 * @param	string	Word describing item to be deleted - eg: 'forum' or 'user' or 'post' etc.
 * @param	mixed	If not empty, an array containing name=>value pairs to be used as hidden input fields
 * @param	string	Extra text to be printed in the dialog box
 * @param	string	Name of 'title' field in the table in the database
 * @param	string	Name of 'idfield' field in the table in the database
 */
function phpkd_vblvb_delete_confirmation($table, $itemid, $phpscript, $do, $itemname = '', $hiddenfields = 0, $extra = '', $titlename = 'title', $idfield = '')
{
	global $vbulletin, $vbphrase;

	$idfield = $idfield ? $idfield : $table . 'id';
	$itemname = $itemname ? $itemname : $table;
	$deleteword = 'delete';
	$encodehtml = true;

	switch($table)
	{
		case 'phpkd_vblvb_host':
			$item = $vbulletin->db->query_first("
				SELECT id, domain AS title
				FROM " . TABLE_PREFIX . "phpkd_vblvb_host
				WHERE id = '" . $vbulletin->db->escape_string($itemid) . "'
			");
			$idfield = 'id';
			break;

		case 'phpkd_vblvb_setting':
			$item = $vbulletin->db->query_first("
				SELECT varname AS title
				FROM " . TABLE_PREFIX . "phpkd_vblvb_setting
				WHERE varname = '" . $vbulletin->db->escape_string($itemid) . "'
			");
			$idfield = 'title';
			break;

		case 'phpkd_vblvb_settinggroup':
			$item = $vbulletin->db->query_first("
				SELECT grouptitle AS title
				FROM " . TABLE_PREFIX . "phpkd_vblvb_settinggroup
				WHERE grouptitle = '" . $vbulletin->db->escape_string($itemid) . "'
			");
			$idfield = 'title';
			break;

		default:
			$handled = false;

			if (!$handled)
			{
				$item = $vbulletin->db->query_first("
					SELECT $idfield, $titlename AS title
					FROM " . TABLE_PREFIX . "$table
					WHERE $idfield = $itemid
				");
			}
			break;
	}

	if ($encodehtml AND (strcspn($item['title'], '<>"') < strlen($item['title']) OR (strpos($item['title'], '&') !== false AND !preg_match('/&(#[0-9]+|amp|lt|gt|quot);/si', $item['title']))))
	{
		// title contains html entities that should be encoded
		$item['title'] = htmlspecialchars_uni($item['title']);
	}

	if ($item["$idfield"] == $itemid AND !empty($itemid))
	{
		echo "<p>&nbsp;</p><p>&nbsp;</p>";
		print_form_header($phpscript, $do, 0, 1, '', '75%');
		construct_hidden_code(($idfield == 'styleid' OR $idfield == 'languageid') ? 'do' . $idfield : $idfield, $itemid);

		if (is_array($hiddenfields))
		{
			foreach($hiddenfields AS $varname => $value)
			{
				construct_hidden_code($varname, $value);
			}
		}

		print_table_header(construct_phrase($vbphrase['confirm_deletion_x'], $item['title']));
		print_description_row("
			<blockquote><br />
			" . construct_phrase($vbphrase["are_you_sure_want_to_{$deleteword}_{$itemname}_x"], $item['title'],
				$idfield, $item["$idfield"], iif($extra, "$extra<br /><br />")) . "
			<br /></blockquote>\n\t");
		print_submit_row($vbphrase['yes'], 0, 2, $vbphrase['no']);
	}
	else
	{
		print_stop_message('could_not_find', '<b>' . $itemname . '</b>', $idfield, $itemid);
	}
}

/**
 * Builds the color picker popup item for the style editor
 *
 * @param	integer	Width of each color swatch (pixels)
 * @param	string	CSS 'display' parameter (default: 'none')
 *
 * @return	string
 */
function phpkd_vblvb_color_picker($size = 12, $display = 'none')
{
	global $vbulletin, $colorPickerWidth, $colorPickerType;

	$previewsize = 3 * $size;
	$surroundsize = $previewsize * 2;
	$colorPickerWidth = 21 * $size + 22;

	$html = "
	<style type=\"text/css\">
	#colorPicker
	{
		background: black;
		position: absolute;
		left: 0px;
		top: 0px;
		width: {$colorPickerWidth}px;
	}
	#colorFeedback
	{
		border: solid 1px black;
		border-bottom: none;
		width: {$colorPickerWidth}px;
	}
	#colorFeedback input
	{
		font: 11px verdana, arial, helvetica, sans-serif;
	}
	#colorFeedback button
	{
		width: 19px;
		height: 19px;
	}
	#txtColor
	{
		border: inset 1px;
		width: 70px;
	}
	#colorSurround
	{
		border: inset 1px;
		white-space: nowrap;
		width: {$surroundsize}px;
		height: 15px;
	}
	#colorSurround td
	{
		background-color: none;
		border: none;
		width: {$previewsize}px;
		height: 15px;
	}
	#swatches
	{
		background-color: black;
		width: {$colorPickerWidth}px;
	}
	#swatches td
	{
		background: black;
		border: none;
		width: {$size}px;
		height: {$size}px;
	}
	</style>
	<div id=\"colorPicker\" style=\"display:$display\" oncontextmenu=\"switch_color_picker(1); return false\" onmousewheel=\"switch_color_picker(event.wheelDelta * -1); return false;\">
	<table id=\"colorFeedback\" class=\"tcat\" cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\">
	<tr>
		<td><button onclick=\"col_click('transparent'); return false\"><img src=\"../cpstyles/" . $vbulletin->options['cpstylefolder'] . "/colorpicker_transparent.gif\" title=\"'transparent'\" alt=\"\" /></button></td>
		<td>
			<table id=\"colorSurround\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
			<tr>
				<td id=\"oldColor\" onclick=\"close_color_picker()\"></td>
				<td id=\"newColor\"></td>
			</tr>
			</table>
		</td>
		<td width=\"100%\"><input id=\"txtColor\" type=\"text\" value=\"\" size=\"8\" /></td>
		<td style=\"white-space:nowrap\">
			<input type=\"hidden\" name=\"colorPickerType\" id=\"colorPickerType\" value=\"$colorPickerType\" />
			<button onclick=\"switch_color_picker(1); return false\"><img src=\"../cpstyles/" . $vbulletin->options['cpstylefolder'] . "/colorpicker_toggle.gif\" alt=\"\" /></button>
			<button onclick=\"close_color_picker(); return false\"><img src=\"../cpstyles/" . $vbulletin->options['cpstylefolder'] . "/colorpicker_close.gif\" alt=\"\" /></button>
		</td>
	</tr>
	</table>
	<table id=\"swatches\" cellpadding=\"0\" cellspacing=\"1\" border=\"0\">\n";

	$colors = array(
		'00', '33', '66',
		'99', 'CC', 'FF'
	);

	$specials = array(
		'#000000', '#333333', '#666666',
		'#999999', '#CCCCCC', '#FFFFFF',
		'#FF0000', '#00FF00', '#0000FF',
		'#FFFF00', '#00FFFF', '#FF00FF'
	);

	$green = array(5, 4, 3, 2, 1, 0, 0, 1, 2, 3, 4, 5);
	$blue = array(0, 0, 0, 5, 4, 3, 2, 1, 0, 0, 1, 2, 3, 4, 5, 5, 4, 3, 2, 1, 0);

	for ($y = 0; $y < 12; $y++)
	{
		$html .= "\t<tr>\n";

		$html .= phpkd_vblvb_color_picker_element(0, $y, '#000000');
		$html .= phpkd_vblvb_color_picker_element(1, $y, $specials["$y"]);
		$html .= phpkd_vblvb_color_picker_element(2, $y, '#000000');

		for ($x = 3; $x < 21; $x++)
		{
			$r = floor((20 - $x) / 6) * 2 + floor($y / 6);
			$g = $green["$y"];
			$b = $blue["$x"];

			$html .= phpkd_vblvb_color_picker_element($x, $y, '#' . $colors["$r"] . $colors["$g"] . $colors["$b"]);
		}

		$html .= "\t</tr>\n";
	}

	$html .= "\t</table>
	</div>
	<script type=\"text/javascript\">
	<!--
	var tds = fetch_tags(fetch_object(\"swatches\"), \"td\");
	for (var i = 0; i < tds.length; i++)
	{
		tds[i].onclick = swatch_click;
		tds[i].onmouseover = swatch_over;
	}
	//-->
	</script>\n";

	return $html;
}

// #############################################################################
/**
 * Builds a single color swatch for the color picker gadget
 *
 * @param	integer	Current X coordinate
 * @param	integer	Current Y coordinate
 * @param	string	Color
 *
 * @return	string
 */
function phpkd_vblvb_color_picker_element($x, $y, $color)
{
	global $vbulletin;
	return "\t\t<td style=\"background:$color\" id=\"sw$x-$y\"><img src=\"../" . $vbulletin->options['cleargifurl'] . "\" alt=\"\" style=\"width:11px; height:11px\" /></td>\r\n";
}

/*
 * Fix Bug: "MySQL server gone away" & "Allowed memory exhausted" errors
 * We've initiated a new DB connection with persistance allowed, so we don't get in such troubles with ongoing queries!!
 */
function phpkd_vblvb_fix_wait_timeout()
{
	global $vbulletin;

	// Set persistent connection ON!
	$vbulletin->config['MasterServer']['usepconnect'] = 1;

	// load database class
	switch (strtolower($vbulletin->config['Database']['dbtype']))
	{
		// load standard MySQL class
		case 'mysql':
		case '':
		{
			if ($vbulletin->debug AND ($vbulletin->input->clean_gpc('r', 'explain', TYPE_UINT) OR (defined('POST_EXPLAIN') AND !empty($_POST))))
			{
				// load 'explain' database class
				require_once(DIR . '/includes/class_database_explain.php');
				$db2 = new vB_Database_Explain($vbulletin);
			}
			else
			{
				$db2 = new vB_Database($vbulletin);
			}
			break;
		}

		case 'mysql_slave':
		{
			require_once(DIR . '/includes/class_database_slave.php');
			$db2 = new vB_Database_Slave($vbulletin);
			break;
		}

		// load MySQLi class
		case 'mysqli':
		{
			if ($vbulletin->debug AND ($vbulletin->input->clean_gpc('r', 'explain', TYPE_UINT) OR (defined('POST_EXPLAIN') AND !empty($_POST))))
			{
				// load 'explain' database class
				require_once(DIR . '/includes/class_database_explain.php');
				$db2 = new vB_Database_MySQLi_Explain($vbulletin);
			}
			else
			{
				$db2 = new vB_Database_MySQLi($vbulletin);
			}
			break;
		}

		case 'mysqli_slave':
		{
			require_once(DIR . '/includes/class_database_slave.php');
			$db2 = new vB_Database_Slave_MySQLi($vbulletin);
			break;
		}

		// load extended, non MySQL class
		default:
		{
			// This is not implemented fully yet
			// $db2 = 'vB_Database_' . $vbulletin->config['Database']['dbtype'];
			// $db2 = new $db($vbulletin);
			die('Fatal error: Database class not found');
		}
	}

	// Make a new database connection
	$db2->connect(
		$vbulletin->config['Database']['dbname'],
		$vbulletin->config['MasterServer']['servername'],
		$vbulletin->config['MasterServer']['port'],
		$vbulletin->config['MasterServer']['username'],
		$vbulletin->config['MasterServer']['password'],
		$vbulletin->config['MasterServer']['usepconnect'],
		$vbulletin->config['SlaveServer']['servername'],
		$vbulletin->config['SlaveServer']['port'],
		$vbulletin->config['SlaveServer']['username'],
		$vbulletin->config['SlaveServer']['password'],
		$vbulletin->config['SlaveServer']['usepconnect'],
		$vbulletin->config['Mysqli']['ini_file'],
		(isset($vbulletin->config['Mysqli']['charset']) ? $vbulletin->config['Mysqli']['charset'] : '')
	);

	// vBulletin doesn't work under MySQL strict mode currently, so force mode required!
	$db2->force_sql_mode('');

	// make $db2 a member of $vbulletin
	$vbulletin->db =& $db2;
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.200
|| # $Revision: 151 $
|| # Released: $Date: 2010-05-15 13:05:27 +0300 (Sat, 15 May 2010) $
|| ########################################################################### ||
\*============================================================================*/
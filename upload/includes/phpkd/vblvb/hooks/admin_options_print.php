<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.135 # ||
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


if (@get_class($this) != 'PHPKD_VBLVB' OR !defined('PHPKD_VBLVB'))
{
	echo 'Prohibited Access!';
	exit;
}


/*
 * Required Initializations
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
 * $vbphrase
 * 
 * Input Parameters:
 * ~~~~~~~~~~~~~~~~~~
 * $setting, $description, $name
 * 
 * Output Parameters:
 * ~~~~~~~~~~~~~~~~~~~
 * $handled, $setting
 * 
 */


// Parameters required!
if (is_array($params) AND !empty($params) AND $this->verify_hook_params($params))
{
	@extract($params);
}
else
{
	trigger_error('Invalid parameters!', E_USER_ERROR);
}


if ($setting['optioncode'] == 'phpkd_vblvb_hosts')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_hosts');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase("<strong>" . $this->vbphrase['settings_bitfield_error'] . "</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_hosts_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_hosts2')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_hosts2');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase("<strong>" . $this->vbphrase['settings_bitfield_error'] . "</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_hosts_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_hosts3')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_hosts3');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase("<strong>" . $this->vbphrase['settings_bitfield_error'] . "</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_hosts_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_bbcodes')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_bbcodes');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase("<strong>" . $this->vbphrase['settings_bitfield_error'] . "</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_bbcodes_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_threadmodes')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_threadmodes');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase("<strong>" . $this->vbphrase['settings_bitfield_error'] . "</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_threadmodes_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_masks')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_masks');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase('<strong>' . $this->vbphrase['settings_bitfield_error'] . '</strong>', implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_masks_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_rprts')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_rprts');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase('<strong>' . $this->vbphrase['settings_bitfield_error'] . '</strong>', implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_rprts_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_rprtu')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_rprtu');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase('<strong>' . $this->vbphrase['settings_bitfield_error'] . '</strong>', implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_rprtu_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_punish')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_punish');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase('<strong>' . $this->vbphrase['settings_bitfield_error'] . '</strong>', implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_punish_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_protocols')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_protocols');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase('<strong>' . $this->vbphrase['settings_bitfield_error'] . '</strong>', implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_protocols_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_threadmodes')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_threadmodes');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase('<strong>' . $this->vbphrase['settings_bitfield_error'] . '</strong>', implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_threadmodes_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_postmodes')
{
	$handled = TRUE;
	$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_postmodes');
	$setting['value'] = intval($setting['value']);
	$setting['html'] = '';

	if ($setting['bitfield'] === NULL)
	{
		print_label_row($description, construct_phrase('<strong>' . $this->vbphrase['settings_bitfield_error'] . '</strong>', implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
	}
	else
	{
		#$setting['html'] .= "<fieldset><legend>" . $this->vbphrase['yes'] . " / " . $this->vbphrase['no'] . "</legend>";
		$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
		$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
		foreach ($setting['bitfield'] AS $key => $value)
		{
			$value = intval($value);
			$setting['html'] .= "<table style=\"width:175px; float:" . phpkd_vblvb_stylevar_compatibility('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
			<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
			<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_postmodes_' . $key) . "</label></td>\r\n</tr></table>\r\n";
		}

		$setting['html'] .= "</div>\r\n";
		#$setting['html'] .= "</fieldset>";
		print_label_row($description, $setting['html'], '', 'top', $name, 40);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_forums')
{
	$handled = TRUE;
	$options = construct_forum_chooser_options();

	// eval($setting['optiondata']);

	if (is_array($options) AND !empty($options))
	{
		print_select_row($description, $name . '[]', array(0 => $this->vbphrase['phpkd_vblvb_select_forums']) + $options, unserialize($setting['value']), FALSE, 8, TRUE);
	}
	else
	{
		print_input_row($description, $name, $setting['value']);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_usergroups')
{
	$handled = TRUE;
	$usergrouplist = array();
	foreach ($this->registry->usergroupcache AS $usergroup)
	{
		$usergrouplist["$usergroup[usergroupid]"] = $usergroup['title'];
	}

	if (is_array($usergrouplist) AND !empty($usergrouplist))
	{
		print_select_row($description, $name . '[]', array(0 => $this->vbphrase['phpkd_vblvb_select_usergroups']) + $usergrouplist, unserialize($setting['value']), FALSE, 8, TRUE);
	}
	else
	{
		print_input_row($description, $name, $setting['value']);
	}
}
else if ($setting['optioncode'] == 'phpkd_vblvb_linkstatus_colors')
{
	$handled = TRUE;
	$setting['html'] = "<div id=\"ctrl_$setting[varname]\"><fieldset id=\"multi_input_fieldset_$setting[varname]\" style=\"padding:4px\">";

	$setting['values'] = unserialize($setting['value']);
	$setting['values'] = (is_array($setting['values']) ? $setting['values'] : array());
	$setting['values'][] = '';

	$setting['html'] .= "<div id=\"multi_input_container_$setting[varname]_0\">" . $this->vbphrase['phpkd_vblvb_linkstatus_alive'] . " <input type=\"text\" class=\"bginput\" name=\"setting[$setting[varname]][0]\" id=\"multi_input_$setting[varname]_0\" size=\"40\" value=\"" . htmlspecialchars_uni($setting['values'][0]) . "\" tabindex=\"1\" /></div>";
	$setting['html'] .= "<div id=\"multi_input_container_$setting[varname]_1\">" . $this->vbphrase['phpkd_vblvb_linkstatus_dead'] . " <input type=\"text\" class=\"bginput\" name=\"setting[$setting[varname]][1]\" id=\"multi_input_$setting[varname]_1\" size=\"40\" value=\"" . htmlspecialchars_uni($setting['values'][1]) . "\" tabindex=\"1\" /></div>";
	$setting['html'] .= "<div id=\"multi_input_container_$setting[varname]_2\">" . $this->vbphrase['phpkd_vblvb_linkstatus_down'] . " <input type=\"text\" class=\"bginput\" name=\"setting[$setting[varname]][2]\" id=\"multi_input_$setting[varname]_2\" size=\"40\" value=\"" . htmlspecialchars_uni($setting['values'][2]) . "\" tabindex=\"1\" /></div>";

	$setting['html'] .= "</fieldset>";

	print_label_row($description, $setting['html']);
}


if ($handled)
{
	return array('handled' => $handled, 'setting' => $setting);
}
else
{
	return FALSE;
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.135
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
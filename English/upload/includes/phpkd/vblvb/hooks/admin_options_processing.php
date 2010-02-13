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


if (@get_class($this) != 'PHPKD_VBLVB' OR !defined('PHPKD_VBLVB'))
{
	echo 'Prohibited Access!';
	exit;
}


/*
 * Required Initializations
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
 * NULL
 * 
 * Input Parameters:
 * ~~~~~~~~~~~~~~~~~~
 * $oldsetting, $settings
 * 
 * Output Parameters:
 * ~~~~~~~~~~~~~~~~~~~
 * $settings
 * 
 */


// Parameters required!
if (is_array($params) AND !empty($params) AND $this->verify_hook_params($params))
{
	$internalhandle = FALSE;
	@extract($params);
}
else
{
	trigger_error('Invalid parameters!', E_USER_ERROR);
}


if ($oldsetting['optioncode'] == 'phpkd_vblvb_forums')
{
	$internalhandle = TRUE;

	if (count($settings["$oldsetting[varname]"]) > 1 AND $settings["$oldsetting[varname]"][0] == 0)
	{
		unset($settings["$oldsetting[varname]"][0]);
	}

	$settings["$oldsetting[varname]"] = serialize($settings["$oldsetting[varname]"]);
}
else if ($oldsetting['optioncode'] == 'phpkd_vblvb_usergroups')
{
	$internalhandle = TRUE;

	if (count($settings["$oldsetting[varname]"]) > 1 AND $settings["$oldsetting[varname]"][0] == 0)
	{
		unset($settings["$oldsetting[varname]"][0]);
	}

	// serialize the array of usergroup inputs
	if (!is_array($settings["$oldsetting[varname]"]))
	{
		 $settings["$oldsetting[varname]"] = array();
	}

	$settings["$oldsetting[varname]"] = array_map('intval', $settings["$oldsetting[varname]"]);
	$settings["$oldsetting[varname]"] = serialize($settings["$oldsetting[varname]"]);
}


if ($internalhandle)
{
	return array('settings' => $settings);
}
else
{
	return FALSE;
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.122
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
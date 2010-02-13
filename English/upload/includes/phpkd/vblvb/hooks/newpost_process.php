<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 4.0.122 # ||
|| # License Type: Commercial License                             $Revision$ # ||
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
 * $type, $post, $dataman
 * 
 * Output Parameters:
 * ~~~~~~~~~~~~~~~~~~~
 * NULL
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


if (($type == 'thread' AND ($this->registry->options['phpkd_vblvb_checked_new'] == 1 OR $this->registry->options['phpkd_vblvb_checked_new'] == 2)) OR $type == 'reply' AND $this->registry->options['phpkd_vblvb_checked_new'] == 1)
{
	// ... Licensing ... //
	$phpkd_vblvb_license_proceed = FALSE;
	$phpkd_commercial40_license = @unserialize($this->registry->options['phpkd_commercial40_license']);
	if ($phpkd_commercial40_license['vblvb']['lc'] > (TIMENOW - 86400))
	{
		$phpkd_vblvb_license_proceed = TRUE;
	}
	else if ($this->verify_license())
	{
		$phpkd_vblvb_license_proceed = TRUE;

		$phpkd_commercial40_license['vblvb']['lc'] = TIMENOW;
		$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . @serialize($phpkd_commercial40_license) . "' WHERE varname = 'phpkd_commercial40_license'");

		require_once(DIR . '/includes/phpkd/vblvb/functions_databuild.php');
		phpkd_vblvb_build_options();
	}
	// ... Licensing ... //


	if ($phpkd_vblvb_license_proceed AND !in_array($this->registry->userinfo['usergroupid'], explode(' ', $this->registry->options['phpkd_vblvb_powerful_ugids'])))
	{
		$internalhandle = TRUE;

		$phpkd_vblvb_fetch_urls = $this->dm(array('hosts' => TRUE, 'masks' => TRUE, 'protocols' => TRUE, 'bbcodes' => TRUE, 'punishments' => TRUE, 'staff_reports' => TRUE, 'user_reports' => TRUE))->fetch_urls($post['message']);

		// Critical Limit/Red Line
		if ($phpkd_vblvb_fetch_urls['dead'] > 0 AND $phpkd_vblvb_fetch_urls['checked'] > 0)
		{
			$phpkd_vblvb_critical = ($phpkd_vblvb_fetch_urls['dead'] / $phpkd_vblvb_fetch_urls['checked']) * 100;
			if ($phpkd_vblvb_critical > $this->registry->options['phpkd_vblvb_critical'])
			{
				$dataman->error('phpkd_vblvb_newpost');
			}
		}
	}
}


if ($internalhandle)
{
	return TRUE;
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
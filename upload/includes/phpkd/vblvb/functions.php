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


function phpkd_vblvb_stylevar_compatibility($dir = 'left')
{
	if (substr(SIMPLE_VERSION, 0, 1) >= 4)
	{
		return vB_Template_Runtime::fetchStyleVar("$dir");
	}
	else
	{
		global $stylevar;
		return $stylevar["$dir"];
	}
}


function phpkd_vblvb_cron_kill($log, $nextitem)
{
	log_cron_action($log, $nextitem, 1);
	exit;
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.132
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 4.0.122 # ||
|| # License Type: Commercial License                             $Revision: 70 $ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # --------------- 'vB Link Verifier Bot' IS NOT FREE SOFTWARE ---------------- # ||
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


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.122
|| # $Revision: 70 $
|| # Released: $Date: 2010-01-14 22:59:36 +0200 (Thu, 14 Jan 2010) $
|| ########################################################################### ||
\*============================================================================*/
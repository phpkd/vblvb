<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 4.0.130 # ||
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
|| # Version: 4.0.130
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
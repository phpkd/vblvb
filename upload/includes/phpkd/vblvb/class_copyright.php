<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.1.212 # ||
|| # License Type: Commercial License                            $Revision: 151 $ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2011 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// No direct access! Should be accessed throuth the core class only!!
if (!defined('VB_AREA'))
{
	echo 'Prohibited Access!';
	exit;
}

$copyright = md5(md5('www.domain.com') . md5(PHPKD_VBLVB_TOCKEN) . md5($vbulletin->userinfo['securitytoken']) . md5(TIMENOW));


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.1.212
|| # $Revision: 151 $
|| # Released: $Date: 2010-05-15 13:05:27 +0300 (Sat, 15 May 2010) $
|| ########################################################################### ||
\*============================================================================*/
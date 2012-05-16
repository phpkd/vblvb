<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.1.331 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2012 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'phpkd_vblvb');
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('phpkd_vblvb');

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
	'PHPKD_VBLVB_SHELL'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'hosts' => array(
		'phpkd_vblvb_hostbit',
		'phpkd_vblvb_hosts'
	)
);

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/phpkd/vblvb/class_core.php');
require_once(DIR . '/includes/functions_user.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'hosts';
}

$includecss = array();

$phpkd_vblvb = new PHPKD_VBLVB($vbulletin, $vbphrase, ERRTYPE_SILENT);

// start the navbar
$navbits = array($vbulletin->phpkd_vblvb['general_scriptname'] . $vbulletin->session->vars['sessionurl_q'] => $vbulletin->phpkd_vblvb['general_scripttitle']);

// set the class for each cell/group
$navclass = array();
foreach (array('hosts', 'livecheck') AS $cellname)
{
	$navclass["$cellname"] = 'inactive';
}

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'hosts';
}

// #######################################################################
if ($_REQUEST['do'] == 'hosts')
{
	$hosts = $phpkd_vblvb->initialize(array('hosts' => array()))->hosts;

	if (!empty($hosts))
	{
		foreach($hosts AS $host)
		{
			$host['status'] = $vbphrase['phpkd_vblvb_linkstatus_' . $host['status']];
			$templater = vB_Template::create('phpkd_vblvb_hostbit');
				$templater->register('host', $host);
			$hostbits .= $templater->render();
		}
	}

	$navclass['hosts'] = 'active';
	$includecss['hosts'] = 'attachments.css';
	$page_templater = vB_Template::create('phpkd_vblvb_hosts');
	$page_templater->register('hostbits', $hostbits);
}

// #############################################################################
// spit out final HTML if we have got this far

if (!empty($page_templater))
{
	// make navbar
	$navbits = construct_navbits($navbits);
	$navbar = render_navbar_template($navbits);

	($hook = vBulletinHook::fetch_hook('profile_complete')) ? eval($hook) : false;

	// add any extra clientscripts
	$clientscripts = (isset($clientscripts_template) ? $clientscripts_template->render() : '');

	if (!$vbulletin->options['storecssasfile'])
	{
		$includecss = implode(',', $includecss);
	}

	// shell template
	$templater = vB_Template::create("PHPKD_VBLVB_SHELL");
	$templater->register_page_templates();
	$templater->register('includecss', $includecss);
	$templater->register('HTML', $page_templater->render());
	$templater->register('navbar', $navbar);
	$templater->register('navclass', $navclass);
	$templater->register('pagetitle', $pagetitle);
	$templater->register('clientscripts', $clientscripts);
	print_output($templater->render());
}

/*============================================================================*\
 || ########################################################################### ||
|| # Version: 4.1.331
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
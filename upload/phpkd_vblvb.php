<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.2.110 # ||
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
$navbits = array($vbulletin->phpkd_vblvb['publicverifier_scriptname'] . $vbulletin->session->vars['sessionurl_q'] => $vbulletin->phpkd_vblvb['publicverifier_scripttitle']);

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

	$navbits[''] = $vbphrase['phpkd_vblvb_nav_hosts'];
	$navclass['hosts'] = 'active';
	$includecss['hosts'] = 'attachments.css';
	$page_templater = vB_Template::create('phpkd_vblvb_hosts');
	$page_templater->register('hostbits', $hostbits);
}

/*
// #######################################################################
if ($_REQUEST['do'] == 'check')
{
	$vbulletin->input->clean_array_gpc('r', array(
			'message'       => TYPE_STR,
	));
	$message = htmlspecialchars_uni($vbulletin->GPC['message']);

	exec_nocache_headers();
	echo '<ol>';
	vbflush();
	sleep(1);
	echo '<li>First Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Second Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Third Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Fourth Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Fifth Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Sixth Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Seventh Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Eighth Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Ninth Link</li>';
	vbflush();
	sleep(1);
	echo '<li>Tenth Link</li>';
	echo '</ol>';
	vbflush();


	$navclass['checker'] = 'active';
	$includecss['hosts'] = 'attachments.css';
	$page_templater = vB_Template::create('phpkd_vblvb_check');
	$page_templater->register('hostbits', $hostbits);
	$page_templater->register('message', $message);

}

// Posted URLs will be saved to a 'session' database table, which will be cleaned periodically, and then the 'docheck' will get back saved URLs, process it, and display results
// Will use VaiSpy for the ajax stuff

// #######################################################################
if ($_REQUEST['do'] == 'docheck')
{
	// Process
	$vbulletin->input->clean_array_gpc('r', array(
			'message'       => TYPE_STR,
	));
	echo $vbulletin->GPC['message'];

	// Taken from edit_post hook, to be reviewed
	if (($type == 'thread' && ($vbulletin->phpkd_vblvb['general_checked_newposts'] == 1 || $vbulletin->phpkd_vblvb['general_checked_newposts'] == 2)) || $type == 'reply' && $vbulletin->phpkd_vblvb['general_checked_newposts'] == 1)
	{
		if ($phpkd_vblvb->verify_license(true) && !in_array($vbulletin->userinfo['usergroupid'], explode(' ', $vbulletin->phpkd_vblvb['punishment_powerful_ugids'])))
		{
			$links = $phpkd_vblvb->getDmhandle()->fetch_urls($post['message'], $post['postid']);

			// Required Sharing Links
			$forums = @unserialize($vbulletin->phpkd_vblvb['general_require_sharing']);
			if (!empty($forums) && in_array($foruminfo['forumid'], $forums) && $links['all'] <= 0)
			{
				$dataman->error('phpkd_vblvb_editpost_require_sharing', $vbulletin->phpkd_vblvb['publicverifier_scriptname']);
			}

			// Critical Limit/Red Line
			if ($links['checked'] > 0 && $links['dead'] > 0)
			{
				$critical = ($links['dead'] / $links['checked']) * 100;

				if ($critical >= $vbulletin->phpkd_vblvb['general_critical_limit'])
				{
					$dataman->error('phpkd_vblvb_invalid_checkpost');
				}
			}
		}
	}
}
*/

// ############################## Start vbflush ####################################
/**
 * Force the output buffers to the browser
 */
function vbflush()
{
	static $gzip_handler = null;
	if ($gzip_handler === null)
	{
		$gzip_handler = false;
		$output_handlers = ob_list_handlers();
		if (is_array($output_handlers))
		{
			foreach ($output_handlers AS $handler)
			{
				if ($handler == 'ob_gzhandler')
				{
					$gzip_handler = true;
					break;
				}
			}
		}
	}

	if ($gzip_handler)
	{
		// forcing a flush with this is very bad
		return;
	}

	if (ob_get_length() !== false)
	{
		@ob_flush();
	}
	flush();
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
|| # Version: 4.2.110
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
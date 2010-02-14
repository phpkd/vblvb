<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 4.0.130 # ||
|| # License Type: Commercial License                            $Revision: 103 $ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # --------------- 'vB Link Verifier Bot' IS NOT FREE SOFTWARE ---------------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE & ~8192);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile$ - $Revision: 32878 $');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('logging', 'cron');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
include_once(DIR . '/includes/phpkd/vblvb/functions.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadmincron'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['phpkd_vblvb_scheduled_task_log']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'log';
}

// ###################### Start view #######################
if ($_REQUEST['do'] == 'log')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_INT,
		//'varname' => TYPE_STR,
		'orderby' => TYPE_STR,
		'page'    => TYPE_INT
	));

	if (empty($vbulletin->GPC['varname']))
	{
		$vbulletin->GPC['varname'] = 'phpkd_vblvb';
	}

	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 15;
	}

	$sqlconds = '';
	if (!empty($vbulletin->GPC['varname']))
	{
		$sqlconds = "WHERE cronlog.varname = '" . $db->escape_string($vbulletin->GPC['varname']) . "'";
	}

	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "cronlog AS cronlog
		$sqlconds
	");
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

	if (empty($vbulletin->GPC['page']))
	{
		$vbulletin->GPC['page'] = 1;
	}

	$startat = ($vbulletin->GPC['page'] - 1) * $vbulletin->GPC['perpage'];

	switch ($vbulletin->GPC['orderby'])
	{
		case 'action':
			$order = 'cronlog.varname ASC, cronlog.dateline DESC';
			break;

		case 'date':
		default:
			$order = 'cronlog.dateline DESC';
	}

	$logs = $db->query_read("
		SELECT cronlog.*
		FROM " . TABLE_PREFIX . "cronlog AS cronlog
		LEFT JOIN " . TABLE_PREFIX . "cron AS cron ON (cronlog.varname = cron.varname)
		$sqlconds
		ORDER BY $order
		LIMIT $startat, " . $vbulletin->GPC['perpage']
	);

	if ($db->num_rows($logs))
	{
		if ($vbulletin->GPC['page'] != 1)
		{
			$prv = $vbulletin->GPC['page'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"&laquo; " . $vbphrase['first_page'] . "\" onclick=\"window.location='cronlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view" .
				"&varname=" . urlencode($vbulletin->GPC['varname']) .
				"&pp=" . $vbulletin->GPC['perpage'] .
				"&orderby=" . urlencode($vbulletin->GPC['orderby']) . "&page=1'\">";
			$prevpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"&lt; " . $vbphrase['prev_page'] . "\" onclick=\"window.location='cronlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view" .
				"&varname=" . urlencode($vbulletin->GPC['varname']) .
				"&pp=" . $vbulletin->GPC['perpage'] .
				"&orderby=" . urlencode($vbulletin->GPC['orderby']) . "&page=$prv'\">";
		}

		if ($vbulletin->GPC['page'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['page'] + 1;
			$page_button = "cronlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&varname=" . urlencode($vbulletin->GPC['varname']) . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . urlencode($vbulletin->GPC['orderby']);
			$nextpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"" . $vbphrase['next_page'] . " &gt;\" onclick=\"window.location='$page_button&page=$nxt'\">";
			$lastpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"" . $vbphrase['last_page'] . " &raquo;\" onclick=\"window.location='$page_button&page=$totalpages'\">";
		}

		// TODO: Replace 'print_form_header' with 'print_table_start'
		print_form_header('cronlog', 'remove');
		print_description_row(construct_link_code($vbphrase['restart'], "cronlog.php?" . $vbulletin->session->vars['sessionurl'] . ""), 0, 4, 'thead', phpkd_vblvb_stylevar_compatibility('right'));
		print_table_header(construct_phrase($vbphrase['scheduled_task_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['page']), vb_number_format($totalpages), vb_number_format($counter['total'])), 4);

		$headings = array();
		$headings[] = $vbphrase['id'];
		$headings[] = "<a href=\"cronlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view" .
			"&varname=" . urlencode($vbulletin->GPC['varname']) .
			"&pp=" . $vbulletin->GPC['perpage'] .
			"&orderby=action" .
			"&page=" . $vbulletin->GPC['page'] . "\" title=\"" . $vbphrase['order_by_action'] . "\">" . $vbphrase['action'] . "</a>";
		$headings[] = "<a href=\"cronlog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view" .
			"&varname=" . urlencode($vbulletin->GPC['varname']) .
			"&pp=" . $vbulletin->GPC['perpage'] .
			"&orderby=date" .
			"&page=" . $vbulletin->GPC['page'] . "\" title=\"" . $vbphrase['order_by_date'] . "\">" . $vbphrase['date'] . "</a>";
		$headings[] = $vbphrase['info'];
		print_cells_row($headings, 1);

		while ($log = $db->fetch_array($logs))
		{
			$cell = array();
			$cell[] = $log['cronlogid'];
			$cell[] = (isset($vbphrase['task_' . $log['varname'] . '_title']) ? $vbphrase['task_' . $log['varname'] . '_title'] : $log['varname']);
			$cell[] = '<span class="smallfont">' . vbdate($vbulletin->options['logdateformat'], $log['dateline']) . '</span>';
			$cell[] = $log['description'];

			print_cells_row($cell, 0, 0, -4);
		}

		print_table_footer(4, "$firstpage $prevpage &nbsp; $nextpage $lastpage");
	}
	else
	{
		print_stop_message('no_matches_found');
	}
}

print_cp_footer();


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.130
|| # $Revision: 103 $
|| # Released: $Date: 2010-02-13 06:43:18 +0200 (Sat, 13 Feb 2010) $
|| ########################################################################### ||
\*============================================================================*/
?>
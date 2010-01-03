<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 3.8.100 # ||
|| # License Type: Commercial License                            $Revision: 124 $ # ||
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
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

require_once(DIR . '/includes/phpkd/vblvb/functions.php');


if ($vbulletin->options['phpkd_vblvb_active'] AND phpkd_vblvb_license())
{
	switch ($vbulletin->options['phpkd_vblvb_inex_users'])
	{
		case 1:
			$inex_users = 'AND post.userid IN (' . trim($vbulletin->options['phpkd_vblvb_inex_users_ids']) . ')';
			break;
		case 2:
			$inex_users = 'AND post.userid NOT IN (' . trim($vbulletin->options['phpkd_vblvb_inex_users_ids']) . ')';
			break;
		default:
			$inex_users = '';
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_inex_usergroups'])
	{
		case 1:
			$inex_usergroups = 'AND user.usergroupid IN (' . implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_usergroups_ids'])) . ')';
			break;
		case 2:
			$inex_usergroups = 'AND user.usergroupid NOT IN (' . implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_usergroups_ids'])) . ')';
			break;
		default:
			$inex_usergroups = '';
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_inex_forums'])
	{
		case 1:
			$inex_forums = 'AND thread.forumid IN (' . implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_forums_ids'])) . ')';
			break;
		case 2:
			$inex_forums = 'AND thread.forumid NOT IN (' . implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_forums_ids'])) . ')';
			break;
		default:
			$inex_forums = '';
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_cutoff_mode'])
	{
		case 0:
			$cutoff = (($vbulletin->options['phpkd_vblvb_cutoff_value'] > 0) ? 'AND post.dateline > UNIX_TIMESTAMP(\'' . $vbulletin->db->escape_string($vbulletin->options['phpkd_vblvb_cutoff_value']) . '\')' : '');
			break;
		case 1:
			$cutoff = (($vbulletin->options['phpkd_vblvb_cutoff_value'] > 0) ? 'AND post.dateline > ' . TIMENOW - ($vbulletin->options['phpkd_vblvb_cutoff_value'] * 86400) : '');
			break;
	}

	$checked_posts = (($vbulletin->options['phpkd_vblvb_checked_posts'] == 2) ? 'AND post.postid = thread.firstpostid' : '');

	$sucperiod = (($vbulletin->options['phpkd_vblvb_succession_period'] > 0) ? 'AND post.phpkd_vblvb_lastcheck < ' . (TIMENOW - (86400 * $vbulletin->options['phpkd_vblvb_succession_period'])) : '');

	$limit = (($vbulletin->options['phpkd_vblvb_limit'] > 0) ? 'LIMIT ' . intval($vbulletin->options['phpkd_vblvb_limit']) : '');


	$posts = $vbulletin->db->query_read("
		SELECT user.username, user.usergroupid, user.email, user.languageid, post.userid, post.postid, post.threadid, post.dateline, post.title, post.pagetext, thread.forumid, thread.title AS threadtitle
		FROM " . TABLE_PREFIX . "post AS post
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
		LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
		Where 1 = 1
			$inex_users
			$inex_usergroups
			$inex_forums
			$cutoff
			$checked_posts
			$sucperiod
			AND thread.open = 1
			AND thread.visible = 1
			AND post.visible = 1
		$limit
	");


	$log = $vbphrase['phpkd_vblvb_log_scan_report'] . '<ol class="smallfont">';
	if (defined('IN_CONTROL_PANEL'))
	{
		echo '<ol class="smallfont">';
		vbflush();
	}

	$logpunished = '';
	$punished = array();
	while ($post = $vbulletin->db->fetch_array($posts))
	{
		$log .= '<li><a href="' . $vbulletin->options['bburl'] . '/showthread.php?p=' . intval($post['postid']) . '" target="_blank">' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '</a><ol>';
		if (defined('IN_CONTROL_PANEL'))
		{
			echo '<li><a href="' . $vbulletin->options['bburl'] . '/showthread.php?p=' . intval($post['postid']) . '" target="_blank">' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '</a><ol>';
			vbflush();
		}

		$links = phpkd_vblvb_fetch_urls($post['pagetext']);

		$links['ignored'] = $links['all'] - ($links['alive'] + $links['down'] + $links['dead']);
		$log .= $links['log'] . "</ol>" . construct_phrase($vbphrase['phpkd_vblvb_log_scan_summery'], $links['all'], $links['checked'], $links['alive'], $links['down'], $links['dead'], $links['ignored']) . '</li>';
		if (defined('IN_CONTROL_PANEL'))
		{
			echo '</ol>' . construct_phrase($vbphrase['phpkd_vblvb_log_scan_summery'], $links['all'], $links['checked'], $links['alive'], $links['down'], $links['dead'], $links['ignored']) . '</li>';
			vbflush();
		}


		// Critical Limit/Red Line
		if ($links['dead'] > 0 AND $links['checked'] > 0)
		{
			$critical = ($links['dead'] / $links['checked']) * 100;
			if ($critical > $vbulletin->options['phpkd_vblvb_critical'])
			{
				$logpunished .= '<li><a href="' . $vbulletin->options['bburl'] . '/showpost.php?p=' . intval($post['postid']) . '" target="_blank">' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '</a></li>';
				$punished[$post['userid']][$post['postid']] = $post;
			}
		}

		// Finished, now update 'post.phpkd_vblvb_lastcheck'
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "post
			SET phpkd_vblvb_lastcheck = " . TIMENOW . "
			WHERE postid = $post[postid]
		");
	}
	$vbulletin->db->free_result($posts);


	$log .= '</ol><br />';
	if (defined('IN_CONTROL_PANEL'))
	{
		echo '</ol><br />';
		vbflush();
	}


	if (is_array($punished) AND count($punished) > 0)
	{
		// Punish Dead Posts
		phpkd_vblvb_punish($punished);
		$log .= $vbphrase['phpkd_vblvb_log_punished_posts'] . '<ol class="smallfont">' . $logpunished . '</ol>';

		// Send User Reports
		phpkd_vblvb_rprtu($punished);

		// Send Staff Reports
		phpkd_vblvb_rprts($log);
	}


	log_cron_action($log, $nextitem, 1);
}
else if (!phpkd_vblvb_license() AND defined('IN_CONTROL_PANEL'))
{
	echo '<span class="diff-deleted">Sorry, this isn\'t a valid license. Please contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for a valid license!!</span>';
	vbflush();
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 3.8.100
|| # $Revision: 124 $
|| # Released: $Date: 2008-07-22 07:23:25 +0300 (Tue, 22 Jul 2008) $
|| ########################################################################### ||
\*============================================================================*/
?>
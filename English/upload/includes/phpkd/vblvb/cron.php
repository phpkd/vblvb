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


// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE & ~8192);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################


if ($vbulletin->options['phpkd_vblvb_active'])
{
	if (!$vbulletin->options['phpkd_vblvb_checked_posts'])
	{
		print_stop_message('phpkd_vblvb_cronjob_off');
	}

	if (!defined('IN_CONTROL_PANEL'))
	{
		global $vbphrase;
	}

	$error_type = (defined('IN_CONTROL_PANEL') ? ERRTYPE_ECHO : ERRTYPE_SILENT);
	require_once(DIR . '/includes/phpkd/vblvb/class_core.php');
	$phpkd_vblvb = new PHPKD_VBLVB($vbulletin, array('vbphrase' => $vbphrase), $error_type);

	if (!$phpkd_vblvb->verify_license())
	{
		print_cp_message('<span class="diff-deleted">Sorry, this isn\'t a valid license. Please contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for a valid license!!</span>');
	}


	$phpkd_vblvb->initialize(array('threadmodes' => TRUE, 'postmodes' => TRUE));
	if (is_array($phpkd_vblvb->threadmodes) AND !empty($phpkd_vblvb->threadmodes))
	{
		$rawthreadmodes = array();
		foreach ($phpkd_vblvb->threadmodes AS $threadmode)
		{
			switch ($threadmode)
			{
				case 'OPENED':
					$rawthreadmodes[] = 'thread.open != 1';
					break;
				case 'CLOSED':
					$rawthreadmodes[] = 'thread.open != 0';
					break;
				case 'MODERATED':
					$rawthreadmodes[] = 'thread.visible != 0';
					break;
				case 'STICKY':
					$rawthreadmodes[] = 'thread.sticky != 1';
					break;
				case 'DELETED':
					$rawthreadmodes[] = 'thread.visible != 2';
					break;
				case 'REDIRECTED':
					$rawthreadmodes[] = 'thread.open != 10';
					break;
			}
		}
	}


	if (is_array($phpkd_vblvb->postmodes) AND !empty($phpkd_vblvb->postmodes))
	{
		$rawpostmodes = array();
		foreach ($phpkd_vblvb->postmodes AS $postmode)
		{
			switch ($postmode)
			{
				case 'OPENED':
					$rawpostmodes[] = 'post.visible != 1';
					break;
				case 'MODERATED':
					$rawpostmodes[] = 'post.visible != 0';
					break;
				case 'DELETED':
					$rawpostmodes[] = 'post.visible != 2';
					break;
			}
		}
	}


	$inex_users = $inex_usergroups = $inex_forums = $cutoff = $threadmodes = $postmodes = '';
	if (is_array($rawthreadmodes) AND !empty($rawthreadmodes))
	{
		$threadmodes = 'AND ' . implode(' AND ', $rawthreadmodes);
	}


	if (is_array($rawpostmodes) AND !empty($rawpostmodes))
	{
		$postmodes = 'AND ' . implode(' AND ', $rawpostmodes);
	}


	switch ($vbulletin->options['phpkd_vblvb_inex_users'])
	{
		case 1:
			$inex_users = ((strlen(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) > 0) ? 'AND post.userid IN (' . $vbulletin->db->escape_string(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) . ')' : '');
			break;
		case 2:
			$inex_users = ((strlen(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) > 0) ? 'AND post.userid NOT IN (' . $vbulletin->db->escape_string(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) . ')' : '');
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_inex_usergroups'])
	{
		case 1:
			$usergroups = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_usergroups_ids']));
			$inex_usergroups = (($usergroups != '' AND strlen($usergroups) > 0) ? 'AND user.usergroupid IN (' . $vbulletin->db->escape_string($usergroups) . ')' : '');
			break;
		case 2:
			$usergroups = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_usergroups_ids']));
			$inex_usergroups = (($usergroups != '' AND strlen($usergroups) > 0) ? 'AND user.usergroupid NOT IN (' . $vbulletin->db->escape_string($usergroups) . ')' : '');
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_inex_forums'])
	{
		case 1:
			$forums = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_forums_ids']));
			$inex_forums = (($forums != '' AND strlen($forums) > 0) ? 'AND thread.forumid IN (' . $vbulletin->db->escape_string($forums) . ')' : '');
			break;
		case 2:
			$forums = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_forums_ids']));
			$inex_forums = (($forums != '' AND strlen($forums) > 0) ? 'AND thread.forumid NOT IN (' . $vbulletin->db->escape_string($forums) . ')' : '');
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_cutoff_mode'])
	{
		case 0:
			$cutoff = ((strlen(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) > 0) ? 'AND post.dateline > UNIX_TIMESTAMP(\'' . $vbulletin->db->escape_string(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) . '\')' : '');
			break;
		case 1:
			$cutoff = ((intval(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) > 0) ? 'AND post.dateline > ' . (TIMENOW - (intval(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) * 86400)) : '');
			break;
	}

	$checked_posts = (($vbulletin->options['phpkd_vblvb_checked_posts'] == 2) ? 'AND post.postid = thread.firstpostid' : '');

	$sucperiod = (($vbulletin->options['phpkd_vblvb_succession_period'] > 0) ? 'AND post.phpkd_vblvb_lastcheck < ' . (TIMENOW - ($vbulletin->options['phpkd_vblvb_succession_period'] * 86400)) : '');

	$limit = (($vbulletin->options['phpkd_vblvb_limit'] > 0) ? 'LIMIT ' . $vbulletin->options['phpkd_vblvb_limit'] : '');


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
			$threadmodes
			$postmodes
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

		$links = $phpkd_vblvb->dm(array('hosts' => TRUE, 'masks' => TRUE, 'protocols' => TRUE, 'bbcodes' => TRUE, 'punishments' => TRUE, 'staff_reports' => TRUE, 'user_reports' => TRUE))->fetch_urls($post['pagetext']);

		$links['ignored'] = $links['all'] - ($links['alive'] + $links['dead'] + $links['down']);
		$log .= $links['log'] . "</ol>" . construct_phrase($vbphrase['phpkd_vblvb_log_scan_summery'], $links['all'], $links['checked'], $links['alive'], $links['dead'], $links['down'], $links['ignored']) . '</li>';
		if (defined('IN_CONTROL_PANEL'))
		{
			echo '</ol>' . construct_phrase($vbphrase['phpkd_vblvb_log_scan_summery'], $links['all'], $links['checked'], $links['alive'], $links['dead'], $links['down'], $links['ignored']) . '</li>';
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
		$phpkd_vblvb->dm()->punish($punished);
		$log .= $vbphrase['phpkd_vblvb_log_punished_posts'] . '<ol class="smallfont">' . $logpunished . '</ol>';

		// Send User Reports
		$phpkd_vblvb->dm()->user_reports($punished);

		// Send Staff Reports
		$phpkd_vblvb->dm()->staff_reports($log);
	}


	unset($phpkd_vblvb);
	log_cron_action($log, $nextitem, 1);
}
else if (defined('IN_CONTROL_PANEL'))
{
	print_stop_message('phpkd_vblvb_inactive');
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.122
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
?>
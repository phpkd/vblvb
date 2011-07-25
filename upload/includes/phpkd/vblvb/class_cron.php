<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.1.211 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2011 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// Bypass PHP INI memory limit!
if (($current_memory_limit = ini_size_to_bytes(@ini_get('memory_limit'))) < 128 * 1024 * 1024 AND $current_memory_limit > 0)
{
	@ini_set('memory_limit', 128 * 1024 * 1024);
}

@set_time_limit(0);

if (!defined('IN_CONTROL_PANEL'))
{
	global $vbphrase;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
require_once(DIR . '/includes/phpkd/vblvb/class_core.php');
require_once(DIR . '/includes/phpkd/vblvb/class_copyright.php');

$phpkd_vblvb = new PHPKD_VBLVB($vbulletin, $vbphrase, defined('IN_CONTROL_PANEL') ? ERRTYPE_CP : ERRTYPE_SILENT);
$plugin = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "plugin WHERE product = 'phpkd_vblvb' AND hookname = 'global_complete'");
$bburl = @parse_url($vbulletin->options['bburl']);
$tocken = md5(md5($bburl['host']) . md5(PHPKD_VBLVB_TOCKEN) . md5($vbulletin->userinfo['securitytoken']) . md5(TIMENOW));

if ((!$plugin['active'] AND $copyright != $tocken) OR md5($plugin['phpcode']) != '9d75af8827a7d278565dd87b7c6d852e')
{
	$phpkd_vblvb->seterror('phpkd_vblvb_copyright_violate');
}
else if ($vbulletin->phpkd_vblvb['general_active'])
{
	if (!$vbulletin->phpkd_vblvb['general_checked_existingposts'])
	{
		$phpkd_vblvb->seterror('phpkd_vblvb_checked_existing');
	}

	$phpkd_vblvb->verify_license();

	// Required Initialization
	$phpkd_vblvb->initialize(array('thread_modes', 'post_modes'));


	if (is_array($phpkd_vblvb->thread_modes) AND !empty($phpkd_vblvb->thread_modes))
	{
		$thread_modes = '';

		foreach ($phpkd_vblvb->thread_modes AS $thread_mode)
		{
			switch ($thread_mode)
			{
				case 'opened':
					$thread_modes .= 'AND thread.open != 1 ';
					break;

				case 'closed':
					$thread_modes .= 'AND thread.open != 0 ';
					break;

				case 'moderated':
					$thread_modes .= 'AND thread.visible != 0 ';
					break;

				case 'sticky':
					$thread_modes .= 'AND thread.sticky != 1 ';
					break;

				case 'deleted':
					$thread_modes .= 'AND thread.visible != 2 ';
					break;

				case 'redirected':
					$thread_modes .= 'AND thread.open != 10 ';
					break;
			}
		}
	}

	if (is_array($phpkd_vblvb->post_modes) AND !empty($phpkd_vblvb->post_modes))
	{
		$post_modes = '';

		foreach ($phpkd_vblvb->post_modes AS $post_mode)
		{
			switch ($post_mode)
			{
				case 'opened':
					$post_modes .= 'AND post.visible != 1 ';
					break;

				case 'moderated':
					$post_modes .= 'AND post.visible != 0 ';
					break;

				case 'deleted':
					$post_modes .= 'AND post.visible != 2 ';
					break;
			}
		}
	}


	$inex_users = '';
	$inex_usergroups = '';
	$inex_forums = '';
	$cutoff = '';

	// Auto exclude report forums/threads & recycle bin forum from being checked: http://forum.phpkd.net/project.php?issueid=71
	$forced_inex_forums = ($vbulletin->phpkd_vblvb['reporting_forumid'] ? $vbulletin->phpkd_vblvb['reporting_forumid'] . ',' : '') . ($vbulletin->phpkd_vblvb['punishment_forumid'] ? $vbulletin->phpkd_vblvb['punishment_forumid'] . ',' : '') . '0';


	switch ($vbulletin->phpkd_vblvb['general_inex_users'])
	{
		case 1:
			$inex_users = ((strlen($vbulletin->phpkd_vblvb['general_inex_users_ids']) > 0) ? 'AND post.userid IN (' . $vbulletin->db->escape_string($vbulletin->phpkd_vblvb['general_inex_users_ids']) . ')' : '');
			break;

		case 2:
			$inex_users = ((strlen($vbulletin->phpkd_vblvb['general_inex_users_ids']) > 0) ? 'AND post.userid NOT IN (' . $vbulletin->db->escape_string($vbulletin->phpkd_vblvb['general_inex_users_ids']) . ')' : '');
			break;
	}

	switch ($vbulletin->phpkd_vblvb['general_inex_usergroups'])
	{
		case 1:
			$usergroups = @implode(',', unserialize($vbulletin->phpkd_vblvb['general_inex_usergroups_ids']));
			$inex_usergroups = (!empty($usergroups) ? 'AND user.usergroupid IN (' . $vbulletin->db->escape_string($usergroups) . ')' : '');
			break;

		case 2:
			$usergroups = @implode(',', unserialize($vbulletin->phpkd_vblvb['general_inex_usergroups_ids']));
			$inex_usergroups = (!empty($usergroups) ? 'AND user.usergroupid NOT IN (' . $vbulletin->db->escape_string($usergroups) . ')' : '');
			break;
	}

	switch ($vbulletin->phpkd_vblvb['general_inex_forums'])
	{
		case 1:
			$forums = @implode(',', unserialize($vbulletin->phpkd_vblvb['general_inex_forums_ids']));
			$inex_forums = (!empty($forums) ? 'AND thread.forumid IN (' . $vbulletin->db->escape_string($forums) . ')' : '');
			break;

		case 2:
			$forums = @implode(',', unserialize($vbulletin->phpkd_vblvb['general_inex_forums_ids']));
			$inex_forums = (!empty($forums) ? 'AND thread.forumid NOT IN (' . $vbulletin->db->escape_string($forums) . ')' : '');
			break;
	}

	switch ($vbulletin->phpkd_vblvb['general_cutoff_mode'])
	{
		case 0:
			$cutoff = (!empty($vbulletin->phpkd_vblvb['general_cutoff_value']) ? 'AND post.dateline > ' . (TIMENOW - (intval($vbulletin->phpkd_vblvb['general_cutoff_value']) * 86400)) : '');
			break;

		case 1:
			$cutoff = (!empty($vbulletin->phpkd_vblvb['general_cutoff_value']) ? 'AND post.dateline < UNIX_TIMESTAMP(\'' . $vbulletin->db->escape_string($vbulletin->phpkd_vblvb['general_cutoff_value']) . '\')' : '');
			break;

		case 2:
			$cutoff = (!empty($vbulletin->phpkd_vblvb['general_cutoff_value']) ? 'AND post.dateline > UNIX_TIMESTAMP(\'' . $vbulletin->db->escape_string($vbulletin->phpkd_vblvb['general_cutoff_value']) . '\')' : '');
			break;

		case 3:
			$cutoff_value = @explode('|', $vbulletin->phpkd_vblvb['general_cutoff_value']);
			$cutoff = ((!empty($vbulletin->phpkd_vblvb['general_cutoff_value']) AND count($cutoff_value) == 2) ? 'AND post.dateline > UNIX_TIMESTAMP(\'' . $vbulletin->db->escape_string($cutoff_value[0]) . '\') AND post.dateline < UNIX_TIMESTAMP(\'' . $vbulletin->db->escape_string($cutoff_value[1]) . '\')' : '');
			break;
	}

	$checked_posts = (($vbulletin->phpkd_vblvb['general_checked_existingposts'] == 2) ? 'AND post.postid = thread.firstpostid' : '');

	$succession = 'AND post.phpkd_vblvb_lastcheck ' . (($vbulletin->phpkd_vblvb['general_succession_period'] > 0) ? '< ' . (TIMENOW - ($vbulletin->phpkd_vblvb['general_succession_period'] * 86400)) : '= 0');

	$limit = 'LIMIT ' . (($vbulletin->phpkd_vblvb['general_query_limit'] > 0) ? $vbulletin->phpkd_vblvb['general_query_limit'] : 50);


	// Main query
	$post_query = $vbulletin->db->query_read("
		SELECT user.username, user.email, user.languageid, post.userid, post.postid, post.threadid, post.title AS posttitle, post.pagetext, post.visible AS pvisible, thread.forumid, forum.title AS forumtitle, forum.replycount, forum.replycount AS countposts, thread.title AS threadtitle, thread.open, thread.sticky, thread.firstpostid, thread.visible, thread.pollid
		FROM " . TABLE_PREFIX . "post AS post
		LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
		LEFT JOIN " . TABLE_PREFIX . "forum AS forum ON (thread.forumid = forum.forumid)
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
		WHERE 1 = 1
			" . (!empty($vbulletin->phpkd_vblvb['reporting_threadid']) ? "AND post.threadid NOT IN (" . $vbulletin->phpkd_vblvb['reporting_threadid'] . ")" : "") . "
			" . (!empty($forced_inex_forums) ? "AND thread.forumid NOT IN (" . $forced_inex_forums . ")" : "") . "
			$inex_users
			$inex_usergroups
			$inex_forums
			$cutoff
			$checked_posts
			$succession
			$thread_modes
			$post_modes
		$limit
	");


	if ($vbulletin->db->num_rows($post_query))
	{
		$punished_links = '';
		$records = array('checked' => 0, 'dead' => 0, 'punished' => 0);
		$colors = unserialize($vbulletin->phpkd_vblvb['lookfeel_linkstatus_colors']);
		$posts = array();
		$punished_content = array();
		$urlrecords = array();
		$checkedposts = array();
		$deadposts = array();
		$phpkd_vblvb->logstring($vbphrase['phpkd_vblvb_log_checked_posts'] . '<ol class="smallfont">', ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1));

		while ($postitem = $vbulletin->db->fetch_array($post_query))
		{
			$checkedposts[] = $postitem['postid'];
			$posts[$postitem['forumid']]['forumtitle'] = $postitem['forumtitle'];
			$posts[$postitem['forumid']][$postitem['threadid']]['threadtitle'] = $postitem['threadtitle'];
			$posts[$postitem['forumid']][$postitem['threadid']][$postitem['postid']] = $postitem;
		}

		foreach ($posts AS $forumid => $forumposts)
		{
			$phpkd_vblvb->logstring('<li>' . construct_phrase($vbphrase['phpkd_vblvb_log_forum'], $vbulletin->options['bburl'] . '/forumdisplay.php?f=' . $forumid, $forumposts['forumtitle']) . '<ol class="smallfont">', ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1));
			unset($forumposts['forumtitle']);

			foreach ($forumposts AS $threadid => $threadposts)
			{
				$phpkd_vblvb->logstring('<li>' . construct_phrase($vbphrase['phpkd_vblvb_log_thread'], $vbulletin->options['bburl'] . '/showthread.php?t=' . $threadid, $threadposts['threadtitle']) . '<ol class="smallfont">', ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1));
				unset($threadposts['threadtitle']);

				foreach ($threadposts AS $postid => $post)
				{
					$phpkd_vblvb->logstring('<li>' . construct_phrase($vbphrase['phpkd_vblvb_log_post'], $vbulletin->options['bburl'] . '/showthread.php?t=' . $threadid . '&amp;p=' . $postid . '&amp;viewfull=1#post' . $postid, ($post['posttitle'] ? $post['posttitle'] : $post['threadtitle'])), ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1), array('postid' => $postid, 'posttitle' => ($post['posttitle'] ? $post['posttitle'] : $post['threadtitle']), 'threadid' => $threadid, 'forumid' => $forumid, 'threadtitle' => $post['threadtitle'], 'forumtitle' => $post['forumtitle'], 'userid' => $post['userid'], 'username' => $post['username'], 'email' => $post['email'], 'languageid' => $post['languageid']));

					$links = $phpkd_vblvb->getDmhandle()->fetch_urls($post['pagetext'], $postid);

					$phpkd_vblvb->logstring(construct_phrase($vbphrase['phpkd_vblvb_log_summery_post'], $colors[0], $colors[1], $colors[2], $links['all'], $links['checked'], $links['alive'], $links['dead'], $links['down'], ($links['all'] - $links['checked'])) . '</li><br />', ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1), $postid);


					// Link directory stuff!
					if ($vbulletin->phpkd_vblvb['linkdir_recording_active'])
					{
						$urlrecords[$postid] = $links['urlrecords'];
					}

					// Dead posts
					if ($links['checked'] > 0 AND $links['dead'] > 0)
					{
						$records['dead']++;
						$critical = ($links['dead'] / $links['checked']) * 100;

						if (!$vbulletin->phpkd_vblvb['reporting_user_reports_mode'])
						{
							$deadposts[] = $postid;
						}

						// Critical Limit/Red Line
						if ($critical >= $vbulletin->phpkd_vblvb['general_critical_limit'])
						{
							$records['punished']++;
							$punished_links .= '<li><a href="' . $vbulletin->options['bburl'] . '/showthread.php?t=' . $threadid . '&amp;p=' . $postid . '&amp;viewfull=1#post' . $postid . '" target="_blank">' . ($post['posttitle'] ? $post['posttitle'] : $post['threadtitle']) . '</a></li>';
							$punished_content['posts'][$postid] = array('forumid' => $forumid, 'threadid' => $threadid, 'postid' => $postid, 'firstpostid' => $post['firstpostid'], 'visible' => $post['visible'], 'pvisible' => $post['pvisible'], 'replycount' => $post['replycount']);
							$punished_content['threads'][$threadid] = array('forumid' => $forumid, 'threadid' => $threadid, 'open' => $post['open'], 'visible' => $post['visible'], 'sticky' => $post['sticky'], 'firstpostid' => $post['firstpostid'], 'replycount' => $post['replycount'], 'title' => $post['threadtitle'], 'pollid' => $post['pollid']);
							$punished_content['forums'][] = $forumid;
						}
					}

					$records['checked']++;
				}

				$phpkd_vblvb->logstring('</ol></li><br />', ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1));
			}

			$phpkd_vblvb->logstring('</ol></li>', ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1));
		}

		$phpkd_vblvb->logstring(construct_phrase($vbphrase['phpkd_vblvb_log_summery_all'], $colors[0], $colors[1], $colors[2], $records['checked'], ($records['checked'] - $records['dead']), $records['dead'], $records['punished']) . '</ol><br />', ($vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1));


		if ($vbulletin->phpkd_vblvb['linkdir_recording_active'] AND !empty($urlrecords))
		{
			$urlrecords_query = '';

			foreach ($urlrecords as $postid => $urlrecord)
			{
				if (!empty($urlrecord))
				{
					foreach ($urlrecord as $reckey => $recvalue)
					{
						$urlrecords_query .= '(\'' . $recvalue['host'] . '\', \'' . $recvalue['url'] . '\', ' . $postid . ', ' . $recvalue['lastcheck'] . ', \'' . $recvalue['hash'] . '\', \'' . $recvalue['status'] . '\'), ';
					}
				}
			}

			if (strlen($urlrecords_query) > 0)
			{
				$vbulletin->db->query_write("
					REPLACE INTO " . TABLE_PREFIX . "phpkd_vblvb_link
					(host, url, postid, lastcheck, hash, status)
					VALUES " . substr($urlrecords_query, 0, -2)
				);
			}
		}


		// Punish Dead Posts (only those over critical limit)
		if ($records['punished'] > 0 AND ($vbulletin->phpkd_vblvb['reporting_included_posts'] == 0 OR $vbulletin->phpkd_vblvb['reporting_included_posts'] == 2))
		{
			$phpkd_vblvb->logstring($vbphrase['phpkd_vblvb_log_punished_posts'] . '<ol class="smallfont">' . $punished_links . '</ol><br />');
			$phpkd_vblvb->updatepostlogs(array_keys($punished_content['posts']), 'punished');
			$phpkd_vblvb->getDmhandle()->punish($punished_content);
		}

		// Send User Reports
		if (!empty($deadposts) OR ($vbulletin->phpkd_vblvb['reporting_user_reports_mode'] AND !empty($punished_content['posts'])))
		{
			$phpkd_vblvb->getDmhandle()->user_reports(!empty($deadposts) ? $deadposts : array_keys($punished_content['posts']));
			$phpkd_vblvb->updatepostlogs($deadposts, 'dead');
		}

		// Send Staff Reports
		if ($vbulletin->phpkd_vblvb['reporting_staff_reports_mode'] == 0 OR ($vbulletin->phpkd_vblvb['reporting_staff_reports_mode'] == 1 AND $records['checked'] > 0) OR ($vbulletin->phpkd_vblvb['reporting_staff_reports_mode'] == 2 AND $records['dead'] > 0) OR ($vbulletin->phpkd_vblvb['reporting_staff_reports_mode'] == 3 AND $records['punished'] > 0))
		{
			$phpkd_vblvb->getDmhandle()->staff_reports($punished_links, $records);
		}


		// Every thing has been finished!
		$phpkd_vblvb->commit('', $checkedposts);
	}
	else
	{
		$phpkd_vblvb->seterror('phpkd_vblvb_checked_nothing');
	}
	$vbulletin->db->free_result($post_query);
}
else
{
	$phpkd_vblvb->seterror('phpkd_vblvb_inactive');
}

unset($phpkd_vblvb);

log_cron_action('', $nextitem, 1);


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.1.211
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
?>